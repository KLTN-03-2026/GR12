<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FoodXpressSeeder extends Seeder
{
    public function run(): void
    {
        // 1. TẠO DANH MỤC (KÈM ICON)
        $categories = [
            ['name' => 'Pizza & Pasta', 'icon' => '🍕'],
            ['name' => 'Ăn Vặt & Xiên Que', 'icon' => '🍢'],
            ['name' => 'Trà Sữa & Giải Khát', 'icon' => '🥤'],
            ['name' => 'Cơm & Mì', 'icon' => '🍱'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['name' => $cat['name']],
                ['slug' => Str::slug($cat['name']), 'icon' => $cat['icon']]
            );
        }

        // 2. TẠO QUÁN ĂN VỚI ẢNH MÓN ĂN THỰC TẾ (Link Unsplash)
        $restaurants = [
            [
                'name' => 'Hoàng Anh Boss',
                'email' => 'quanpizza@gmail.com',
                'restaurant_name' => 'Pizza FoodXpress Đà Nẵng',
                'address' => '254 Nguyễn Văn Linh, Đà Nẵng',
                'lat' => 16.0610, 'lng' => 108.2158,
                'foods' => [
                    [
                        'name' => 'Pizza Hải Sản Pesto', 
                        'price' => 199000, 
                        'cat' => 'Pizza & Pasta',
                        'img' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=500&auto=format'
                    ],
                    [
                        'name' => 'Mì Ý Bò Bằm Premium', 
                        'price' => 129000, 
                        'cat' => 'Pizza & Pasta',
                        'img' => 'https://images.unsplash.com/photo-1473093226795-af9932fe5856?w=500&auto=format'
                    ],
                ]
            ],
            [
                'name' => 'Lee Min Hooo',
                'email' => 'quancay@gmail.com',
                'restaurant_name' => 'Bún Đậu Mắm Tôm Lee Min Hooo',
                'address' => '126 Phan Châu Trinh, Đà Nẵng',
                'lat' => 16.0655, 'lng' => 108.2210,
                'foods' => [
                    [
                        'name' => 'Mẹt Bún Đậu Thập Cẩm', 
                        'price' => 55000, 
                        'cat' => 'Ăn Vặt & Xiên Que',
                        'img' => 'https://images.unsplash.com/photo-1567103473219-daec819441c2?w=500&auto=format'
                    ],
                    [
                        'name' => 'Trà Chanh Giã Tay', 
                        'price' => 25000, 
                        'cat' => 'Trà Sữa & Giải Khát',
                        'img' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=500&auto=format'
                    ],
                ]
            ]
        ];

        foreach ($restaurants as $resData) {
            $user = User::updateOrCreate(
                ['email' => $resData['email']],
                [
                    'name' => $resData['name'],
                    'restaurant_name' => $resData['restaurant_name'],
                    'address' => $resData['address'],
                    'latitude' => $resData['lat'],
                    'longitude' => $resData['lng'],
                    'password' => Hash::make('password'),
                    'role' => 'restaurant',
                    'status' => 'active',
                    'phone' => '0905' . rand(111111, 999999),
                    'gender' => 'Nam',
                    'birthday' => '1995-01-01',
                    'occupation' => 'Kinh doanh',
                ]
            );

            foreach ($resData['foods'] as $food) {
                $catRecord = Category::where('name', $food['cat'])->first();

                Product::updateOrCreate(
                    ['name' => $food['name'], 'user_id' => $user->id],
                    [
                        'slug' => Str::slug($food['name']) . '-' . rand(100, 999),
                        'price' => $food['price'],
                        'description' => 'Món ăn đặc sắc mang đậm hương vị FoodXpress từ ' . $resData['restaurant_name'],
                        'category_id' => $catRecord->id,
                        'image' => $food['img'], // Lưu trực tiếp URL ảnh
                        'is_available' => true,
                        'stock_quantity' => 99,
                    ]
                );
            }
        }

        // Tạo 1 Khách hàng để test
        User::updateOrCreate(
            ['email' => 'khachhang@gmail.com'],
            [
                'name' => 'Hoàng Anh Customer',
                'phone' => '0905000111',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'status' => 'active',
                'gender' => 'Nam',
                'birthday' => '2004-01-01',
                'occupation' => 'Sinh viên',
            ]
        );
    }
}