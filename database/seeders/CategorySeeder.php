<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Cơm Trưa', 'icon' => '🍱'],
            ['name' => 'Bún & Phở', 'icon' => '🍜'],
            ['name' => 'Pizza & Pasta', 'icon' => '🍕'],
            ['name' => 'Gà Rán & Burger', 'icon' => '🍗'],
            ['name' => 'Trà Sữa & Giải Khát', 'icon' => '🧋'],
            ['name' => 'Bánh Mì & Xôi', 'icon' => '🥖'],
            ['name' => 'Ăn Vặt & Xiên Que', 'icon' => '🍢'],
            ['name' => 'Hải Sản Tươi Sống', 'icon' => '🦀'],
            ['name' => 'Cà Phê & Bánh Ngọt', 'icon' => '☕'],
            ['name' => 'Món Chay', 'icon' => '🥗'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['name' => $cat['name']],
                [
                    'slug' => Str::slug($cat['name']),
                    'is_active' => true,
                    // Nếu bạn có cột icon thì lưu, không thì bỏ qua dòng dưới
                    // 'icon' => $cat['icon'], 
                ]
            );
        }
    }
}