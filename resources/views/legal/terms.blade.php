<!DOCTYPE html>
<html lang="ru" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$el.classList.toggle('dark', darkMode)" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Условия использования — SubTracker</title>
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
    <h1 class="text-3xl font-bold mb-2">Условия использования</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-10">Последнее обновление: {{ date('d.m.Y') }}</p>

    <div class="prose dark:prose-invert max-w-none space-y-8 text-gray-700 dark:text-gray-300 leading-relaxed">

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">1. Принятие условий</h2>
            <p>Используя сервис SubTracker (далее — «Сервис»), вы принимаете настоящие Условия использования в полном объёме. Если вы не согласны с какими-либо условиями — пожалуйста, не используйте Сервис.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">2. Описание сервиса</h2>
            <p>SubTracker — бесплатный инструмент для учёта подписок, серверов и доменов с уведомлениями об истечении сроков оплаты. Сервис предоставляется «как есть» (as is).</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">3. Регистрация и аккаунт</h2>
            <ul class="list-disc pl-6 space-y-2">
                <li>Для использования основных функций требуется регистрация.</li>
                <li>Вы обязаны предоставить достоверные данные при регистрации.</li>
                <li>Вы несёте ответственность за сохранность своего пароля и всех действий в вашем аккаунте.</li>
                <li>Один человек — один аккаунт. Массовая автоматическая регистрация запрещена.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">4. Допустимое использование</h2>
            <p>Запрещается:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Использовать Сервис в незаконных целях.</li>
                <li>Пытаться получить несанкционированный доступ к данным других пользователей.</li>
                <li>Нагружать инфраструктуру автоматическими запросами (DDoS, парсинг).</li>
                <li>Распространять вредоносное программное обеспечение через Сервис.</li>
                <li>Создавать аккаунты в обход ограничений.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">5. Данные пользователя</h2>
            <p>Вы сохраняете все права на данные, которые вносите в Сервис. Мы не претендуем на право собственности на ваши данные. Вы можете экспортировать свои данные в любой момент через настройки аккаунта.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">6. API</h2>
            <p>Сервис предоставляет REST API для интеграции. Использование API допустимо в разумных пределах. Злоупотребление API (чрезмерное количество запросов, автоматизированный сбор данных) может привести к блокировке токена.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">7. Доступность сервиса</h2>
            <p>Мы стремимся обеспечить непрерывную работу Сервиса, однако не гарантируем 100% доступность. Сервис может быть временно недоступен в связи с техническим обслуживанием или форс-мажорными обстоятельствами.</p>
            <p>Мы не несём ответственности за убытки, связанные с недоступностью Сервиса или потерей данных.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">8. Блокировка аккаунта</h2>
            <p>Мы оставляем за собой право заблокировать или удалить аккаунт без предупреждения в случае нарушения настоящих Условий.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">9. Изменения условий</h2>
            <p>Мы можем изменять настоящие Условия. Продолжение использования Сервиса после публикации изменений означает ваше согласие с новой редакцией.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">10. Контакты</h2>
            <p>По вопросам, связанным с условиями использования: <a href="mailto:{{ config('app.contact_email', 'admin@sub.syspage.ru') }}" class="text-blue-600 hover:underline">{{ config('app.contact_email', 'admin@sub.syspage.ru') }}</a></p>
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
