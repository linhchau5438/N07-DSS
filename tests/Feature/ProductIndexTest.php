<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_list_can_search_and_filter_by_category(): void
    {
        $this->authenticateAs('staff');
        $beverageCategoryId = $this->createCategory('Nước giải khát');
        $noodleCategoryId = $this->createCategory('Mì ăn liền');

        $this->createProduct($beverageCategoryId, 'SODA-01', 'Soda mẫu');
        $this->createProduct($noodleCategoryId, 'NOODLE-01', 'Mì mẫu');

        $response = $this->get(route('products.index', [
            'search' => 'mẫu',
            'category_id' => $beverageCategoryId,
        ]));

        $response->assertOk()
            ->assertSee('Soda mẫu')
            ->assertDontSee('Mì mẫu')
            ->assertSee('Hi, Test User')
            ->assertSee('id="live-clock"', false)
            ->assertDontSee('global-search')
            ->assertSee('images/product-placeholder.svg');

        self::assertSame(
            1,
            substr_count($response->getContent(), 'class="icon-button sidebar-toggle"'),
        );
    }

    public function test_pagination_keeps_the_search_filter(): void
    {
        $this->authenticateAs('staff');
        $categoryId = $this->createCategory('Đồ ăn');

        for ($number = 1; $number <= 15; $number++) {
            $this->createProduct(
                $categoryId,
                sprintf('TEST-%02d', $number),
                sprintf('Sản phẩm kiểm tra %02d', $number),
            );
        }

        $firstPage = $this->get(route('products.index', ['search' => 'kiểm tra']));

        $firstPage->assertOk()
            ->assertSee('Sản phẩm kiểm tra 11')
            ->assertDontSee('Sản phẩm kiểm tra 01')
            ->assertSee('pagination-nav')
            ->assertSee('Hiển thị 1–14')
            ->assertSee('trong 15 kết quả')
            ->assertSee('search=ki%E1%BB%83m%20tra');

        $secondPage = $this->get(route('products.index', [
            'search' => 'kiểm tra',
            'page' => 2,
        ]));

        $secondPage->assertOk()
            ->assertSee('Sản phẩm kiểm tra 01')
            ->assertSee('Hiển thị 15–15')
            ->assertSee('trong 15 kết quả')
            ->assertSee('aria-label="Trang trước"', false);
    }

    private function createCategory(string $name): int
    {
        return DB::table('categories')->insertGetId([
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function authenticateAs(string $role): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test-'.$role.'@example.com',
            'password' => Hash::make('password123'),
            'role' => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post(route('login.store'), [
            'email' => DB::table('users')->where('id', $userId)->value('email'),
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));
    }

    private function createProduct(int $categoryId, string $code, string $name): void
    {
        DB::table('products')->insert([
            'category_id' => $categoryId,
            'product_code' => $code,
            'name' => $name,
            'unit' => 'Cái',
            'purchase_price' => 1000,
            'selling_price' => 1500,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
