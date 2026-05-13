<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key')->map(function($item) {
            return Setting::getValue($item->key);
        })->toArray();

        // Cấu hình mặc định nếu DB trống
        if (empty($settings)) {
            $settings = [
                'platform_commission' => 20, 
                'base_shipping_fee' => 15000,
                'extra_shipping_fee_per_km' => 3000,
                'base_radius_km' => 2,
            ];
        }

        return Inertia::render('Admin/Settings', [
            'settings' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'platform_commission' => 'required|numeric|min:0|max:100',
            'base_shipping_fee' => 'required|numeric|min:0',
            'extra_shipping_fee_per_km' => 'required|numeric|min:0',
            'base_radius_km' => 'required|numeric|min:0',
        ]);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => (string) $value, 
                    'type' => is_int($value) ? 'integer' : 'float',
                    'group' => 'system'
                ]
            );
        }

        return back()->with('success', 'Đã cập nhật cấu hình hệ thống thành công!');
    }
}
