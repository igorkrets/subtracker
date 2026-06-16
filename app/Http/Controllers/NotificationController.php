<?php

namespace App\Http\Controllers;

use App\Models\NotificationRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $rules = Auth::user()->notificationRules()->with(['group', 'service'])->get();
        return view('dashboard.notifications', compact('rules'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $limit = $user->maxNotificationRules();
        if ($user->notificationRules()->count() >= $limit) {
            return response()->json(['success' => false, 'message' => "Достигнут лимит правил уведомлений ({$limit})"], 422);
        }

        $data = $request->validate([
            'channel' => ['required', 'in:tg,webhook'],
            'days_before' => ['required', 'integer', 'min:0', 'max:365'],
            'is_global' => ['nullable', 'boolean'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'service_id' => ['nullable', 'exists:services,id'],
        ]);

        $rule = $user->notificationRules()->create($data);
        return response()->json(['success' => true, 'data' => $rule]);
    }

    public function destroy(NotificationRule $rule)
    {
        abort_if($rule->user_id !== Auth::id(), 403);
        $rule->delete();
        return response()->json(['success' => true]);
    }
}
