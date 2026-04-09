<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
{
    // Cập nhật dòng tạo User mẫu này
    \App\Models\User::factory()->create([
        'name' => 'Hoàng Anh Admin',
        'email' => 'admin@foodxpress.com',
        'phone' => '0901234567', // Thêm SĐT mẫu
        'gender' => 'Nam',       // Thêm giới tính
        'birthday' => '2004-01-01',   // Thêm năm sinh
        'occupation' => 'Sinh viên', // Thêm nghề nghiệp
        'password' => bcrypt('123456'), // Mật khẩu mẫu
    ]);

    // Gọi thêm Seeder món ăn của bạn ở đây để chạy 1 lần là xong hết
    $this->call([
        FoodXpressSeeder::class,
    ]);
}
}
