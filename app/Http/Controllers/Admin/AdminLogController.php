<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;
use App\Models\NotificationLog;
use App\Models\RequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLogController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->is_admin, 403);

        $type = $request->get('type', 'requests');

        $logs = match ($type) {
            'errors' => ErrorLog::orderBy('id', 'desc')->paginate(50),
            'notifications' => NotificationLog::with(['service'])->orderBy('id', 'desc')->paginate(100),
            default => RequestLog::with('user')->orderBy('id', 'desc')->paginate(100),
        };

        return view('admin.logs', compact('logs', 'type'));
    }
}
