<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\User;

class FoodXpressSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Lấy ID của một quán ăn bất kỳ để gán làm chủ sở hữu món ăn
        // Nếu chưa có quán nào, bạn nên tạo thủ công trong DB hoặc tạo một User mới ở đây
        $restaurant = User::where('role', 'restaurant')->first();

        if (!$restaurant) {
            $this->command->warn('Chưa có tài khoản Quán ăn nào trong DB. Vui lòng tạo một tài khoản role="restaurant" trước!');
            return;
        }

        // 2. Tạo danh mục (Dùng updateOrCreate để không bị lỗi trùng Slug khi chạy lại)
        $cat1 = Category::updateOrCreate(
            ['slug' => 'pizza'],
            ['name' => 'Pizza', 'is_active' => true]
        );
        
        $cat2 = Category::updateOrCreate(
            ['slug' => 'do-uong'],
            ['name' => 'Đồ uống', 'is_active' => true]
        );

        // 3. Tạo Pizza Hải Sản
        $p1 = Product::updateOrCreate(
            ['slug' => 'pizza-hai-san'],
            [
                'user_id' => $restaurant->id, // QUAN TRỌNG: Gán ID chủ quán
                'category_id' => $cat1->id,
                'name' => 'Pizza Hải Sản',
                'description' => 'Tôm, mực, thanh cua kết hợp sốt Pesto đặc biệt.',
                'price' => 199000,
                'image' => 'products/pizza-hai-san.jpg', // Đường dẫn chuẩn trong storage
                'stock_quantity' => 20,
                'is_available' => true
            ]
        );

        // Thêm Option cho Pizza (Size)
        ProductOption::updateOrCreate(
            ['product_id' => $p1->id, 'option_value' => 'M'],
            ['option_name' => 'Size', 'additional_price' => 0]
        );
        
        ProductOption::updateOrCreate(
            ['product_id' => $p1->id, 'option_value' => 'L'],
            ['option_name' => 'Size', 'additional_price' => 50000]
        );

        // 4. Tạo Trà Sữa
        Product::updateOrCreate(
            ['slug' => 'tra-sua-tran-chau'],
            [
                'user_id' => $restaurant->id, // QUAN TRỌNG: Gán ID chủ quán
                'category_id' => $cat2->id,
                'name' => 'Trà Sữa Trân Châu',
                'description' => 'Trà sữa truyền thống kèm trân châu đen dai giòn.',
                'price' => 35000,
                'image' => 'products/tra-sua.jpg',
                'stock_quantity' => 100,
                'is_available' => true
            ]
        );

        $this->command->info('Đã đổ dữ liệu mẫu FoodXpress thành công!');
    }
}