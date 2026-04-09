<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
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
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
        'phone' => ['required', 'regex:/^0[0-9]{9}$/', 'unique:'.User::class],
        'gender' => ['required', 'in:Nam,Nữ,Khác'],
        'birthday' => ['required', 'date', 'before:-15 years'], // Phải từ 15 tuổi trở lên
        'occupation' => ['required', 'in:Sinh viên,Nhân viên văn phòng,Lao động tự do,Kinh doanh,Khác'],
        'role' => ['required', 'in:customer,restaurant,shipper'],
        'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers()],
        
        // Ràng buộc động theo Role
        'restaurant_name' => $request->role === 'restaurant' ? ['required', 'string', 'max:255'] : ['nullable'],
        'address' => $request->role === 'restaurant' ? ['required', 'string', 'max:255'] : ['nullable'],
        'id_card_number' => $request->role === 'shipper' ? ['required', 'digits:12'] : ['nullable'],
        'license_plate' => $request->role === 'shipper' ? ['required', 'string', 'max:20'] : ['nullable'],
    ], [
        'email.unique' => 'Email này đã tồn tại trong hệ thống.',
        'phone.unique' => 'Số điện thoại này đã được đăng ký.',
        'phone.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có đúng 10 chữ số.',
        'birthday.before' => 'Bạn phải từ 15 tuổi trở lên để tham gia FoodXpress.',
        'id_card_number.digits' => 'Số CCCD phải có đúng 12 chữ số.',
    ]);

    // Trạng thái: Chỉ khách hàng mới được tự động kích hoạt
    $status = ($request->role === 'customer') ? 'active' : 'pending';

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
        'address' => $request->address,
        'id_card_number' => $request->id_card_number,
        'license_plate' => $request->license_plate,
        'password' => Hash::make($request->password),
    ]);

    event(new Registered($user));
    Auth::login($user);

    if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'customer') {
            return redirect()->route('dashboard');
        }

        // Mặc định cho Quán ăn/Shipper mới đăng ký
        return redirect()->route('wait.approval');
    }
}
