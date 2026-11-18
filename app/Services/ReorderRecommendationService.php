<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReorderRecommendationService
{
    public function refreshIfNeeded(int $userId): int
    {
        return DB::transaction(function () use ($userId) {
            $count = 0;
            $products = DB::table('products')->where('status', 1)->orderBy('id')->lockForUpdate()->get();
            foreach ($products as $product) {
                $setting = DB::table('product_reorder_settings')->where('product_id', $product->id)->first();
                $window = (int) ($setting->ma_days ?? 30);
                // Use complete days; today's partial sales must not dilute the forecast.
                $end = now()->subDay()->toDateString();
                $start = now()->subDays($window)->toDateString();
                $stock = (int) DB::table('inventory_transaction_items as i')
                    ->join('inventory_transactions as t', 'i.inventory_transaction_id', '=', 't.id')
                    ->where('i.product_id', $product->id)
                    ->selectRaw("COALESCE(SUM(CASE WHEN t.type = 'import' THEN i.quantity ELSE -i.quantity END), 0) as stock")->value('stock');
                $onOrder = (int) DB::table('reorder_recommendations')->where('product_id', $product->id)
                    ->where('status', 'approved')->selectRaw('COALESCE(SUM(approved_quantity - received_quantity), 0) as quantity')->value('quantity');
                $status = ! $setting || ! $setting->observation_start ? 'MISSING_CONFIG'
                    : ($setting->observation_start > $start ? 'INSUFFICIENT_DATA' : ($stock < 0 ? 'INVALID_DATA' : 'READY'));
                $daily = app(DemandForecastService::class)->series($product->id, $start, $end);
                $statistics = DB::table('v_daily_demand')->where('product_id', $product->id)->whereBetween('demand_date', [$start, $end])
                    ->selectRaw('COALESCE(SUM(consumed_qty), 0) * 1.0 / ? AS average, COALESCE(SUM(consumed_qty * consumed_qty), 0) * 1.0 / ? AS mean_square', [$window, $window])->first();
                $average = (float) $statistics->average;
                $safety = 0;
                $method = 'ma';
                $forecastRunId = null;
                $demand = $average;
                if ($status === 'READY') {
                    $variance = max(0, (float) $statistics->mean_square - $average ** 2);
                    $safety = $setting->safety_mode === 'manual' ? $setting->manual_safety_stock
                        : ceil($setting->safety_factor * sqrt($variance) * sqrt($setting->lead_time_days));
                    if ($setting->forecast_method === 'auto') {
                        $run = DB::table('forecast_runs')->where('product_id', $product->id)
                            ->where('period_end', $end)->where('ma_days', $window)
                            ->where('period_start', '>=', $setting->observation_start)->latest('id')->first();
                        if ($run) {
                            $result = json_decode($run->results, true);
                            $current = app(DemandForecastService::class)->series($product->id, $run->period_start, $end);
                            if (($result['sufficient'] ?? false) && hash('sha256', json_encode($current)) === $result['series_sha256']) {
                                $method = $result['selected'];
                                $demand = $result['metrics'][$method]['forecast'];
                                $forecastRunId = $run->id;
                            }
                        }
                    }
                }
                $calc = app(ReorderCalculator::class)->calculateSql($demand, (int) ($setting->lead_time_days ?? 0),
                    (int) ($setting->target_days ?? 0), $safety, $stock, $onOrder, (int) ($setting->pack_size ?? 1));
                if ($status === 'READY') {
                    $status = $calc['stock_status'];
                }
                $quantity = $status === 'REORDER' ? $calc['quantity'] : 0;
                $snapshot = ['ma_days' => $window, 'lead_time_days' => $setting->lead_time_days ?? null,
                    'target_days' => $setting->target_days ?? null, 'safety_mode' => $setting->safety_mode ?? null,
                    'safety_factor' => $setting->safety_factor ?? null, 'pack_size' => $setting->pack_size ?? null,
                    'observation_start' => $setting->observation_start ?? null,
                    'method' => $method, 'model_version' => 'linear-trend-v1', 'forecast_run_id' => $forecastRunId,
                    'moving_average' => $average, 'estimated_cost' => $quantity * (float) $product->purchase_price,
                    'formula' => 'IP=on_hand+on_order; ROP=d*L+SS; Q=ceil(max(0,d*(L+T)+SS-IP)/pack)*pack if IP<=ROP',
                    'daily' => $daily];
                $labels = ['MISSING_CONFIG' => 'Chưa cấu hình đầy đủ thông số và ngày bắt đầu dữ liệu.',
                    'INSUFFICIENT_DATA' => 'Chưa đủ lịch sử cho cửa sổ MA đã chọn.',
                    'INVALID_DATA' => 'Tồn kho âm: cần đối soát giao dịch.',
                    'REORDER' => 'Cần xem xét nhập hàng.', 'NORMAL' => 'Bình thường, IP lớn hơn ROP.'];
                $explanation = sprintf('%s Kỳ %s đến %s (%d ngày đầy đủ). Nhu cầu %.4f %s/ngày (%s). IP = %d + %d - 0 = %d. L=%d, T=%d, SS=%d, ROP=%.4f. Q=%d; chi phí tham khảo %.0f đ.',
                    $labels[$status], $start, $end, $window, $demand, $product->unit, $method,
                    $stock, $onOrder, $calc['ip'], $setting->lead_time_days ?? 0, $setting->target_days ?? 0,
                    $safety, $calc['rop'], $quantity, $snapshot['estimated_cost']);
                $previous = DB::table('reorder_recommendations')->where('product_id', $product->id)->latest('id')->first();
                $json = json_encode($snapshot, JSON_UNESCAPED_UNICODE);
                if ($previous && $previous->explanation === $explanation && json_decode($previous->calculation_snapshot ?? '{}', true) == $snapshot) {
                    continue;
                }
                if ($previous?->status === 'pending') {
                    DB::table('reorder_recommendations')->where('id', $previous->id)->update(['status' => 'superseded', 'updated_at' => now()]);
                }
                DB::table('reorder_recommendations')->insert([
                    'product_id' => $product->id, 'proposed_by' => $userId, 'period_start' => $start, 'period_end' => $end,
                    'average_daily_demand' => $demand, 'safety_stock' => $safety, 'reorder_point' => $calc['rop'],
                    'on_hand' => max(0, $stock), 'on_order' => $onOrder, 'committed' => 0, 'inventory_position' => $calc['ip'],
                    'suggested_quantity' => $quantity, 'stock_status' => $status,
                    'status' => $quantity > 0 ? 'pending' : (in_array($status, ['NORMAL', 'REORDER']) ? 'no_action' : strtolower($status)),
                    'calculation_snapshot' => $json, 'explanation' => $explanation, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $count++;
            }

            return $count;
        });
    }
}
