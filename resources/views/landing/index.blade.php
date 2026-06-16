<!DOCTYPE html>
<html lang="ru" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$el.classList.toggle('dark', darkMode)" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <x-seo-meta
        title="SubTracker — трекер подписок, серверов и VPS с уведомлениями"
        description="Учёт серверов, доменов, VPS и подписок в одном месте: Telegram-напоминания об оплате, REST API, экспорт в XLSX/PDF и аналитика расходов. Бесплатно, без ограничений." />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="manifest" href="/manifest.json">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "SubTracker",
        "url": "{{ url('/') }}",
        "description": "Бесплатный сервис для учёта серверов, доменов, VPS и подписок с напоминаниями о сроках оплаты",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "RUB"
        }
    }
    </script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "SubTracker бесплатный?",
                "acceptedAnswer": { "@type": "Answer", "text": "Да, сервис полностью бесплатный, без ограничений на количество подписок, серверов или доменов." }
            },
            {
                "@type": "Question",
                "name": "Какие уведомления поддерживаются?",
                "acceptedAnswer": { "@type": "Answer", "text": "Telegram-бот и вебхуки напоминают о продлении за 1, 3, 7, 14 или 30 дней — настройка для каждой группы отдельно." }
            },
            {
                "@type": "Question",
                "name": "Есть ли API для интеграций?",
                "acceptedAnswer": { "@type": "Answer", "text": "Да, доступен полноценный REST API v1 с встроенной документацией для интеграции с другими инструментами." }
            },
            {
                "@type": "Question",
                "name": "Можно ли перенести данные из таблиц Excel или Google Sheets?",
                "acceptedAnswer": { "@type": "Answer", "text": "Да, поддерживается импорт из CSV и JSON, а также полный экспорт и бекап базы в один клик." }
            },
            {
                "@type": "Question",
                "name": "Насколько безопасно хранить данные в SubTracker?",
                "acceptedAnswer": { "@type": "Answer", "text": "Заметки шифруются по алгоритму AES-256, ключ хранится локально и не передаётся на сервер." }
            },
            {
                "@type": "Question",
                "name": "Поддерживаются российские сервисы и провайдеры?",
                "acceptedAnswer": { "@type": "Answer", "text": "Да, в каталоге 85+ сервисов, включая российских провайдеров: Timeweb, Яндекс 360 и другие, помимо международных AWS, Hetzner, DigitalOcean." }
            }
        ]
    }
    </script>
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
<section class="relative py-20 px-4 text-center overflow-hidden">

    {{-- Floating background cards --}}
    <div id="hero-floats" class="absolute inset-0 pointer-events-none" aria-hidden="true">

        {{-- Left column --}}
        <div class="float-el float-side items-center gap-2 bg-white/75 dark:bg-gray-800/75 backdrop-blur-sm border border-gray-100 dark:border-gray-700 rounded-xl px-3 py-2 shadow-sm text-xs absolute" style="left:2%;top:18%" data-depth="0.5">
            <div class="float-inner flex items-center gap-2">
                <div class="w-5 h-5 rounded-md flex-shrink-0" style="background:#d50c2d"></div>
                <span class="font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Hetzner CX21</span>
                <span class="px-1.5 py-0.5 rounded-md bg-orange-100 dark:bg-orange-900/40 text-orange-600 dark:text-orange-400 font-medium whitespace-nowrap">3 дн.</span>
            </div>
        </div>

        <div class="float-el float-side items-center gap-2 bg-white/75 dark:bg-gray-800/75 backdrop-blur-sm border border-gray-100 dark:border-gray-700 rounded-xl px-3 py-2 shadow-sm text-xs absolute" style="left:3%;top:44%" data-depth="0.3">
            <div class="float-inner flex items-center gap-2">
                <div class="w-5 h-5 rounded-md flex-shrink-0" style="background:#e50914"></div>
                <span class="font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Netflix</span>
                <span class="text-gray-400 dark:text-gray-500 whitespace-nowrap">690 ₽/мес</span>
            </div>
        </div>

        <div class="float-el float-side items-center gap-2 bg-white/75 dark:bg-gray-800/75 backdrop-blur-sm border border-gray-100 dark:border-gray-700 rounded-xl px-3 py-2 shadow-sm text-xs absolute" style="left:1%;top:70%" data-depth="0.4">
            <div class="float-inner flex flex-col gap-0.5">
                <div class="flex items-center gap-2">
                    <div class="w-5 h-5 rounded-md flex-shrink-0" style="background:#005bff"></div>
                    <span class="font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Timeweb VPS</span>
                </div>
                <span class="text-gray-400 dark:text-gray-500 font-mono text-[10px] pl-7">145.11.11.112</span>
            </div>
        </div>

        {{-- Right column --}}
        <div class="float-el float-side items-center gap-2 bg-white/75 dark:bg-gray-800/75 backdrop-blur-sm border border-gray-100 dark:border-gray-700 rounded-xl px-3 py-2 shadow-sm text-xs absolute" style="right:2%;top:20%" data-depth="0.45">
            <div class="float-inner flex items-center gap-2">
                <div class="w-5 h-5 rounded-md flex-shrink-0" style="background:#24292e"></div>
                <span class="font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">GitHub Pro</span>
                <span class="px-1.5 py-0.5 rounded-md bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400 font-medium whitespace-nowrap">Активен</span>
            </div>
        </div>

        <div class="float-el float-side items-center gap-2 bg-white/75 dark:bg-gray-800/75 backdrop-blur-sm border border-gray-100 dark:border-gray-700 rounded-xl px-3 py-2 shadow-sm text-xs absolute" style="right:3%;top:48%" data-depth="0.35">
            <div class="float-inner flex flex-col gap-0.5">
                <div class="flex items-center gap-2">
                    <div class="w-5 h-5 rounded-md flex-shrink-0" style="background:#0080ff"></div>
                    <span class="font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">DigitalOcean</span>
                </div>
                <div class="flex items-center justify-between gap-3 pl-7">
                    <span class="text-gray-400 dark:text-gray-500">$6/мес</span>
                    <span class="px-1.5 py-0.5 rounded-md bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400 font-medium">32 дн.</span>
                </div>
            </div>
        </div>

        <div class="float-el float-side items-center gap-2 bg-white/75 dark:bg-gray-800/75 backdrop-blur-sm border border-gray-100 dark:border-gray-700 rounded-xl px-3 py-2 shadow-sm text-xs absolute" style="right:1%;top:71%" data-depth="0.5">
            <div class="float-inner flex items-center gap-2">
                <div class="w-5 h-5 rounded-md flex-shrink-0" style="background:#ff9900"></div>
                <span class="font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">AWS EC2</span>
                <span class="text-gray-400 dark:text-gray-500 whitespace-nowrap">$29.99/мес</span>
            </div>
        </div>

        {{-- Top / bottom (visible on md+) --}}
        <div class="float-el float-mid items-center gap-2 bg-white/75 dark:bg-gray-800/75 backdrop-blur-sm border border-gray-100 dark:border-gray-700 rounded-xl px-3 py-2 shadow-sm text-xs absolute" style="left:19%;top:4%" data-depth="0.2">
            <div class="float-inner flex items-center gap-2">
                <div class="w-5 h-5 rounded-md flex-shrink-0" style="background:#f38020"></div>
                <span class="font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Cloudflare</span>
                <span class="text-gray-400 dark:text-gray-500 whitespace-nowrap">бесплатно</span>
            </div>
        </div>

        <div class="float-el float-mid items-center gap-2 bg-white/75 dark:bg-gray-800/75 backdrop-blur-sm border border-gray-100 dark:border-gray-700 rounded-xl px-3 py-2 shadow-sm text-xs absolute" style="right:18%;top:5%" data-depth="0.25">
            <div class="float-inner flex items-center gap-2">
                <div class="w-5 h-5 rounded-md flex-shrink-0" style="background:#fc3f1d"></div>
                <span class="font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Яндекс 360</span>
                <span class="text-gray-400 dark:text-gray-500 whitespace-nowrap">219 ₽/мес</span>
            </div>
        </div>

        <div class="float-el float-mid items-center gap-2 bg-white/75 dark:bg-gray-800/75 backdrop-blur-sm border border-gray-100 dark:border-gray-700 rounded-xl px-3 py-2 shadow-sm text-xs absolute" style="left:17%;bottom:6%" data-depth="0.3">
            <div class="float-inner flex items-center gap-2">
                <div class="w-5 h-5 rounded-md flex-shrink-0" style="background:#1a9cff"></div>
                <span class="font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">example.ru</span>
                <span class="px-1.5 py-0.5 rounded-md bg-yellow-100 dark:bg-yellow-900/40 text-yellow-600 dark:text-yellow-400 font-medium whitespace-nowrap">14 дн.</span>
            </div>
        </div>

        <div class="float-el float-mid items-center gap-2 bg-white/75 dark:bg-gray-800/75 backdrop-blur-sm border border-gray-100 dark:border-gray-700 rounded-xl px-3 py-2 shadow-sm text-xs absolute" style="right:16%;bottom:7%" data-depth="0.2">
            <div class="float-inner flex items-center gap-2">
                <div class="w-5 h-5 rounded-md flex-shrink-0" style="background:#f87171"></div>
                <span class="font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Spotify</span>
                <span class="px-1.5 py-0.5 rounded-md bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 font-medium whitespace-nowrap">Просрочено</span>
            </div>
        </div>

    </div>

    <style>
    @keyframes heroFloatA {
        0%,100% { transform: translateY(0px) rotate(-1deg); }
        50%      { transform: translateY(-10px) rotate(1deg); }
    }
    @keyframes heroFloatB {
        0%,100% { transform: translateY(0px) rotate(1deg); }
        50%      { transform: translateY(-14px) rotate(-1deg); }
    }
    @keyframes heroFloatC {
        0%,100% { transform: translateY(0px); }
        50%      { transform: translateY(-8px); }
    }
    .float-el { transition: transform 0.12s ease-out; will-change: transform; }
    /* Responsive visibility without Tailwind — controls which elements show at which breakpoints */
    .float-side { display: none; }
    .float-mid  { display: none; }
    @media (min-width: 640px)  { .float-mid  { display: flex; } }
    @media (min-width: 1024px) { .float-side { display: flex; } }
    </style>

    <div class="relative z-10 max-w-3xl mx-auto">
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
        <div class="flex flex-col sm:flex-row gap-3 justify-center flex-wrap">
            @if(config('app.register_enable'))
            <a href="{{ route('register') }}" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-lg shadow-lg shadow-blue-600/25 transition text-center">
                Начать
            </a>
            @endif
            <a href="{{ route('login') }}" class="px-8 py-3 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-900 dark:text-white rounded-xl font-semibold text-lg transition text-center">
                Войти
            </a>
        </div>
        <div id="pwa-install-wrap" class="mt-3 justify-center" style="display:none">
            <button id="pwa-install-btn"
                class="inline-flex items-center gap-2 px-8 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold text-lg shadow-lg shadow-green-600/25 transition w-full sm:w-auto justify-center">
                <x-icon icon="download" icon-set="lucide" class="w-5 h-5" />
                Установить приложение
            </button>
        </div>
        <div class="mt-3 flex justify-center">
            <a href="https://github.com/igorkrets/subtracker" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-6 py-2 border border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-xl font-medium text-base transition">
                <x-icon icon="github" icon-set="simple-icons" class="w-4 h-4" />
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
                    <span class="text-sm text-gray-400"><img src="/imgs/landing/googlesheets.jpg" alt="Ручной учёт подписок и серверов в таблице Google Sheets" width="640" height="360" loading="lazy" class="w-full h-full object-cover"></span>
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
                    <span class="text-sm text-blue-400"><img src="/imgs/landing/subservice.jpg" alt="Интерфейс SubTracker — учёт подписок, серверов и VPS с уведомлениями" width="640" height="360" loading="lazy" class="w-full h-full object-cover"></span>
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

