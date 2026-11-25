<?php

namespace App\Console\Commands;

use App\Services\ReorderRecommendationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDemoData extends Command
{
    protected $signature = 'dss:demo-clean {--force : Bỏ qua bước xác nhận}';

    protected $description = 'Dọn dẹp sạch toàn bộ dữ liệu mô phỏng DEMO-N07 mà không làm ảnh hưởng đến dữ liệu thật';

    public function handle(): int
    {
        $demoProductIds = DB::table('products')
            ->where('product_code', 'like', 'DEMO-N07-%')
            ->pluck('id')
            ->all();

        if (empty($demoProductIds)) {
            $this->info('Không tìm thấy sản phẩm mô phỏng (DEMO-N07) nào trong hệ thống.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Bạn có chắc chắn muốn dọn sạch ' . count($demoProductIds) . ' sản phẩm mô phỏng DEMO-N07 và toàn bộ lịch sử giao dịch liên quan?')) {
            $this->info('Đã hủy thao tác.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($demoProductIds) {
            // 1. Tìm các giao dịch liên quan
            $txIds = DB::table('inventory_transaction_items')
                ->whereIn('product_id', $demoProductIds)
                ->pluck('inventory_transaction_id')
                ->unique()
                ->all();

            // 2. Xóa các mục giao dịch và giao dịch demo
            DB::table('inventory_transaction_items')->whereIn('product_id', $demoProductIds)->delete();
            if (! empty($txIds)) {
                DB::table('inventory_transactions')->whereIn('id', $txIds)->delete();
            }

            // 3. Xóa đề xuất, dự báo, cài đặt liên quan
            DB::table('reorder_recommendations')->whereIn('product_id', $demoProductIds)->delete();
            DB::table('forecast_runs')->whereIn('product_id', $demoProductIds)->delete();
            DB::table('product_reorder_settings')->whereIn('product_id', $demoProductIds)->delete();

            // 4. Tìm các danh mục demo để xóa nếu trống
            $categoryIds = DB::table('products')->whereIn('id', $demoProductIds)->pluck('category_id')->unique()->all();

            // 5. Xóa sản phẩm demo
            DB::table('products')->whereIn('id', $demoProductIds)->delete();

            // 6. Xóa danh mục demo
            foreach ($categoryIds as $catId) {
                $hasOther = DB::table('products')->where('category_id', $catId)->exists();
                if (! $hasOther) {
                    DB::table('categories')->where('id', $catId)->delete();
                }
            }
        });

        $adminId = DB::table('users')->where('role', 'admin')->where('is_active', 1)->value('id');
        if ($adminId) {
            app(ReorderRecommendationService::class)->refreshIfNeeded((int) $adminId);
        }

        $this->info('Đã dọn dẹp an toàn toàn bộ ' . count($demoProductIds) . ' sản phẩm mô phỏng DEMO-N07. Kho hàng đã trở về trạng thái sạch.');

        return self::SUCCESS;
    }
}

