<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_receive_stock_and_inventory_shows_the_quantity(): void
    {
        $this->authenticateStaff();
        $productId = $this->createProduct();

        $this->post(route('inventory.store', 'import'), [
            'transaction_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $productId, 'quantity' => 25, 'unit_price' => 10000],
            ],
        ])->assertRedirect(route('inventory.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('inventory_transactions', [
            'type' => 'import',
            'total_value' => 250000,
        ]);

        $transactionId = DB::table('inventory_transactions')->value('id');
        $this->get(route('inventory.show', $transactionId))->assertOk()->assertSee('25');
        $this->get(route('products.show', $productId))->assertOk()->assertSee(route('inventory.show', $transactionId));

        $this->get(route('inventory.index'))
            ->assertOk()
            ->assertSee('25')
            ->assertSee('Đủ hàng');
    }

    public function test_staff_cannot_export_more_than_available_stock(): void
    {
        $this->authenticateStaff();
        $productId = $this->createProduct();

        $this->post(route('inventory.store', 'import'), [
            'transaction_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $productId, 'quantity' => 5, 'unit_price' => 10000],
            ],
        ])->assertRedirect(route('inventory.index'));

        $this->post(route('inventory.store', 'export'), [
            'issue_purpose' => 'sale',
            'transaction_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $productId, 'quantity' => 6, 'unit_price' => 12000],
            ],
        ])->assertSessionHasErrors('items');

        $this->assertDatabaseCount('inventory_transactions', 1);
    }

    public function test_stock_is_reduced_after_a_valid_export(): void
    {
        $this->authenticateStaff();
        $productId = $this->createProduct();

        $this->post(route('inventory.store', 'import'), [
            'transaction_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $productId, 'quantity' => 12, 'unit_price' => 10000],
            ],
        ])->assertRedirect(route('inventory.index'));

        $this->post(route('inventory.store', 'export'), [
            'issue_purpose' => 'sale',
            'transaction_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $productId, 'quantity' => 3, 'unit_price' => 12000],
            ],
        ])->assertRedirect(route('inventory.index'));

        $this->get(route('inventory.index'))->assertOk()->assertSee('9');
        $this->assertDatabaseCount('inventory_transactions', 2);
    }

    public function test_attention_filter_includes_low_and_empty_stock(): void
    {
        $this->authenticateStaff();
        $productId = $this->createProduct();

        $this->get(route('inventory.index', ['stock' => 'attention']))
            ->assertOk()
            ->assertSee('Nước mẫu')
            ->assertSee('0 Chai');

        $this->post(route('inventory.store', 'import'), [
            'transaction_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $productId, 'quantity' => 5, 'unit_price' => 10000],
            ],
        ])->assertRedirect(route('inventory.index'));

        $this->get(route('inventory.index', ['stock' => 'attention']))
            ->assertOk()
            ->assertSee('5 Chai');
    }

    private function authenticateStaff(): void
    {
        DB::table('users')->insert([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post(route('login.store'), [
            'email' => 'staff@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));
    }

    private function createProduct(): int
    {
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Đồ uống',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'product_code' => 'DRINK-01',
            'name' => 'Nước mẫu',
            'unit' => 'Chai',
            'purchase_price' => 10000,
            'selling_price' => 12000,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
