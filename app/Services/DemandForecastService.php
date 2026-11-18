<?php

namespace App\Services;

use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class DemandForecastService
{
    public function series(int $productId, string $from, string $to): array
    {
        $rows = DB::table('inventory_transaction_items as i')
            ->join('inventory_transactions as t', 'i.inventory_transaction_id', '=', 't.id')
            ->where('i.product_id', $productId)->where('t.type', 'export')
            ->where('t.issue_purpose', 'sale')->whereBetween('t.transaction_date', [$from, $to])
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
}
