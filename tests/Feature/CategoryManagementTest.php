<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_be_created_updated_and_deleted(): void
    {
        $this->authenticateAsAdmin();

        $this->post(route('categories.store'), [
            'name' => 'Nước uống',
            'description' => 'Các loại nước uống',
        ])->assertRedirect(route('categories.index'))
            ->assertSessionHas('success', 'Thêm danh mục thành công!');

        $category = DB::table('categories')->where('name', 'Nước uống')->first();

        $this->assertNotNull($category);

        $this->put(route('categories.update', $category->id), [
            'name' => 'Đồ uống',
            'description' => 'Danh mục đã cập nhật',
        ])->assertRedirect(route('categories.index'))
            ->assertSessionHas('success', 'Cập nhật danh mục thành công!');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Đồ uống',
            'description' => 'Danh mục đã cập nhật',
        ]);

        $this->delete(route('categories.destroy', $category->id))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('success', 'Xóa danh mục thành công!');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $this->authenticateAsAdmin();

        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Thực phẩm',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'category_id' => $categoryId,
            'product_code' => 'FOOD-01',
            'name' => 'Mặt hàng mẫu',
            'unit' => 'Gói',
            'purchase_price' => 1000,
            'selling_price' => 1500,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->delete(route('categories.destroy', $categoryId))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $categoryId]);
    }

    private function authenticateAsAdmin(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post(route('login.store'), [
            'email' => DB::table('users')->where('id', $userId)->value('email'),
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));
    }
}
