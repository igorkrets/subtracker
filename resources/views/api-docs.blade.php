<x-app-layout>
<x-slot name="title">API Документация</x-slot>

<div class="max-w-4xl" x-data="{ section: 'intro' }">
    <div class="flex gap-8">
        {{-- TOC Sidebar --}}
        <nav class="w-52 flex-shrink-0 hidden md:block">
            <div class="sticky top-6 text-sm space-y-1">
                @foreach([
                    ['intro', 'Введение'],
                    ['auth', 'Аутентификация'],
                    ['me', 'Текущий пользователь'],
                    ['services', 'Сервисы'],
                    ['groups', 'Группы'],
                    ['expiring', 'Истекающие'],
                    ['errors', 'Ошибки'],
                ] as [$key, $label])
                <button @click="section = '{{ $key }}'"
                    :class="section === '{{ $key }}' ? 'text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                    class="block text-left w-full py-1 px-2 rounded">{{ $label }}</button>
                @endforeach
            </div>
        </nav>

        {{-- Content --}}
        <div class="flex-1 min-w-0 prose dark:prose-invert prose-sm max-w-none">

            <div x-show="section === 'intro'">
                <h1 class="text-2xl font-bold mb-4">REST API v1</h1>
                <p class="text-gray-600 dark:text-gray-400 mb-6">Полный доступ к вашим данным через HTTP API.</p>

                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 mb-6">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Базовый URL</div>
                    <code class="text-blue-600 dark:text-blue-400">{{ config('app.url') }}/api/v1</code>
                </div>

                <h3 class="text-base font-semibold mb-3">Формат ответов</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Все ответы в JSON. Успешный ответ:</p>
                <pre class="bg-gray-900 text-green-400 rounded-xl p-4 text-xs overflow-x-auto"><code>{
  "success": true,
  "data": { ... },
  "meta": { "page": 1, "per_page": 50, "total": 123 }
}</code></pre>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-3 mb-3">Ошибка:</p>
                <pre class="bg-gray-900 text-red-400 rounded-xl p-4 text-xs overflow-x-auto"><code>{
  "success": false,
  "message": "Описание ошибки",
  "errors": { "field": ["Ошибка валидации"] }
}</code></pre>
            </div>

            <div x-show="section === 'auth'">
                <h2 class="text-xl font-bold mb-4">Аутентификация</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Используйте Bearer-токен в заголовке каждого запроса.</p>
                <pre class="bg-gray-900 text-green-400 rounded-xl p-4 text-xs overflow-x-auto"><code>Authorization: Bearer YOUR_API_TOKEN</code></pre>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-4">Получить токен — в <a href="{{ route('settings') }}?tab=api" class="text-blue-600 hover:underline">Настройках → API</a>.</p>
                <div class="mt-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3 text-sm text-amber-700 dark:text-amber-300">
                    Rate limit: 60 запросов в минуту на токен/IP. При превышении — статус 429.
                </div>
            </div>

            <div x-show="section === 'me'">
                <h2 class="text-xl font-bold mb-4">Текущий пользователь</h2>
                @include('partials.api-endpoint', [
                    'method' => 'GET',
                    'path' => '/api/v1/me',
                    'desc' => 'Данные текущего пользователя.',
                    'response' => '{ "id": 1, "name": "Иван", "email": "ivan@example.com", "timezone": "Europe/Moscow", "default_currency": "RUB", "tg_connected": true }',
                ])
            </div>

            <div x-show="section === 'services'">
                <h2 class="text-xl font-bold mb-6">Сервисы</h2>

                @include('partials.api-endpoint', [
                    'method' => 'GET',
                    'path' => '/api/v1/services',
                    'desc' => 'Список сервисов с фильтрацией и пагинацией.',
                    'params' => [
                        ['name' => 'status', 'type' => 'string', 'desc' => 'overdue|critical|soon|ok|none'],
                        ['name' => 'group_id', 'type' => 'int', 'desc' => 'Фильтр по группе'],
                        ['name' => 'q', 'type' => 'string', 'desc' => 'Поиск по имени'],
                        ['name' => 'page', 'type' => 'int', 'desc' => 'Страница (50/стр)'],
                    ],
                    'response' => '{ "data": [{ "id": 1, "name": "My VPS", "expires_at": "2025-03-01", "days_left": 15, "status": "soon", "cost": 500, "currency": "RUB" }], "meta": { ... } }',
                ])

                @include('partials.api-endpoint', [
                    'method' => 'POST',
                    'path' => '/api/v1/services',
                    'desc' => 'Создать сервис.',
                    'params' => [
                        ['name' => 'name', 'type' => 'string*', 'desc' => 'Название'],
                        ['name' => 'expires_at', 'type' => 'date', 'desc' => 'Дата истечения (YYYY-MM-DD)'],
                        ['name' => 'group_id', 'type' => 'int', 'desc' => 'ID группы'],
                        ['name' => 'cost', 'type' => 'decimal', 'desc' => 'Стоимость'],
                        ['name' => 'currency', 'type' => 'string', 'desc' => 'Валюта (RUB, USD, EUR, ...)'],
                        ['name' => 'billing_cycle', 'type' => 'string', 'desc' => 'monthly|quarterly|biannual|annual|biennial|custom'],
                        ['name' => 'notes', 'type' => 'string', 'desc' => 'Заметки'],
                        ['name' => 'ip', 'type' => 'string', 'desc' => 'IP-адрес'],
                        ['name' => 'url', 'type' => 'string', 'desc' => 'URL'],
                    ],
                    'response' => '{ "data": { "id": 42, "name": "My VPS", ... } }',
                ])

                @include('partials.api-endpoint', [
                    'method' => 'GET',
                    'path' => '/api/v1/services/{id}',
                    'desc' => 'Получить сервис.',
                    'response' => '{ "data": { "id": 42, ... } }',
                ])

                @include('partials.api-endpoint', [
                    'method' => 'PUT',
                    'path' => '/api/v1/services/{id}',
                    'desc' => 'Обновить сервис. Те же поля что и при создании.',
                    'response' => '{ "data": { ... } }',
                ])

                @include('partials.api-endpoint', [
                    'method' => 'DELETE',
                    'path' => '/api/v1/services/{id}',
                    'desc' => 'Удалить сервис (soft delete, 30 дней в корзине).',
                    'response' => '{ "success": true }',
                ])
            </div>

            <div x-show="section === 'groups'">
                <h2 class="text-xl font-bold mb-6">Группы</h2>

                @include('partials.api-endpoint', [
                    'method' => 'GET',
                    'path' => '/api/v1/groups',
                    'desc' => 'Список групп.',
                    'response' => '{ "data": [{ "id": 1, "name": "Серверы", "color": "#3b82f6", "services_count": 5 }] }',
                ])

                @include('partials.api-endpoint', [
                    'method' => 'POST',
                    'path' => '/api/v1/groups',
                    'desc' => 'Создать группу.',
                    'params' => [
                        ['name' => 'name', 'type' => 'string*', 'desc' => 'Название'],
                        ['name' => 'color', 'type' => 'string', 'desc' => 'Цвет (#hex)'],
                        ['name' => 'icon', 'type' => 'string', 'desc' => 'Slug иконки'],
                    ],
                    'response' => '{ "data": { "id": 3, "name": "Облако", ... } }',
                ])

                @include('partials.api-endpoint', [
                    'method' => 'DELETE',
                    'path' => '/api/v1/groups/{id}',
                    'desc' => 'Удалить группу (сервисы переносятся без группы).',
                    'response' => '{ "success": true }',
                ])
            </div>

            <div x-show="section === 'expiring'">
                <h2 class="text-xl font-bold mb-4">Истекающие сервисы</h2>
                @include('partials.api-endpoint', [
                    'method' => 'GET',
                    'path' => '/api/v1/services/expiring',
                    'desc' => 'Сервисы, истекающие в ближайшие N дней.',
                    'params' => [
                        ['name' => 'days', 'type' => 'int', 'desc' => 'Горизонт в днях (default: 30, max: 365)'],
                    ],
                    'response' => '{ "data": [...], "meta": { "total": 3, "days": 30 } }',
                ])
            </div>

            <div x-show="section === 'errors'">
                <h2 class="text-xl font-bold mb-4">Коды ошибок</h2>
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-2 pr-4 font-medium">Код</th>
                            <th class="text-left py-2 font-medium">Описание</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach([
                            ['401', 'Не передан или недействителен API-токен'],
                            ['403', 'Нет доступа к ресурсу (чужой объект)'],
                            ['404', 'Ресурс не найден'],
                            ['422', 'Ошибка валидации — проверьте поле errors'],
                            ['429', 'Превышен rate limit (60 запр/мин)'],
                            ['500', 'Ошибка сервера'],
                        ] as [$code, $msg])
                        <tr>
                            <td class="py-2 pr-4 font-mono text-red-600 dark:text-red-400">{{ $code }}</td>
                            <td class="py-2 text-gray-600 dark:text-gray-400">{{ $msg }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
</x-app-layout>
