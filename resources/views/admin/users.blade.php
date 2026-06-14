@extends('layouts.admin')

@section('title', 'Пользователи')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-semibold">Пользователи</h1>
    <form method="GET" class="flex gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Поиск по email/имени..."
            class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
        <button type="submit" class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">Найти</button>
    </form>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Пользователь</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Сервисов</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Telegram</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Регистрация</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Статус</th>
                <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Действия</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($users as $user)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="px-4 py-3">
                    <div class="font-medium">{{ $user->name }}</div>
                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                </td>
                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $user->services_count }}</td>
                <td class="px-4 py-3">
                    @if($user->tg_chat_id)
                    <span class="text-xs text-green-600 dark:text-green-400">Подключён</span>
                    @else
                    <span class="text-xs text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ $user->created_at->format('d.m.Y H:i') }}</td>
                <td class="px-4 py-3">
                    @if($user->is_admin)
                    <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full">Admin</span>
                    @elseif($user->is_blocked)
                    <span class="text-xs bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 px-2 py-0.5 rounded-full">Блок</span>
                    @else
                    <span class="text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-2 py-0.5 rounded-full">Активен</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    @if(!$user->is_admin)
                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="text-xs px-2 py-1 rounded {{ $user->is_blocked ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200' }}">
                            {{ $user->is_blocked ? 'Разблокировать' : 'Заблокировать' }}
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $users->withQueryString()->links() }}</div>
@endsection
