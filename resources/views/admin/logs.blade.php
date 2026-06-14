@extends('layouts.admin')

@section('title', 'Логи')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-semibold">Системные логи</h1>
    <div class="flex gap-2">
        <a href="{{ request()->fullUrlWithQuery(['type' => 'requests']) }}"
            class="px-3 py-1.5 text-sm rounded-lg {{ request('type', 'requests') === 'requests' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
            Запросы
        </a>
        <a href="{{ request()->fullUrlWithQuery(['type' => 'errors']) }}"
            class="px-3 py-1.5 text-sm rounded-lg {{ request('type') === 'errors' ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
            Ошибки
        </a>
        <a href="{{ request()->fullUrlWithQuery(['type' => 'notifications']) }}"
            class="px-3 py-1.5 text-sm rounded-lg {{ request('type') === 'notifications' ? 'bg-purple-600 text-white' : 'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
            Уведомления
        </a>
    </div>
</div>

@if(request('type', 'requests') === 'requests')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Время</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Метод</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">URL</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Статус</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Пользователь</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">IP</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($logs as $log)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="px-4 py-2 text-xs text-gray-500">{{ $log->created_at->format('d.m H:i:s') }}</td>
                <td class="px-4 py-2">
                    <span class="font-mono text-xs font-bold {{ match($log->method) { 'GET' => 'text-green-600', 'POST' => 'text-blue-600', 'DELETE' => 'text-red-600', default => 'text-gray-600' } }}">
                        {{ $log->method }}
                    </span>
                </td>
                <td class="px-4 py-2 text-xs font-mono max-w-xs truncate">{{ $log->url }}</td>
                <td class="px-4 py-2">
                    <span class="text-xs font-mono {{ $log->status_code >= 400 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $log->status_code }}
                    </span>
                </td>
                <td class="px-4 py-2 text-xs text-gray-500">{{ $log->user?->email ?? '—' }}</td>
                <td class="px-4 py-2 text-xs font-mono text-gray-500">{{ $log->ip_address }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@elseif(request('type') === 'errors')
<div class="space-y-3">
    @foreach($logs as $log)
    <div x-data="{ open: false }" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-4 px-4 py-3 cursor-pointer" @click="open = !open">
            <span class="text-xs text-gray-400">{{ $log->created_at->format('d.m H:i:s') }}</span>
            <span class="font-mono text-sm text-red-600 flex-1">{{ Str::limit($log->message, 100) }}</span>
            <x-icon :icon="open ? 'chevron-up' : 'chevron-down'" icon-set="lucide" class="w-4 h-4 text-gray-400" />
        </div>
        <div x-show="open" class="border-t border-gray-200 dark:border-gray-700 px-4 py-3">
            <pre class="text-xs text-red-600 dark:text-red-400 overflow-x-auto whitespace-pre-wrap">{{ $log->stack_trace }}</pre>
            @if($log->context)
            <div class="mt-2 text-xs text-gray-500">
                <pre>{{ json_encode($log->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
            @endif
        </div>
    </div>
    @endforeach
</div>

@else
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Время</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Канал</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Сервис</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Статус</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($logs as $log)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="px-4 py-2 text-xs text-gray-500">{{ ($log->sent_at ?? $log->created_at)?->format('d.m H:i:s') }}</td>
                <td class="px-4 py-2 text-xs font-medium">{{ $log->channel }}</td>
                <td class="px-4 py-2 text-xs">{{ $log->service?->name ?? '—' }}</td>
                <td class="px-4 py-2 text-xs {{ $log->status === 'sent' ? 'text-green-600' : 'text-red-600' }}">{{ $log->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="mt-4">{{ $logs->withQueryString()->links() }}</div>
@endsection
