<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DemandForecastService;
use App\Services\ReorderRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase2DssOptimizationTest extends TestCase
{
    use RefreshDatabase;

    private int $adminId;
    private int $staffId;
    private int $categoryId;
    private User $adminUser;
    private User $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminId = DB::table('users')->insertGetId([
            'name' => 'Admin N07',
            'email' => 'admin_p2@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->staffId = DB::table('users')->insertGetId([
            'name' => 'Staff N07',
            'email' => 'staff_p2@example.com',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->adminUser = User::find($this->adminId);
        $this->staffUser = User::find($this->staffId);

        $this->categoryId = DB::table('categories')->insertGetId([
            'name' => 'Vật tư Dự Án IS402',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createProduct(string $code = 'P2-01'): int
    {
        return DB::table('products')->insertGetId([
            'category_id' => $this->categoryId,
            'product_code' => $code,
            'name' => 'Vật tư tối ưu DSS ' . $code,
            'unit' => 'Hộp',
            'purchase_price' => 25000,
            'selling_price' => null,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function setupSettings(int $productId, array $overrides = []): void
    {
        $defaults = [
            'lead_time_days' => 4,
            'target_days' => 7,
            'safety_factor' => 1.65,
            'ma_days' => 7,
            'safety_mode' => 'statistical',
            'manual_safety_stock' => 0,
            'pack_size' => 1,
            'observation_start' => now()->subDays(60)->toDateString(),
            'forecast_method' => 'ma',
            'updated_by' => $this->adminId,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('product_reorder_settings')->updateOrInsert(
            ['product_id' => $productId],
            array_merge($defaults, $overrides)
        );
    }

    private function recordImport(int $productId, int $quantity, string $date, ?int $recommendationId = null): void
    {
        $txId = DB::table('inventory_transactions')->insertGetId([
            'transaction_code' => 'PN-' . uniqid(),
            'type' => 'import',
            'issue_purpose' => 'receipt',
            'transaction_date' => $date,
            'user_id' => $this->staffId,
            'total_value' => $quantity * 25000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('inventory_transaction_items')->insert([
            'inventory_transaction_id' => $txId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => 25000,
            'line_total' => $quantity * 25000,
            'reorder_recommendation_id' => $recommendationId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function recordExport(int $productId, int $quantity, string $date, string $purpose = 'consumption'): void
    {
        $txId = DB::table('inventory_transactions')->insertGetId([
            'transaction_code' => 'PX-' . uniqid(),
            'type' => 'export',
            'issue_purpose' => $purpose,
            'transaction_date' => $date,
            'user_id' => $this->staffId,
            'total_value' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('inventory_transaction_items')->insert([
            'inventory_transaction_id' => $txId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => null,
            'line_total' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test 2.1: Khi nhu cầu đều (phương sai = 0), Statistical Safety Stock tự động kích hoạt Fallback cơ sở.
     */
    public function test_statistical_safety_stock_fallback_when_variance_zero(): void
    {
        $product = $this->createProduct('P2-TEST01');
        $this->setupSettings($product, [
            'safety_mode' => 'statistical',
            'manual_safety_stock' => 0,
            'lead_time_days' => 4,
            'ma_days' => 7,
        ]);

        $this->recordImport($product, 200, now()->subDays(10)->toDateString());

        // Xuất đều chằn chặn 10 cái mỗi ngày trong 7 ngày -> variance = 0
        for ($d = 7; $d >= 1; $d--) {
            $this->recordExport($product, 10, now()->subDays($d)->toDateString(), 'consumption');
        }

        app(ReorderRecommendationService::class)->refreshIfNeeded($this->adminId);
        $rec = DB::table('reorder_recommendations')->where('product_id', $product)->latest('id')->first();

        $snapshot = json_decode($rec->calculation_snapshot, true);
        $this->assertTrue($snapshot['safety_fallback_applied'], 'Hệ thống phải ghi nhận cờ fallback đã được áp dụng.');
        $this->assertGreaterThan(0, (int) $rec->safety_stock, 'Safety stock không được bằng 0 khi có nhu cầu tiêu thụ.');
        $this->assertStringContainsString('Fallback SS', $rec->explanation);
    }

    /**
     * Test 2.1b: Cấu hình manual_safety_stock đóng vai trò ngưỡng sàn bảo vệ (Floor Boundary) cho Statistical Mode.
     */
    public function test_statistical_safety_stock_uses_manual_as_floor(): void
    {
        $product = $this->createProduct('P2-TEST02');
        $this->setupSettings($product, [
            'safety_mode' => 'statistical',
            'manual_safety_stock' => 15, // Ngưỡng sàn bảo vệ của Admin
            'lead_time_days' => 4,
            'ma_days' => 7,
        ]);

        $this->recordImport($product, 200, now()->subDays(10)->toDateString());
        for ($d = 7; $d >= 1; $d--) {
            $this->recordExport($product, 10, now()->subDays($d)->toDateString(), 'consumption');
        }

        app(ReorderRecommendationService::class)->refreshIfNeeded($this->adminId);
        $rec = DB::table('reorder_recommendations')->where('product_id', $product)->latest('id')->first();

        $this->assertGreaterThanOrEqual(15, (int) $rec->safety_stock);
    }

    /**
     * Test 2.2: Đo lường Lead Time thực tế (ngày nhận - ngày đặt) từ lịch sử đơn hàng.
     */
    public function test_actual_lead_time_computation_from_order_and_receipt_history(): void
    {
        $product = $this->createProduct('P2-TEST03');
        $this->setupSettings($product, ['lead_time_days' => 3]);

        // Tạo 1 đề xuất đã đặt hàng 6 ngày trước
        $orderedAt = now()->subDays(6);
        $recId = DB::table('reorder_recommendations')->insertGetId([
            'product_id' => $product,
            'period_start' => now()->subDays(30)->toDateString(),
            'period_end' => now()->subDays(6)->toDateString(),
            'status' => 'partially_received',
            'suggested_quantity' => 100,
            'approved_quantity' => 100,
            'ordered_quantity' => 100,
            'received_quantity' => 50,
            'ordered_at' => $orderedAt,
            'explanation' => 'Test proposal',
            'created_at' => $orderedAt,
            'updated_at' => now(),
        ]);

        // Nhận hàng thực tế 2 ngày trước -> Lead Time thực tế = 6 - 2 = 4 ngày!
        $this->recordImport($product, 50, now()->subDays(2)->toDateString(), $recId);

        $stats = app(DemandForecastService::class)->actualLeadTimeStats($product);

        $this->assertTrue($stats['has_history']);
        $this->assertSame(1, $stats['sample_count']);
        $this->assertSame(4, $stats['avg_lead_time_days']);
        $this->assertSame(4, $stats['latest_lead_time_days']);
    }

    /**
     * Test 2.2b: Admin đồng bộ Lead Time kế hoạch theo số liệu Lead Time thực tế (sync-lead-time).
     */
    public function test_sync_lead_time_updates_planned_settings(): void
    {
        $product = $this->createProduct('P2-TEST04');
        $this->setupSettings($product, ['lead_time_days' => 2]);

        $orderedAt = now()->subDays(7);
        $recId = DB::table('reorder_recommendations')->insertGetId([
            'product_id' => $product,
            'period_start' => now()->subDays(30)->toDateString(),
            'period_end' => now()->subDays(7)->toDateString(),
            'status' => 'fulfilled',
            'suggested_quantity' => 50,
            'approved_quantity' => 50,
            'ordered_quantity' => 50,
            'received_quantity' => 50,
            'ordered_at' => $orderedAt,
            'explanation' => 'Test proposal',
            'created_at' => $orderedAt,
            'updated_at' => now(),
        ]);

        // Nhận hàng 2 ngày trước -> Lead Time thực tế = 7 - 2 = 5 ngày
        $this->recordImport($product, 50, now()->subDays(2)->toDateString(), $recId);

        // Staff thử đồng bộ -> 403 Forbidden
        $this->actingAs($this->staffUser);
        $this->post(route('dss.sync-lead-time', $product))->assertForbidden();

        // Admin đồng bộ -> Thành công
        $this->actingAs($this->adminUser);
        $this->post(route('dss.sync-lead-time', $product))
            ->assertRedirect(route('dss.index'))
            ->assertSessionHas('success');

        $updatedSetting = DB::table('product_reorder_settings')->where('product_id', $product)->first();
        $this->assertSame(5, (int) $updatedSetting->lead_time_days);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'SYNC_ACTUAL_LEAD_TIME',
            'entity_type' => 'product_reorder_settings',
            'entity_id' => $product,
        ]);
    }

    /**
     * Test 2.3: Phát hiện và cảnh báo Censored Demand khi kho bị đứt hàng kéo dài.
     */
    public function test_censored_demand_detection_during_stockout_days(): void
    {
        $product = $this->createProduct('P2-TEST05');
        $this->setupSettings($product, ['ma_days' => 7]);

        // Ban đầu nhập 20 đơn vị vào 7 ngày trước
        $this->recordImport($product, 20, now()->subDays(7)->toDateString());

        // Ngày thứ 7 xuất sạch 20 đơn vị -> Tồn kho từ ngày thứ 6 đến ngày 1 bằng 0!
        $this->recordExport($product, 20, now()->subDays(7)->toDateString(), 'consumption');

        // Phân tích đứt hàng
        $analysis = app(DemandForecastService::class)->detectStockout(
            $product,
            now()->subDays(7)->toDateString(),
            now()->subDays(1)->toDateString()
        );

        $this->assertTrue($analysis['is_censored'], 'Phải phát hiện hiện tượng Censored Demand do hết hàng.');
        $this->assertGreaterThanOrEqual(5, $analysis['stockout_days']);
        $this->assertGreaterThan($analysis['observed_demand'], $analysis['adjusted_demand']);

        // Chạy DSS và kiểm tra explanation
        app(ReorderRecommendationService::class)->refreshIfNeeded($this->adminId);
        $rec = DB::table('reorder_recommendations')->where('product_id', $product)->latest('id')->first();

        $snapshot = json_decode($rec->calculation_snapshot, true);
        $this->assertTrue($snapshot['stockout_analysis']['is_censored']);
        $this->assertStringContainsString('Cảnh báo Censored Demand', $rec->explanation);
    }

    /**
     * Test 2.4: Giao diện DSS hiển thị trực quan các huy hiệu Fallback SS, Censored Demand và Lead Time.
     */
    public function test_ui_renders_badges_for_fallback_and_censored_demand(): void
    {
        $product = $this->createProduct('P2-TEST06');
        $this->setupSettings($product, [
            'safety_mode' => 'statistical',
            'manual_safety_stock' => 50,
            'ma_days' => 7,
        ]);

        $this->recordImport($product, 30, now()->subDays(7)->toDateString());
        $this->recordExport($product, 30, now()->subDays(7)->toDateString(), 'consumption');

        app(ReorderRecommendationService::class)->refreshIfNeeded($this->adminId);

        $this->actingAs($this->adminUser);
        $response = $this->get(route('dss.index'));

        $response->assertStatus(200);
        $response->assertSee('P2-TEST06');
        $response->assertSee('Cảnh báo méo nhu cầu (Censored Demand)');
        $response->assertSee('Fallback SS');
        $response->assertSee('Lead Time:');
    }
}
