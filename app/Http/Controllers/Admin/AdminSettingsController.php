<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSettingsController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->is_admin, 403);
        $settings = AppSetting::current();
        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        abort_unless(Auth::user()->is_admin, 403);

        $data = $request->validate([
            'max_services' => ['required', 'integer', 'min:1', 'max:100000'],
            'max_notification_rules' => ['required', 'integer', 'min:1', 'max:1000'],
            'max_webhooks' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        AppSetting::current()->update($data);
        AppSetting::forget();

        return back()->with('success', 'Глобальные лимиты обновлены');
    }
}
