<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Bia',
                'description' => 'Các loại bia đóng lon và đóng chai.'
            ],
            [
                'name' => 'Nước ngọt',
                'description' => 'Các loại nước giải khát có gas và không gas.'
            ],
            [
                'name' => 'Nước suối',
                'description' => 'Các loại nước uống đóng chai.'
            ],
            [
                'name' => 'Mì ăn liền',
                'description' => 'Mì tôm, mì ly và các loại mì ăn liền.'
            ],
            [
                'name' => 'Gia vị',
                'description' => 'Nước mắm, nước tương, dầu ăn, hạt nêm.'
            ],
            [
                'name' => 'Bánh kẹo',
                'description' => 'Các loại bánh, kẹo và đồ ăn vặt.'
            ],
            [
                'name' => 'Sữa',
                'description' => 'Sữa tươi, sữa đặc và các sản phẩm từ sữa.'
            ],
            [
                'name' => 'Đồ hộp',
                'description' => 'Cá hộp, thịt hộp và thực phẩm đóng hộp.'
            ],
            [
                'name' => 'Đồ dùng cá nhân',
                'description' => 'Dầu gội, kem đánh răng, xà phòng.'
            ],
            [
                'name' => 'Đồ dùng gia đình',
                'description' => 'Bột giặt, nước rửa chén, giấy vệ sinh.'
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}