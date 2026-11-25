<?php

namespace Tests\Feature;

use App\Services\DemandForecastService;
use App\Services\ReorderCalculator;
use App\Services\ReorderRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DssCompletionTest extends TestCase
{
    use RefreshDatabase;

    private function setupProduct(): array
    {
        $user = DB::table('users')->insertGetId(['name' => 'Admin', 'email' => 'admin@test.local', 'password' => Hash::make('password123'), 'role' => 'admin']);
        $this->post('/login', ['email' => 'admin@test.local', 'password' => 'password123'])->assertRedirect();
        $category = DB::table('categories')->insertGetId(['name' => 'Demo']);
        $product = DB::table('products')->insertGetId(['category_id' => $category, 'product_code' => 'TEST01', 'name' => 'Sản phẩm kiểm thử', 'unit' => 'Hộp', 'purchase_price' => 100, 'selling_price' => 120, 'status' => 1]);

        return [$user, $product];
    }

    private function settings(int $product, int $days = 60): void
    {
        $this->put(route('dss.settings', $product), ['lead_time_days' => 4, 'target_days' => 7, 'ma_days' => 7, 'safety_mode' => 'manual', 'manual_safety_stock' => 20, 'safety_factor' => 0, 'pack_size' => 1, 'observation_start' => now()->subDays($days)->toDateString(), 'forecast_method' => 'auto'])->assertSessionHasNoErrors();
    }

    private function movement(int $product, string $type, int $quantity, string $purpose = 'sale', int $days = 1): void
    {
        $this->post(route('inventory.store', $type), ['transaction_date' => now()->subDays($days)->toDateString(), 'issue_purpose' => $purpose, 'items' => [['product_id' => $product, 'quantity' => $quantity, 'unit_price' => 100]]])->assertSessionHasNoErrors()->assertRedirect(route('inventory.index'));
    }

    public function test_missing_config_and_short_history_never_propose_orders(): void
    {
        [$user,$product] = $this->setupProduct();
        app(ReorderRecommendationService::class)->refreshIfNeeded($user);
        $this->assertDatabaseHas('reorder_recommendations', ['product_id' => $product, 'stock_status' => 'MISSING_CONFIG', 'suggested_quantity' => 0]);
        $this->settings($product, 2);
        $this->assertDatabaseHas('reorder_recommendations', ['product_id' => $product, 'stock_status' => 'INSUFFICIENT_DATA', 'suggested_quantity' => 0]);
        $this->get('/dss')->assertOk()->assertSee('Thiếu dữ liệu');
        $this->get('/dss/history')->assertOk();
    }

    public function test_damage_changes_stock_but_not_demand_and_legacy_review_is_audited(): void
    {
        [$user,$product] = $this->setupProduct();
        $this->settings($product);
        $this->movement($product, 'import', 100, 'sale', 10);
        $this->movement($product, 'export', 14, 'sale');
        $this->movement($product, 'export', 20, 'damage');
        $series = app(DemandForecastService::class)->series($product, now()->subDays(7)->toDateString(), now()->subDay()->toDateString());
        $this->assertSame(14, array_sum($series));
        $latest = DB::table('reorder_recommendations')->latest('id')->first();
        $this->assertEquals(66, $latest->on_hand);
        $this->assertSame('NORMAL', $latest->stock_status);
        $this->assertEquals(0, $latest->suggested_quantity);
        $id = DB::table('inventory_transactions')->where('issue_purpose', 'damage')->value('id');
        $this->put(route('inventory.purpose', $id), ['issue_purpose' => 'sale', 'reason' => 'Đối chiếu chứng từ'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('audit_logs', ['action' => 'REVIEW_ISSUE_PURPOSE', 'entity_id' => $id]);
    }

    public function test_adjustment_requires_reason_and_partial_receipts_do_not_double_count(): void
    {
        [$user,$product] = $this->setupProduct();
        $this->settings($product);
        $this->movement($product, 'import', 30, 'sale', 10);
        $this->movement($product, 'export', 30);
        $r = DB::table('reorder_recommendations')->latest('id')->first();
        $this->post(route('dss.decide', $r->id), ['decision' => 'approved', 'approved_quantity' => 100])->assertSessionHasErrors('decision_note');
        $this->post(route('dss.decide', $r->id), ['decision' => 'approved', 'approved_quantity' => 100, 'decision_note' => 'Dự phòng'])->assertSessionHasNoErrors();

        // Xác nhận đặt hàng thực tế với NCC
        $this->post(route('dss.order', $r->id), ['ordered_quantity' => 100, 'supplier_name' => 'NCC Ánh Dương'])->assertSessionHasNoErrors();

        $payload = ['transaction_date' => now()->toDateString(), 'items' => [['product_id' => $product, 'quantity' => 40, 'reorder_recommendation_id' => $r->id]]];
        $this->post(route('inventory.store', 'import'), $payload)->assertSessionHasNoErrors();
        $latest = DB::table('reorder_recommendations')->latest('id')->first();
        $this->assertEquals(40, $latest->on_hand);
        $this->assertEquals(60, $latest->on_order);
        $this->assertEquals(100, $latest->inventory_position);
        $payload['items'][0]['quantity'] = 61;
        $this->post(route('inventory.store', 'import'), $payload)->assertSessionHasErrors('items');
        $this->assertDatabaseHas('reorder_recommendations', ['id' => $r->id, 'received_quantity' => 40]);
        $this->get(route('inventory.create', ['type' => 'import', 'recommendation_id' => $r->id]))->assertOk();
    }

    public function test_locked_account_cannot_login_or_keep_existing_session(): void
    {
        [$user,$product] = $this->setupProduct();
        DB::table('users')->where('id', $user)->update(['is_active' => 0]);
        $this->app['auth']->forgetGuards();
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->post('/login', ['email' => 'admin@test.local', 'password' => 'password123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_forecast_run_is_saved_and_staff_cannot_train(): void
    {
        [$user,$product] = $this->setupProduct();
        $this->settings($product);
        $this->movement($product, 'import', 1000, 'sale', 20);
        for ($day = 14; $day >= 1; $day--) {
            $this->movement($product, 'export', 15 - $day, 'sale', $day);
        }
        $this->post(route('forecast.run'), ['product_id' => $product])->assertSessionHasNoErrors();
        $run = DB::table('forecast_runs')->first();
        $this->assertNotNull($run);
        $result = json_decode($run->results, true);
        $this->assertSame('linear', $result['selected']);
        $latest = DB::table('reorder_recommendations')->latest('id')->first();
        $this->assertEqualsWithDelta(15, $latest->average_daily_demand, 0.0001);
        $this->get(route('reports.bc05'))->assertOk()->assertSee('linear-trend-v1');
        DB::table('users')->where('id', $user)->update(['role' => 'staff']);
        $this->app['auth']->forgetGuards();
        $this->post(route('forecast.run'), ['product_id' => $product])->assertForbidden();
        $this->get(route('audit.index'))->assertForbidden();
    }

    public function test_sql_formulas_and_dashboard_report_use_same_reorder_state(): void
    {
        [$user, $product] = $this->setupProduct();
        $sql = app(ReorderCalculator::class)->calculateSql(13, 4, 7, 20, 70, 0, 1);
        $this->assertEquals(72, $sql['rop']);
        $this->assertEquals(93, $sql['quantity']);
        $this->assertEquals(96, app(ReorderCalculator::class)->calculateSql(13, 4, 7, 20, 70, 0, 12)['quantity']);
        $this->settings($product);
        $this->movement($product, 'import', 161, 'sale', 10);
        for ($day = 7; $day >= 1; $day--) {
            $this->movement($product, 'export', 13, 'sale', $day);
        }
        $this->assertDatabaseHas('reorder_recommendations', ['product_id' => $product, 'stock_status' => 'REORDER', 'suggested_quantity' => 93]);
        $this->get('/dashboard')->assertViewHas('reorderCount', 1);
        $this->get('/reports/bc01?stock_status=REORDER')->assertOk()->assertViewHas('rows', fn ($rows) => count($rows) === 1);
        foreach (['/reports/bc02', '/reports/bc03', '/reports/bc04', '/reports/olap?dimension=month', '/reports/olap?dimension=category', '/audit'] as $url) {
            $this->get($url)->assertOk();
        }
        DB::table('products')->where('id', $product)->update(['status' => 0]);
        $this->get('/dashboard')->assertViewHas('reorderCount', 0);
    }

    public function test_demo_is_repeatable_without_duplicate_data_and_balances_match(): void
    {
        $this->setupProduct();
        $this->artisan('dss:demo', ['--days' => 37])->assertSuccessful();
        $this->assertSame(20, DB::table('products')->where('product_code', 'like', 'DEMO-N07-%')->count());
        $before = DB::table('inventory_transactions')->count();
        $this->artisan('dss:demo', ['--days' => 37])->assertSuccessful();
        $this->assertSame($before, DB::table('inventory_transactions')->count());
        $this->assertSame(0, DB::table('v_stock_balances')->where('on_hand', '<', 0)->count());
        $product = DB::table('products')->where('product_code', 'DEMO-N07-01')->value('id');
        $this->assertEquals(5, DB::table('v_stock_balances')->where('product_id', $product)->value('on_hand'));
    }
}
