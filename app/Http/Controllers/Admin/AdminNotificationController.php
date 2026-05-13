<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Models\AdminActivityLog;

class AdminNotificationController extends Controller
{
    public function index()
    {
        // Get recent notifications sent to users
        // Since Laravel notifications are polymorphic, we just show a generic UI
        // We will just render the send page
        return Inertia::render('Admin/PushNotifications');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error',
            'target' => 'required|in:all,customer,restaurant,shipper',
        ]);

        $query = User::query();

        if ($request->target !== 'all') {
            $query->where('role', $request->target);
        }

        $users = $query->get();
        
        // Gửi thông báo (Sẽ ghi vào bảng notifications)
        \Illuminate\Support\Facades\Notification::send($users, new SystemNotification(
            $request->title,
            $request->message,
            $request->type
        ));

        AdminActivityLog::log('send_notification', "Gửi thông báo '{$request->title}' tới đối tượng: {$request->target}");

        return redirect()->back()->with('success', 'Đã gửi thông báo thành công tới ' . $users->count() . ' người dùng.');
    }
}
