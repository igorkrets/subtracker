<!DOCTYPE html>
<html lang="ru" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$el.classList.toggle('dark', darkMode)" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }} — SubTracker Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex">

{{-- Sidebar --}}
<aside class="w-56 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-sm font-bold text-blue-600 dark:text-blue-400">
            <x-icon icon="layers" icon-set="lucide" class="w-5 h-5" />
            SubTracker Admin
        </a>
    </div>
    <nav class="flex-1 p-3 space-y-1">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <x-icon icon="home" icon-set="lucide" class="w-4 h-4" /> Обзор
        </a>
        <a href="{{ route('admin.users') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.users') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <x-icon icon="user" icon-set="lucide" class="w-4 h-4" /> Пользователи
        </a>
        <a href="{{ route('admin.logs') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.logs') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <x-icon icon="list" icon-set="lucide" class="w-4 h-4" /> Логи
        </a>
        <a href="{{ route('admin.catalog') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.catalog*') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <x-icon icon="layers" icon-set="lucide" class="w-4 h-4" /> Каталог
        </a>
    </nav>
    <div class="p-3 border-t border-gray-200 dark:border-gray-700">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full">
                <x-icon icon="log-out" icon-set="lucide" class="w-4 h-4" /> Выйти
            </button>
        </form>
    </div>
</aside>

<main class="flex-1 p-6 overflow-auto">
    @hasSection('content')
        @yield('content')
    @else
        {{ $slot }}
    @endif
</main>

<script>window.csrfToken = '{{ csrf_token() }}';</script>
@stack('scripts')
</body>
</html>
