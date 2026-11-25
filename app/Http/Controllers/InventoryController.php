<?php

namespace App\Http\Controllers;

use App\Services\AuditLog;
use App\Services\ReorderRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        app(ReorderRecommendationService::class)->refreshIfNeeded((int) auth()->id());
        $filters = $request->validate([
            'search' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer|exists:categories,id',
            'stock' => 'nullable|in:all,low,out,attention,NORMAL,REORDER,MISSING_CONFIG,INSUFFICIENT_DATA,INVALID_DATA',
        ]);

        $search = trim($filters['search'] ?? '');
        $stockQuery = $this->stockTotalsQuery();
        $latestRecommendations = DB::table('reorder_recommendations')
            ->select('product_id', DB::raw('MAX(id) as latest_id'))
            ->groupBy('product_id');

        $inventoryProducts = DB::table('products')
            ->leftJoinSub($stockQuery, 'stock_totals', function ($join) {
                $join->on('products.id', '=', 'stock_totals.product_id');
            })
            ->leftJoinSub($latestRecommendations, 'latest', function ($join) {
                $join->on('products.id', '=', 'latest.product_id');
            })
            ->leftJoin('reorder_recommendations as recommendations', 'latest.latest_id', '=', 'recommendations.id');

        $productsQuery = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoinSub($stockQuery, 'stock_totals', function ($join) {
                $join->on('products.id', '=', 'stock_totals.product_id');
            })
            ->leftJoinSub($latestRecommendations, 'latest', function ($join) {
                $join->on('products.id', '=', 'latest.product_id');
            })
            ->leftJoin('reorder_recommendations as recommendations', 'latest.latest_id', '=', 'recommendations.id')
            ->select('products.*', 'categories.name as category_name')
            ->addSelect('recommendations.safety_stock', 'recommendations.reorder_point', 'recommendations.on_order', 'recommendations.inventory_position', 'recommendations.stock_status')
            ->selectRaw('COALESCE(stock_totals.on_hand, 0) as on_hand');

        if ($search !== '') {
            $productsQuery->where(function ($query) use ($search) {
                $query->where('products.product_code', 'like', "%{$search}%")
                    ->orWhere('products.name', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['category_id'])) {
            $productsQuery->where('products.category_id', $filters['category_id']);
        }

        if (($filters['stock'] ?? 'all') === 'attention') {
            $productsQuery->where(function ($query) {
                $query->whereRaw('COALESCE(stock_totals.on_hand, 0) <= 10')
                    ->orWhere('recommendations.stock_status', 'REORDER');
            });
        } elseif (($filters['stock'] ?? 'all') === 'low') {
            $productsQuery->whereRaw('COALESCE(stock_totals.on_hand, 0) BETWEEN 1 AND 10');
        } elseif (($filters['stock'] ?? 'all') === 'out') {
            $productsQuery->whereRaw('COALESCE(stock_totals.on_hand, 0) <= 0');
        }

        if (in_array($filters['stock'] ?? '', ['NORMAL', 'REORDER', 'MISSING_CONFIG', 'INSUFFICIENT_DATA', 'INVALID_DATA'])) {
            $productsQuery->where('recommendations.stock_status', $filters['stock']);
        }
        $products = $productsQuery
            ->orderBy('products.name')
            ->paginate(15)
            ->withQueryString();

        $categories = DB::table('categories')->orderBy('name')->get();

        $transactions = DB::table('inventory_transactions')
            ->leftJoin('users', 'inventory_transactions.user_id', '=', 'users.id')
            ->select(
                'inventory_transactions.*',
                'users.name as staff_name',
            )
            ->orderByDesc('transaction_date')
            ->orderByDesc('inventory_transactions.id')
            ->limit(12)
            ->get();

        $metrics = (clone $inventoryProducts)
            ->where('products.status', 1)
            ->selectRaw('COUNT(products.id) as active_product_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN COALESCE(stock_totals.on_hand, 0) > 0 THEN stock_totals.on_hand * products.purchase_price ELSE 0 END), 0) as inventory_value')
            ->selectRaw('SUM(CASE WHEN COALESCE(stock_totals.on_hand, 0) <= 10 THEN 1 ELSE 0 END) as low_stock_count')
            ->selectRaw('SUM(CASE WHEN recommendations.id IS NOT NULL AND recommendations.suggested_quantity > 0 AND recommendations.status IN (?, ?) THEN 1 ELSE 0 END) as reorder_count', ['pending', 'approved'])
            ->first();

        return view('inventory.index', compact('products', 'categories', 'transactions', 'search', 'filters', 'metrics'));
    }

    public function create(string $type)
    {
        abort_unless(in_array($type, ['import', 'export'], true), 404);

        $products = DB::table('products')
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'product_code', 'name', 'unit', 'purchase_price', 'selling_price']);

        $stockTotals = DB::table('products')
            ->leftJoinSub($this->stockTotalsQuery(), 'stock_totals', function ($join) {
                $join->on('products.id', '=', 'stock_totals.product_id');
            })
            ->select('products.id')
            ->selectRaw('COALESCE(stock_totals.on_hand, 0) as on_hand')
            ->pluck('on_hand', 'products.id');

        $openRecommendations = $type === 'import'
            ? DB::table('reorder_recommendations')
                ->join('products', 'reorder_recommendations.product_id', '=', 'products.id')
                ->whereIn('reorder_recommendations.status', ['ordered', 'partially_received'])
                ->whereColumn('reorder_recommendations.ordered_quantity', '>', 'reorder_recommendations.received_quantity')
                ->select(
                    'reorder_recommendations.id',
                    'reorder_recommendations.product_id',
                    'reorder_recommendations.approved_quantity',
                    'reorder_recommendations.ordered_quantity',
                    'reorder_recommendations.received_quantity',
                    'reorder_recommendations.supplier_name',
                    'products.product_code',
                    'products.name',
                    'products.unit',
                )
                ->get()
            : collect();

        $selectedRecommendation = $openRecommendations->firstWhere('id', (int) request()->query('recommendation_id'));
        $selectedProductId = $selectedRecommendation->product_id ?? null;

        return view('inventory.create', compact('type', 'products', 'stockTotals', 'openRecommendations', 'selectedRecommendation', 'selectedProductId'));
    }

    public function show(int $id)
    {
        $transaction = DB::table('inventory_transactions')
            ->leftJoin('users', 'inventory_transactions.user_id', '=', 'users.id')
            ->where('inventory_transactions.id', $id)
            ->select('inventory_transactions.*', 'users.name as staff_name')
            ->first();

        if (! $transaction) {
            abort(404);
        }

        $items = DB::table('inventory_transaction_items')
            ->join('products', 'inventory_transaction_items.product_id', '=', 'products.id')
            ->where('inventory_transaction_items.inventory_transaction_id', $id)
            ->select(
                'products.id as product_id',
                'products.product_code',
                'products.name',
                'products.unit',
                'inventory_transaction_items.quantity',
                'inventory_transaction_items.unit_price',
                'inventory_transaction_items.line_total',
                'inventory_transaction_items.reorder_recommendation_id',
            )
            ->get();

        return view('inventory.show', compact('transaction', 'items'));
    }

    public function store(Request $request, string $type)
    {
        abort_unless(in_array($type, ['import', 'export'], true), 404);

        $validated = $request->validate([
            'transaction_date' => 'required|date|before_or_equal:today',
            'note' => 'nullable|string|max:2000',
            'issue_purpose' => ($type === 'export' ? 'required' : 'nullable').'|in:consumption,sale,damage,return_vendor,transfer,adjustment,other',
            'reference_no' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|distinct|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:1000000',
            'items.*.unit_price' => 'nullable|numeric|min:0|max:9999999999999.99',
            'items.*.reorder_recommendation_id' => 'nullable|integer|exists:reorder_recommendations,id',
        ], [
            'transaction_date.required' => 'Vui lòng chọn ngày lập phiếu.',
            'transaction_date.before_or_equal' => 'Ngày lập phiếu không được ở tương lai.',
            'items.required' => 'Phiếu phải có ít nhất một sản phẩm.',
            'items.min' => 'Phiếu phải có ít nhất một sản phẩm.',
            'items.*.product_id.required' => 'Vui lòng chọn sản phẩm.',
            'items.*.product_id.distinct' => 'Không thêm trùng sản phẩm trong cùng một phiếu.',
            'items.*.product_id.exists' => 'Sản phẩm được chọn không tồn tại.',
            'items.*.quantity.required' => 'Vui lòng nhập số lượng.',
            'items.*.quantity.min' => 'Số lượng phải lớn hơn 0.',
            'items.*.quantity.max' => 'Số lượng vượt quá giới hạn cho phép.',
            'items.*.unit_price.numeric' => 'Đơn giá phải là số hợp lệ.',
            'items.*.unit_price.min' => 'Đơn giá không được âm.',
        ]);

        $transactionId = DB::transaction(function () use ($validated, $type, $request) {
            $productIds = collect($validated['items'])
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values();

            $products = DB::table('products')
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $productIds->count() || $products->contains('status', 0)) {
                abort(422, 'Có sản phẩm không còn kinh doanh hoặc không tồn tại.');
            }

            $currentStocks = $this->stockForProducts($productIds);
            $preparedItems = [];
            $totalValue = 0;

            foreach ($validated['items'] as $item) {
                $productId = (int) $item['product_id'];
                $product = $products->get($productId);
                $quantity = (int) $item['quantity'];
                $onHand = (int) ($currentStocks[$productId] ?? 0);

                if ($type === 'export' && $quantity > $onHand) {
                    $this->abortWithValidationError(
                        'items',
                        "Sản phẩm {$product->name} chỉ còn {$onHand} {$product->unit}, không thể xuất {$quantity}."
                    );
                }

                $recommendationId = $item['reorder_recommendation_id'] ?? null;
                if ($recommendationId !== null) {
                    if ($type !== 'import') {
                        $this->abortWithValidationError('items', 'Chỉ phiếu nhập mới được liên kết với đề xuất đã duyệt.');
                    }

                    $recommendation = DB::table('reorder_recommendations')
                        ->where('id', $recommendationId)
                        ->lockForUpdate()
                        ->first();

                    $remainingToReceive = (int) ($recommendation->ordered_quantity ?? 0) - (int) ($recommendation->received_quantity ?? 0);
                    if (
                        ! $recommendation
                        || ! in_array($recommendation->status, ['ordered', 'partially_received'], true)
                        || (int) $recommendation->product_id !== $productId
                        || $remainingToReceive < $quantity
                    ) {
                        $this->abortWithValidationError(
                            'items',
                            "Phiếu nhập không khớp với số lượng còn chờ nhận ({$remainingToReceive}) của đơn đặt hàng cho {$product->name}."
                        );
                    }

                    $newReceived = (int) $recommendation->received_quantity + $quantity;
                    $isFulfilled = $newReceived >= (int) $recommendation->ordered_quantity;
                    DB::table('reorder_recommendations')
                        ->where('id', $recommendationId)
                        ->update([
                            'received_quantity' => $newReceived,
                            'status' => $isFulfilled
                                ? 'fulfilled'
                                : 'partially_received',
                            'on_order' => max(0, (int) $recommendation->ordered_quantity - $newReceived),
                            'updated_at' => now(),
                        ]);
                }

                $unitPrice = $type === 'import'
                    ? ($item['unit_price'] ?? $product->purchase_price)
                    : null;
                $lineTotal = $unitPrice !== null ? $quantity * (float) $unitPrice : null;
                if ($lineTotal !== null) {
                    if ($lineTotal > 9999999999999.99 || $totalValue + $lineTotal > 9999999999999.99) {
                        $this->abortWithValidationError('items', 'Tổng giá trị phiếu vượt quá giới hạn lưu trữ.');
                    }
                    $totalValue += $lineTotal;
                }

                $preparedItems[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'reorder_recommendation_id' => $recommendationId,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $transactionId = DB::table('inventory_transactions')->insertGetId([
                'transaction_code' => strtoupper($type === 'import' ? 'PN-' : 'PX-').now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
                'type' => $type,
                'issue_purpose' => $type === 'export' ? $validated['issue_purpose'] : 'receipt',
                'reference_no' => $validated['reference_no'] ?? null,
                'transaction_date' => $validated['transaction_date'],
                'user_id' => $request->user()->id,
                'total_value' => $totalValue,
                'note' => $validated['note'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($preparedItems as $item) {
                $item['inventory_transaction_id'] = $transactionId;
                DB::table('inventory_transaction_items')->insert($item);
            }

            AuditLog::record('CONFIRM_'.strtoupper($type), 'inventory_transactions', $transactionId, null, $validated);

            return $transactionId;
        });

        app(ReorderRecommendationService::class)->refreshIfNeeded((int) $request->user()->id);

        return redirect()->route('inventory.index')
            ->with('success', ($type === 'import' ? 'Lập phiếu nhập thành công: ' : 'Lập phiếu xuất thành công: ')
                .DB::table('inventory_transactions')->where('id', $transactionId)->value('transaction_code'));
    }

    public function reviewPurpose(Request $request, int $id)
    {
        $data = $request->validate(['issue_purpose' => 'required|in:consumption,sale,damage,return_vendor,transfer,adjustment,other', 'reason' => 'required|string|max:1000']);
        DB::transaction(function () use ($id, $data) {
            $row = DB::table('inventory_transactions')->where('id', $id)->lockForUpdate()->first();
            abort_unless($row && $row->type === 'export', 404);
            DB::table('inventory_transactions')->where('id', $id)->update(['issue_purpose' => $data['issue_purpose'], 'updated_at' => now()]);
            AuditLog::record('REVIEW_ISSUE_PURPOSE', 'inventory_transactions', $id, ['issue_purpose' => $row->issue_purpose], $data);
        });
        app(ReorderRecommendationService::class)->refreshIfNeeded((int) $request->user()->id);

        return redirect()->route('inventory.show', $id)->with('success', 'Đã xác nhận mục đích xuất và lưu lịch sử.');
    }

    private function stockTotalsQuery()
    {
        return DB::table('inventory_transaction_items')
            ->join('inventory_transactions', 'inventory_transaction_items.inventory_transaction_id', '=', 'inventory_transactions.id')
            ->select('inventory_transaction_items.product_id')
            ->selectRaw("SUM(CASE WHEN inventory_transactions.type = 'import' THEN inventory_transaction_items.quantity ELSE -inventory_transaction_items.quantity END) as on_hand")
            ->groupBy('inventory_transaction_items.product_id');
    }

    private function stockForProducts($productIds): array
    {
        return DB::table('inventory_transaction_items')
            ->join('inventory_transactions', 'inventory_transaction_items.inventory_transaction_id', '=', 'inventory_transactions.id')
            ->whereIn('inventory_transaction_items.product_id', $productIds)
            ->select('inventory_transaction_items.product_id')
            ->selectRaw("SUM(CASE WHEN inventory_transactions.type = 'import' THEN inventory_transaction_items.quantity ELSE -inventory_transaction_items.quantity END) as on_hand")
            ->groupBy('inventory_transaction_items.product_id')
            ->pluck('on_hand', 'inventory_transaction_items.product_id')
            ->all();
    }

    private function abortWithValidationError(string $field, string $message): never
    {
        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }
}
