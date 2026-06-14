<x-app-layout>
<x-slot name="title">Настройки</x-slot>

<div x-data="{ tab: '{{ request('tab', 'profile') }}' }">
    <h1 class="text-xl font-semibold mb-6">Настройки аккаунта</h1>

    {{-- Tabs --}}
    <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700 mb-6">
        @foreach([['profile','Профиль'],['api','API'],['telegram','Telegram'],['notifications','Уведомления'],['webhooks','Вебхуки']] as [$key, $label])
        <button @click="tab = '{{ $key }}'" :class="tab === '{{ $key }}' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
            class="px-4 py-2 text-sm font-medium transition -mb-px">{{ $label }}</button>
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
                    @foreach($timezones as $tz)
                    <option value="{{ $tz }}" {{ $user->timezone === $tz ? 'selected' : '' }}>{{ $tz }}</option>
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
    </div>

    {{-- Telegram --}}
    <div x-show="tab === 'telegram'" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 max-w-lg">
        <h2 class="text-lg font-medium mb-4">Telegram-уведомления</h2>
        @if($user->tg_chat_id)
        <div class="flex items-center gap-2 text-green-600 dark:text-green-400 mb-4">
            <x-icon icon="check" icon-set="lucide" class="w-5 h-5" />
            <span>Подключён (с {{ $user->tg_connected_at?->format('d.m.Y') }})</span>
        </div>
        <form method="POST" action="{{ route('settings.tg.unlink') }}">
            @csrf
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg">Отвязать</button>
        </form>
        @else
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
            <p class="text-sm text-blue-700 dark:text-blue-300">
                Напишите боту <strong>@{{ env('TELEGRAM_BOT_USERNAME', 'YourBotName') }}</strong> команду:
            </p>
            <code class="block mt-2 bg-blue-100 dark:bg-blue-900/40 rounded px-3 py-2 text-sm font-mono select-all">
                /start {{ $user->tg_code }}
            </code>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">Ваш код привязки: <strong>{{ $user->tg_code }}</strong></p>
        @endif
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
    <div x-show="tab === 'webhooks'" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 max-w-2xl" x-data="webhooksTab()">
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
function webhooksTab() {
    return {
        showForm: false,
        whForm: { name: '', url: '', secret: '', is_active: true },
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
</script>
@endpush

</x-app-layout>
