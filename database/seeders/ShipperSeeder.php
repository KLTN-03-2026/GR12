<?php

namespace Database\Seeders;

use App\Models\Shipper;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ShipperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo user shipper mẫu
        $user = User::create([
            'name' => 'Nguyễn Văn Shipper',
            'email' => 'shipper@foodxpress.com',
            'password' => Hash::make('password'),
            'phone' => '0987654321',
            'role' => 'shipper',
            'status' => 'active',
            'license_plate' => '29A-12345',
            'gender' => 'Nam',
            'birthday' => '1990-01-01',
            'occupation' => 'Shipper',
        ]);

        Shipper::create([
            'user_id' => $user->id,
            'status' => 'available',
            'current_latitude' => 16.0544, // Đà Nẵng
            'current_longitude' => 108.2022,
            'vehicle_type' => 'xe máy',
            'rating' => 4.8,
            'total_deliveries' => 25,
        ]);

        // Tạo thêm shipper khác
        $user2 = User::create([
            'name' => 'Trần Thị Shipper',
            'email' => 'shipper2@foodxpress.com',
            'password' => Hash::make('password'),
            'phone' => '0987654322',
            'role' => 'shipper',
            'status' => 'active',
            'license_plate' => '29A-67890',
            'gender' => 'Nữ',
            'birthday' => '1992-05-15',
            'occupation' => 'Shipper',
        ]);

        Shipper::create([
            'user_id' => $user2->id,
            'status' => 'available',
            'current_latitude' => 16.0471,
            'current_longitude' => 108.2062,
            'vehicle_type' => 'ô tô',
            'rating' => 4.9,
            'total_deliveries' => 45,
        ]);
    }
}
