<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->is_admin, 403);

        $stats = [
            'users_total' => User::count(),
            'users_active' => User::where('updated_at', '>=', now()->subDays(30))->count(),
            'users_blocked' => User::where('is_blocked', true)->count(),
            'services_total' => Service::count(),
            'pending_jobs' => DB::table('jobs')->count(),
            'errors_24h' => ErrorLog::where('created_at', '>=', now()->subDay())->count(),
        ];

        $recentUsers = User::orderBy('created_at', 'desc')->limit(10)->get();

        return view('admin.dashboard', compact('stats', 'recentUsers'));
    }
}
