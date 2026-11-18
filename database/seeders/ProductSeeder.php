<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // BIA
            [
                'category' => 'Bia',
                'code' => 'BIA001',
                'name' => 'Bia Tiger lon 330ml',
                'unit' => 'Lon',
                'purchase_price' => 12000,
                'selling_price' => 15000,
            ],
            [
                'category' => 'Bia',
                'code' => 'BIA002',
                'name' => 'Bia Heineken lon 330ml',
                'unit' => 'Lon',
                'purchase_price' => 16000,
                'selling_price' => 19000,
            ],
            [
                'category' => 'Bia',
                'code' => 'BIA003',
                'name' => 'Bia Larue lon 330ml',
                'unit' => 'Lon',
                'purchase_price' => 10000,
                'selling_price' => 13000,
            ],

            // NƯỚC NGỌT
            [
                'category' => 'Nước ngọt',
                'code' => 'NN001',
                'name' => 'Coca-Cola lon 330ml',
                'unit' => 'Lon',
                'purchase_price' => 8000,
                'selling_price' => 10000,
            ],
            [
                'category' => 'Nước ngọt',
                'code' => 'NN002',
                'name' => 'Pepsi lon 330ml',
                'unit' => 'Lon',
                'purchase_price' => 8000,
                'selling_price' => 10000,
            ],
            [
                'category' => 'Nước ngọt',
                'code' => 'NN003',
                'name' => 'Sting đỏ chai 330ml',
                'unit' => 'Chai',
                'purchase_price' => 8000,
                'selling_price' => 10000,
            ],

            // NƯỚC SUỐI
            [
                'category' => 'Nước suối',
                'code' => 'NS001',
                'name' => 'Aquafina chai 500ml',
                'unit' => 'Chai',
                'purchase_price' => 4000,
                'selling_price' => 6000,
            ],
            [
                'category' => 'Nước suối',
                'code' => 'NS002',
                'name' => 'Lavie chai 500ml',
                'unit' => 'Chai',
                'purchase_price' => 4000,
                'selling_price' => 6000,
            ],

            // MÌ ĂN LIỀN
            [
                'category' => 'Mì ăn liền',
                'code' => 'MI001',
                'name' => 'Mì Hảo Hảo tôm chua cay',
                'unit' => 'Gói',
                'purchase_price' => 3500,
                'selling_price' => 5000,
            ],
            [
                'category' => 'Mì ăn liền',
                'code' => 'MI002',
                'name' => 'Mì Omachi sườn hầm ngũ quả',
                'unit' => 'Gói',
                'purchase_price' => 6000,
                'selling_price' => 8000,
            ],
            [
                'category' => 'Mì ăn liền',
                'code' => 'MI003',
                'name' => 'Mì Kokomi 90',
                'unit' => 'Gói',
                'purchase_price' => 3000,
                'selling_price' => 4500,
            ],

            // GIA VỊ
            [
                'category' => 'Gia vị',
                'code' => 'GV001',
                'name' => 'Nước mắm Nam Ngư 500ml',
                'unit' => 'Chai',
                'purchase_price' => 18000,
                'selling_price' => 22000,
            ],
            [
                'category' => 'Gia vị',
                'code' => 'GV002',
                'name' => 'Dầu ăn Tường An 1 lít',
                'unit' => 'Chai',
                'purchase_price' => 38000,
                'selling_price' => 45000,
            ],
            [
                'category' => 'Gia vị',
                'code' => 'GV003',
                'name' => 'Hạt nêm Knorr 400g',
                'unit' => 'Gói',
                'purchase_price' => 28000,
                'selling_price' => 33000,
            ],

            // BÁNH KẸO
            [
                'category' => 'Bánh kẹo',
                'code' => 'BK001',
                'name' => 'Bánh Oreo socola',
                'unit' => 'Gói',
                'purchase_price' => 9000,
                'selling_price' => 12000,
            ],
            [
                'category' => 'Bánh kẹo',
                'code' => 'BK002',
                'name' => 'Bánh Chocopie hộp',
                'unit' => 'Hộp',
                'purchase_price' => 35000,
                'selling_price' => 42000,
            ],

            // SỮA
            [
                'category' => 'Sữa',
                'code' => 'SUA001',
                'name' => 'Sữa tươi Vinamilk 180ml',
                'unit' => 'Hộp',
                'purchase_price' => 7000,
                'selling_price' => 9000,
            ],
            [
                'category' => 'Sữa',
                'code' => 'SUA002',
                'name' => 'Sữa đặc Ông Thọ 380g',
                'unit' => 'Lon',
                'purchase_price' => 26000,
                'selling_price' => 31000,
            ],

            // ĐỒ HỘP
            [
                'category' => 'Đồ hộp',
                'code' => 'DH001',
                'name' => 'Cá hộp ba cô gái',
                'unit' => 'Hộp',
                'purchase_price' => 18000,
                'selling_price' => 23000,
            ],
            [
                'category' => 'Đồ hộp',
                'code' => 'DH002',
                'name' => 'Pate gan hộp',
                'unit' => 'Hộp',
                'purchase_price' => 15000,
                'selling_price' => 19000,
            ],

            // ĐỒ DÙNG CÁ NHÂN
            [
                'category' => 'Đồ dùng cá nhân',
                'code' => 'CN001',
                'name' => 'Kem đánh răng P/S',
                'unit' => 'Tuýp',
                'purchase_price' => 18000,
                'selling_price' => 23000,
            ],
            [
                'category' => 'Đồ dùng cá nhân',
                'code' => 'CN002',
                'name' => 'Dầu gội Sunsilk',
                'unit' => 'Chai',
                'purchase_price' => 45000,
                'selling_price' => 55000,
            ],

            // ĐỒ DÙNG GIA ĐÌNH
            [
                'category' => 'Đồ dùng gia đình',
                'code' => 'GD001',
                'name' => 'Nước rửa chén Sunlight',
                'unit' => 'Chai',
                'purchase_price' => 22000,
                'selling_price' => 27000,
            ],
            [
                'category' => 'Đồ dùng gia đình',
                'code' => 'GD002',
                'name' => 'Bột giặt OMO',
                'unit' => 'Gói',
                'purchase_price' => 35000,
                'selling_price' => 42000,
            ],
        ];

        foreach ($products as $product) {

            $categoryId = DB::table('categories')
                ->where('name', $product['category'])
                ->value('id');

            if (!$categoryId) {
                throw new \Exception(
                    'Không tìm thấy danh mục: ' . $product['category']
                );
            }

            DB::table('products')->updateOrInsert(
                ['product_code' => $product['code']],
                [
                    'category_id' => $categoryId,
                    'name' => $product['name'],
                    'unit' => $product['unit'],
                    'purchase_price' => $product['purchase_price'],
                    'selling_price' => $product['selling_price'],
                    'description' => null,
                    'image' => null,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}