{{-- FAQ --}}
<section class="py-16 px-4">
    <div class="max-w-3xl mx-auto">
        <h2 class="text-2xl font-bold text-center mb-10">Частые вопросы</h2>
        <div class="space-y-3">
            @foreach([
                ['q' => 'SubTracker бесплатный?', 'a' => 'Да, сервис полностью бесплатный, без ограничений на количество подписок, серверов или доменов.'],
                ['q' => 'Какие уведомления поддерживаются?', 'a' => 'Telegram-бот и вебхуки напоминают о продлении за 1, 3, 7, 14 или 30 дней — настройка для каждой группы отдельно.'],
                ['q' => 'Есть ли API для интеграций?', 'a' => 'Да, доступен полноценный REST API v1 с встроенной документацией для интеграции с другими инструментами.'],
                ['q' => 'Можно ли перенести данные из таблиц Excel или Google Sheets?', 'a' => 'Да, поддерживается импорт из CSV и JSON, а также полный экспорт и бекап базы в один клик.'],
                ['q' => 'Насколько безопасно хранить данные в SubTracker?', 'a' => 'Заметки шифруются по алгоритму AES-256, ключ хранится локально и не передаётся на сервер.'],
                ['q' => 'Поддерживаются российские сервисы и провайдеры?', 'a' => 'Да, в каталоге 85+ сервисов, включая российских провайдеров: Timeweb, Яндекс 360 и другие, помимо международных AWS, Hetzner, DigitalOcean.'],
            ] as $faq)
            <details class="group bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 px-5 py-4">
                <summary class="font-medium cursor-pointer flex items-center justify-between gap-3 list-none">
                    {{ $faq['q'] }}
                    <x-icon icon="chevron-down" icon-set="lucide" class="w-4 h-4 flex-shrink-0 transition group-open:rotate-180" />
                </summary>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">{{ $faq['a'] }}</p>
            </details>
            @endforeach
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
        <div class="flex items-center gap-4 flex-wrap justify-center">
            @if(config('app.contact_email'))
            <a href="mailto:{{ config('app.contact_email') }}" class="hover:text-blue-600">{{ config('app.contact_email') }}</a>
            @endif
            @if(config('app.contact_tg'))
            <a href="https://t.me/{{ ltrim(config('app.contact_tg'), '@') }}" class="hover:text-blue-600" target="_blank">{{ config('app.contact_tg') }}</a>
            @endif
            <a href="{{ route('privacy') }}" class="hover:text-blue-600">Конфиденциальность</a>
            <a href="{{ route('terms') }}" class="hover:text-blue-600">Условия</a>
            <a href="{{ route('api.docs') }}" class="hover:text-blue-600">API</a>
            <a href="https://github.com/igorkrets/subtracker" target="_blank" rel="noopener" class="flex items-center gap-1.5 hover:text-blue-600">
                <x-icon icon="github" icon-set="simple-icons" class="w-4 h-4" />
                GitHub
            </a>
        </div>
    </div>
