@php
$statusColors = [
    'overdue' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border-red-200 dark:border-red-800',
    'critical' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 border-orange-200 dark:border-orange-800',
    'soon' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 border-yellow-200 dark:border-yellow-800',
    'ok' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 border-green-200 dark:border-green-800',
    'none' => 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700',
];
$statusLabels = ['overdue' => 'Просрочено', 'critical' => 'Критично', 'soon' => 'Скоро', 'ok' => 'Активен', 'none' => 'Без даты'];
$status = $service->status;
$daysLeft = $service->days_left;
@endphp
<div class="service-row flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 group/row"
    data-id="{{ $service->id }}"
    data-service="{{ json_encode(['name' => $service->name, 'url' => $service->url, 'ip' => $service->ip, 'group_id' => $service->group_id, 'type_slug' => $service->type_slug, 'expires_at' => $service->expires_at?->toDateString(), 'billing_cycle' => $service->billing_cycle, 'cost' => $service->cost, 'currency' => $service->currency, 'provider_name' => $service->provider_name, 'provider_url' => $service->provider_url, 'notes' => $service->notes, 'notifications_enabled' => $service->notifications_enabled, 'auto_renew' => $service->auto_renew]) }}">

    {{-- Drag handle --}}
    <x-icon icon="grip-vertical" icon-set="lucide" class="w-4 h-4 text-gray-300 dark:text-gray-600 cursor-grab service-drag-handle opacity-0 group-hover/row:opacity-100 flex-shrink-0" />

    {{-- Icon --}}
    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
        style="background-color: {{ $service->color ? $service->color . '20' : '#64748b20' }}">
        @if($service->icon)
            <x-icon :icon="$service->icon" :icon-set="$service->icon_set ?? 'lucide'" class="w-5 h-5" :color="$service->color" />
        @else
            <x-icon icon="tag" icon-set="lucide" class="w-5 h-5 text-gray-400" />
        @endif
    </div>

    {{-- Name & meta --}}
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <span class="font-medium text-sm truncate">{{ $service->name }}</span>
            @if($service->is_trial)
            <span class="text-xs bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-300 px-1.5 py-0.5 rounded">Trial</span>
            @endif
        </div>
        <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 mt-0.5">
            @if($service->ip) <span>{{ $service->ip }}</span> @endif
            @if($service->provider_name) <span>{{ $service->provider_name }}</span> @endif
            @if($service->cost) <span>{{ $service->cost }} {{ $service->currency }}</span> @endif
        </div>
    </div>

    {{-- Expiry date with inline edit --}}
    <div class="text-right flex-shrink-0 min-w-24">
        @if($service->expires_at)
        <div x-data="{ editing: false, date: '{{ $service->expires_at->toDateString() }}' }">
            <span x-show="!editing" @click="editing = true"
                class="text-xs font-mono cursor-pointer hover:text-blue-600">{{ $service->expires_at->format('d.m.Y') }}</span>
            <input x-show="editing" type="date" x-model="date"
                @blur="editing = false; updateExpiry({{ $service->id }}, date)"
                @keydown.enter="editing = false; updateExpiry({{ $service->id }}, date)"
                @keydown.escape="editing = false"
                x-ref="dateInput"
                x-init="$watch('editing', v => v && $nextTick(() => $refs.dateInput.focus()))"
                class="w-32 text-xs border border-blue-500 rounded px-1 py-0.5 bg-white dark:bg-gray-700">
        </div>
        <span class="text-xs px-1.5 py-0.5 rounded border {{ $statusColors[$status] }}">
            @if($daysLeft !== null)
                {{ $status === 'overdue' ? abs($daysLeft).' д. назад' : ($daysLeft === 0 ? 'сегодня' : $daysLeft.' д.') }}
            @else
                {{ $statusLabels[$status] }}
            @endif
        </span>
        @else
        <span class="text-xs text-gray-400">Без даты</span>
        @endif
    </div>

    {{-- Notifications toggle --}}
    <button @click="toggleServiceNotifications({{ $service->id }})"
        class="p-1 rounded opacity-0 group-hover/row:opacity-100 hover:bg-gray-100 dark:hover:bg-gray-700 flex-shrink-0"
        aria-label="{{ $service->notifications_enabled ? 'Отключить уведомления' : 'Включить уведомления' }}">
        <x-icon icon="bell" icon-set="lucide" class="w-4 h-4 {{ $service->notifications_enabled ? 'text-blue-500' : 'text-gray-300' }}" />
    </button>

    {{-- Actions menu --}}
    <div class="relative flex-shrink-0" x-data="{ open: false }">
        <button @click="open = !open"
            class="p-1 rounded opacity-0 group-hover/row:opacity-100 hover:bg-gray-100 dark:hover:bg-gray-700"
            aria-label="Меню действий">
            <x-icon icon="more-vertical" icon-set="lucide" class="w-4 h-4 text-gray-500" />
        </button>
        <div x-show="open" @click.outside="open = false" x-transition
            class="absolute right-0 top-full mt-1 w-44 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-30 text-sm overflow-hidden">
            <button @click="open=false; editService({{ $service->id }})" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 w-full text-left">
                <x-icon icon="edit-2" icon-set="lucide" class="w-4 h-4" /> Редактировать
            </button>
            <button @click="open=false; openRenew({{ json_encode(['id' => $service->id, 'name' => $service->name, 'billing_cycle' => $service->billing_cycle]) }})" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 w-full text-left">
                <x-icon icon="refresh-cw" icon-set="lucide" class="w-4 h-4" /> Продлить
            </button>
            <button @click="open=false; duplicateService({{ $service->id }})" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 w-full text-left">
                <x-icon icon="copy" icon-set="lucide" class="w-4 h-4" /> Дублировать
            </button>
            <div class="border-t border-gray-200 dark:border-gray-700"></div>
            <button @click="open=false; deleteService({{ $service->id }})" class="flex items-center gap-2 px-4 py-2 hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 w-full text-left">
                <x-icon icon="trash-2" icon-set="lucide" class="w-4 h-4" /> Удалить
            </button>
        </div>
    </div>
</div>

<script>
async function updateExpiry(id, date) {
    await fetch(`/services/${id}/expiry`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ expires_at: date }),
    });
}
async function duplicateService(id) {
    const res = await fetch(`/services/${id}/duplicate`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
    });
    const data = await res.json();
    if (data.success) { showToast('Сервис скопирован'); window.location.reload(); }
}
</script>
