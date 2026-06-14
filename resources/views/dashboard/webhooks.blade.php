<x-app-layout>
<x-slot name="title">Вебхуки</x-slot>

<div x-data="webhooksPage()" x-init="init()">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Вебхуки</h1>
        <button @click="showForm = true"
            class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
            <x-icon icon="plus" icon-set="lucide" class="w-4 h-4" />
            Добавить вебхук
        </button>
    </div>

    <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 text-sm text-blue-700 dark:text-blue-300">
        <p>Вебхуки отправляются POST-запросом при наступлении события (истечение сервиса).</p>
        <p class="mt-1">Заголовок <code class="bg-blue-100 dark:bg-blue-900/40 px-1 rounded">X-SubTracker-Signature</code>: HMAC-SHA256 тела запроса с вашим секретом.</p>
    </div>

    {{-- Webhooks list --}}
    @if($webhooks->isEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 py-16 text-center text-gray-400 dark:text-gray-500">
        <x-icon icon="webhook" icon-set="lucide" class="w-10 h-10 mx-auto mb-3 opacity-40" />
        <p class="text-sm">Нет вебхуков</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($webhooks as $webhook)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 flex items-center gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-medium">{{ $webhook->name }}</span>
                        @if($webhook->is_active)
                        <span class="text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-2 py-0.5 rounded-full">Активен</span>
                        @else
                        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 px-2 py-0.5 rounded-full">Отключён</span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-500 mt-1 truncate">{{ $webhook->url }}</div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="testWebhook({{ $webhook->id }})"
                        class="text-sm text-blue-600 hover:text-blue-800 px-3 py-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20">
                        Тест
                    </button>
                    <button @click="deleteWebhook({{ $webhook->id }})"
                        class="text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                        <x-icon icon="trash-2" icon-set="lucide" class="w-4 h-4" />
                    </button>
                </div>
            </div>
            <div id="result-{{ $webhook->id }}" class="hidden px-5 py-2 bg-gray-50 dark:bg-gray-700 text-xs font-mono text-gray-600 dark:text-gray-400 border-t border-gray-200 dark:border-gray-600"></div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Add webhook modal --}}
    <div x-show="showForm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="showForm = false">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
            <h2 class="text-lg font-semibold mb-4">Новый вебхук</h2>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Название *</label>
                    <input type="text" x-model="form.name" placeholder="Мой вебхук" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL *</label>
                    <input type="url" x-model="form.url" placeholder="https://example.com/webhook" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Секрет (HMAC)</label>
                    <input type="text" x-model="form.secret" placeholder="Необязательно"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" x-model="form.is_active" class="rounded">
                    <span>Активен</span>
                </label>
            </div>
            <div class="flex gap-3 mt-6">
                <button @click="saveWebhook()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm font-medium">Создать</button>
                <button @click="showForm = false" class="flex-1 bg-gray-100 dark:bg-gray-700 py-2 rounded-lg text-sm">Отмена</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function webhooksPage() {
    return {
        showForm: false,
        form: { name: '', url: '', secret: '', is_active: true },
        init() {},
        async saveWebhook() {
            if (!this.form.name || !this.form.url) { showToast('Заполните обязательные поля', 'error'); return; }
            const res = await fetch('/webhooks', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(this.form),
            });
            const data = await res.json();
            if (data.success) { showToast('Вебхук создан'); window.location.reload(); }
            else showToast(data.message || 'Ошибка', 'error');
        },
        async testWebhook(id) {
            const el = document.getElementById(`result-${id}`);
            el.textContent = 'Отправка...';
            el.classList.remove('hidden');
            const res = await fetch(`/webhooks/${id}/test`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
            });
            const data = await res.json();
            el.textContent = `Код: ${data.status_code ?? '—'} | ${data.response_body ? data.response_body.substring(0, 200) : 'нет ответа'}`;
        },
        async deleteWebhook(id) {
            if (!confirm('Удалить вебхук и все его логи?')) return;
            await fetch(`/webhooks/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
            });
            showToast('Вебхук удалён');
            window.location.reload();
        },
    };
}
</script>
@endpush
</x-app-layout>
