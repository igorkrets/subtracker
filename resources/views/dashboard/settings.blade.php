<x-app-layout>
<x-slot name="title">Настройки</x-slot>

<div x-data="settingsPage()" x-init="init()">
    <h1 class="text-xl font-semibold mb-6">Настройки аккаунта</h1>

    {{-- Tabs: select on mobile, button tabs on desktop --}}
    <select class="sm:hidden w-full px-3 py-2.5 mb-5 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
        x-model="tab" @change="history.replaceState(null, '', '?tab=' + tab)">
        @foreach([['profile','Профиль'],['security','Безопасность'],['api','API'],['telegram','Telegram'],['notifications','Уведомления'],['webhooks','Вебхуки']] as [$key, $label])
        <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
    </select>
    <div class="hidden sm:flex gap-1 border-b border-gray-200 dark:border-gray-700 mb-6">
        @foreach([['profile','Профиль'],['security','Безопасность'],['api','API'],['telegram','Telegram'],['notifications','Уведомления'],['webhooks','Вебхуки']] as [$key, $label])
        <button @click="tab = '{{ $key }}'; history.replaceState(null, '', '?tab={{ $key }}')"
            :class="tab === '{{ $key }}' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
            class="px-4 py-2 text-sm font-medium transition -mb-px whitespace-nowrap">{{ $label }}</button>
        @endforeach
    </div>

    {{-- Profile --}}
    <div x-show="tab === 'profile'" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 max-w-lg">
        <h2 class="text-lg font-medium mb-4">Профиль</h2>
        <form method="POST" action="{{ route('settings.profile') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Имя</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Таймзона</label>
                <select name="timezone" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($timezones as $tzId => $tzLabel)
                    <option value="{{ $tzId }}" {{ $user->timezone === $tzId ? 'selected' : '' }}>{{ $tzLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Валюта по умолчанию</label>
                <select name="default_currency" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['RUB','USD','EUR','GBP','CNY'] as $cur)
                    <option value="{{ $cur }}" {{ $user->default_currency === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition">
                Сохранить
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-medium mb-3">Смена пароля</h3>
            <form method="POST" action="{{ route('settings.password') }}" class="space-y-3">
                @csrf @method('PUT')
                <input type="password" name="current_password" placeholder="Текущий пароль"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="password" name="password" placeholder="Новый пароль (мин. 8 символов)"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="password" name="password_confirmation" placeholder="Повторите пароль"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('current_password') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                @error('password') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white text-sm px-4 py-2 rounded-lg transition">Сменить пароль</button>
            </form>
        </div>
    </div>

    {{-- Security / Local password --}}
    <div x-show="tab === 'security'" class="max-w-lg space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-medium mb-1 flex items-center gap-2">
                <x-icon icon="lock" icon-set="lucide" class="w-5 h-5 text-amber-500" />
                Локальный пароль
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Используется для поля <strong>«Заметки (шифр)»</strong> в карточках сервисов.</p>

            {{-- Info box --}}
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 text-sm text-amber-800 dark:text-amber-200 mb-5 space-y-1.5">
                <div class="flex gap-2"><x-icon icon="shield" icon-set="lucide" class="w-4 h-4 flex-shrink-0 mt-0.5" /><span>Пароль хранится <strong>только в этом браузере</strong> (localStorage). На сервер не передаётся.</span></div>
                <div class="flex gap-2"><x-icon icon="eye-off" icon-set="lucide" class="w-4 h-4 flex-shrink-0 mt-0.5" /><span>На сервер отправляется только зашифрованный текст. Даже администратор не может его прочитать.</span></div>
                <div class="flex gap-2"><x-icon icon="alert-triangle" icon-set="lucide" class="w-4 h-4 flex-shrink-0 mt-0.5" /><span><strong>Если потеряете пароль — данные не восстановить.</strong> Используйте надёжный пароль (12+ символов, цифры, спецсимволы).</span></div>
                <div class="flex gap-2"><x-icon icon="monitor" icon-set="lucide" class="w-4 h-4 flex-shrink-0 mt-0.5" /><span>При смене браузера или устройства задайте этот же пароль повторно.</span></div>
            </div>

            {{-- Current status --}}
            <div class="mb-4">
                <div x-show="localPwd" class="space-y-2">
                    <div class="flex items-center gap-2 text-green-600 dark:text-green-400 text-sm">
                        <x-icon icon="check-circle" icon-set="lucide" class="w-4 h-4 flex-shrink-0" />
                        Пароль задан в этом браузере
                    </div>
                    <div class="flex gap-2 text-sm text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg px-3 py-2.5">
                        <x-icon icon="alert-triangle" icon-set="lucide" class="w-4 h-4 flex-shrink-0 mt-0.5" />
                        <span>Если смените пароль, вы потеряете доступ к зашифрованным заметкам, созданным со старым паролем. Убедитесь, что помните текущий пароль или записали его в надёжном месте.</span>
                    </div>
                </div>
                <div x-show="!localPwd" class="flex items-center gap-2 text-gray-400 text-sm">
                    <x-icon icon="circle" icon-set="lucide" class="w-4 h-4" />
                    Пароль не задан — шифрование заметок недоступно
                </div>
            </div>

            {{-- Password input --}}
            <div class="space-y-3">
                <div class="relative">
                    <input :type="showPwd ? 'text' : 'password'" x-model="newPwd"
                        placeholder="Введите новый локальный пароль"
                        @input="pwdStrength = window.SubCrypto.strength(newPwd)"
                        class="w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <button type="button" @click="showPwd = !showPwd"
                        class="absolute right-2 top-2 p-1 text-gray-400 hover:text-gray-600">
                        <x-icon icon="eye" icon-set="lucide" class="w-4 h-4" />
                    </button>
                </div>

                {{-- Strength bar --}}
                <div x-show="newPwd" class="space-y-1">
                    <div class="flex gap-1">
                        <template x-for="i in 4">
                            <div class="h-1.5 flex-1 rounded-full transition-colors"
                                :class="i <= pwdStrength
                                    ? (pwdStrength <= 1 ? 'bg-red-500' : pwdStrength <= 2 ? 'bg-amber-500' : pwdStrength <= 3 ? 'bg-yellow-400' : 'bg-green-500')
                                    : 'bg-gray-200 dark:bg-gray-700'">
                            </div>
                        </template>
                    </div>
                    <p class="text-xs" :class="{
                        'text-red-600': pwdStrength <= 1,
                        'text-amber-600': pwdStrength === 2,
                        'text-yellow-600': pwdStrength === 3,
                        'text-green-600': pwdStrength >= 4
                    }" x-text="['', 'Слабый — легко подобрать', 'Средний', 'Хороший', 'Надёжный'][pwdStrength] || ''"></p>
                </div>

                <div class="flex gap-2">
                    <button type="button" @click="saveLocalPwd()"
                        :disabled="!newPwd"
                        class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg disabled:opacity-50 transition">
                        <span x-text="localPwd ? 'Изменить пароль' : 'Задать пароль'"></span>
                    </button>
                    <button type="button" @click="forgetLocalPwd()" x-show="localPwd"
                        class="px-4 py-2 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-sm rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50">
                        Забыть пароль
                    </button>
                </div>
            </div>
        </div>

        {{-- Encryption info --}}
        <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-xs text-gray-500 dark:text-gray-400 space-y-1">
            <div class="font-medium text-gray-700 dark:text-gray-300 mb-1">Техническая информация</div>
            <div>Алгоритм: <code>AES-256-GCM</code> (аутентифицированное шифрование)</div>
            <div>Производная ключа: <code>PBKDF2-SHA256</code>, 310 000 итераций</div>
            <div>Соль и IV: генерируются случайно для каждой записи</div>
            <div>Шифрование и расшифровка выполняются только в вашем браузере (Web Crypto API)</div>
        </div>
    </div>

    {{-- API --}}
    <div x-show="tab === 'api'" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 max-w-lg">
        <h2 class="text-lg font-medium mb-4">API-токен</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Используйте токен в заголовке <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">Authorization: Bearer {token}</code></p>
        <div x-data="{ show: false }" class="flex gap-2 mb-4">
            <input :type="show ? 'text' : 'password'" value="{{ $user->api_token }}" readonly
                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm font-mono">
            <button @click="show = !show" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                <x-icon icon="eye" icon-set="lucide" class="w-4 h-4" />
            </button>
            <button @click="navigator.clipboard.writeText('{{ $user->api_token }}').then(() => showToast('Скопировано!'))"
                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                <x-icon icon="copy" icon-set="lucide" class="w-4 h-4" />
            </button>
        </div>
        <form method="POST" action="{{ route('settings.token') }}">
            @csrf
            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white text-sm px-4 py-2 rounded-lg transition"
                onclick="return confirm('Старый токен перестанет работать. Продолжить?')">
                Перегенерировать токен
            </button>
        </form>
        <div class="mt-5 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('api.docs') }}" target="_blank"
                class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:underline">
                <x-icon icon="book-open" icon-set="lucide" class="w-4 h-4" />
                Документация API
                <x-icon icon="external-link" icon-set="lucide" class="w-3 h-3 opacity-60" />
            </a>
        </div>
    </div>

    {{-- Telegram --}}
    <div x-show="tab === 'telegram'" class="max-w-lg space-y-4">

        {{-- How to connect --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-base font-semibold mb-3 flex items-center gap-2">
                <x-icon icon="send" icon-set="lucide" class="w-4 h-4 text-blue-500" />
                Как подключить
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                Откройте бота
                <a href="https://t.me/{{ config('services.telegram.username', env('TELEGRAM_BOT_USERNAME')) }}"
                   target="_blank"
                   class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                    {{ '@' }}{{ config('services.telegram.username', env('TELEGRAM_BOT_USERNAME', 'YourBot')) }}
                </a>
                и отправьте команду:
            </p>
            <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-700 rounded-lg px-3 py-2.5">
                <code class="flex-1 text-sm font-mono select-all text-gray-800 dark:text-gray-100">
                    /start {{ $user->tg_code }}
                </code>
                <button type="button" onclick="navigator.clipboard.writeText('/start {{ $user->tg_code }}').then(() => showToast('Скопировано'))"
                    class="flex-shrink-0 p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-500 hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
                    title="Скопировать">
                    <x-icon icon="copy" icon-set="lucide" class="w-4 h-4" />
                </button>
            </div>
        </div>

        {{-- Connected accounts --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-base font-semibold mb-3 flex items-center gap-2">
                <x-icon icon="smartphone" icon-set="lucide" class="w-4 h-4 text-gray-500" />
                Подключённые аккаунты
            </h2>

            @if($user->tg_chat_id)
            <div class="flex items-center gap-3 p-3 rounded-lg border border-green-100 dark:border-green-900/40 bg-green-50 dark:bg-green-900/20">
                {{-- Avatar placeholder --}}
                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0 text-white font-bold text-lg">
                    <x-icon icon="send" icon-set="lucide" class="w-5 h-5" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Telegram</span>
                        <span class="inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                            <x-icon icon="check-circle" icon-set="lucide" class="w-3.5 h-3.5" />
                            Подключён
                        </span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 space-y-0.5">
                        <div>Chat ID: <span class="font-mono">{{ $user->tg_chat_id }}</span></div>
                        @if($user->tg_connected_at)
                        <div>С {{ $user->tg_connected_at->format('d.m.Y') }}</div>
                        @endif
                    </div>
                </div>
                {{-- Unlink button --}}
                <form method="POST" action="{{ route('settings.tg.unlink') }}" class="flex-shrink-0"
                      onsubmit="return confirm('Отвязать Telegram? Уведомления перестанут приходить.')">
                    @csrf
                    <button type="submit"
                        class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
                        title="Отвязать">
                        <x-icon icon="unlink" icon-set="lucide" class="w-4 h-4" />
                    </button>
                </form>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-6 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                    <x-icon icon="smartphone" icon-set="lucide" class="w-6 h-6 text-gray-400" />
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Подключённые аккаунты отсутствуют</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Используйте команду выше для подключения</p>
            </div>
            @endif
        </div>

    </div>

    {{-- Notification rules --}}
    <div x-show="tab === 'notifications'" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 max-w-2xl">
        <h2 class="text-lg font-medium mb-4">Правила уведомлений</h2>
        <div class="space-y-2 mb-4">
            @forelse($notificationRules as $rule)
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg text-sm">
                <span class="font-medium">{{ $rule->channel === 'tg' ? 'Telegram' : 'Webhook' }}</span>
                <span class="text-gray-500">за {{ $rule->days_before }} дн.</span>
                @if($rule->is_global) <span class="text-xs bg-blue-100 text-blue-600 px-1.5 rounded">Глобальное</span> @endif
                @if($rule->group) <span class="text-xs bg-gray-200 dark:bg-gray-600 px-1.5 rounded">{{ $rule->group->name }}</span> @endif
                <button @click="deleteRule({{ $rule->id }})" class="ml-auto text-red-500 hover:text-red-700">
                    <x-icon icon="trash-2" icon-set="lucide" class="w-4 h-4" />
                </button>
            </div>
            @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">Нет правил. Добавьте глобальное правило.</p>
            @endforelse
        </div>
        <div x-data="{ channel: 'tg', days: 7, adding: false }" class="flex gap-2 flex-wrap">
            <select x-model="channel" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                <option value="tg">Telegram</option>
                <option value="webhook">Webhook</option>
            </select>
            <select x-model="days" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                @foreach([1,3,7,14,30,60] as $d)
                <option value="{{ $d }}">За {{ $d }} дн.</option>
                @endforeach
            </select>
            <button @click="addRule(channel, days)"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">+ Добавить</button>
        </div>
        @push('scripts')
        <script>
        async function addRule(channel, days) {
            const res = await fetch('/notifications', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
                body: JSON.stringify({ channel, days_before: parseInt(days), is_global: true }),
            });
            if ((await res.json()).success) window.location.reload();
        }
        async function deleteRule(id) {
            if (!confirm('Удалить правило?')) return;
            await fetch(`/notifications/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': window.csrfToken } });
            window.location.reload();
        }
        </script>
        @endpush
    </div>

    {{-- Webhooks --}}
    <div x-show="tab === 'webhooks'" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 max-w-2xl">
        <h2 class="text-lg font-medium mb-4">Вебхуки</h2>
        <div class="space-y-3 mb-4">
            @forelse($webhooks as $wh)
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg text-sm">
                <div class="flex-1">
                    <div class="font-medium">{{ $wh->name }}</div>
                    <div class="text-xs text-gray-500 truncate">{{ $wh->url }}</div>
                </div>
                <span class="text-xs {{ $wh->is_active ? 'text-green-600' : 'text-gray-400' }}">
                    {{ $wh->is_active ? 'Активен' : 'Отключён' }}
                </span>
                <button @click="testWebhook({{ $wh->id }})" class="text-xs text-blue-600 hover:underline">Тест</button>
                <button @click="deleteWebhook({{ $wh->id }})" class="text-red-500 hover:text-red-700">
                    <x-icon icon="trash-2" icon-set="lucide" class="w-4 h-4" />
                </button>
            </div>
            @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">Нет вебхуков.</p>
            @endforelse
        </div>
        <button @click="showForm = !showForm" class="text-sm text-blue-600 hover:underline">+ Добавить вебхук</button>
        <form x-show="showForm" @submit.prevent="addWebhook()" class="mt-3 space-y-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <input type="text" x-model="whForm.name" placeholder="Название" required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-600 text-sm">
            <input type="url" x-model="whForm.url" placeholder="https://..." required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-600 text-sm">
            <input type="text" x-model="whForm.secret" placeholder="Секрет (необязательно)"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-600 text-sm">
            <button type="submit" class="bg-blue-600 text-white text-sm px-4 py-2 rounded-lg">Создать</button>
        </form>
        <div id="test-result" class="mt-3 text-xs text-gray-600 dark:text-gray-400"></div>
    </div>
</div>

@push('scripts')
<script>
function settingsPage() {
    return {
        tab: '{{ request('tab', 'profile') }}',
        // Local password (security tab)
        localPwd: null,
        newPwd: '',
        showPwd: false,
        pwdStrength: 0,
        // Webhooks tab
        showForm: false,
        whForm: { name: '', url: '', secret: '', is_active: true },

        init() {
            this.localPwd = window.SubCrypto.getPassword();
        },

        saveLocalPwd() {
            if (!this.newPwd) return;
            if (this.pwdStrength < 2) {
                if (!confirm('Пароль слабый — его легко подобрать. Продолжить?')) return;
            }
            window.SubCrypto.setPassword(this.newPwd);
            this.localPwd = this.newPwd;
            this.newPwd = '';
            this.pwdStrength = 0;
            showToast('Локальный пароль сохранён в браузере');
        },

        forgetLocalPwd() {
            if (!confirm('Вы не сможете расшифровать зашифрованные заметки без этого пароля. Забыть?')) return;
            window.SubCrypto.setPassword(null);
            this.localPwd = null;
            showToast('Локальный пароль удалён из браузера');
        },

        async addWebhook() {
            const res = await fetch('/webhooks', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
                body: JSON.stringify(this.whForm),
            });
            const data = await res.json();
            if (data.success) { this.showForm = false; window.location.reload(); }
        },
        async testWebhook(id) {
            const el = document.getElementById('test-result');
            el.textContent = 'Отправка...';
            const res = await fetch(`/webhooks/${id}/test`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
            });
            const data = await res.json();
            el.textContent = `Статус: ${data.status_code ?? '—'} | ${data.response_body ?? ''}`;
        },
        async deleteWebhook(id) {
            if (!confirm('Удалить вебхук?')) return;
            await fetch(`/webhooks/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': window.csrfToken } });
            window.location.reload();
        },
    };
}

async function addRule(channel, days) {
    const res = await fetch('/notifications', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
        body: JSON.stringify({ channel, days_before: parseInt(days), is_global: true }),
    });
    if ((await res.json()).success) window.location.reload();
}
async function deleteRule(id) {
    if (!confirm('Удалить правило?')) return;
    await fetch(`/notifications/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': window.csrfToken } });
    window.location.reload();
}
</script>
@endpush

</x-app-layout>
