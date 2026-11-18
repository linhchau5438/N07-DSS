<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReorderCalculator
{
    public function calculateSql(float $demand, int $lead, int $target, float $safety, int $onHand, int $onOrder, int $pack): array
    {
        $cast = DB::connection()->getDriverName() === 'mysql' ? 'SIGNED' : 'INTEGER';
        $sql = "SELECT demand, ip, rop, CASE WHEN ip <= rop THEN 'REORDER' ELSE 'NORMAL' END AS stock_status,
            CASE WHEN ip <= rop AND raw_quantity > 0
                THEN (CAST(raw_quantity / pack AS {$cast}) + CASE WHEN raw_quantity / pack > CAST(raw_quantity / pack AS {$cast}) THEN 1 ELSE 0 END) * pack
                ELSE 0 END AS quantity
            FROM (SELECT demand, pack, on_hand + on_order AS ip, demand * lead_days + safety AS rop,
                    demand * (lead_days + target_days) + safety - on_hand - on_order AS raw_quantity
                FROM (SELECT 1.0 * ? AS demand, 1.0 * ? AS lead_days, 1.0 * ? AS target_days,
                    1.0 * ? AS safety, 1.0 * ? AS on_hand, 1.0 * ? AS on_order, 1.0 * ? AS pack) inputs) calculation";

        return (array) DB::selectOne($sql, [$demand, $lead, $target, $safety, $onHand, $onOrder, $pack]);
    }

    public function calculate(array $daily, int $lead, int $target, float $safety, int $onHand, int $onOrder, int $pack = 1, ?float $forecast = null): array
    {
        $demand = $forecast ?? (count($daily) ? array_sum($daily) / count($daily) : 0);
        $ip = $onHand + $onOrder; // No sales reservation module in the agreed single-warehouse scope.
        $rop = $demand * $lead + $safety;
        $status = $ip <= $rop ? 'REORDER' : 'NORMAL';
        $quantity = $status === 'REORDER' ? (int) (ceil(max(0, $demand * ($lead + $target) + $safety - $ip) / $pack) * $pack) : 0;

        return ['demand' => $demand, 'ip' => $ip, 'rop' => $rop, 'quantity' => $quantity, 'stock_status' => $status];
    }
}
