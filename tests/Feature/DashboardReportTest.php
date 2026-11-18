<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_and_reports_render_for_authenticated_staff(): void
    {
        $this->authenticateStaff();
        $productId = $this->createProduct();

        $this->createMovement($productId, 'import', now()->subDays(3)->toDateString(), 20);
        $this->createMovement($productId, 'export', now()->subDays(2)->toDateString(), 4);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Tổng mặt hàng')
            ->assertSee('Nước mẫu');

        $this->get(route('reports.bc02', [
            'from' => now()->subDays(5)->toDateString(),
            'to' => now()->toDateString(),
        ]))->assertOk()
            ->assertSee('BC02 - Báo cáo nhập - xuất - tồn')
            ->assertSee('16');

        $this->get(route('reports.olap', ['dimension' => 'product']))
            ->assertOk()
            ->assertSee('Nước mẫu');

        $this->get(route('reports.bc05', ['product_id' => $productId]))
            ->assertOk()
            ->assertSee('Phân tích nhu cầu xuất hàng')
            ->assertSee('Nước mẫu')
            ->assertSee('Moving Average')
            ->assertSee('Linear Regression')
            ->assertSee('Chưa đủ dữ liệu');
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
            'name' => 'Nước suối',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'product_code' => 'WATER-01',
            'name' => 'Nước mẫu',
            'unit' => 'Chai',
            'purchase_price' => 5000,
            'selling_price' => 7000,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createMovement(int $productId, string $type, string $date, int $quantity): void
    {
        $transactionId = DB::table('inventory_transactions')->insertGetId([
            'transaction_code' => strtoupper($type).'-'.uniqid(),
            'type' => $type,
            'issue_purpose' => $type === 'export' ? 'sale' : 'receipt',
            'transaction_date' => $date,
            'total_value' => $quantity * 5000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('inventory_transaction_items')->insert([
            'inventory_transaction_id' => $transactionId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => 5000,
            'line_total' => $quantity * 5000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
