<?php

namespace App\Http\Controllers;

use App\Services\ReorderRecommendationService;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        app(ReorderRecommendationService::class)->refreshIfNeeded((int) auth()->id());
        $stockTotals = $this->stockTotalsQuery();
        $metrics = DB::table('products')
            ->leftJoinSub($stockTotals, 'stock_totals', function ($join) {
                $join->on('products.id', '=', 'stock_totals.product_id');
            })
            ->selectRaw('COUNT(products.id) as product_count')
            ->selectRaw('SUM(CASE WHEN products.status = 1 THEN 1 ELSE 0 END) as active_product_count')
            ->selectRaw('COALESCE(SUM(COALESCE(stock_totals.on_hand, 0)), 0) as total_stock')
            ->selectRaw('COALESCE(SUM(CASE WHEN COALESCE(stock_totals.on_hand, 0) > 0 THEN COALESCE(stock_totals.on_hand, 0) * products.purchase_price ELSE 0 END), 0) as inventory_value')
            ->selectRaw('SUM(CASE WHEN products.status = 1 AND COALESCE(stock_totals.on_hand, 0) BETWEEN 1 AND 10 THEN 1 ELSE 0 END) as low_stock_count')
            ->selectRaw('SUM(CASE WHEN products.status = 1 AND COALESCE(stock_totals.on_hand, 0) <= 0 THEN 1 ELSE 0 END) as out_of_stock_count')
            ->first();

        $weekStart = now()->subDays(6)->toDateString();
        $weekEnd = now()->toDateString();
        $movementRows = DB::table('inventory_transaction_items')
            ->join('inventory_transactions', 'inventory_transaction_items.inventory_transaction_id', '=', 'inventory_transactions.id')
            ->whereBetween('inventory_transactions.transaction_date', [$weekStart, $weekEnd])
            ->select('inventory_transactions.transaction_date', 'inventory_transactions.type')
            ->selectRaw('SUM(inventory_transaction_items.quantity) as quantity')
            ->groupBy('inventory_transactions.transaction_date', 'inventory_transactions.type')
            ->get();

        $chart = [];
        foreach (CarbonPeriod::create($weekStart, $weekEnd) as $date) {
            $dateKey = $date->toDateString();
            $chart[$dateKey] = ['date' => $date->format('d/m'), 'import' => 0, 'export' => 0];
        }
        foreach ($movementRows as $row) {
            $dateKey = $row->transaction_date;
            if (isset($chart[$dateKey])) {
                $chart[$dateKey][$row->type] = (int) $row->quantity;
            }
        }

        $lowStock = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoinSub($this->stockTotalsQuery(), 'stock_totals', function ($join) {
                $join->on('products.id', '=', 'stock_totals.product_id');
            })
            ->where('products.status', 1)
            ->whereRaw('COALESCE(stock_totals.on_hand, 0) <= 10')
            ->select(
                'products.product_code',
                'products.name',
                'products.unit',
                'categories.name as category_name',
            )
            ->selectRaw('COALESCE(stock_totals.on_hand, 0) as on_hand')
            ->orderBy('on_hand')
            ->orderBy('products.name')
            ->limit(6)
            ->get();

        $topSellers = DB::table('inventory_transaction_items')
            ->join('inventory_transactions', 'inventory_transaction_items.inventory_transaction_id', '=', 'inventory_transactions.id')
            ->join('products', 'inventory_transaction_items.product_id', '=', 'products.id')
            ->where('inventory_transactions.type', 'export')
            ->where('inventory_transactions.issue_purpose', 'sale')
            ->whereBetween('inventory_transactions.transaction_date', [
                now()->subDays(29)->toDateString(),
                $weekEnd,
            ])
            ->select('products.product_code', 'products.name', 'products.unit')
            ->selectRaw('SUM(inventory_transaction_items.quantity) as sold_quantity')
            ->groupBy('products.id', 'products.product_code', 'products.name', 'products.unit')
            ->orderByDesc('sold_quantity')
            ->limit(5)
            ->get();

        $recentTransactions = DB::table('inventory_transactions')
            ->leftJoin('users', 'inventory_transactions.user_id', '=', 'users.id')
            ->select(
                'inventory_transactions.id',
                'inventory_transactions.transaction_code',
                'inventory_transactions.type',
                'inventory_transactions.transaction_date',
                'inventory_transactions.total_value',
                'users.name as staff_name',
            )
            ->orderByDesc('inventory_transactions.transaction_date')
            ->orderByDesc('inventory_transactions.id')
            ->limit(6)
            ->get();

        return view('dashboard.index', [
            'metrics' => $metrics,
            'chart' => array_values($chart),
            'lowStock' => $lowStock,
            'topSellers' => $topSellers,
            'recentTransactions' => $recentTransactions,
            'reorderCount' => DB::table('v_reorder as r')->join('products as p', 'r.product_id', '=', 'p.id')->where('p.status', 1)->where('r.stock_status', 'REORDER')->count(),
            'pendingRecommendations' => DB::table('v_reorder as r')->join('products as p', 'r.product_id', '=', 'p.id')->where('p.status', 1)->where('r.status', 'pending')->count(),
        ]);
    }

    private function stockTotalsQuery()
    {
        return DB::table('inventory_transaction_items')
            ->join('inventory_transactions', 'inventory_transaction_items.inventory_transaction_id', '=', 'inventory_transactions.id')
            ->select('inventory_transaction_items.product_id')
            ->selectRaw("SUM(CASE WHEN inventory_transactions.type = 'import' THEN inventory_transaction_items.quantity ELSE -inventory_transaction_items.quantity END) as on_hand")
            ->groupBy('inventory_transaction_items.product_id');
    }
}
