<x-app-layout>
<x-slot name="title">Уведомления</x-slot>

<div x-data="notificationsPage()" x-init="init()">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Правила уведомлений</h1>
        <button @click="showForm = true"
            class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
            <x-icon icon="plus" icon-set="lucide" class="w-4 h-4" />
            Добавить правило
        </button>
    </div>

    {{-- Telegram status notice --}}
    @if(!auth()->user()->tg_chat_id)
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 mb-6 flex items-start gap-3">
        <x-icon icon="alert-triangle" icon-set="lucide" class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" />
        <div>
            <div class="font-medium text-amber-800 dark:text-amber-200 text-sm">Telegram не подключён</div>
            <div class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                Для получения Telegram-уведомлений <a href="{{ route('settings') }}?tab=telegram" class="underline hover:no-underline">привяжите Telegram-аккаунт</a>.
            </div>
        </div>
    </div>
    @endif

    {{-- Rules list --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        @if($rules->isEmpty())
        <div class="py-16 text-center text-gray-400 dark:text-gray-500">
            <x-icon icon="bell-off" icon-set="lucide" class="w-10 h-10 mx-auto mb-3 opacity-40" />
            <p class="text-sm">Нет правил уведомлений</p>
            <p class="text-xs mt-1">Добавьте глобальное правило чтобы получать напоминания о всех сервисах</p>
        </div>
        @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Канал</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">За сколько дней</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Область</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Группа / Сервис</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($rules as $rule)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-3">
                        @if($rule->channel === 'tg')
                        <span class="flex items-center gap-2">
                            <x-icon icon="message-circle" icon-set="lucide" class="w-4 h-4 text-blue-500" />
                            Telegram
                        </span>
                        @else
                        <span class="flex items-center gap-2">
                            <x-icon icon="webhook" icon-set="lucide" class="w-4 h-4 text-purple-500" />
                            Webhook
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="font-medium">{{ $rule->days_before }}</span>
                        <span class="text-gray-500"> дн.</span>
                    </td>
                    <td class="px-4 py-3">
                        @if($rule->is_global)
                        <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full">Глобальное</span>
                        @elseif($rule->group_id)
                        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full">Группа</span>
                        @elseif($rule->service_id)
                        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full">Сервис</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">
                        {{ $rule->group?->name ?? $rule->service?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <button @click="deleteRule({{ $rule->id }})"
                            class="text-red-500 hover:text-red-700 p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20">
                            <x-icon icon="trash-2" icon-set="lucide" class="w-4 h-4" />
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Add rule modal --}}
    <div x-show="showForm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="showForm = false">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
            <h2 class="text-lg font-semibold mb-4">Новое правило</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Канал</label>
                    <select x-model="form.channel" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <option value="tg">Telegram</option>
                        <option value="webhook">Webhook</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">За сколько дней</label>
                    <select x-model="form.days_before" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        @foreach([1,3,7,14,30,60] as $d)
                        <option value="{{ $d }}">{{ $d }} дней</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Область</label>
                    <select x-model="form.scope" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <option value="global">Все сервисы (глобальное)</option>
                        @foreach(auth()->user()->groups()->orderBy('sort_order')->get() as $group)
                        <option value="group:{{ $group->id }}">Группа: {{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button @click="saveRule()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm font-medium">Сохранить</button>
                <button @click="showForm = false" class="flex-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 py-2 rounded-lg text-sm">Отмена</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function notificationsPage() {
    return {
        showForm: false,
        form: { channel: 'tg', days_before: 7, scope: 'global' },
        init() {},
        async saveRule() {
            const payload = {
                channel: this.form.channel,
                days_before: parseInt(this.form.days_before),
                is_global: this.form.scope === 'global',
                group_id: this.form.scope.startsWith('group:') ? parseInt(this.form.scope.split(':')[1]) : null,
            };
            const res = await fetch('/notifications', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (data.success) { showToast('Правило добавлено'); window.location.reload(); }
            else showToast(data.message || 'Ошибка', 'error');
        },
        async deleteRule(id) {
            if (!confirm('Удалить правило?')) return;
            await fetch(`/notifications/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
            });
            showToast('Правило удалено');
            window.location.reload();
        },
    };
}
</script>
@endpush
</x-app-layout>
