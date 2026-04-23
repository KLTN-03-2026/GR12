<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminApprovalController extends Controller
{
    public function index()
    {
        $requests = ApprovalRequest::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Approvals/Index', [
            'requests' => $requests,
        ]);
    }

    public function approve(Request $request, ApprovalRequest $approvalRequest)
    {
        $request->validate([
            'comment' => 'nullable|string|max:500',
        ]);

        $approvalRequest->update([
            'status' => 'approved',
            'admin_id' => Auth::id(),
            'admin_comment' => $request->comment,
        ]);

        // Áp d?ng thay d?i vào user
        $user = $approvalRequest->user;
        foreach ($approvalRequest->new_value as $key => $value) {
            $user->$key = $value;
        }
        $user->save();

        return back()->with('success', 'Yêu c?u dã du?c phê duy?t.');
    }

    public function reject(Request $request, ApprovalRequest $approvalRequest)
    {
        $request->validate([
            'comment' => 'required|string|max:500',
        ]);

        $approvalRequest->update([
            'status' => 'rejected',
            'admin_id' => Auth::id(),
            'admin_comment' => $request->comment,
        ]);

        return back()->with('success', 'Yêu c?u dã b? t? ch?i.');
    }
}

