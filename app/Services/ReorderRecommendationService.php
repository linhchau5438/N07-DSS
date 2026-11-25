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
                    ->whereIn('status', ['ordered', 'partially_received'])
                    ->selectRaw('COALESCE(SUM(ordered_quantity - received_quantity), 0) as quantity')->value('quantity');
                $status = ! $setting || ! $setting->observation_start ? 'MISSING_CONFIG'
                    : ($setting->observation_start > $start ? 'INSUFFICIENT_DATA' : ($stock < 0 ? 'INVALID_DATA' : 'READY'));
                $daily = app(DemandForecastService::class)->series($product->id, $start, $end);
                $statistics = DB::table('v_daily_demand')->where('product_id', $product->id)->whereBetween('demand_date', [$start, $end])
                    ->selectRaw('COALESCE(SUM(consumed_qty), 0) * 1.0 / ? AS average, COALESCE(SUM(consumed_qty * consumed_qty), 0) * 1.0 / ? AS mean_square', [$window, $window])->first();
                $average = (float) $statistics->average;
                $safety = 0;
                $safetyFallbackApplied = false;
                $standardDeviation = 0.0;
                $method = 'ma';
                $forecastRunId = null;
                $demand = $average;
                if ($status === 'READY') {
                    $variance = max(0, (float) $statistics->mean_square - $average ** 2);
                    $standardDeviation = sqrt($variance);
                    if ($setting->safety_mode === 'manual') {
                        $safety = (int) $setting->manual_safety_stock;
                    } else {
                        if ((float) ($setting->safety_factor ?? 0) <= 0) {
                            $safety = 0;
                        } else {
                            $statSafety = (int) ceil($setting->safety_factor * $standardDeviation * sqrt((float) ($setting->lead_time_days ?? 0)));
                            if ($statSafety <= 0 && $demand > 0) {
                                $safety = (int) ($setting->manual_safety_stock > 0
                                    ? $setting->manual_safety_stock
                                    : max(1, (int) ceil(0.15 * $demand * max(1, (int) ($setting->lead_time_days ?? 1)))));
                                $safetyFallbackApplied = true;
                            } else {
                                $safety = max($statSafety, (int) ($setting->manual_safety_stock ?? 0));
                                if ($safety > $statSafety && $statSafety > 0) {
                                    $safetyFallbackApplied = true;
                                }
                            }
                        }
                    }
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
                $stockoutAnalysis = app(DemandForecastService::class)->detectStockout($product->id, $start, $end);
                $actualLeadTime = app(DemandForecastService::class)->actualLeadTimeStats($product->id);

                $calc = app(ReorderCalculator::class)->calculateSql($demand, (int) ($setting->lead_time_days ?? 0),
                    (int) ($setting->target_days ?? 0), $safety, $stock, $onOrder, (int) ($setting->pack_size ?? 1));
                if ($status === 'READY') {
                    $status = $calc['stock_status'];
                }
                $quantity = $status === 'REORDER' ? $calc['quantity'] : 0;

                $calcAdjusted = null;
                if ($stockoutAnalysis['is_censored'] && (float) $stockoutAnalysis['adjusted_demand'] > 0) {
                    $calcAdjusted = app(ReorderCalculator::class)->calculateSql(
                        (float) $stockoutAnalysis['adjusted_demand'],
                        (int) ($setting->lead_time_days ?? 0),
                        (int) ($setting->target_days ?? 0),
                        $safety,
                        $stock,
                        $onOrder,
                        (int) ($setting->pack_size ?? 1)
                    );
                }

                $snapshot = ['ma_days' => $window, 'lead_time_days' => $setting->lead_time_days ?? null,
                    'target_days' => $setting->target_days ?? null, 'safety_mode' => $setting->safety_mode ?? null,
                    'safety_factor' => $setting->safety_factor ?? null, 'pack_size' => $setting->pack_size ?? null,
                    'observation_start' => $setting->observation_start ?? null,
                    'method' => $method, 'model_version' => 'linear-trend-v1', 'forecast_run_id' => $forecastRunId,
                    'moving_average' => $average, 'standard_deviation' => round($standardDeviation, 4),
                    'safety_fallback_applied' => $safetyFallbackApplied,
                    'stockout_analysis' => $stockoutAnalysis,
                    'actual_lead_time' => $actualLeadTime,
                    'adjusted_calculation' => $calcAdjusted,
                    'estimated_cost' => $quantity * (float) $product->purchase_price,
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
                if ($safetyFallbackApplied) {
                    $explanation .= ' [Fallback SS: kích hoạt đệm an toàn cơ sở].';
                }
                if ($stockoutAnalysis['is_censored']) {
                    $explanation .= sprintf(
                        ' [Cảnh báo Censored Demand: Hết hàng %d/%d ngày (%.1f%%). Tốc độ khi có hàng %.4f %s/ngày].',
                        $stockoutAnalysis['stockout_days'], $stockoutAnalysis['total_days'],
                        $stockoutAnalysis['stockout_ratio'] * 100,
                        $stockoutAnalysis['adjusted_demand'], $product->unit
                    );
                }
                if ($actualLeadTime['has_history']) {
                    $diffL = $actualLeadTime['avg_lead_time_days'] - (int) ($setting->lead_time_days ?? 0);
                    $explanation .= sprintf(
                        ' [Lead Time thực tế: TB %d ngày qua %d lần nhập%s].',
                        $actualLeadTime['avg_lead_time_days'], $actualLeadTime['sample_count'],
                        $diffL > 0 ? " (trễ {$diffL} ngày so với kế hoạch)" : ($diffL < 0 ? " (sớm ".abs($diffL)." ngày)" : " (khớp kế hoạch)")
                    );
                }
                $active = DB::table('reorder_recommendations')
                    ->where('product_id', $product->id)
                    ->whereIn('status', ['pending', 'approved', 'ordered', 'partially_received'])
                    ->latest('id')
                    ->first();

                $previous = DB::table('reorder_recommendations')->where('product_id', $product->id)->latest('id')->first();
                $json = json_encode($snapshot, JSON_UNESCAPED_UNICODE);

                if ($previous && $previous->explanation === $explanation && json_decode($previous->calculation_snapshot ?? '{}', true) == $snapshot) {
                    continue;
                }

                // Chống tạo đề xuất trùng lặp khi đang có đề xuất/đơn đặt đang xử lý:
                if ($active) {
                    // Nếu đang có đề xuất 'pending': cập nhật số lượng và căn cứ mới nhất thay vì sinh dòng mới
                    if ($active->status === 'pending') {
                        DB::table('reorder_recommendations')->where('id', $active->id)->update([
                            'period_start' => $start,
                            'period_end' => $end,
                            'average_daily_demand' => $demand,
                            'safety_stock' => $safety,
                            'reorder_point' => $calc['rop'],
                            'on_hand' => max(0, $stock),
                            'on_order' => $onOrder,
                            'committed' => 0,
                            'inventory_position' => $calc['ip'],
                            'suggested_quantity' => $quantity,
                            'stock_status' => $status,
                            'calculation_snapshot' => $json,
                            'explanation' => $explanation,
                            'updated_at' => now(),
                        ]);
                        $count++;
                        continue;
                    }

                    // Nếu đang ở trạng thái 'approved' (Admin đã duyệt, chờ xác nhận đặt hàng với NCC):
                    // Cập nhật số liệu tồn kho mới nhất, giữ nguyên quyết định đã duyệt, không sinh đề xuất trùng lặp
                    if ($active->status === 'approved') {
                        DB::table('reorder_recommendations')->where('id', $active->id)->update([
                            'on_hand' => max(0, $stock),
                            'on_order' => $onOrder,
                            'inventory_position' => $calc['ip'],
                            'average_daily_demand' => $demand,
                            'reorder_point' => $calc['rop'],
                            'stock_status' => $status,
                            'updated_at' => now(),
                        ]);
                        continue;
                    }

                    // Nếu đang ở trạng thái 'ordered' hoặc 'partially_received':
                    // Cập nhật On-Hand và On-Order mới nhất theo các phiếu nhập thực tế
                    if (in_array($active->status, ['ordered', 'partially_received'], true)) {
                        DB::table('reorder_recommendations')->where('id', $active->id)->update([
                            'on_hand' => max(0, $stock),
                            'on_order' => $onOrder,
                            'inventory_position' => $calc['ip'],
                            'average_daily_demand' => $demand,
                            'reorder_point' => $calc['rop'],
                            'stock_status' => $status,
                            'updated_at' => now(),
                        ]);
                        if ($quantity <= 0) {
                            continue;
                        }
                    }
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
