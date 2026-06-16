<!DOCTYPE html>
<html lang="ru" class="{{ auth()->user()?->dark_mode ? 'dark' : '' }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$el.classList.toggle('dark', darkMode)" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-seo-meta
        :title="($title ?? 'SubTracker') . ' — SubTracker'"
        :description="$description ?? 'Учёт серверов, доменов, VPS и подписок с напоминаниями об оплате.'" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="manifest" href="/manifest.json">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen">

{{-- Toast notifications --}}
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2" aria-live="polite"></div>

{{-- Top navbar --}}
<nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-bold text-lg text-blue-600 dark:text-blue-400">
                    <x-icon icon="layers" icon-set="lucide" class="w-6 h-6" />
                    SubTracker
                </a>
            </div>
            <div class="flex items-center gap-3">
                {{-- Dark mode toggle --}}
                <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode); $el.closest('html').classList.toggle('dark', darkMode)"
                    class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                    aria-label="Переключить тему">
                    <x-icon icon="moon" icon-set="lucide" class="w-5 h-5 dark:hidden" />
                    <x-icon icon="sun" icon-set="lucide" class="w-5 h-5 hidden dark:block" />
                </button>
                @auth
                <a href="{{ route('settings') }}" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition" aria-label="Настройки">
                    <x-icon icon="settings" icon-set="lucide" class="w-5 h-5" />
                </a>
                @if(auth()->user()->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition" aria-label="Админка">
                    <x-icon icon="shield-check" icon-set="lucide" class="w-5 h-5" />
                </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition" aria-label="Выйти">
                        <x-icon icon="log-out" icon-set="lucide" class="w-5 h-5" />
                    </button>
                </form>
                @endauth
            </div>
        </div>
    </div>
</nav>

{{-- Overdue banner --}}
@auth
@php $overdueCount = auth()->user()->services()->where('expires_at', '<', today())->whereNull('deleted_at')->count(); @endphp
@if($overdueCount > 0)
<div class="bg-red-50 dark:bg-red-900/30 border-b border-red-200 dark:border-red-800">
    <div class="max-w-7xl mx-auto px-4 py-2 flex items-center gap-2 text-sm text-red-700 dark:text-red-300">
        <x-icon icon="alert-triangle" icon-set="lucide" class="w-4 h-4 flex-shrink-0" />
        У вас {{ $overdueCount }} просроченных подписок
    </div>
</div>
@endif
@endauth

{{-- Flash messages --}}
@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
    class="fixed top-4 right-4 z-50 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg text-sm">
    {{ session('success') }}
</div>
@endif

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{ $slot }}
</main>
@stack('scripts')

<script>
window.csrfToken = '{{ csrf_token() }}';

function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const div = document.createElement('div');
    div.className = `px-4 py-2 rounded-lg shadow text-sm text-white ${type === 'error' ? 'bg-red-500' : 'bg-green-500'}`;
    div.textContent = message;
    container.appendChild(div);
    setTimeout(() => div.remove(), 3500);
}
</script>
</body>
</html>
