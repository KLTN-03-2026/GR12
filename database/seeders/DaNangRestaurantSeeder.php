<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DaNangRestaurantSeeder extends Seeder
{
    public function run(): void
    {
        // Đảm bảo có danh mục
        $categories = [
            ['name' => 'Món Nước & Bún', 'icon' => '🍜'],
            ['name' => 'Đặc Sản Đà Nẵng', 'icon' => '🌯'],
            ['name' => 'Hải Sản', 'icon' => '🦀'],
            ['name' => 'Cơm & Mì', 'icon' => '🍱'],
            ['name' => 'Trà Sữa & Giải Khát', 'icon' => '🥤'],
            ['name' => 'Pizza & Pasta', 'icon' => '🍕'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['name' => $cat['name']],
                ['slug' => Str::slug($cat['name']), 'icon' => $cat['icon']]
            );
        }

        $restaurants = [
            [
                'name' => 'Chủ Quán Bún Chả Cá',
                'email' => 'bunchaca@danang.com',
                'restaurant_name' => 'Bún Chả Cá Ông Tạ',
                'address' => '113A Nguyễn Chí Thanh, Hải Châu, Đà Nẵng',
                'lat' => 16.0682, 'lng' => 108.2140,
                'cat' => 'Món Nước & Bún',
                'foods' => [
                    ['name' => 'Bún Chả Cá Thập Cẩm', 'price' => 40000, 'img' => 'https://images.unsplash.com/photo-1598514982205-f36b96d1e8d4?w=500&auto=format'],
                    ['name' => 'Bún Chả Cá Ngừ', 'price' => 35000, 'img' => 'https://images.unsplash.com/photo-1598514982205-f36b96d1e8d4?w=500&auto=format'],
                    ['name' => 'Bún Chả Cá Thu', 'price' => 45000, 'img' => 'https://images.unsplash.com/photo-1598514982205-f36b96d1e8d4?w=500&auto=format'],
                    ['name' => 'Bún Sứa Thập Cẩm', 'price' => 50000, 'img' => 'https://images.unsplash.com/photo-1598514982205-f36b96d1e8d4?w=500&auto=format'],
                    ['name' => 'Bún Riêu Cua Biển', 'price' => 45000, 'img' => 'https://images.unsplash.com/photo-1598514982205-f36b96d1e8d4?w=500&auto=format'],
                ],
                'toppings' => [
                    ['name' => 'Topping', 'value' => 'Thêm chả cá', 'price' => 10000],
                    ['name' => 'Topping', 'value' => 'Thêm sứa', 'price' => 15000],
                    ['name' => 'Size', 'value' => 'Tô Lớn', 'price' => 10000],
                ]
            ],
            [
                'name' => 'Bà Mua Mì Quảng',
                'email' => 'miquangbamua@danang.com',
                'restaurant_name' => 'Mì Quảng Bà Mua',
                'address' => '95A Nguyễn Tri Phương, Thanh Khê, Đà Nẵng',
                'lat' => 16.0592, 'lng' => 108.2173,
                'cat' => 'Đặc Sản Đà Nẵng',
                'foods' => [
                    ['name' => 'Mì Quảng Gà Ta', 'price' => 45000, 'img' => 'https://images.unsplash.com/photo-1555126634-323283e090fa?w=500&auto=format'],
                    ['name' => 'Mì Quảng Ếch', 'price' => 55000, 'img' => 'https://images.unsplash.com/photo-1555126634-323283e090fa?w=500&auto=format'],
                    ['name' => 'Mì Quảng Tôm Thịt', 'price' => 40000, 'img' => 'https://images.unsplash.com/photo-1555126634-323283e090fa?w=500&auto=format'],
                    ['name' => 'Mì Quảng Bò', 'price' => 50000, 'img' => 'https://images.unsplash.com/photo-1555126634-323283e090fa?w=500&auto=format'],
                    ['name' => 'Mì Quảng Lươn', 'price' => 55000, 'img' => 'https://images.unsplash.com/photo-1555126634-323283e090fa?w=500&auto=format'],
                ],
                'toppings' => [
                    ['name' => 'Topping', 'value' => 'Thêm bánh tráng', 'price' => 5000],
                    ['name' => 'Topping', 'value' => 'Thêm trứng cút', 'price' => 5000],
                    ['name' => 'Topping', 'value' => 'Thêm thịt', 'price' => 15000],
                ]
            ],
            [
                'name' => 'Trần Bánh Tráng',
                'email' => 'quanstran@danang.com',
                'restaurant_name' => 'Bánh Tráng Cuốn Thịt Heo Trần',
                'address' => '4 Lê Duẩn, Hải Châu, Đà Nẵng',
                'lat' => 16.0715, 'lng' => 108.2205,
                'cat' => 'Đặc Sản Đà Nẵng',
                'foods' => [
                    ['name' => 'Bánh Tráng Cuốn Thịt Heo Hai Đầu Da', 'price' => 120000, 'img' => 'https://images.unsplash.com/photo-1627308595229-7830f5c9c66e?w=500&auto=format'],
                    ['name' => 'Bánh Tráng Cuốn Thịt Heo Quay', 'price' => 130000, 'img' => 'https://images.unsplash.com/photo-1627308595229-7830f5c9c66e?w=500&auto=format'],
                    ['name' => 'Mì Quảng Phú Chiêm', 'price' => 55000, 'img' => 'https://images.unsplash.com/photo-1555126634-323283e090fa?w=500&auto=format'],
                    ['name' => 'Bún Mắm Thịt Quay', 'price' => 45000, 'img' => 'https://images.unsplash.com/photo-1598514982205-f36b96d1e8d4?w=500&auto=format'],
                    ['name' => 'Bánh Đập Mắm Nêm', 'price' => 35000, 'img' => 'https://images.unsplash.com/photo-1627308595229-7830f5c9c66e?w=500&auto=format'],
                ],
                'toppings' => [
                    ['name' => 'Topping', 'value' => 'Thêm thịt', 'price' => 50000],
                    ['name' => 'Topping', 'value' => 'Thêm mắm nêm', 'price' => 10000],
                    ['name' => 'Topping', 'value' => 'Thêm rau sống', 'price' => 10000],
                ]
            ],
            [
                'name' => 'Năm Đảnh Quán',
                'email' => 'namdanh@danang.com',
                'restaurant_name' => 'Hải Sản Năm Đảnh',
                'address' => 'K139/H59/38 Trần Quang Khải, Thọ Quang, Sơn Trà, Đà Nẵng',
                'lat' => 16.1082, 'lng' => 108.2435,
                'cat' => 'Hải Sản',
                'foods' => [
                    ['name' => 'Nghêu Hấp Sả', 'price' => 60000, 'img' => 'https://images.unsplash.com/photo-1599084993091-1cb5c0721cc6?w=500&auto=format'],
                    ['name' => 'Mực Hấp Hành Gừng', 'price' => 60000, 'img' => 'https://images.unsplash.com/photo-1599084993091-1cb5c0721cc6?w=500&auto=format'],
                    ['name' => 'Hàu Nướng Mỡ Hành', 'price' => 60000, 'img' => 'https://images.unsplash.com/photo-1599084993091-1cb5c0721cc6?w=500&auto=format'],
                    ['name' => 'Cá Nướng Da Vàng', 'price' => 60000, 'img' => 'https://images.unsplash.com/photo-1599084993091-1cb5c0721cc6?w=500&auto=format'],
                    ['name' => 'Tôm Sú Nướng Mọi', 'price' => 60000, 'img' => 'https://images.unsplash.com/photo-1599084993091-1cb5c0721cc6?w=500&auto=format'],
                ],
                'toppings' => [
                    ['name' => 'Size', 'value' => 'Dĩa Lớn', 'price' => 40000],
                    ['name' => 'Topping', 'value' => 'Thêm bánh mì', 'price' => 5000],
                ]
            ],
            [
                'name' => 'Chủ Quán Nhà Đỏ',
                'email' => 'comnieu@danang.com',
                'restaurant_name' => 'Cơm Niêu Nhà Đỏ',
                'address' => '86 Nguyễn Tri Phương, Thanh Khê, Đà Nẵng',
                'lat' => 16.0515, 'lng' => 108.2115,
                'cat' => 'Cơm & Mì',
                'foods' => [
                    ['name' => 'Cơm Niêu', 'price' => 20000, 'img' => 'https://images.unsplash.com/photo-1617093727343-374698b1b08d?w=500&auto=format'],
                    ['name' => 'Cá Kho Tộ', 'price' => 75000, 'img' => 'https://images.unsplash.com/photo-1617093727343-374698b1b08d?w=500&auto=format'],
                    ['name' => 'Thịt Luộc Chấm Mắm Nêm', 'price' => 80000, 'img' => 'https://images.unsplash.com/photo-1617093727343-374698b1b08d?w=500&auto=format'],
                    ['name' => 'Canh Chua Cá Lóc', 'price' => 65000, 'img' => 'https://images.unsplash.com/photo-1617093727343-374698b1b08d?w=500&auto=format'],
                    ['name' => 'Rau Muống Xào Tỏi', 'price' => 45000, 'img' => 'https://images.unsplash.com/photo-1617093727343-374698b1b08d?w=500&auto=format'],
                ],
                'toppings' => [
                    ['name' => 'Topping', 'value' => 'Thêm cơm', 'price' => 10000],
                    ['name' => 'Size', 'value' => 'Phần Lớn', 'price' => 30000],
                ]
            ],
            [
                'name' => 'Koi Thé Đà Nẵng',
                'email' => 'koi@danang.com',
                'restaurant_name' => 'Trà Sữa Koi Thé',
                'address' => '87 Nguyễn Văn Linh, Hải Châu, Đà Nẵng',
                'lat' => 16.0645, 'lng' => 108.2195,
                'cat' => 'Trà Sữa & Giải Khát',
                'foods' => [
                    ['name' => 'Trà Sữa Trân Châu Hoàng Kim', 'price' => 65000, 'img' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=500&auto=format'],
                    ['name' => 'Trà Đen Macchiato', 'price' => 55000, 'img' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=500&auto=format'],
                    ['name' => 'Trà Xanh Koi', 'price' => 45000, 'img' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=500&auto=format'],
                    ['name' => 'Sữa Tươi Trân Châu Đường Đen', 'price' => 70000, 'img' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=500&auto=format'],
                    ['name' => 'Trà Sữa Khoai Môn', 'price' => 65000, 'img' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=500&auto=format'],
                ],
                'toppings' => [
                    ['name' => 'Size', 'value' => 'Size L', 'price' => 15000],
                    ['name' => 'Topping', 'value' => 'Trân châu hoàng kim', 'price' => 12000],
                    ['name' => 'Topping', 'value' => 'Kem Macchiato', 'price' => 15000],
                    ['name' => 'Đá', 'value' => 'Ít Đá', 'price' => 0],
                    ['name' => 'Đường', 'value' => '50% Đường', 'price' => 0],
                ]
            ],
            [
                'name' => 'Bà Thương Bún Bò',
                'email' => 'bunbobathuong@danang.com',
                'restaurant_name' => 'Bún Bò Huế Bà Thương',
                'address' => '23 Trần Quốc Toản, Hải Châu, Đà Nẵng',
                'lat' => 16.0678, 'lng' => 108.2162,
                'cat' => 'Món Nước & Bún',
                'foods' => [
                    ['name' => 'Bún Bò Tái Nạm', 'price' => 50000, 'img' => 'https://images.unsplash.com/photo-1598514982205-f36b96d1e8d4?w=500&auto=format'],
                    ['name' => 'Bún Bò Giò Heo', 'price' => 55000, 'img' => 'https://images.unsplash.com/photo-1598514982205-f36b96d1e8d4?w=500&auto=format'],
                    ['name' => 'Bún Bò Chả Cua', 'price' => 50000, 'img' => 'https://images.unsplash.com/photo-1598514982205-f36b96d1e8d4?w=500&auto=format'],
                    ['name' => 'Bún Bò Thập Cẩm Đặc Biệt', 'price' => 70000, 'img' => 'https://images.unsplash.com/photo-1598514982205-f36b96d1e8d4?w=500&auto=format'],
                    ['name' => 'Bún Bò Sườn', 'price' => 60000, 'img' => 'https://images.unsplash.com/photo-1598514982205-f36b96d1e8d4?w=500&auto=format'],
                ],
                'toppings' => [
                    ['name' => 'Topping', 'value' => 'Thêm thịt bò', 'price' => 20000],
                    ['name' => 'Topping', 'value' => 'Thêm chả cua', 'price' => 15000],
                    ['name' => 'Topping', 'value' => 'Thêm giò', 'price' => 20000],
                    ['name' => 'Size', 'value' => 'Tô Đặc Biệt', 'price' => 15000],
                ]
            ],
            [
                'name' => 'Chủ Quán Cơm Gà',
                'email' => 'comgahoian@danang.com',
                'restaurant_name' => 'Cơm Gà Xối Mỡ Hội An',
                'address' => '253 Hồ Nghinh, Sơn Trà, Đà Nẵng',
                'lat' => 16.0655, 'lng' => 108.2230,
                'cat' => 'Cơm & Mì',
                'foods' => [
                    ['name' => 'Cơm Gà Xối Mỡ', 'price' => 55000, 'img' => 'https://images.unsplash.com/photo-1617093727343-374698b1b08d?w=500&auto=format'],
                    ['name' => 'Cơm Gà Luộc Chặt', 'price' => 60000, 'img' => 'https://images.unsplash.com/photo-1617093727343-374698b1b08d?w=500&auto=format'],
                    ['name' => 'Cơm Gà Xé Phay', 'price' => 50000, 'img' => 'https://images.unsplash.com/photo-1617093727343-374698b1b08d?w=500&auto=format'],
                    ['name' => 'Gỏi Gà Hành Tây', 'price' => 80000, 'img' => 'https://images.unsplash.com/photo-1617093727343-374698b1b08d?w=500&auto=format'],
                    ['name' => 'Lòng Gà Xào Đậu Cu-ve', 'price' => 45000, 'img' => 'https://images.unsplash.com/photo-1617093727343-374698b1b08d?w=500&auto=format'],
                ],
                'toppings' => [
                    ['name' => 'Topping', 'value' => 'Thêm đùi gà', 'price' => 30000],
                    ['name' => 'Topping', 'value' => 'Thêm trứng ốp la', 'price' => 10000],
                    ['name' => 'Topping', 'value' => 'Thêm cơm', 'price' => 10000],
                ]
            ],
            [
                'name' => 'Bà Dưỡng Bánh Xèo',
                'email' => 'banhxeobaduong@danang.com',
                'restaurant_name' => 'Bánh Xèo Bà Dưỡng',
                'address' => 'K280/23 Hoàng Diệu, Hải Châu, Đà Nẵng',
                'lat' => 16.0601, 'lng' => 108.2154,
                'cat' => 'Đặc Sản Đà Nẵng',
                'foods' => [
                    ['name' => 'Bánh Xèo Đặc Biệt', 'price' => 65000, 'img' => 'https://images.unsplash.com/photo-1627308595229-7830f5c9c66e?w=500&auto=format'],
                    ['name' => 'Nem Lụi Nướng', 'price' => 7000, 'img' => 'https://images.unsplash.com/photo-1627308595229-7830f5c9c66e?w=500&auto=format'],
                    ['name' => 'Thịt Bò Nướng Lá Lốt', 'price' => 9000, 'img' => 'https://images.unsplash.com/photo-1627308595229-7830f5c9c66e?w=500&auto=format'],
                    ['name' => 'Bún Thịt Nướng', 'price' => 40000, 'img' => 'https://images.unsplash.com/photo-1598514982205-f36b96d1e8d4?w=500&auto=format'],
                    ['name' => 'Nước Mía', 'price' => 15000, 'img' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=500&auto=format'],
                ],
                'toppings' => [
                    ['name' => 'Topping', 'value' => 'Thêm tương đậu', 'price' => 5000],
                    ['name' => 'Topping', 'value' => 'Thêm rau sống', 'price' => 5000],
                    ['name' => 'Topping', 'value' => 'Thêm bánh tráng', 'price' => 5000],
                ]
            ],
            [
                'name' => '4Ps Pizza',
                'email' => 'pizza4ps@danang.com',
                'restaurant_name' => 'Pizza 4P\'s Hoàng Văn Thụ',
                'address' => '8 Hoàng Văn Thụ, Hải Châu, Đà Nẵng',
                'lat' => 16.0642, 'lng' => 108.2215,
                'cat' => 'Pizza & Pasta',
                'foods' => [
                    ['name' => 'Pizza Burrata Margherita', 'price' => 290000, 'img' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=500&auto=format'],
                    ['name' => 'Pizza Half & Half', 'price' => 280000, 'img' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=500&auto=format'],
                    ['name' => 'Mì Ý Sốt Kem Cua', 'price' => 220000, 'img' => 'https://images.unsplash.com/photo-1473093226795-af9932fe5856?w=500&auto=format'],
                    ['name' => 'Salad Kèm Phô Mai Tươi', 'price' => 150000, 'img' => 'https://images.unsplash.com/photo-1473093226795-af9932fe5856?w=500&auto=format'],
                    ['name' => 'Mì Ý Pesto Gà', 'price' => 190000, 'img' => 'https://images.unsplash.com/photo-1473093226795-af9932fe5856?w=500&auto=format'],
                ],
                'toppings' => [
                    ['name' => 'Size', 'value' => 'Size L', 'price' => 60000],
                    ['name' => 'Topping', 'value' => 'Thêm phô mai', 'price' => 45000],
                    ['name' => 'Topping', 'value' => 'Thêm nấm', 'price' => 30000],
                    ['name' => 'Topping', 'value' => 'Thêm hải sản', 'price' => 80000],
                ]
            ],
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
                    'birthday' => '1990-01-01',
                    'occupation' => 'Kinh doanh',
                ]
            );

            $catRecord = Category::where('name', $resData['cat'])->first();

            foreach ($resData['foods'] as $food) {
                $product = Product::updateOrCreate(
                    ['name' => $food['name'], 'user_id' => $user->id],
                    [
                        'slug' => Str::slug($food['name']) . '-' . rand(100, 9999),
                        'price' => $food['price'],
                        'description' => 'Món ăn đặc sắc mang đậm hương vị tại ' . $resData['restaurant_name'],
                        'category_id' => $catRecord->id,
                        'image' => $food['img'], 
                        'is_available' => true,
                        'stock_quantity' => rand(50, 200),
                    ]
                );

                // Add Toppings for each product
                if (isset($resData['toppings'])) {
                    foreach ($resData['toppings'] as $topping) {
                        DB::table('product_options')->updateOrInsert(
                            [
                                'product_id' => $product->id,
                                'option_name' => $topping['name'],
                                'option_value' => $topping['value'],
                            ],
                            [
                                'additional_price' => $topping['price'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            }
        }
    }
}
