<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AdminActivityLog;

class AdminLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AdminActivityLog::with('admin')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/AuditLogs', [
            'logs' => $logs
        ]);
    }
}
