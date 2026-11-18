<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = DB::table('categories')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->select('categories.*', DB::raw('COUNT(products.id) as products_count'))
            ->groupBy('categories.id', 'categories.name', 'categories.description', 'categories.created_at', 'categories.updated_at')
            ->orderBy('categories.name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.form', ['category' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'name.unique' => 'Tên danh mục này đã tồn tại.',
            'name.max' => 'Tên danh mục không được vượt quá 100 ký tự.',
        ]);

        DB::table('categories')->insert([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Thêm danh mục thành công!');
    }

    public function edit(int $id)
    {
        $category = DB::table('categories')->find($id);

        if (! $category) {
            abort(404);
        }

        return view('categories.form', compact('category'));
    }

    public function update(Request $request, int $id)
    {
        if (! DB::table('categories')->where('id', $id)->exists()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,'.$id,
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'name.unique' => 'Tên danh mục này đã tồn tại.',
            'name.max' => 'Tên danh mục không được vượt quá 100 ký tự.',
        ]);

        DB::table('categories')
            ->where('id', $id)
            ->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'updated_at' => now(),
            ]);

        return redirect()->route('categories.index')
            ->with('success', 'Cập nhật danh mục thành công!');
    }

    public function destroy(int $id)
    {
        $category = DB::table('categories')->where('id', $id)->first();

        if (! $category) {
            abort(404);
        }

        $productsCount = DB::table('products')
            ->where('category_id', $id)
            ->count();

        if ($productsCount > 0) {
            return redirect()->route('categories.index')
                ->with('error', "Không thể xóa danh mục \"{$category->name}\" vì vẫn còn {$productsCount} sản phẩm liên quan.");
        }

        DB::table('categories')->where('id', $id)->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Xóa danh mục thành công!');
    }
}
