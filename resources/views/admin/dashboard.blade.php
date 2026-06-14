@extends('layouts.admin')

@section('title', 'Дашборд')

@section('content')
<h1 class="text-xl font-semibold mb-6">Обзор системы</h1>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @foreach([
        ['label' => 'Пользователей', 'value' => $stats['users_total'], 'icon' => 'users', 'color' => 'blue'],
        ['label' => 'Активных (30 дн)', 'value' => $stats['users_active'], 'icon' => 'user-check', 'color' => 'green'],
        ['label' => 'Сервисов', 'value' => $stats['services_total'], 'icon' => 'layers', 'color' => 'purple'],
        ['label' => 'Заблокировано', 'value' => $stats['users_blocked'], 'icon' => 'ban', 'color' => 'red'],
    ] as $card)
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $card['label'] }}</span>
            <x-icon :icon="$card['icon']" icon-set="lucide" class="w-5 h-5 text-{{ $card['color'] }}-500" />
        </div>
        <div class="text-2xl font-bold">{{ $card['value'] }}</div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Recent users --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 font-medium">Новые пользователи</div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($recentUsers as $u)
            <div class="px-5 py-3 flex items-center gap-3 text-sm">
                <div class="flex-1">
                    <div class="font-medium">{{ $u->name }}</div>
                    <div class="text-xs text-gray-500">{{ $u->email }}</div>
                </div>
                <div class="text-xs text-gray-400">{{ $u->created_at->diffForHumans() }}</div>
                @if($u->is_blocked)
                <span class="text-xs text-red-500">Заблокирован</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- System info --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 font-medium">Система</div>
        <div class="p-5 space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Версия Laravel</span><span>{{ app()->version() }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">PHP</span><span>{{ PHP_VERSION }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Окружение</span><span>{{ app()->environment() }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Очередь</span><span>{{ config('queue.default') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Задания в очереди</span><span>{{ $stats['pending_jobs'] }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Ошибок за 24ч</span><span class="{{ $stats['errors_24h'] > 0 ? 'text-red-500' : '' }}">{{ $stats['errors_24h'] }}</span></div>
        </div>
    </div>
</div>
@endsection
