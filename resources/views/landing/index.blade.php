<!DOCTYPE html>
<html lang="ru" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$el.classList.toggle('dark', darkMode)" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SubTracker — трекер подписок и серверов</title>
    <meta name="description" content="Бесплатный сервис для учёта серверов, доменов, VPS и подписок с напоминаниями о сроках оплаты">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="manifest" href="/manifest.json">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">

{{-- Nav --}}
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
            @if(config('app.register_enable'))
            <a href="{{ route('register') }}" class="text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg transition">Начать</a>
            @endif
        </div>
    </div>
</nav>

{{-- Hero --}}
<section class="py-20 px-4 text-center">
    <div class="max-w-3xl mx-auto">
        <div class="flex justify-center mb-6">
            <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-600/30">
                <x-icon icon="layers" icon-set="lucide" class="w-9 h-9 text-white" />
            </div>
        </div>
        <h1 class="text-4xl sm:text-5xl font-bold mb-5 leading-tight">
            Все подписки и серверы<br>в одном месте
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-6 max-w-xl mx-auto">
            SubTracker напомнит о продлении VPS, домена, облачного сервиса или любой подписки.
            Telegram-уведомления, REST API, экспорт. Бесплатно.
        </p>
        @if($stats['users'] > 0)
        <div class="flex items-center justify-center gap-4 mb-8 text-sm text-gray-500 dark:text-gray-400">
            <span><strong class="text-gray-900 dark:text-white font-bold">{{ number_format($stats['users']) }}</strong> пользователей</span>
            <span class="text-gray-300 dark:text-gray-600">·</span>
            <span><strong class="text-gray-900 dark:text-white font-bold">{{ number_format($stats['services']) }}</strong> записей</span>
        </div>
        @endif
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            @if(config('app.register_enable'))
            <a href="{{ route('register') }}" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-lg shadow-lg shadow-blue-600/25 transition">
                Начать
            </a>
            @endif
            <a href="{{ route('login') }}" class="px-8 py-3 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-900 dark:text-white rounded-xl font-semibold text-lg transition">
                Войти
            </a>
        </div>
        <br>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="https://github.com/igorkrets/subtracker" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-8 py-3 border border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white rounded-xl font-semibold text-lg transition">
                <x-icon icon="github" icon-set="simple-icons" class="w-5 h-5" />
                GitHub
            </a>
        </div>
    </div>
</section>

