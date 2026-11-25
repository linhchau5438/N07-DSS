<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DemandForecastService;
use App\Services\ReorderRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase1WarehouseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private int $adminId;
    private int $staffId;
    private int $categoryId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminId = DB::table('users')->insertGetId([
            'name' => 'Admin User',
            'email' => 'admin_test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->staffId = DB::table('users')->insertGetId([
            'name' => 'Staff User',
            'email' => 'staff_test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->categoryId = DB::table('categories')->insertGetId([
            'name' => 'Kho Tổng Hợp',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createProduct(string $code = 'SP001', ?float $purchasePrice = 10000, ?float $sellingPrice = null): int
    {
        return DB::table('products')->insertGetId([
            'category_id' => $this->categoryId,
            'product_code' => $code,
            'name' => 'Vật tư ' . $code,
            'unit' => 'Cái',
            'purchase_price' => $purchasePrice ?? 10000,
            'selling_price' => $sellingPrice,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function setupSettings(int $productId, int $lead = 4, int $target = 7, int $ss = 20, int $pack = 1, int $ma = 7): void
    {
        DB::table('product_reorder_settings')->updateOrInsert(
            ['product_id' => $productId],
            [
                'lead_time_days' => $lead,
                'target_days' => $target,
                'safety_factor' => 1.65,
                'ma_days' => $ma,
                'safety_mode' => 'manual',
                'manual_safety_stock' => $ss,
                'pack_size' => $pack,
                'observation_start' => now()->subDays(60)->toDateString(),
                'forecast_method' => 'ma',
                'updated_by' => $this->adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function recordExport(int $productId, int $quantity, string $purpose = 'consumption', int $daysAgo = 1): void
    {
        $txId = DB::table('inventory_transactions')->insertGetId([
            'transaction_code' => 'PX-' . uniqid(),
            'type' => 'export',
            'issue_purpose' => $purpose,
            'transaction_date' => now()->subDays($daysAgo)->toDateString(),
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

    private function recordImport(int $productId, int $quantity, ?int $recommendationId = null, int $daysAgo = 1): void
    {
        $txId = DB::table('inventory_transactions')->insertGetId([
            'transaction_code' => 'PN-' . uniqid(),
            'type' => 'import',
            'issue_purpose' => 'receipt',
            'transaction_date' => now()->subDays($daysAgo)->toDateString(),
            'user_id' => $this->staffId,
            'total_value' => $quantity * 10000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('inventory_transaction_items')->insert([
            'inventory_transaction_id' => $txId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => 10000,
            'line_total' => $quantity * 10000,
            'reorder_recommendation_id' => $recommendationId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test 1: Admin duyệt đề xuất nhưng On-Order KHÔNG tăng (chưa đặt NCC).
     */
    public function test_01_admin_approval_does_not_increase_on_order(): void
    {
        $product = $this->createProduct('TEST01');
        $this->setupSettings($product);
        $this->recordImport($product, 100, null, 10);
        for ($d = 7; $d >= 1; $d--) {
            $this->recordExport($product, 13, 'consumption', $d);
        }

        app(ReorderRecommendationService::class)->refreshIfNeeded($this->adminId);
        $rec = DB::table('reorder_recommendations')->where('product_id', $product)->latest('id')->first();
        $this->assertSame('pending', $rec->status);
        $this->assertGreaterThan(0, $rec->suggested_quantity);

        $this->actingAs(User::find($this->adminId));
        $this->post(route('dss.decide', $rec->id), [
            'decision' => 'approved',
            'approved_quantity' => $rec->suggested_quantity,
        ])->assertSessionHasNoErrors();

        // Kiểm tra sau khi duyệt: status là approved, nhưng On Order vẫn = 0!
        app(ReorderRecommendationService::class)->refreshIfNeeded($this->adminId);
        $after = DB::table('reorder_recommendations')->where('product_id', $product)->latest('id')->first();
        $this->assertSame('approved', $after->status);
        $this->assertSame(0, (int) $after->on_order);
        $this->assertSame((int) $after->on_hand, (int) $after->inventory_position);
    }

    /**
     * Test 2: Xác nhận đặt hàng thực tế với NCC thì On-Order MỚI tăng.
     */
    public function test_02_order_confirmation_increases_on_order(): void
    {
        $product = $this->createProduct('TEST02');
        $this->setupSettings($product);
        $this->recordImport($product, 100, null, 10);
        for ($d = 7; $d >= 1; $d--) {
            $this->recordExport($product, 13, 'consumption', $d);
        }

        app(ReorderRecommendationService::class)->refreshIfNeeded($this->adminId);
        $rec = DB::table('reorder_recommendations')->where('product_id', $product)->latest('id')->first();

        $this->actingAs(User::find($this->adminId));
        $this->post(route('dss.decide', $rec->id), [
            'decision' => 'approved',
            'approved_quantity' => 100,
            'decision_note' => 'Đồng ý nhập',
        ])->assertSessionHasNoErrors();

        // Xác nhận đặt hàng thực tế với NCC
        $this->post(route('dss.order', $rec->id), [
            'ordered_quantity' => 100,
            'supplier_name' => 'Công ty NCC Ánh Dương',
            'expected_delivery_date' => now()->addDays(4)->toDateString(),
        ])->assertSessionHasNoErrors();

        app(ReorderRecommendationService::class)->refreshIfNeeded($this->adminId);
        $after = DB::table('reorder_recommendations')->where('product_id', $product)->latest('id')->first();
        $this->assertSame('ordered', $after->status);
        $this->assertSame(100, (int) $after->ordered_quantity);
        $this->assertSame(100, (int) $after->on_order);
        $this->assertSame((int) $after->on_hand + 100, (int) $after->inventory_position);
    }

    /**
     * Test 3 & 4: Số lượng đặt thực tế nhỏ hơn số lượng duyệt (NCC chỉ đáp ứng 1 phần) và không được đặt vượt duyệt.
     */
    public function test_03_and_04_ordered_quantity_bounds_and_reason(): void
    {
        $product = $this->createProduct('TEST03');
        $this->setupSettings($product);
        $this->recordImport($product, 50, null, 10);
        $this->recordExport($product, 30, 'consumption', 1);

        app(ReorderRecommendationService::class)->refreshIfNeeded($this->adminId);
        $rec = DB::table('reorder_recommendations')->where('product_id', $product)->latest('id')->first();

        $this->actingAs(User::find($this->adminId));
        $this->post(route('dss.decide', $rec->id), [
            'decision' => 'approved',
            'approved_quantity' => 100,
            'decision_note' => 'Duyệt 100',
        ])->assertSessionHasNoErrors();

        // Đặt vượt 100 -> Phải báo lỗi validation
        $this->post(route('dss.order', $rec->id), [
            'ordered_quantity' => 120,
        ])->assertSessionHasErrors('ordered_quantity');

        // Đặt 80 (< 100) nhưng không có lý do -> Phải bắt nhập lý do
        $this->post(route('dss.order', $rec->id), [
            'ordered_quantity' => 80,
            'order_note' => '',
        ])->assertSessionHasErrors('order_note');

        // Đặt 80 có lý do NCC chỉ còn 80 -> Thành công
        $this->post(route('dss.order', $rec->id), [
            'ordered_quantity' => 80,
            'order_note' => 'NCC chỉ còn 80 đơn vị giao đợt này',
        ])->assertSessionHasNoErrors();

        $ordered = DB::table('reorder_recommendations')->find($rec->id);
        $this->assertSame('ordered', $ordered->status);
        $this->assertSame(80, (int) $ordered->ordered_quantity);
    }

    /**
     * Test 5, 6, 7: Nhập hàng một phần, nhận đủ, và chặn nhận vượt lượng đặt.
     */
    public function test_05_06_07_receipt_workflow_and_boundaries(): void
    {
        $product = $this->createProduct('TEST05');
        $this->setupSettings($product);
        $this->recordImport($product, 50, null, 10);
        $this->recordExport($product, 50, 'consumption', 1);

        app(ReorderRecommendationService::class)->refreshIfNeeded($this->adminId);
        $rec = DB::table('reorder_recommendations')->where('product_id', $product)->latest('id')->first();

        $this->actingAs(User::find($this->adminId));
        $this->post(route('dss.decide', $rec->id), [
            'decision' => 'approved',
            'approved_quantity' => 100,
            'decision_note' => 'Duyệt 100',
        ])->assertSessionHasNoErrors();

        $this->post(route('dss.order', $rec->id), [
            'ordered_quantity' => 80,
            'order_note' => 'Đặt 80',
        ])->assertSessionHasNoErrors();

        $this->actingAs(User::find($this->staffId));

        // Nhập đợt 1: nhận 30 đơn vị (nhận 1 phần)
        $payload1 = [
            'transaction_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $product, 'quantity' => 30, 'reorder_recommendation_id' => $rec->id],
            ],
        ];
        $this->post(route('inventory.store', 'import'), $payload1)->assertSessionHasNoErrors();

        $recAfter1 = DB::table('reorder_recommendations')->find($rec->id);
        $this->assertSame('partially_received', $recAfter1->status);
        $this->assertSame(30, (int) $recAfter1->received_quantity);
        $this->assertSame(50, (int) $recAfter1->on_order); // 80 - 30 = 50

        // Thử nhận 51 (vượt quá 50 còn lại) -> Bị chặn lỗi
        $payloadOver = [
            'transaction_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $product, 'quantity' => 51, 'reorder_recommendation_id' => $rec->id],
            ],
        ];
        $this->post(route('inventory.store', 'import'), $payloadOver)->assertSessionHasErrors('items');

        // Nhập nốt 50 đơn vị còn lại -> Hoàn thành (fulfilled)
        $payload2 = [
            'transaction_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $product, 'quantity' => 50, 'reorder_recommendation_id' => $rec->id],
            ],
        ];
        $this->post(route('inventory.store', 'import'), $payload2)->assertSessionHasNoErrors();

        $recAfter2 = DB::table('reorder_recommendations')->find($rec->id);
        $this->assertSame('fulfilled', $recAfter2->status);
        $this->assertSame(80, (int) $recAfter2->received_quantity);
        $this->assertSame(0, (int) $recAfter2->on_order);
    }

    /**
     * Test 8, 9, 10: Hủy đơn đặt hàng và hoàn nguyên On-Order mà không làm mất hàng đã nhận.
     */
    public function test_08_09_10_cancel_order_preserves_stock(): void
    {
        $product = $this->createProduct('TEST08');
        $this->setupSettings($product);
        $this->recordImport($product, 50, null, 10);
        $this->recordExport($product, 50, 'consumption', 1);

        app(ReorderRecommendationService::class)->refreshIfNeeded($this->adminId);
        $rec = DB::table('reorder_recommendations')->where('product_id', $product)->latest('id')->first();

        $this->actingAs(User::find($this->adminId));
        $this->post(route('dss.decide', $rec->id), ['decision' => 'approved', 'approved_quantity' => 100, 'decision_note' => 'Ok']);
        $this->post(route('dss.order', $rec->id), ['ordered_quantity' => 100]);

        // Nhận 40 đơn vị
        $this->post(route('inventory.store', 'import'), [
            'transaction_date' => now()->toDateString(),
            'items' => [['product_id' => $product, 'quantity' => 40, 'reorder_recommendation_id' => $rec->id]],
        ]);

        // Hủy 60 đơn vị còn lại do NCC bị cháy kho
        $this->post(route('dss.cancel', $rec->id), [
            'cancellation_reason' => 'NCC thông báo cháy kho, hủy phần 60 cái chưa giao',
        ])->assertSessionHasNoErrors();

        $recCancelled = DB::table('reorder_recommendations')->find($rec->id);
        $this->assertSame('cancelled', $recCancelled->status);
        $this->assertSame(40, (int) $recCancelled->received_quantity);

        // On-Order phải về 0 (vì phần còn lại đã hủy)
        app(ReorderRecommendationService::class)->refreshIfNeeded($this->adminId);
        $stock = DB::table('v_stock_balances')->where('product_id', $product)->value('on_hand');
        $this->assertSame(40, (int) $stock); // Tồn kho đã nhận 40 vẫn được bảo toàn nguyên vẹn!
    }

    /**
     * Test 11 & 12: Chống đề xuất trùng lặp khi đang có đề xuất/đơn đặt đang xử lý.
     */
    public function test_11_12_no_duplicate_proposals_when_in_progress(): void
    {
        $product = $this->createProduct('TEST11');
        $this->setupSettings($product);
        $this->recordImport($product, 10, null, 10);
        $this->recordExport($product, 10, 'consumption', 1);

        app(ReorderRecommendationService::class)->refreshIfNeeded($this->adminId);
        $rec = DB::table('reorder_recommendations')->where('product_id', $product)->latest('id')->first();

        $this->actingAs(User::find($this->adminId));
        $this->post(route('dss.decide', $rec->id), ['decision' => 'approved', 'approved_quantity' => 50, 'decision_note' => 'Duyệt 50']);

        $countBefore = DB::table('reorder_recommendations')->where('product_id', $product)->count();

        // Chạy refreshIfNeeded nhiều lần
        app(ReorderRecommendationService::class)->refreshIfNeeded($this->adminId);
        app(ReorderRecommendationService::class)->refreshIfNeeded($this->adminId);

        $countAfter = DB::table('reorder_recommendations')->where('product_id', $product)->count();
        // Không tạo thêm dòng đề xuất trùng lặp nào!
        $this->assertSame($countBefore, $countAfter);
    }

    /**
     * Test 14 & 15: Phân quyền - Staff không thể duyệt đề xuất, Guest không thể đặt hàng.
     */
    public function test_14_15_rbac_authorization(): void
    {
        $product = $this->createProduct('TEST14');
        $this->setupSettings($product);
        $this->recordImport($product, 10, null, 10);
        $this->recordExport($product, 10, 'consumption', 1);

        app(ReorderRecommendationService::class)->refreshIfNeeded($this->adminId);
        $rec = DB::table('reorder_recommendations')->where('product_id', $product)->latest('id')->first();

        // Staff thử duyệt -> 403 Forbidden
        $this->actingAs(User::find($this->staffId));
        $this->post(route('dss.decide', $rec->id), ['decision' => 'approved'])->assertForbidden();

        // Guest thử xác nhận đặt hàng -> Redirect to login
        auth()->logout();
        $this->post(route('dss.order', $rec->id), ['ordered_quantity' => 10])->assertRedirect(route('login'));
    }

    /**
     * Test 17: Tạo sản phẩm không bắt buộc selling_price (kho nội bộ thuần túy).
     */
    public function test_17_create_product_without_selling_price(): void
    {
        $this->actingAs(User::find($this->adminId));
        $this->post(route('products.store'), [
            'category_id' => $this->categoryId,
            'name' => 'Vật tư tiêu hao nội bộ',
            'unit' => 'Bao',
            'purchase_price' => 45000,
            // selling_price bỏ trống
        ])->assertSessionHasNoErrors()->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Vật tư tiêu hao nội bộ',
            'selling_price' => null,
            'purchase_price' => 45000,
        ]);
    }

    /**
     * Test 18: Xuất kho quản lý số lượng và mục đích, không cần đơn giá bán.
     */
    public function test_18_export_voucher_without_selling_price(): void
    {
        $product = $this->createProduct('TEST18');
        $this->recordImport($product, 100, null, 5);

        $this->actingAs(User::find($this->staffId));
        $this->post(route('inventory.store', 'export'), [
            'transaction_date' => now()->toDateString(),
            'issue_purpose' => 'consumption',
            'items' => [
                ['product_id' => $product, 'quantity' => 25], // không truyền unit_price
            ],
        ])->assertSessionHasNoErrors();

        $balance = DB::table('v_stock_balances')->where('product_id', $product)->value('on_hand');
        $this->assertSame(75, (int) $balance);
    }

    /**
     * Test 19, 20, 21, 22, 23: Phân biệt mục đích xuất - Chỉ consumption và sale được tính vào Daily Demand và MA.
     */
    public function test_19_to_23_issue_purpose_classification(): void
    {
        $product = $this->createProduct('TEST19');
        $this->recordImport($product, 500, null, 10);

        // Xuất tiêu thụ mới: 10 cái
        $this->recordExport($product, 10, 'consumption', 2);
        // Xuất bán lịch sử: 15 cái
        $this->recordExport($product, 15, 'sale', 2);
        // Xuất hỏng: 20 cái
        $this->recordExport($product, 20, 'damage', 2);
        // Xuất trả NCC: 30 cái
        $this->recordExport($product, 30, 'return_vendor', 2);
        // Xuất điều chuyển: 40 cái
        $this->recordExport($product, 40, 'transfer', 2);
        // Xuất kiểm kê điều chỉnh: 50 cái
        $this->recordExport($product, 50, 'adjustment', 2);

        // Tồn kho phải giảm tổng cộng 10+15+20+30+40+50 = 165
        $stock = DB::table('v_stock_balances')->where('product_id', $product)->value('on_hand');
        $this->assertSame(335, (int) $stock);

        // Nhưng Daily Demand chỉ ghi nhận consumption (10) + sale (15) = 25!
        $consumedInView = DB::table('v_daily_demand')->where('product_id', $product)->value('consumed_qty');
        $this->assertSame(25, (int) $consumedInView);

        $series = app(DemandForecastService::class)->series($product, now()->subDays(3)->toDateString(), now()->toDateString());
        $this->assertSame(25, array_sum($series));
    }
}