</footer>

{{-- Cookie consent banner --}}
<div x-data="{ show: !localStorage.getItem('cookie_ok') }" x-show="show" x-cloak
    class="fixed bottom-0 left-0 right-0 z-50 p-4">
    <div class="max-w-3xl mx-auto bg-gray-900 dark:bg-gray-800 text-white rounded-2xl shadow-2xl px-5 py-4 flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <x-icon icon="cookie" icon-set="lucide" class="w-6 h-6 text-amber-400 flex-shrink-0 mt-0.5 sm:mt-0" />
        <p class="text-sm text-gray-200 flex-1">
            Мы используем обязательные cookie для авторизации и защиты форм. Рекламных или трекинговых cookie нет.
            <a href="{{ route('privacy') }}" class="underline text-blue-400 hover:text-blue-300 ml-1">Подробнее</a>
        </p>
        <div class="flex gap-2 flex-shrink-0">
            <button @click="localStorage.setItem('cookie_ok', '1'); show = false"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-lg transition whitespace-nowrap">
                Понятно
            </button>
            <a href="{{ route('privacy') }}"
                class="px-4 py-2 border border-gray-600 hover:border-gray-400 text-gray-300 hover:text-white text-sm rounded-lg transition whitespace-nowrap">
                Узнать больше
            </a>
        </div>
    </div>