{{-- Features --}}
<section class="py-16 px-4 bg-gray-50 dark:bg-gray-800/50">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-2xl font-bold text-center mb-12">Что умеет SubTracker</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach([
                ['icon' => 'bell', 'title' => 'Уведомления вовремя', 'desc' => 'Telegram-бот и вебхуки напомнят за 1, 3, 7, 14 или 30 дней до истечения. Настройте для каждой группы отдельно.'],
                ['icon' => 'layers', 'title' => '85+ популярных сервисов', 'desc' => 'AWS, Hetzner, DigitalOcean, Timeweb, Cloudflare, GitHub, Netflix — выбирайте из готового каталога.'],
                ['icon' => 'shield-check', 'title' => 'REST API', 'desc' => 'Полный API v1 для интеграции с вашими инструментами. Документация встроена в сервис.'],
                ['icon' => 'download', 'title' => 'Экспорт и бекап', 'desc' => 'XLSX, PDF, HTML, JSON. Импорт из CSV/JSON. Полный бекап базы в один клик.'],
                ['icon' => 'bar-chart-2', 'title' => 'Аналитика расходов', 'desc' => 'Видите сколько тратите на сервисы по валютам и группам. Планируйте бюджет с помощью прогнозов.'],
                ['icon' => 'move', 'title' => 'Гибкая организация', 'desc' => 'Группы, цвета, иконки, перетаскивание. Переключайтесь между сгруппированным и плоским видом.'],
            ] as $feat)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mb-4">
                    <x-icon :icon="$feat['icon']" icon-set="lucide" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
                <h3 class="font-semibold mb-2">{{ $feat['title'] }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $feat['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Comparison section --}}
<section class="py-16 px-4">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-2xl font-bold text-center mb-3">Не таблицы в Excel</h2>
        <p class="text-center text-gray-500 dark:text-gray-400 mb-12 max-w-lg mx-auto">
            Ручной учёт в таблицах неудобен — нет уведомлений, нет мобильной версии, легко забыть обновить
        </p>
        <div class="grid sm:grid-cols-2 gap-6 lg:gap-10">

            {{-- "Before" --}}
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-lg font-semibold text-gray-400">Раньше</span>
                    <span class="text-xs bg-gray-100 dark:bg-gray-800 text-gray-500 px-2 py-0.5 rounded-full">Google Таблицы / Excel</span>
                </div>
                {{-- Image placeholder --}}
                <div id="comparison-before" class="aspect-video bg-gray-100 dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 overflow-hidden flex items-center justify-center">
                    <span class="text-sm text-gray-400"><img src="/imgs/landing/googlesheets.jpg" alt="Google Sheets" class="w-full h-full object-cover"></span>
                </div>
                <ul class="mt-5 space-y-2.5 text-sm text-gray-500 dark:text-gray-400">
                    <li class="flex items-center gap-2"><span class="text-red-400 font-bold">✕</span> Нет уведомлений — легко пропустить</li>
                    <li class="flex items-center gap-2"><span class="text-red-400 font-bold">✕</span> Обновлять вручную каждый раз</li>
                    <li class="flex items-center gap-2"><span class="text-red-400 font-bold">✕</span> Неудобно с телефона</li>
                    <li class="flex items-center gap-2"><span class="text-red-400 font-bold">✕</span> Нет истории платежей и аналитики</li>
                    <li class="flex items-center gap-2"><span class="text-red-400 font-bold">✕</span> Пароли и данные в открытом виде</li>
                </ul>
            </div>

            {{-- "After" --}}
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-lg font-semibold text-blue-600 dark:text-blue-400">SubTracker</span>
                    <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 px-2 py-0.5 rounded-full">Умный трекер</span>
                </div>
                {{-- Image placeholder --}}
                <div id="comparison-after" class="aspect-video bg-blue-50 dark:bg-blue-900/10 rounded-xl border-2 border-blue-200 dark:border-blue-800 overflow-hidden flex items-center justify-center">
                    <span class="text-sm text-blue-400"><img src="/imgs/landing/subservice.jpg" alt="SubTracker" class="w-full h-full object-cover"></span>
                </div>
                <ul class="mt-5 space-y-2.5 text-sm text-gray-700 dark:text-gray-300">
                    <li class="flex items-center gap-2"><span class="text-green-500 font-bold">✓</span> Telegram-уведомления за 1–30 дней</li>
                    <li class="flex items-center gap-2"><span class="text-green-500 font-bold">✓</span> Продление в один клик — даже в боте</li>
                    <li class="flex items-center gap-2"><span class="text-green-500 font-bold">✓</span> Удобно на телефоне и планшете</li>
                    <li class="flex items-center gap-2"><span class="text-green-500 font-bold">✓</span> История, аналитика расходов, экспорт</li>
                    <li class="flex items-center gap-2"><span class="text-green-500 font-bold">✓</span> Заметки с шифрованием AES-256 (ключ локально)</li>
                </ul>
            </div>

        </div>
    </div>
</section>

{{-- Supported services --}}
<section class="py-16 px-4">
    <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-2xl font-bold mb-4">Каталог популярных сервисов</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-10">{{ $presets->count() }}+ провайдеров — международные и российские</p>
        <div class="flex flex-wrap justify-center gap-3">
            @foreach($presets->where('is_popular', true)->take(20) as $preset)
            <div class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-medium">
                @if($preset->icon)
                <x-icon :icon="$preset->icon" :icon-set="$preset->icon_set ?? 'simple-icons'" class="w-4 h-4" :color="$preset->color" />
                @endif
                {{ $preset->name }}
            </div>
            @endforeach
            @if($presets->count() > 20)
            <div class="flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-xl text-sm text-gray-500">
                +{{ $presets->count() - 20 }} ещё
            </div>
            @endif
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-20 px-4 bg-blue-600">
    <div class="max-w-xl mx-auto text-center text-white">
        <h2 class="text-3xl font-bold mb-4">Начните прямо сейчас</h2>
        <p class="text-blue-100 mb-8">Бесплатно. Без ограничений на количество сервисов.</p>
        <a href="{{ route('register') }}" class="inline-block px-8 py-3 bg-white text-blue-600 rounded-xl font-semibold text-lg hover:bg-blue-50 transition">
            Зарегистрироваться
        </a>
    </div>
</section>

{{-- Footer --}}
<footer class="border-t border-gray-200 dark:border-gray-800">
    <div class="max-w-6xl mx-auto px-4 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-gray-400">
        <div class="flex items-center gap-2">
            <x-icon icon="layers" icon-set="lucide" class="w-4 h-4" />
            <span>SubTracker &copy; {{ date('Y') }}</span>
        </div>
        <div class="flex items-center gap-4">
            @if(config('app.contact_email'))
            <a href="mailto:{{ config('app.contact_email') }}" class="hover:text-blue-600">{{ config('app.contact_email') }}</a>
            @endif
            @if(config('app.contact_tg'))
            <a href="https://t.me/{{ ltrim(config('app.contact_tg'), '@') }}" class="hover:text-blue-600" target="_blank">{{ config('app.contact_tg') }}</a>
            @endif
            <a href="{{ route('api.docs') }}" class="hover:text-blue-600">API</a>
            <a href="https://github.com/igorkrets/subtracker" target="_blank" rel="noopener" class="flex items-center gap-1.5 hover:text-blue-600">
                <x-icon icon="github" icon-set="simple-icons" class="w-4 h-4" />
                GitHub
            </a>
        </div>
    </div>
</footer>

</body>
</html>
