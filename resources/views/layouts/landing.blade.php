<!DOCTYPE html>
<html lang="ru" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', mobileMenu: false }" x-init="$el.classList.toggle('dark', darkMode)" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SubTracker — учёт серверов и подписок' }}</title>
    <meta name="description" content="Бесплатный сервис для учёта серверов, доменов, VPS и подписок с напоминаниями о сроках оплаты">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="manifest" href="/manifest.json">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">

<nav class="border-b border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm sticky top-0 z-40">
    <div class="max-w-6xl mx-auto px-4 flex items-center justify-between h-14">
        <a href="/" class="flex items-center gap-2 font-bold text-lg text-blue-600 dark:text-blue-400">
            <x-icon icon="layers" icon-set="lucide" class="w-6 h-6" />
            SubTracker
        </a>
        <div class="flex items-center gap-3">
            <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode); $el.closest('html').classList.toggle('dark', darkMode)"
                class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                <x-icon icon="moon" icon-set="lucide" class="w-5 h-5 dark:hidden" />
                <x-icon icon="sun" icon-set="lucide" class="w-5 h-5 hidden dark:block" />
            </button>
            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">Войти</a>
            <a href="{{ route('register') }}" class="text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg transition">Начать</a>
        </div>
    </div>
</nav>

@hasSection('content')
    @yield('content')
@else
    {{ $slot }}
@endif

<footer class="border-t border-gray-200 dark:border-gray-800 mt-20">
    <div class="max-w-6xl mx-auto px-4 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-gray-400">
        <div class="flex items-center gap-2">
            <x-icon icon="layers" icon-set="lucide" class="w-4 h-4" />
            <span>SubTracker &copy; {{ date('Y') }}</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="mailto:{{ env('CONTACT_EMAIL') }}" class="hover:text-blue-600">{{ env('CONTACT_EMAIL') }}</a>
            <a href="https://t.me/{{ ltrim(env('CONTACT_TG', '@igorkrets'), '@') }}" class="hover:text-blue-600" target="_blank">{{ env('CONTACT_TG') }}</a>
            <a href="{{ route('api.docs') }}" class="hover:text-blue-600">API</a>
        </div>
    </div>
</footer>
</body>
</html>
