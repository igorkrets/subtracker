@extends('layouts.admin')

@section('title', 'Настройки')

@section('content')
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 rounded-lg text-sm">
    {{ session('success') }}
</div>
@endif

<h1 class="text-xl font-semibold mb-6">Глобальные лимиты</h1>

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 max-w-lg">
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
        Действуют для всех пользователей, у которых не задано индивидуальное значение лимита (страница «Пользователи»).
    </p>
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium mb-1">Максимум сервисов на аккаунт</label>
            <input type="number" name="max_services" min="1" value="{{ old('max_services', $settings->max_services) }}"
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Максимум правил уведомлений</label>
            <input type="number" name="max_notification_rules" min="1" value="{{ old('max_notification_rules', $settings->max_notification_rules) }}"
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Максимум вебхуков</label>
            <input type="number" name="max_webhooks" min="1" value="{{ old('max_webhooks', $settings->max_webhooks) }}"
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
            Сохранить
        </button>
    </form>
</div>
@endsection
