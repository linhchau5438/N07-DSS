<?php

namespace App\Console\Commands;

use App\Services\ReorderRecommendationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateDemoData extends Command
{
    protected $signature = 'dss:demo {--days=183 : Số ngày lịch sử, tối thiểu 37}';

    protected $description = 'Tạo 20 sản phẩm DEMO và lịch sử mô phỏng, không sửa sản phẩm thật';

    public function handle(): int
    {
        $days = filter_var($this->option('days'), FILTER_VALIDATE_INT);
        if ($days === false || $days < 37 || $days > 365) {
            $this->error('Số ngày phải nằm trong 37–365.');

            return self::FAILURE;
        }
        $user = DB::table('users')->where('role', 'admin')->where('is_active', 1)->value('id');
        if (! $user) {
            $this->error('Hãy tạo admin bằng users:create-admin trước.');

            return self::FAILURE;
        }
        if (DB::table('products')->where('product_code', 'like', 'DEMO-N07-%')->exists()) {
            $this->info('Bộ DEMO-N07 đã tồn tại; không thêm trùng hoặc sửa dữ liệu.');

            return self::SUCCESS;
        }
        DB::transaction(function () use ($days, $user) {
            $category = DB::table('categories')->insertGetId(['name' => 'Dữ liệu mô phỏng N07 '.now()->format('YmdHis'), 'created_at' => now(), 'updated_at' => now()]);
            for ($n = 1; $n <= 20; $n++) {
                $product = DB::table('products')->insertGetId([
                    'category_id' => $category, 'product_code' => 'DEMO-N07-'.str_pad($n, 2, '0', STR_PAD_LEFT),
                    'name' => '[MÔ PHỎNG] '.(['Nước suối', 'Trà xanh', 'Mì gói', 'Dầu gội', 'Giấy A4'][($n - 1) % 5]).' '.$n,
                    'unit' => ['Chai', 'Chai', 'Gói', 'Chai', 'Ram'][($n - 1) % 5],
                    'purchase_price' => [3500, 6000, 3200, 78000, 56000][($n - 1) % 5],
                    'selling_price' => [5000, 8000, 4500, 95000, 65000][($n - 1) % 5],
                    'description' => 'Dữ liệu mô phỏng xác định, dùng minh họa IS402; không phải dữ liệu kinh doanh thực tế.',
                    'status' => 1, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $quantities = [];
                for ($i = 0; $i < $days; $i++) {
                    $quantities[] = match ($n % 4) {
                        0 => 8 + ($i % 7),
                        1 => 5 + intdiv($i, 10),
                        2 => max(1, 30 - intdiv($i, 8)),
                        default => $i % 7 === 0 ? 4 : 0,
                    };
                }
                $remaining = $n % 3 === 0 ? 5000 : ($n % 3 === 1 ? 5 : 150);
                $this->movement($product, $user, 'import', array_sum($quantities) + $remaining, now()->subDays($days + 1)->toDateString(), $n, 0);
                foreach ($quantities as $i => $quantity) {
                    if ($quantity > 0) {
                        $this->movement($product, $user, 'export', $quantity, now()->subDays($days - $i)->toDateString(), $n, $i + 1);
                    }
                }
                DB::table('product_reorder_settings')->insert([
                    'product_id' => $product, 'lead_time_days' => 4, 'target_days' => 7, 'safety_factor' => 1.65,
                    'ma_days' => 7, 'safety_mode' => 'manual', 'manual_safety_stock' => 20, 'pack_size' => ($n % 2 === 0 ? 12 : 1),
                    'observation_start' => now()->subDays($days)->toDateString(), 'forecast_method' => 'auto',
                    'updated_by' => $user, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });
        app(ReorderRecommendationService::class)->refreshIfNeeded($user);
        $this->info("Đã tạo 20 sản phẩm mô phỏng với {$days} ngày lịch sử đến hôm qua. Tồn khớp tổng nhập trừ xuất.");

        return self::SUCCESS;
    }

    private function movement(int $product, int $user, string $type, int $quantity, string $date, int $number, int $index): void
    {
        $id = DB::table('inventory_transactions')->insertGetId([
            'transaction_code' => "DEMO-N07-{$number}-{$index}", 'type' => $type, 'issue_purpose' => $type === 'export' ? 'consumption' : 'receipt',
            'transaction_date' => $date, 'user_id' => $user, 'total_value' => $quantity * 1000,
            'note' => 'SIMULATED: dữ liệu mô phỏng, không phản ánh kinh doanh thực tế.', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('inventory_transaction_items')->insert(['inventory_transaction_id' => $id, 'product_id' => $product, 'quantity' => $quantity, 'unit_price' => 1000, 'line_total' => $quantity * 1000, 'created_at' => now(), 'updated_at' => now()]);
    }
}
