<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReorderDecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_moving_average_proposal_can_be_reviewed_and_counts_as_on_order(): void
    {
        $staffId = $this->createUser('staff', 'staff@example.com');
        $adminId = $this->createUser('admin', 'admin@example.com');
        $productId = $this->createProduct();

        $this->login('admin@example.com');
        $this->put(route('dss.settings', $productId), [
            'lead_time_days' => 2,
            'target_days' => 3,
            'safety_factor' => 0,
            'ma_days' => 30, 'safety_mode' => 'manual', 'manual_safety_stock' => 0, 'pack_size' => 1,
            'observation_start' => now()->subDays(60)->toDateString(), 'forecast_method' => 'ma',
        ])->assertRedirect(route('dss.index'));

        $this->post(route('logout'));
        $this->login('staff@example.com');

        $this->post(route('inventory.store', 'import'), [
            'transaction_date' => now()->subDay()->toDateString(),
            'items' => [
                ['product_id' => $productId, 'quantity' => 30, 'unit_price' => 10000],
            ],
        ])->assertRedirect(route('inventory.index'));

        $this->post(route('inventory.store', 'export'), [
            'issue_purpose' => 'sale',
            'transaction_date' => now()->subDay()->toDateString(),
            'items' => [
                ['product_id' => $productId, 'quantity' => 30, 'unit_price' => 12000],
            ],
        ])->assertRedirect(route('inventory.index'));

        $this->post(route('dss.generate'), ['period_days' => 30])
            ->assertRedirect(route('dss.index'));

        $recommendation = DB::table('reorder_recommendations')
            ->where('product_id', $productId)
            ->latest('id')
            ->first();

        $this->assertNotNull($recommendation);
        $this->assertEqualsWithDelta(1.0, (float) $recommendation->average_daily_demand, 0.0001);
        $this->assertSame(2, (int) $recommendation->reorder_point);
        $this->assertSame(5, (int) $recommendation->suggested_quantity);
        $this->assertSame('pending', $recommendation->status);
        $this->assertSame($staffId, (int) $recommendation->proposed_by);

        $this->post(route('logout'));
        $this->login('admin@example.com');

        $this->post(route('dss.decide', $recommendation->id), [
            'decision' => 'approved',
            'approved_quantity' => 7,
            'decision_note' => 'Đã kiểm tra với nhà cung cấp',
        ])->assertRedirect(route('dss.index'));

        $this->assertDatabaseHas('reorder_recommendations', [
            'id' => $recommendation->id,
            'status' => 'approved',
            'approved_quantity' => 7,
            'reviewed_by' => $adminId,
        ]);

        // Nghiệp vụ mới: Duyệt đề xuất chưa làm tăng On Order
        $this->post(route('dss.generate'), ['period_days' => 30])
            ->assertRedirect(route('dss.index'));
        $afterApproved = DB::table('reorder_recommendations')->where('product_id', $productId)->latest('id')->first();
        $this->assertSame(0, (int) $afterApproved->on_order);

        // Xác nhận đặt hàng thực tế với Nhà cung cấp
        $this->post(route('dss.order', $recommendation->id), [
            'ordered_quantity' => 7,
            'supplier_name' => 'Nhà cung cấp Ánh Dương',
        ])->assertRedirect(route('dss.index'));

        $this->post(route('dss.generate'), ['period_days' => 30])
            ->assertRedirect(route('dss.index'));

        $latest = DB::table('reorder_recommendations')
            ->where('product_id', $productId)
            ->latest('id')
            ->first();

        $this->assertSame(7, (int) $latest->on_order);
        $this->assertSame(7, (int) $latest->inventory_position);

        $this->post(route('inventory.store', 'import'), [
            'transaction_date' => now()->subDay()->toDateString(),
            'items' => [
                [
                    'product_id' => $productId,
                    'quantity' => 7,
                    'unit_price' => 10000,
                    'reorder_recommendation_id' => $recommendation->id,
                ],
            ],
        ])->assertRedirect(route('inventory.index'));

        $this->assertDatabaseHas('reorder_recommendations', [
            'id' => $recommendation->id,
            'status' => 'fulfilled',
            'approved_quantity' => 7,
            'ordered_quantity' => 7,
            'received_quantity' => 7,
        ]);

        $this->post(route('dss.generate'), ['period_days' => 30])
            ->assertRedirect(route('dss.index'));

        $latest = DB::table('reorder_recommendations')
            ->where('product_id', $productId)
            ->latest('id')
            ->first();

        $this->assertSame(0, (int) $latest->on_order);
        $this->assertSame(7, (int) $latest->on_hand);
        $this->assertSame(7, (int) $latest->inventory_position);
    }

    private function createUser(string $role, string $email): int
    {
        return DB::table('users')->insertGetId([
            'name' => ucfirst($role).' User',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function login(string $email): void
    {
        $this->post(route('login.store'), [
            'email' => $email,
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));
    }

    private function createProduct(): int
    {
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Mì ăn liền',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'product_code' => 'NOODLE-01',
            'name' => 'Mì kiểm tra',
            'unit' => 'Gói',
            'purchase_price' => 10000,
            'selling_price' => 12000,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
