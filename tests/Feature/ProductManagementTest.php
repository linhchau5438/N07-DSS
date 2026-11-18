<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_a_product(): void
    {
        $this->authenticateAdmin();
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Bánh kẹo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post(route('products.store'), [
            'category_id' => $categoryId,
            'product_code' => 'CAKE-01',
            'name' => 'Bánh mẫu',
            'unit' => 'Hộp',
            'purchase_price' => 10000,
            'selling_price' => 15000,
            'description' => 'Bánh thử nghiệm',
        ])->assertRedirect(route('products.index'))
            ->assertSessionHas('success', 'Thêm sản phẩm thành công!');

        $product = DB::table('products')->where('product_code', 'SP0001')->first();
        $this->assertNotNull($product);
        $this->assertNull($product->image);

        $this->put(route('products.update', $product->id), [
            'category_id' => $categoryId,
            'product_code' => 'CAKE-01',
            'name' => 'Bánh mẫu mới',
            'unit' => 'Hộp',
            'purchase_price' => 11000,
            'selling_price' => 16000,
            'description' => 'Mô tả đã cập nhật',
        ])->assertRedirect(route('products.index'))
            ->assertSessionHas('success', 'Cập nhật sản phẩm thành công!');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Bánh mẫu mới',
            'purchase_price' => 11000,
        ]);

        $this->get(route('products.show', $product->id))
            ->assertOk()
            ->assertSee('Bánh mẫu mới')
            ->assertSee('Lịch sử nhập xuất');

        $this->put(route('products.status', $product->id), ['status' => 0])
            ->assertRedirect(route('products.show', $product->id))
            ->assertSessionHas('success', 'Đã ngừng kinh doanh sản phẩm. Sản phẩm vẫn được giữ lại trong lịch sử kho.');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 0,
        ]);

        $this->delete(route('products.destroy', $product->id))
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('success', 'Xóa sản phẩm thành công!');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_admin_cannot_delete_a_product_with_inventory_history(): void
    {
        $this->authenticateAdmin();
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Sữa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'product_code' => 'MILK-01',
            'name' => 'Sữa mẫu',
            'unit' => 'Hộp',
            'purchase_price' => 10000,
            'selling_price' => 13000,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $transactionId = DB::table('inventory_transactions')->insertGetId([
            'transaction_code' => 'PN-TEST',
            'type' => 'import',
            'transaction_date' => now()->toDateString(),
            'total_value' => 10000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('inventory_transaction_items')->insert([
            'inventory_transaction_id' => $transactionId,
            'product_id' => $productId,
            'quantity' => 1,
            'unit_price' => 10000,
            'line_total' => 10000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->delete(route('products.destroy', $productId))
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('products', ['id' => $productId]);
    }

    private function authenticateAdmin(): void
    {
        DB::table('users')->insert([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post(route('login.store'), [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));
    }
}
