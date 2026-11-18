<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProductController extends Controller
{
    // Hiển thị danh sách sản phẩm
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer|exists:categories,id',
            'status' => 'nullable|in:all,active,inactive',
        ]);

        $search = trim($filters['search'] ?? '');

        $query = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoinSub($this->stockTotalsQuery(), 'stock_totals', function ($join) {
                $join->on('products.id', '=', 'stock_totals.product_id');
            })
            ->select(
                'products.*',
                'categories.name as category_name'
            )
            ->selectRaw('COALESCE(stock_totals.on_hand, 0) as on_hand');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('products.product_code', 'like', "%{$search}%")
                    ->orWhere('products.name', 'like', "%{$search}%")
                    ->orWhere('categories.name', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }
        if (($filters['status'] ?? 'all') === 'active') {
            $query->where('products.status', 1);
        } elseif (($filters['status'] ?? 'all') === 'inactive') {
            $query->where('products.status', 0);
        }

        $products = $query
            ->orderBy('products.id', 'desc')
            ->paginate(14)
            ->withQueryString();

        $categories = DB::table('categories')
            ->orderBy('name')
            ->get();

        return view('products.index', compact('products', 'categories', 'search', 'filters'));
    }

    public function show(int $id)
    {
        $latestRecommendation = DB::table('reorder_recommendations')
            ->where('product_id', $id)
            ->orderByDesc('id')
            ->first(['safety_stock', 'reorder_point', 'suggested_quantity', 'average_daily_demand', 'explanation']);

        $product = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoinSub($this->stockTotalsQuery(), 'stock_totals', function ($join) {
                $join->on('products.id', '=', 'stock_totals.product_id');
            })
            ->where('products.id', $id)
            ->select('products.*', 'categories.name as category_name')
            ->selectRaw('COALESCE(stock_totals.on_hand, 0) as on_hand')
            ->first();

        if (! $product) {
            abort(404);
        }

        $movements = DB::table('inventory_transaction_items')
            ->join('inventory_transactions', 'inventory_transaction_items.inventory_transaction_id', '=', 'inventory_transactions.id')
            ->leftJoin('users', 'inventory_transactions.user_id', '=', 'users.id')
            ->where('inventory_transaction_items.product_id', $id)
            ->select(
                'inventory_transactions.transaction_code',
                'inventory_transactions.type',
                'inventory_transactions.transaction_date',
                'inventory_transaction_items.quantity',
                'inventory_transaction_items.unit_price',
                'users.name as staff_name',
            )
            ->orderByDesc('inventory_transactions.transaction_date')
            ->orderByDesc('inventory_transactions.id')
            ->limit(10)
            ->get();

        return view('products.show', [
            'product' => $product,
            'movements' => $movements,
            'recommendation' => $latestRecommendation,
        ]);
    }

    // Hiển thị form thêm sản phẩm
    public function create()
    {
        $categories = DB::table('categories')->get();

        return view('products.create', [
            'categories' => $categories,
            'product' => null,
        ]);
    }

    // Hiển thị form sửa sản phẩm
    public function edit(int $id)
    {
        $product = DB::table('products')->find($id);

        if (! $product) {
            abort(404);
        }

        $categories = DB::table('categories')->get();

        return view('products.create', compact('categories', 'product'));
    }

    // Xử lý thêm sản phẩm
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|max:150',
            'unit' => 'required|max:30',
            'purchase_price' => 'required|numeric|min:0|max:9999999999999.99',
            'selling_price' => 'required|numeric|min:0|max:9999999999999.99',
            'description' => 'nullable|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'nullable|in:0,1',
        ], [
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'name.required' => 'Vui lòng nhập tên sản phẩm.',
            'unit.required' => 'Vui lòng nhập đơn vị tính.',
            'purchase_price.required' => 'Vui lòng nhập giá nhập.',
            'selling_price.required' => 'Vui lòng nhập giá bán.',
            'image.image' => 'File tải lên phải là hình ảnh.',
            'image.mimes' => 'Ảnh phải có định dạng JPG, JPEG, PNG hoặc WEBP.',
            'image.max' => 'Dung lượng ảnh tối đa là 2MB.',
        ]);

        // Lưu hình ảnh
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store('products', 'public');

            if ($imagePath === false) {
                throw new RuntimeException('Không thể lưu hình ảnh sản phẩm.');
            }
        }

        // Lưu thông tin sản phẩm vào database
        DB::transaction(function () use ($validated, $imagePath) {
            $temporaryCode = 'TMP'.Str::upper(Str::random(24));
            $productId = DB::table('products')->insertGetId([
                'category_id' => $validated['category_id'],
                'product_code' => $temporaryCode,
                'name' => $validated['name'],
                'unit' => $validated['unit'],
                'purchase_price' => $validated['purchase_price'],
                'selling_price' => $validated['selling_price'],
                'description' => $validated['description'] ?? null,
                'image' => $imagePath,
                'status' => (int) ($validated['status'] ?? 1),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence = $productId;
            do {
                $productCode = 'SP'.str_pad((string) $sequence++, 4, '0', STR_PAD_LEFT);
            } while (DB::table('products')->where('product_code', $productCode)->where('id', '<>', $productId)->exists());

            DB::table('products')->where('id', $productId)->update(['product_code' => $productCode]);
        });

        return redirect()->route('products.index')
            ->with('success', 'Thêm sản phẩm thành công!');
    }

    // Xử lý cập nhật sản phẩm
    public function update(Request $request, int $id)
    {
        $product = DB::table('products')->find($id);

        if (! $product) {
            abort(404);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|max:150',
            'unit' => 'required|max:30',
            'purchase_price' => 'required|numeric|min:0|max:9999999999999.99',
            'selling_price' => 'required|numeric|min:0|max:9999999999999.99',
            'description' => 'nullable|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'nullable|in:0,1',
        ], [
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'name.required' => 'Vui lòng nhập tên sản phẩm.',
            'unit.required' => 'Vui lòng nhập đơn vị tính.',
            'purchase_price.required' => 'Vui lòng nhập giá nhập.',
            'selling_price.required' => 'Vui lòng nhập giá bán.',
            'image.image' => 'File tải lên phải là hình ảnh.',
            'image.mimes' => 'Ảnh phải có định dạng JPG, JPEG, PNG hoặc WEBP.',
            'image.max' => 'Dung lượng ảnh tối đa là 2MB.',
        ]);

        $imagePath = $product->image;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');

            if ($imagePath === false) {
                throw new RuntimeException('Không thể lưu hình ảnh sản phẩm.');
            }
        }

        DB::table('products')
            ->where('id', $id)
            ->update([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'unit' => $validated['unit'],
                'purchase_price' => $validated['purchase_price'],
                'selling_price' => $validated['selling_price'],
                'description' => $validated['description'] ?? null,
                'image' => $imagePath,
                'status' => (int) ($validated['status'] ?? $product->status),
                'updated_at' => now(),
            ]);

        if ($request->hasFile('image') && $product->image) {
            if (! Storage::disk('public')->delete($product->image)) {
                throw new RuntimeException('Đã cập nhật sản phẩm nhưng không thể xóa hình ảnh cũ.');
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Cập nhật sản phẩm thành công!');
    }

    public function updateStatus(Request $request, int $id)
    {
        $product = DB::table('products')->find($id);

        if (! $product) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => 'required|in:0,1',
        ]);
        $status = (int) $validated['status'];

        DB::table('products')->where('id', $id)->update([
            'status' => $status,
            'updated_at' => now(),
        ]);

        return redirect()->route('products.show', $id)
            ->with('success', $status === 1
                ? 'Đã mở kinh doanh sản phẩm.'
                : 'Đã ngừng kinh doanh sản phẩm. Sản phẩm vẫn được giữ lại trong lịch sử kho.');
    }

    // Xử lý xóa sản phẩm
    public function destroy(int $id)
    {
        $product = DB::table('products')->find($id);

        if (! $product) {
            abort(404);
        }

        if (
            DB::table('inventory_transaction_items')->where('product_id', $id)->exists()
            || DB::table('reorder_recommendations')->where('product_id', $id)->exists()
        ) {
            return redirect()->route('products.index')
                ->with('error', 'Không thể xóa sản phẩm đã có lịch sử nhập/xuất hoặc đề xuất DSS.');
        }

        DB::table('products')->where('id', $id)->delete();

        if ($product->image) {
            if (! Storage::disk('public')->delete($product->image)) {
                throw new RuntimeException('Đã xóa sản phẩm nhưng không thể xóa hình ảnh trên bộ nhớ.');
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Xóa sản phẩm thành công!');
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
