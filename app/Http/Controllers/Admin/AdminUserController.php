<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->is_admin, 403);

        $query = User::withCount('services');
        if ($search = $request->get('q')) {
            $query->where(fn($b) => $b->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(50);
        $globalSettings = AppSetting::current();
        return view('admin.users', compact('users', 'globalSettings'));
    }

    public function block(User $user)
    {
        abort_unless(Auth::user()->is_admin, 403);
        $user->update(['is_blocked' => true]);
        return back()->with('success', "Пользователь {$user->email} заблокирован");
    }

    public function unblock(User $user)
    {
        abort_unless(Auth::user()->is_admin, 403);
        $user->update(['is_blocked' => false]);
        return back()->with('success', "Пользователь {$user->email} разблокирован");
    }

    public function toggle(User $user)
    {
        abort_unless(Auth::user()->is_admin, 403);
        $user->update(['is_blocked' => !$user->is_blocked]);
        $msg = $user->is_blocked ? 'заблокирован' : 'разблокирован';
        return back()->with('success', "Пользователь {$user->email} {$msg}");
    }

    public function toggleAdmin(User $user)
    {
        abort_unless(Auth::user()->is_admin, 403);
        abort_if($user->id === Auth::id(), 403, 'Нельзя изменить роль самому себе');
        $user->update(['is_admin' => !$user->is_admin]);
        $msg = $user->is_admin ? 'назначен администратором' : 'разжалован из администраторов';
        return back()->with('success', "Пользователь {$user->email} {$msg}");
    }

    public function updateLimits(Request $request, User $user)
    {
        abort_unless(Auth::user()->is_admin, 403);

        $data = $request->validate([
            'max_services' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'max_notification_rules' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'max_webhooks' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $user->update($data);
        return back()->with('success', "Лимиты пользователя {$user->email} обновлены");
    }
}
