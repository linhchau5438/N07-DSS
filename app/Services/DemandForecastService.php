<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class DemandForecastService
{
    public function series(int $productId, string $from, string $to): array
    {
        $rows = DB::table('inventory_transaction_items as i')
            ->join('inventory_transactions as t', 'i.inventory_transaction_id', '=', 't.id')
            ->where('i.product_id', $productId)->where('t.type', 'export')
            ->whereIn('t.issue_purpose', ['sale', 'consumption'])->whereBetween('t.transaction_date', [$from, $to])
            ->groupBy('t.transaction_date')->selectRaw('t.transaction_date, SUM(i.quantity) as quantity')
            ->pluck('quantity', 'transaction_date');
        $daily = [];
        foreach (CarbonPeriod::create($from, $to) as $date) {
            $daily[] = (int) ($rows[$date->toDateString()] ?? 0);
        }

        return $daily;
    }

    // Rolling one-day evaluation: every training window precedes its target day.
    public function evaluate(array $daily, int $window): array
    {
        $enough = count($daily) >= $window + 7;
        $metrics = [];
        foreach (['ma', 'linear'] as $method) {
            $errors = [];
            if ($enough) {
                for ($i = count($daily) - 7; $i < count($daily); $i++) {
                    $errors[] = $this->predict(array_slice($daily, $i - $window, $window), $method) - $daily[$i];
                }
            }
            $metrics[$method] = [
                'mae' => $enough ? array_sum(array_map('abs', $errors)) / 7 : null,
                'rmse' => $enough ? sqrt(array_sum(array_map(fn ($e) => $e * $e, $errors)) / 7) : null,
                'forecast' => $this->predict(array_slice($daily, -$window), $method),
            ];
        }
        $selected = $enough && $metrics['linear']['mae'] < $metrics['ma']['mae']
            && $metrics['linear']['rmse'] < $metrics['ma']['rmse'] ? 'linear' : 'ma';

        return ['sufficient' => $enough, 'selected' => $selected, 'metrics' => $metrics,
            'test_days' => $enough ? 7 : 0, 'observations' => count($daily),
            'series_sha256' => hash('sha256', json_encode($daily)), 'daily' => $daily];
    }

    public function predict(array $values, string $method): float
    {
        $n = count($values);
        if ($n === 0) {
            return 0;
        }
        $mean = array_sum($values) / $n;
        if ($method === 'ma' || $n < 2) {
            return $mean;
        }
        $meanX = ($n - 1) / 2;
        $numerator = $denominator = 0;
        foreach ($values as $x => $y) {
            $numerator += ($x - $meanX) * ($y - $mean);
            $denominator += ($x - $meanX) ** 2;
        }

        return max(0, $mean + $numerator / $denominator * ($n - $meanX));
    }

    public function detectStockout(int $productId, string $from, string $to): array
    {
        $initialStock = (int) DB::table('inventory_transaction_items as i')
            ->join('inventory_transactions as t', 'i.inventory_transaction_id', '=', 't.id')
            ->where('i.product_id', $productId)
            ->where('t.transaction_date', '<', $from)
            ->selectRaw("COALESCE(SUM(CASE WHEN t.type = 'import' THEN i.quantity ELSE -i.quantity END), 0) as balance")
            ->value('balance');

        $dailyMovements = DB::table('inventory_transaction_items as i')
            ->join('inventory_transactions as t', 'i.inventory_transaction_id', '=', 't.id')
            ->where('i.product_id', $productId)
            ->whereBetween('t.transaction_date', [$from, $to])
            ->groupBy('t.transaction_date')
            ->selectRaw("t.transaction_date, SUM(CASE WHEN t.type = 'import' THEN i.quantity ELSE -i.quantity END) as net_qty, SUM(CASE WHEN t.type = 'export' AND t.issue_purpose IN ('sale', 'consumption') THEN i.quantity ELSE 0 END) as consumed_qty")
            ->get()
            ->keyBy('transaction_date');

        $runningStock = $initialStock;
        $stockoutDays = 0;
        $totalDays = 0;
        $activeDays = 0;
        $totalConsumption = 0;

        foreach (CarbonPeriod::create($from, $to) as $date) {
            $dStr = $date->toDateString();
            $mov = $dailyMovements->get($dStr);
            $net = $mov ? (int) $mov->net_qty : 0;
            $consumed = $mov ? (int) $mov->consumed_qty : 0;
            $runningStock += $net;
            $totalDays++;
            $totalConsumption += $consumed;
            if ($consumed > 0 || $runningStock > 0) {
                $activeDays++;
            } else {
                $stockoutDays++;
            }
        }

        $stockoutRatio = $totalDays > 0 ? round($stockoutDays / $totalDays, 4) : 0;
        $observedDemand = $totalDays > 0 ? round($totalConsumption / $totalDays, 4) : 0;
        $adjustedDemand = $activeDays > 0 ? round($totalConsumption / $activeDays, 4) : $observedDemand;
        $isCensored = ($stockoutDays >= 1 && $stockoutRatio >= 0.10 && $adjustedDemand > $observedDemand);

        return [
            'total_days' => $totalDays,
            'stockout_days' => $stockoutDays,
            'active_days' => $activeDays,
            'stockout_ratio' => $stockoutRatio,
            'observed_demand' => $observedDemand,
            'adjusted_demand' => $adjustedDemand,
            'is_censored' => $isCensored,
        ];
    }

    public function actualLeadTimeStats(int $productId): array
    {
        $records = DB::table('inventory_transaction_items as i')
            ->join('inventory_transactions as t', 'i.inventory_transaction_id', '=', 't.id')
            ->join('reorder_recommendations as r', 'i.reorder_recommendation_id', '=', 'r.id')
            ->where('i.product_id', $productId)
            ->where('t.type', 'import')
            ->whereNotNull('r.ordered_at')
            ->select('r.id as rec_id', 'r.ordered_at', 'r.status as rec_status', 't.transaction_date')
            ->get();

        if ($records->isEmpty()) {
            return [
                'has_history' => false,
                'sample_count' => 0,
                'avg_lead_time_days' => null,
                'exact_avg_lead_time' => null,
                'avg_full_lead_time_days' => null,
                'min_lead_time_days' => null,
                'max_lead_time_days' => null,
                'latest_lead_time_days' => null,
            ];
        }

        $firstLeadTimes = [];
        $fullLeadTimes = [];
        $byRec = $records->groupBy('rec_id');
        foreach ($byRec as $recId => $items) {
            $orderedDate = Carbon::parse($items->first()->ordered_at)->startOfDay();
            $firstReceiptDate = Carbon::parse($items->min('transaction_date'))->startOfDay();
            $diffFirst = (int) max(0, $orderedDate->diffInDays($firstReceiptDate, false));
            $firstLeadTimes[] = $diffFirst;

            if ($items->first()->rec_status === 'fulfilled') {
                $lastReceiptDate = Carbon::parse($items->max('transaction_date'))->startOfDay();
                $diffFull = (int) max(0, $orderedDate->diffInDays($lastReceiptDate, false));
                $fullLeadTimes[] = $diffFull;
            }
        }

        $count = count($firstLeadTimes);
        $avgFirst = round(array_sum($firstLeadTimes) / $count, 1);
        $avgFull = ! empty($fullLeadTimes) ? round(array_sum($fullLeadTimes) / count($fullLeadTimes), 1) : $avgFirst;

        return [
            'has_history' => true,
            'sample_count' => $count,
            'avg_lead_time_days' => (int) round($avgFirst),
            'exact_avg_lead_time' => $avgFirst,
            'avg_full_lead_time_days' => (int) round($avgFull),
            'min_lead_time_days' => (int) min($firstLeadTimes),
            'max_lead_time_days' => (int) max($firstLeadTimes),
            'latest_lead_time_days' => (int) end($firstLeadTimes),
        ];
    }
}
