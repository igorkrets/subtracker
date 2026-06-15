<!DOCTYPE html>
<html lang="ru" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$el.classList.toggle('dark', darkMode)" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Политика конфиденциальности — SubTracker</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">

<nav class="border-b border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm sticky top-0 z-40">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between h-14">
        <a href="/" class="flex items-center gap-2 font-bold text-lg text-blue-600 dark:text-blue-400">
            <x-icon icon="layers" icon-set="lucide" class="w-5 h-5" />
            SubTracker
        </a>
        <a href="/" class="text-sm text-gray-500 hover:text-gray-900 dark:hover:text-white">← На главную</a>
    </div>
</nav>

<main class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold mb-2">Политика конфиденциальности</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-10">Последнее обновление: {{ date('d.m.Y') }}</p>

    <div class="prose dark:prose-invert max-w-none space-y-8 text-gray-700 dark:text-gray-300 leading-relaxed">

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">1. Общие положения</h2>
            <p>Настоящая Политика конфиденциальности описывает, какие данные собирает сервис SubTracker (далее — «Сервис», «мы»), расположенный по адресу <strong>{{ config('app.url') }}</strong>, как они используются и защищаются.</p>
            <p>Используя Сервис, вы соглашаетесь с условиями настоящей Политики. Если вы не согласны — пожалуйста, прекратите использование Сервиса.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">2. Какие данные мы собираем</h2>
            <ul class="list-disc pl-6 space-y-2">
                <li><strong>Данные аккаунта:</strong> имя, адрес электронной почты, пароль (в зашифрованном виде).</li>
                <li><strong>Данные о сервисах и подписках:</strong> названия, даты, стоимость — вся информация, которую вы вводите в Сервис.</li>
                <li><strong>Технические данные:</strong> IP-адрес, тип браузера, время запросов — для обеспечения безопасности и диагностики ошибок.</li>
                <li><strong>Telegram:</strong> ID вашего чата, если вы подключаете Telegram-уведомления.</li>
                <li><strong>Google OAuth:</strong> имя и email из вашего Google-аккаунта, если вы входите через Google.</li>
                <li><strong>Cookie:</strong> сессионные cookie для поддержания авторизации, а также локальные данные браузера (localStorage) для сохранения настроек темы.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">3. Как мы используем данные</h2>
            <ul class="list-disc pl-6 space-y-2">
                <li>Для предоставления функций Сервиса: хранение и отображение ваших подписок, отправка уведомлений.</li>
                <li>Для обеспечения безопасности: защита от несанкционированного доступа, блокировка подозрительных IP.</li>
                <li>Для диагностики и устранения ошибок.</li>
                <li>Мы <strong>не продаём</strong> ваши данные третьим лицам.</li>
                <li>Мы <strong>не используем</strong> ваши данные для рекламы или маркетинга.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">4. Передача данных третьим сторонам</h2>
            <p>Данные могут передаваться только в следующих случаях:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li><strong>Telegram</strong> — для отправки уведомлений (только если вы подключили Telegram).</li>
                <li><strong>Google</strong> — при входе через Google OAuth (обрабатывается на стороне Google по их политике).</li>
                <li>По требованию законодательства Российской Федерации.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">5. Хранение данных</h2>
            <p>Данные хранятся на серверах в защищённой базе данных. Сессии зашифрованы. Соединение осуществляется исключительно по протоколу HTTPS.</p>
            <p>Данные хранятся в течение всего срока существования аккаунта. После удаления аккаунта данные удаляются в течение 30 дней.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">6. Ваши права</h2>
            <ul class="list-disc pl-6 space-y-2">
                <li>Вы можете запросить копию или удаление ваших данных, обратившись по адресу: <a href="mailto:{{ config('app.contact_email', 'admin@sub.syspage.ru') }}" class="text-blue-600 hover:underline">{{ config('app.contact_email', 'admin@sub.syspage.ru') }}</a>.</li>
                <li>Вы можете изменить данные профиля в настройках аккаунта.</li>
                <li>Вы можете в любой момент удалить свой аккаунт.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">7. Cookie</h2>
            <p>Сервис использует следующие типы cookie:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li><strong>Сессионные cookie</strong> (обязательные) — для поддержания вашей авторизации. Без них Сервис не работает.</li>
                <li><strong>CSRF-токены</strong> (обязательные) — для защиты форм от межсайтовых атак.</li>
                <li><strong>localStorage</strong> — сохранение выбранной темы оформления (светлая/тёмная) на вашем устройстве.</li>
            </ul>
            <p>Мы не используем рекламные или трекинговые cookie.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">8. Изменения политики</h2>
            <p>Мы можем обновлять настоящую Политику. При существенных изменениях уведомим через интерфейс Сервиса. Продолжение использования Сервиса после изменений означает ваше согласие с новой редакцией.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">9. Контакты</h2>
            <p>По вопросам конфиденциальности обращайтесь: <a href="mailto:{{ config('app.contact_email', 'admin@sub.syspage.ru') }}" class="text-blue-600 hover:underline">{{ config('app.contact_email', 'admin@sub.syspage.ru') }}</a></p>
        </section>

    </div>
</main>

<footer class="border-t border-gray-200 dark:border-gray-800 mt-16">
    <div class="max-w-4xl mx-auto px-4 py-6 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
        <span>SubTracker &copy; {{ date('Y') }}</span>
        <div class="flex gap-4">
            <a href="{{ route('privacy') }}" class="hover:text-blue-600">Конфиденциальность</a>
            <a href="{{ route('terms') }}" class="hover:text-blue-600">Условия</a>
        </div>
    </div>
</footer>

</body>
</html>