</div>

<script>
// Service Worker registration for PWA
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').catch(() => {});
}

// PWA install prompt — mobile only
(function () {
    // Don't show on desktop (pointer: fine = mouse)
    if (window.matchMedia('(pointer: fine)').matches) return;

    var deferredPrompt = null;
    var wrap = document.getElementById('pwa-install-wrap');
    var btn  = document.getElementById('pwa-install-btn');

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;
        if (wrap) wrap.style.display = 'flex';
    });

    if (btn) {
        btn.addEventListener('click', function () {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function () {
                deferredPrompt = null;
                if (wrap) wrap.style.display = 'none';
            });
        });
    }

    window.addEventListener('appinstalled', function () {
        if (wrap) wrap.style.display = 'none';
        deferredPrompt = null;
    });
})();
</script>

<script>
(function () {
    var els = [];
    var mouseX = 0, mouseY = 0, scrollY = 0;
    var ticking = false;

    function init() {
        els = Array.from(document.querySelectorAll('#hero-floats .float-el'));
        els.forEach(function (el, i) {
            // stagger the float animation so they don't all move in sync
            var inner = el.querySelector('.float-inner');
            if (inner) inner.style.animationDelay = (i * 0.4) + 's';
        });
    }

    function apply() {
        ticking = false;
        var cx = window.innerWidth / 2;
        var cy = window.innerHeight / 2;
        els.forEach(function (el) {
            var depth = parseFloat(el.dataset.depth || 0.3);
            var tx = (mouseX - cx) / cx * depth * 28;
            var ty = (mouseY - cy) / cy * depth * 18 - scrollY * depth * 0.08;
            el.style.transform = 'translate(' + tx.toFixed(2) + 'px,' + ty.toFixed(2) + 'px)';
        });
    }

    function schedule() {
        if (!ticking) { ticking = true; requestAnimationFrame(apply); }
    }

    document.addEventListener('mousemove', function (e) {
        mouseX = e.clientX;
        mouseY = e.clientY;
        schedule();
    });

    window.addEventListener('scroll', function () {
        scrollY = window.scrollY;
        schedule();
    }, { passive: true });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
</body>
</html>
