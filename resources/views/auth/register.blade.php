<!DOCTYPE html>
<html lang="ru" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$el.classList.toggle('dark', darkMode)" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация — SubTracker</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center p-4">
<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <a href="/" class="inline-flex items-center gap-2 text-2xl font-bold text-blue-600 dark:text-blue-400">
            <x-icon icon="layers" icon-set="lucide" class="w-7 h-7" />
            SubTracker
        </a>
        <p class="mt-2 text-gray-500 dark:text-gray-400 text-sm">Создайте бесплатный аккаунт</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        @if(config('services.google.client_id'))
        <a href="{{ route('auth.google') }}"
            class="flex items-center justify-center gap-3 w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-4 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition mb-4">
            <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Зарегистрироваться через Google
        </a>
        <div class="relative mb-4">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200 dark:border-gray-700"></div></div>
            <div class="relative flex justify-center text-xs text-gray-400 dark:text-gray-500"><span class="bg-white dark:bg-gray-800 px-2">или</span></div>
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}" id="register-form">
            @csrf
            <input type="hidden" name="timezone" id="reg-timezone" value="{{ old('timezone', 'UTC') }}">
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Имя</label>
                    <input id="name" name="name" type="text" autocomplete="name" required value="{{ old('name') }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Пароль</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Повторите пароль</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition">
                    Создать аккаунт
                </button>
            </div>
        </form>
    </div>
    <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-4">
        Уже есть аккаунт? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Войти</a>
    </p>
</div>
<script>
(function () {
    try {
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (tz) document.getElementById('reg-timezone').value = tz;
    } catch(e) {}
}());
</script>
</body>
</html>
