<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:7'],
            'icon' => ['nullable', 'string', 'max:50'],
            'icon_set' => ['nullable', 'string'],
            'notifications_enabled' => ['nullable', 'boolean'],
        ]);

        $maxOrder = Auth::user()->groups()->max('sort_order') ?? 0;
        $group = Auth::user()->groups()->create(array_merge($data, ['sort_order' => $maxOrder + 1]));

        return response()->json(['success' => true, 'data' => $group]);
    }

    public function update(Request $request, Group $group)
    {
        abort_if($group->user_id !== Auth::id(), 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:7'],
            'icon' => ['nullable', 'string', 'max:50'],
            'icon_set' => ['nullable', 'string'],
            'notifications_enabled' => ['nullable', 'boolean'],
        ]);

        $group->update($data);

        return response()->json(['success' => true, 'data' => $group]);
    }

    public function destroy(Group $group)
    {
        abort_if($group->user_id !== Auth::id(), 403);
        $group->delete();
        return response()->json(['success' => true]);
    }

    public function sort(Request $request)
    {
        $data = $request->validate(['ids' => ['required', 'array']]);

        foreach ($data['ids'] as $order => $id) {
            Auth::user()->groups()->where('id', $id)->update(['sort_order' => $order]);
        }

        return response()->json(['success' => true]);
    }

    public function toggleNotifications(Group $group)
    {
        abort_if($group->user_id !== Auth::id(), 403);
        $group->update(['notifications_enabled' => !$group->notifications_enabled]);
        return response()->json(['success' => true, 'notifications_enabled' => $group->notifications_enabled]);
    }
}
