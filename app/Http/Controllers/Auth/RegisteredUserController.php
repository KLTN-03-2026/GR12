<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'regex:/^0[0-9]{9}$/', 'unique:'.User::class],
            'gender' => ['required', 'in:Nam,Nữ,Khác'],
            'birthday' => ['required', 'date', 'before:-15 years'], 
            'occupation' => ['required', 'in:Sinh viên,Nhân viên văn phòng,Lao động tự do,Kinh doanh,Khác'],
            'role' => ['required', 'in:customer,restaurant,shipper'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            
            // Ràng buộc cho Nhà hàng
            'restaurant_name' => $request->role === 'restaurant' ? ['required', 'string', 'max:255'] : ['nullable'],
            'house_number'    => $request->role === 'restaurant' ? ['required', 'string', 'max:255'] : ['nullable'],
            'address'         => $request->role === 'restaurant' ? ['required', 'string', 'max:255'] : ['nullable'],
            'latitude'        => $request->role === 'restaurant' ? ['required', 'numeric'] : ['nullable'],
            'longitude'       => $request->role === 'restaurant' ? ['required', 'numeric'] : ['nullable'],
            
            // Ràng buộc cho Shipper
            'id_card_number'  => $request->role === 'shipper' ? ['required', 'digits:12'] : ['nullable'],
            'license_plate'   => $request->role === 'shipper' ? ['required', 'string', 'max:20'] : ['nullable'],
        ], [
            'email.unique' => 'Email này đã tồn tại trong hệ thống.',
            'phone.unique' => 'Số điện thoại này đã được đăng ký.',
            'phone.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có đúng 10 chữ số.',
            'birthday.before' => 'Bạn phải từ 15 tuổi trở lên để tham gia FoodXpress.',
            'id_card_number.digits' => 'Số CCCD phải có đúng 12 chữ số.',
            'latitude.required' => 'Vui lòng chọn địa chỉ từ bản đồ để xác định vị trí quán.',
            'house_number.required' => 'Vui lòng nhập số nhà hoặc tên tòa nhà cụ thể.',
        ]);

        // Trạng thái tài khoản
        $status = ($request->role === 'customer') ? 'active' : 'pending';

        // Xử lý địa chỉ
        $fullAddress = $request->address;
        if ($request->role === 'restaurant' && $request->house_number) {
            $fullAddress = $request->house_number . ', ' . $request->address;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'birthday' => $request->birthday,
            'occupation' => $request->occupation,
            'role' => $request->role,
            'status' => $status,
            'restaurant_name' => $request->restaurant_name,
            'address' => $fullAddress,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'id_card_number' => $request->id_card_number,
            'license_plate' => $request->license_plate,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        // Tự động đăng nhập cho Khách hàng
        if ($user->role === 'customer') {
            $toastData = [
                'type' => 'success',
                'message' => 'Đăng ký thành công! Chào mừng bạn đến với FoodXpress. Vui lòng đăng nhập.'
            ];
        } else {
            $roleName = ($user->role === 'restaurant') ? 'Quán ăn' : 'Shipper';
            $toastData = [
                'type' => 'info',
                'message' => 'Hồ sơ ' . $roleName . ' đã được gửi! Vui lòng đợi quản trị viên phê duyệt.'
            ];
        }

        // 2. Ép tất cả về trang Login kèm thông báo Flash
        // Lưu ý: Dùng key 'success' để khớp với logic hiển thị ở Frontend mình đã làm cho bạn
        return redirect()->route('login')->with('success', $toastData['message'])->with('toast', $toastData);
    }
}