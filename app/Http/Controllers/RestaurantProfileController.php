<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RestaurantProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $pendingRequests = ApprovalRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->get();

        return Inertia::render('Restaurant/Profile/Edit', [
            'user' => $user,
            'pendingRequests' => $pendingRequests,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'type' => 'required|in:password,avatar,location',
        ]);

        $oldValue = null;
        $newValue = [];

        switch ($request->type) {
            case 'password':
                $request->validate([
                    'current_password' => 'required',
                    'new_password' => 'required|min:8|confirmed',
                ]);

                if (!Hash::check($request->current_password, $user->password)) {
                    return back()->withErrors(['current_password' => 'M?t kh?u hi?n t?i không dúng']);
                }

                $newValue = ['password' => Hash::make($request->new_password)];
                break;

            case 'avatar':
                $request->validate([
                    'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                ]);

                $path = $request->file('avatar')->store('avatars', 'public');
                $newValue = ['avatar' => $path];
                $oldValue = ['avatar' => $user->avatar];
                break;

            case 'location':
                $request->validate([
                    'restaurant_name' => 'required|string|max:255',
                    'address' => 'required|string|max:255',
                    'address_detail' => 'nullable|string|max:255',
                    'latitude' => 'required|numeric',
                    'longitude' => 'required|numeric',
                ]);

                $newValue = [
                    'restaurant_name' => $request->restaurant_name,
                    'address' => $request->address,
                    'address_detail' => $request->address_detail,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ];
                $oldValue = [
                    'restaurant_name' => $user->restaurant_name,
                    'address' => $user->address,
                    'address_detail' => $user->address_detail,
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude,
                ];
                break;
        }

        ApprovalRequest::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);

        return back()->with('success', 'Yêu c?u c?p nh?t dã du?c g?i và dang ch? phê duy?t t? admin.');
    }
}

