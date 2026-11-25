<?php

namespace App\Http\Controllers;

use App\Services\ReorderRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function alerts(Request $request)
    {
        app(ReorderRecommendationService::class)->refreshIfNeeded((int) auth()->id());
        [, , $categoryId, $productId] = $this->filters($request);
        $status = $request->validate(['stock_status' => 'nullable|in:NORMAL,REORDER,MISSING_CONFIG,INSUFFICIENT_DATA,INVALID_DATA'])['stock_status'] ?? null;
        $query = DB::table('v_reorder as r')->join('products as p', 'r.product_id', '=', 'p.id')->join('categories as c', 'p.category_id', '=', 'c.id')
            ->where('p.status', 1)->when($categoryId, fn ($q, $id) => $q->where('p.category_id', $id))
            ->when($productId, fn ($q, $id) => $q->where('p.id', $id))->when($status, fn ($q, $v) => $q->where('r.stock_status', $v))
            ->select('p.product_code', 'p.name', 'c.name as category', 'r.on_hand', 'r.on_order', 'r.inventory_position', 'r.average_daily_demand', 'r.safety_stock', 'r.reorder_point', 'r.stock_status')
            ->orderBy('p.name');
        $data = $query->get();

        return $this->tableReport('BC01 - Tồn kho và cảnh báo ROP', ['Mã', 'Sản phẩm', 'Danh mục', 'Tồn', 'Đang về', 'IP', 'Nhu cầu/ngày', 'SS', 'ROP', 'Trạng thái'], $data->map(fn ($r) => array_values((array) $r))->all(), null, null, $categoryId, $productId, ['statusCounts' => $data->countBy('stock_status')->all(), 'showStockStatus' => true]);
    }

    public function stockFlow(Request $request)
    {
        [$from, $to, $categoryId, $productId] = $this->filters($request);

        $movements = DB::table('inventory_transaction_items')
            ->join('inventory_transactions', 'inventory_transaction_items.inventory_transaction_id', '=', 'inventory_transactions.id')
            ->whereDate('inventory_transactions.transaction_date', '<=', $to)
            ->select('inventory_transaction_items.product_id')
            ->selectRaw(
                "SUM(CASE WHEN inventory_transactions.transaction_date < ? AND inventory_transactions.type = 'import' THEN inventory_transaction_items.quantity WHEN inventory_transactions.transaction_date < ? AND inventory_transactions.type = 'export' THEN -inventory_transaction_items.quantity ELSE 0 END) as opening_stock",
                [$from, $from],
            )
            ->selectRaw(
                "SUM(CASE WHEN inventory_transactions.transaction_date BETWEEN ? AND ? AND inventory_transactions.type = 'import' THEN inventory_transaction_items.quantity ELSE 0 END) as imported",
                [$from, $to],
            )
            ->selectRaw(
                "SUM(CASE WHEN inventory_transactions.transaction_date BETWEEN ? AND ? AND inventory_transactions.type = 'export' THEN inventory_transaction_items.quantity ELSE 0 END) as exported",
                [$from, $to],
            )
            ->selectRaw("SUM(CASE WHEN inventory_transactions.transaction_date BETWEEN ? AND ? AND inventory_transactions.type = 'export' AND inventory_transactions.issue_purpose IN ('sale', 'consumption') THEN inventory_transaction_items.quantity ELSE 0 END) as consumed", [$from, $to])
            ->groupBy('inventory_transaction_items.product_id');

        $query = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoinSub($movements, 'movements', function ($join) {
                $join->on('products.id', '=', 'movements.product_id');
            })
            ->select(
                'products.product_code',
                'products.name',
                'categories.name as category_name',
                'products.unit',
            )
            ->selectRaw('COALESCE(movements.opening_stock, 0) as opening_stock')
            ->selectRaw('COALESCE(movements.imported, 0) as imported')
            ->selectRaw('COALESCE(movements.exported, 0) as exported')
            ->selectRaw('COALESCE(movements.consumed, 0) as consumed')
            ->selectRaw('COALESCE(movements.opening_stock, 0) + COALESCE(movements.imported, 0) - COALESCE(movements.exported, 0) as closing_stock')
            ->orderBy('categories.name')
            ->orderBy('products.name');

        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }
        if ($productId) {
            $query->where('products.id', $productId);
        }

        $rows = $query->get()->map(fn ($row) => [
            $row->product_code,
            $row->name,
            $row->category_name,
            $row->unit,
            (int) $row->opening_stock,
            (int) $row->imported,
            (int) $row->exported,
            (int) $row->consumed,
            (int) $row->exported - (int) $row->consumed,
            (int) $row->closing_stock,
        ])->all();

        return $this->tableReport(
            'BC02 - Báo cáo nhập - xuất - tồn',
            ['Mã', 'Sản phẩm', 'Danh mục', 'Đơn vị', 'Tồn đầu kỳ', 'Nhập kỳ', 'Tổng xuất', 'Xuất tiêu thụ', 'Xuất khác/chưa rõ', 'Tồn cuối kỳ'],
            $rows,
            $from,
            $to,
            $categoryId,
            $productId,
        );
    }

    public function reorder(Request $request)
    {
        app(ReorderRecommendationService::class)->refreshIfNeeded((int) auth()->id());
        [, , $categoryId, $productId] = $this->filters($request);

        $latest = DB::table('reorder_recommendations')
            ->select('product_id', DB::raw('MAX(id) as latest_id'))
            ->groupBy('product_id');

        $query = DB::table('reorder_recommendations')
            ->joinSub($latest, 'latest', function ($join) {
                $join->on('reorder_recommendations.id', '=', 'latest.latest_id');
            })
            ->join('products', 'reorder_recommendations.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.status', 1)
            ->where('reorder_recommendations.stock_status', 'REORDER')
            ->whereRaw('(reorder_recommendations.suggested_quantity > 0 OR reorder_recommendations.status IN (?, ?, ?))', ['approved', 'ordered', 'partially_received'])
            ->select(
                'products.product_code',
                'products.name',
                'categories.name as category_name',
                'reorder_recommendations.suggested_quantity',
                'reorder_recommendations.approved_quantity',
                'reorder_recommendations.ordered_quantity',
                'reorder_recommendations.received_quantity',
                'reorder_recommendations.status',
                'reorder_recommendations.reorder_point',
                'reorder_recommendations.inventory_position',
                'reorder_recommendations.explanation',
            )
            ->orderBy('categories.name')
            ->orderBy('products.name');

        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }
        if ($productId) {
            $query->where('products.id', $productId);
        }

        $rows = $query->get()->map(fn ($row) => [
            $row->product_code,
            $row->name,
            $row->category_name,
            $row->reorder_point,
            $row->inventory_position,
            $row->suggested_quantity,
            $row->approved_quantity ?? '—',
            $row->ordered_quantity ?? '—',
            $row->received_quantity,
            $row->status,
            $row->explanation,
        ])->all();

        return $this->tableReport(
            'BC03 - Báo cáo sản phẩm cần nhập hàng',
            ['Mã', 'Sản phẩm', 'Danh mục', 'ROP', 'IP', 'Q đề xuất', 'Q duyệt', 'Q đặt thực tế', 'Đã nhận', 'Trạng thái', 'Giải thích'],
            $rows,
            null,
            null,
            $categoryId,
            $productId,
        );
    }

    public function bestSelling(Request $request)
    {
        [$from, $to, $categoryId, $productId] = $this->filters($request);

        $query = DB::table('inventory_transaction_items')
            ->join('inventory_transactions', 'inventory_transaction_items.inventory_transaction_id', '=', 'inventory_transactions.id')
            ->join('products', 'inventory_transaction_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('inventory_transactions.type', 'export')
            ->whereIn('inventory_transactions.issue_purpose', ['sale', 'consumption'])
            ->whereBetween('inventory_transactions.transaction_date', [$from, $to])
            ->select('products.product_code', 'products.name', 'categories.name as category_name', 'products.unit')
            ->selectRaw('SUM(inventory_transaction_items.quantity) as sold_quantity')
            ->groupBy('products.id', 'products.product_code', 'products.name', 'categories.name', 'products.unit')
            ->orderByDesc('sold_quantity');

        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }
        if ($productId) {
            $query->where('products.id', $productId);
        }

        $rows = $query->get()->map(fn ($row) => [
            $row->product_code,
            $row->name,
            $row->category_name,
            $row->sold_quantity,
            $row->unit,
        ])->all();

        return $this->tableReport(
            'Báo cáo sản phẩm tiêu thụ nhiều (Consumption)',
            ['Mã', 'Sản phẩm', 'Danh mục', 'Đã xuất tiêu thụ', 'Đơn vị'],
            $rows,
            $from,
            $to,
            $categoryId,
            $productId,
        );
    }

    public function slowMoving(Request $request)
    {
        [$from, $to, $categoryId, $productId] = $this->filters($request);

        $days = max(1, (int) Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1);
        $stock = $this->stockTotalsQuery()->whereDate('inventory_transactions.transaction_date', '<=', $to);
        $sales = DB::table('inventory_transaction_items')
            ->join('inventory_transactions', 'inventory_transaction_items.inventory_transaction_id', '=', 'inventory_transactions.id')
            ->where('inventory_transactions.type', 'export')
            ->whereIn('inventory_transactions.issue_purpose', ['sale', 'consumption'])
            ->whereDate('inventory_transactions.transaction_date', '<=', $to)
            ->select('inventory_transaction_items.product_id')
            ->selectRaw('MAX(inventory_transactions.transaction_date) as last_sold_at')
            ->selectRaw('SUM(CASE WHEN inventory_transactions.transaction_date BETWEEN ? AND ? THEN inventory_transaction_items.quantity ELSE 0 END) as recent_sales', [$from, $to])
            ->groupBy('inventory_transaction_items.product_id');

        $query = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoinSub($stock, 'stock_totals', function ($join) {
                $join->on('products.id', '=', 'stock_totals.product_id');
            })
            ->leftJoinSub($sales, 'sales', function ($join) {
                $join->on('products.id', '=', 'sales.product_id');
            })
            ->whereRaw('COALESCE(stock_totals.on_hand, 0) > 0')
            ->whereRaw('(COALESCE(sales.recent_sales, 0) = 0 OR COALESCE(stock_totals.on_hand, 0) * ? > sales.recent_sales * 60)', [$days])
            ->select(
                'products.product_code',
                'products.name',
                'categories.name as category_name',
                'products.unit',
                'sales.last_sold_at', 'products.purchase_price',
            )
            ->selectRaw('COALESCE(stock_totals.on_hand, 0) as on_hand')
            ->selectRaw('COALESCE(sales.recent_sales, 0) as recent_sales')
            ->orderByDesc('on_hand');

        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }
        if ($productId) {
            $query->where('products.id', $productId);
        }

        $rows = $query->get()->map(function ($row) use ($days) {
            $daysOfSupply = $row->recent_sales > 0 ? round($row->on_hand * $days / $row->recent_sales, 1) : null;

            if ($row->recent_sales <= 0 || ! $row->last_sold_at) {
                $classification = 'Hàng không tiêu thụ (Dead Stock)';
            } elseif ($daysOfSupply !== null && $daysOfSupply > 90) {
                $classification = 'Hàng tồn dư mức cao (Excess Stock)';
            } elseif ($daysOfSupply !== null && $daysOfSupply > 60) {
                $classification = 'Hàng chậm luân chuyển (Slow Moving)';
            } else {
                $classification = 'Tồn kho mức bình thường';
            }

            return [
                $row->product_code,
                $row->name,
                $row->category_name,
                $row->on_hand,
                $row->unit,
                $row->recent_sales,
                $row->last_sold_at ?: 'Chưa từng xuất',
                $daysOfSupply !== null ? $daysOfSupply . ' ngày' : 'Không có tiêu thụ',
                $classification,
                number_format($row->on_hand * $row->purchase_price, 0).' đ',
            ];
        })->all();

        return $this->tableReport(
            'BC04 - Báo cáo phân loại hàng tồn dư, chậm luân chuyển & không tiêu thụ',
            ['Mã', 'Sản phẩm', 'Danh mục', 'Tồn', 'Đơn vị', 'Xuất trong kỳ', 'Ngày xuất gần nhất', 'Số ngày tồn (DOS)', 'Phân loại rủi ro', 'Vốn tồn tham khảo'],
            $rows,
            $from,
            $to,
            $categoryId,
            $productId,
        );
    }

    public function olap(Request $request)
    {
        $validated = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'category_id' => 'nullable|integer|exists:categories,id',
            'product_id' => 'nullable|integer|exists:products,id',
            'dimension' => 'nullable|in:category,product,month',
            'type' => 'nullable|in:all,import,export',
        ]);
        [$from, $to] = $this->dateRange($validated);
        $dimension = $validated['dimension'] ?? 'category';
        $type = $validated['type'] ?? 'all';
        $driver = DB::connection()->getDriverName();

        if ($dimension === 'product') {
            $groupField = $driver === 'sqlite'
                ? "categories.name || ' / ' || products.product_code || ' - ' || products.name"
                : "CONCAT(categories.name, ' / ', products.product_code, ' - ', products.name)";
            $groupBy = ['categories.name', 'products.id', 'products.product_code', 'products.name'];
        } elseif ($dimension === 'month') {
            $groupField = $driver === 'sqlite'
                ? "strftime('%Y-%m', inventory_transactions.transaction_date)"
                : "DATE_FORMAT(inventory_transactions.transaction_date, '%Y-%m')";
            $groupBy = [DB::raw($groupField)];
        } else {
            $groupField = 'categories.name';
            $groupBy = ['categories.name'];
        }

        $query = DB::table('inventory_transaction_items')
            ->join('inventory_transactions', 'inventory_transaction_items.inventory_transaction_id', '=', 'inventory_transactions.id')
            ->join('products', 'inventory_transaction_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('inventory_transactions.transaction_date', [$from, $to])
            ->selectRaw($groupField.' as dimension_value')
            ->selectRaw("SUM(CASE WHEN inventory_transactions.type = 'import' THEN inventory_transaction_items.quantity ELSE 0 END) as imported")
            ->selectRaw("SUM(CASE WHEN inventory_transactions.type = 'export' THEN inventory_transaction_items.quantity ELSE 0 END) as exported")
            ->groupBy(...$groupBy)
            ->orderBy('dimension_value');

        if (! empty($validated['category_id'])) {
            $query->where('products.category_id', $validated['category_id']);
        }
        if (! empty($validated['product_id'])) {
            $query->where('products.id', $validated['product_id']);
        }

        if ($type !== 'all') {
            $query->where('inventory_transactions.type', $type);
        }

        $rows = $query->get()->map(fn ($row) => [
            $row->dimension_value,
            $row->imported,
            $row->exported,
            $row->imported - $row->exported,
        ])->all();

        return view('reports.table', [
            'title' => 'OLAP - Roll-up / Drill-down / Slice / Dice / Pivot',
            'headers' => ['Chiều phân tích', 'Tổng nhập', 'Tổng xuất', 'Chênh lệch'],
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
            'categories' => DB::table('categories')->orderBy('name')->get(),
            'categoryId' => $validated['category_id'] ?? '',
            'products' => DB::table('products')->orderBy('name')->get(['id', 'name', 'product_code']),
            'productId' => $validated['product_id'] ?? '',
            'dimension' => $dimension,
            'type' => $type,
            'showOlapFilters' => true,
        ]);
    }

    private function tableReport(
        string $title,
        array $headers,
        array $rows,
        ?string $from,
        ?string $to,
        ?int $categoryId,
        ?int $productId = null,
        array $extra = [],
    ) {
        return view('reports.table', array_merge([
            'title' => $title,
            'headers' => $headers,
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
            'categories' => DB::table('categories')->orderBy('name')->get(),
            'products' => DB::table('products')->orderBy('name')->get(['id', 'name', 'product_code']),
            'categoryId' => $categoryId ?? '',
            'productId' => $productId ?? '',
            'showOlapFilters' => false,
        ], $extra));
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'category_id' => 'nullable|integer|exists:categories,id',
            'product_id' => 'nullable|integer|exists:products,id',
        ]);

        [$from, $to] = $this->dateRange($validated);

        return [
            $from,
            $to,
            isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            isset($validated['product_id']) ? (int) $validated['product_id'] : null,
        ];
    }

    private function dateRange(array $validated): array
    {
        return [
            $validated['from'] ?? now()->startOfMonth()->toDateString(),
            $validated['to'] ?? now()->toDateString(),
        ];
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
