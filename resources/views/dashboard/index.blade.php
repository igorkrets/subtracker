<x-app-layout>
<x-slot name="title">Дашборд</x-slot>

<div x-data="dashboard()" x-init="init()">

{{-- Summary cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @php $cards = [
        ['label' => 'Истекает сегодня', 'value' => $stats['today'], 'color' => 'red', 'status' => 'today'],
        ['label' => 'В 7 дней', 'value' => $stats['week'], 'color' => 'orange', 'status' => 'week'],
        ['label' => 'В 30 дней', 'value' => $stats['month'], 'color' => 'yellow', 'status' => 'month'],
        ['label' => 'Всего', 'value' => $stats['total'], 'color' => 'blue', 'status' => ''],
    ]; @endphp
    @foreach($cards as $card)
    <a href="{{ route('dashboard', ['status' => $card['status']]) }}"
        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition group">
        <div class="text-2xl font-bold text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400">{{ $card['value'] }}</div>
        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $card['label'] }}</div>
    </a>
    @endforeach
</div>

{{-- Spend analytics --}}
@if(!empty($spendStats['monthly']))
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Расходы на подписки</h3>
    <div class="flex flex-wrap gap-6">
        @foreach($spendStats['monthly'] as $currency => $amount)
        <div>
            <div class="text-xs text-gray-500 dark:text-gray-400">В месяц ({{ $currency }})</div>
            <div class="font-semibold">{{ number_format($amount, 2) }} {{ $currency }}</div>
        </div>
        <div>
            <div class="text-xs text-gray-500 dark:text-gray-400">В год ({{ $currency }})</div>
            <div class="font-semibold">{{ number_format($spendStats['yearly'][$currency] ?? 0, 2) }} {{ $currency }}</div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Toolbar --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-3 mb-4 flex flex-wrap gap-3 items-center">
    {{-- Search --}}
    <div class="flex-1 min-w-48">
        <form method="GET" action="{{ route('dashboard') }}">
            <div class="relative">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Поиск по названию, IP, URL..."
                    class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <x-icon icon="search" icon-set="lucide" class="w-4 h-4 absolute left-2.5 top-2.5 text-gray-400" />
            </div>
        </form>
    </div>
    {{-- Sort --}}
    <select name="sort" form="filter-form" onchange="document.getElementById('filter-form').submit()"
        class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="expires_asc" {{ request('sort','expires_asc')==='expires_asc'?'selected':'' }}>По дате ↑</option>
        <option value="expires_desc" {{ request('sort')==='expires_desc'?'selected':'' }}>По дате ↓</option>
        <option value="name_asc" {{ request('sort')==='name_asc'?'selected':'' }}>По названию ↑</option>
        <option value="cost_desc" {{ request('sort')==='cost_desc'?'selected':'' }}>По стоимости ↓</option>
        <option value="created_desc" {{ request('sort')==='created_desc'?'selected':'' }}>Новые</option>
    </select>
    <form id="filter-form" method="GET" action="{{ route('dashboard') }}" class="hidden">
        <input type="hidden" name="q" value="{{ request('q') }}">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="hidden" name="mode" value="{{ $mode }}">
        <input type="hidden" name="sort" value="" id="sort-input">
    </form>
    {{-- Mode toggle --}}
    <div class="flex border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
        <a href="{{ route('dashboard', array_merge(request()->all(), ['mode' => 'grouped'])) }}"
            class="px-3 py-2 text-sm {{ $mode === 'grouped' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50' }}">
            <x-icon icon="layers" icon-set="lucide" class="w-4 h-4" />
        </a>
        <a href="{{ route('dashboard', array_merge(request()->all(), ['mode' => 'flat'])) }}"
            class="px-3 py-2 text-sm {{ $mode === 'flat' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50' }}">
            <x-icon icon="list" icon-set="lucide" class="w-4 h-4" />
        </a>
    </div>
    {{-- Actions --}}
    <button @click="showServiceModal = true; editingService = null"
        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
        <x-icon icon="plus" icon-set="lucide" class="w-4 h-4" /> Сервис
    </button>
    <button @click="showGroupModal = true; editingGroup = null"
        class="flex items-center gap-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">
        <x-icon icon="plus" icon-set="lucide" class="w-4 h-4" /> Группа
    </button>
    {{-- Export dropdown --}}
    <div class="relative" x-data="{ open: false }">
        <button @click="open = !open" class="flex items-center gap-1 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm px-3 py-2 rounded-lg transition">
            <x-icon icon="download" icon-set="lucide" class="w-4 h-4" />
            <x-icon icon="chevron-down" icon-set="lucide" class="w-3 h-3" />
        </button>
        <div x-show="open" @click.outside="open = false" x-transition
            class="absolute right-0 top-full mt-1 w-40 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-20 text-sm overflow-hidden">
            <a href="{{ route('export', ['format' => 'html']) }}" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700">HTML</a>
            <a href="{{ route('export', ['format' => 'pdf']) }}" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700">PDF</a>
            <a href="{{ route('export', ['format' => 'xlsx']) }}" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700">Excel</a>
            <div class="border-t border-gray-200 dark:border-gray-700"></div>
            <a href="{{ route('backup.download', ['format' => 'json']) }}" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700">JSON-бекап</a>
            <button @click="open=false; document.getElementById('import-form').classList.toggle('hidden')"
                class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 w-full text-left">Импорт</button>
        </div>
    </div>
</div>

{{-- Import form --}}
<div id="import-form" class="hidden bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 mb-4">
    <form method="POST" action="{{ route('backup.import') }}" enctype="multipart/form-data" class="flex items-center gap-3">
        @csrf
        <input type="file" name="file" accept=".json,.csv,.sql" class="text-sm" required>
        <select name="mode" class="text-sm border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-gray-700">
            <option value="merge">Слить</option>
            <option value="clear">Очистить и импортировать</option>
        </select>
        <button type="submit" class="bg-blue-600 text-white text-sm px-4 py-1.5 rounded-lg">Импортировать</button>
    </form>
</div>

{{-- Empty state --}}
@if($services->isEmpty())
<div class="text-center py-20">
    <x-icon icon="layers" icon-set="lucide" class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" />
    <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">
        @if(request('q'))
            Ничего не найдено по запросу "{{ request('q') }}"
        @else
            Добавьте первый сервис или импортируйте бекап
        @endif
    </h3>
    @if(!request('q'))
    <button @click="showServiceModal = true" class="mt-4 bg-blue-600 text-white text-sm px-6 py-2 rounded-lg hover:bg-blue-700 transition">
        Добавить сервис
    </button>
    @endif
</div>
@elseif($mode === 'flat')
{{-- Flat mode --}}
<div class="space-y-2" id="services-list">
    @foreach($services as $service)
        @include('components.service-card', ['service' => $service])
    @endforeach
</div>
{{ $services->links() }}
@else
{{-- Grouped mode --}}
<div id="groups-container" class="space-y-3">
    @foreach($groupedServices as $groupId => $groupData)
    @php $group = $groupData['group']; $groupServices = $groupData['services']; @endphp
    <div class="group-block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
        data-group-id="{{ $group?->id ?? 'null' }}"
        x-data="{ expanded: JSON.parse(localStorage.getItem('group_{{ $group?->id ?? 'null' }}') ?? 'true') }"
        x-init="$watch('expanded', v => localStorage.setItem('group_{{ $group?->id ?? 'null' }}', v))">

        {{-- Group header --}}
        <div class="flex items-center gap-2 px-4 py-3 cursor-pointer select-none" @click.self="expanded = !expanded">
            <x-icon icon="grip-vertical" icon-set="lucide" class="w-4 h-4 text-gray-400 drag-handle cursor-grab" />
            <button @click="expanded = !expanded" class="flex items-center gap-1 text-sm" aria-expanded="expanded">
                <x-icon icon="chevron-right" icon-set="lucide" class="w-4 h-4 transition-transform" :class="expanded ? 'rotate-90' : ''" />
            </button>
            @if($group)
                <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $group->color ?? '#94a3b8' }}"></span>
                <span class="font-medium text-sm">{{ $group->name }}</span>
                <span class="ml-auto text-xs text-gray-400">
                    @php $nearest = $group->nearest_expires_at; @endphp
                    @if($nearest) {{ \Carbon\Carbon::parse($nearest)->format('d.m.Y') }} @endif
                </span>
                <button wire:click.stop class="p-1 hover:text-red-500" title="{{ $group->notifications_enabled ? 'Отключить уведомления' : 'Включить уведомления' }}"
                    @click.stop="toggleGroupNotifications({{ $group->id }}, $el)">
                    <x-icon icon="{{ $group->notifications_enabled ? 'bell' : 'bell' }}" icon-set="lucide" class="w-4 h-4 {{ $group->notifications_enabled ? 'text-blue-500' : 'text-gray-300' }}" />
                </button>
                <button @click.stop="editGroup({{ $group->id }}, '{{ addslashes($group->name) }}', '{{ $group->color ?? '#94a3b8' }}')"
                    class="p-1 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                    <x-icon icon="settings" icon-set="lucide" class="w-4 h-4" />
                </button>
            @else
                <span class="font-medium text-sm text-gray-400">Без группы</span>
                <span class="ml-auto"></span>
            @endif
        </div>

        {{-- Services in group --}}
        <div x-show="expanded" x-collapse class="services-container divide-y divide-gray-100 dark:divide-gray-700/50" data-group-id="{{ $group?->id ?? '' }}">
            @forelse($groupServices as $service)
                @include('components.service-card', ['service' => $service])
            @empty
                <div class="px-4 py-3 text-sm text-gray-400 text-center">Нет сервисов</div>
            @endforelse
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Service Modal --}}
<div x-show="showServiceModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
    @keydown.escape.window="showServiceModal = false">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
        @click.outside="showServiceModal = false" role="dialog" aria-modal="true">
        <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="font-semibold text-lg" x-text="editingService ? 'Редактировать сервис' : 'Добавить сервис'"></h2>
            <button @click="showServiceModal = false" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <x-icon icon="x" icon-set="lucide" class="w-5 h-5" />
            </button>
        </div>
        <form @submit.prevent="saveService()" class="p-6 space-y-4">
            {{-- Autocomplete name --}}
            <div x-data="catalogAutocomplete()" x-init="init()" class="relative">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Название *</label>
                <input type="text" x-model="$parent.form.name" @input="search()" @focus="search()"
                    placeholder="Начните вводить название..."
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div x-show="results.length > 0" x-transition
                    class="absolute z-30 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg overflow-hidden max-h-48 overflow-y-auto">
                    <template x-for="item in results" :key="item.slug">
                        <button type="button" @click="select(item)"
                            class="flex items-center gap-3 w-full px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm text-left">
                            <img :src="item.icon_url" class="w-5 h-5 object-contain" onerror="this.style.display='none'" :alt="item.name">
                            <span x-text="item.name"></span>
                            <span class="text-gray-400 text-xs ml-auto" x-text="item.type_slug"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL</label>
                    <input type="url" x-model="form.url" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">IP-адрес</label>
                    <input type="text" x-model="form.ip" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Группа</label>
                    <select x-model="form.group_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Без группы</option>
                        @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тип</label>
                    <select x-model="form.type_slug" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— выберите тип —</option>
                        <option value="vps">VPS / VDS</option>
                        <option value="dedicated">Выделенный сервер</option>
                        <option value="hosting">Хостинг</option>
                        <option value="domain">Домен</option>
                        <option value="ssl">SSL</option>
                        <option value="cloud">Облако</option>
                        <option value="saas">SaaS</option>
                        <option value="ai">AI-сервис</option>
                        <option value="streaming">Стриминг</option>
                        <option value="other">Другое</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата истечения</label>
                    <input type="date" x-model="form.expires_at" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Периодичность</label>
                    <select x-model="form.billing_cycle" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— не указана —</option>
                        <option value="monthly">Ежемесячно</option>
                        <option value="quarterly">Ежеквартально</option>
                        <option value="semiannual">Раз в полгода</option>
                        <option value="yearly">Ежегодно</option>
                        <option value="one_time">Разовый</option>
                        <option value="custom">Произвольно</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Стоимость</label>
                    <input type="number" x-model="form.cost" step="0.01" min="0"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Валюта</label>
                    <select x-model="form.currency" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option>RUB</option><option>USD</option><option>EUR</option><option>GBP</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Провайдер</label>
                    <input type="text" x-model="form.provider_name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ссылка провайдера</label>
                    <input type="url" x-model="form.provider_url" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Заметки</label>
                <textarea x-model="form.notes" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            {{-- Encrypted notes --}}
            <div>
                <label class="flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <x-icon icon="lock" icon-set="lucide" class="w-3.5 h-3.5 text-amber-500" />
                    Заметки (шифр)
                </label>
                <textarea x-model="encNote.plain" rows="3"
                    :placeholder="encNote.wrongPwd ? 'Зашифровано другим паролем — расшифруйте ниже' : (localPwd ? 'Хранится только в зашифрованном виде...' : 'Задайте Локальный пароль в настройках...')"
                    :disabled="!localPwd && !encNote.wrongPwd"
                    class="w-full px-3 py-2 border border-amber-300 dark:border-amber-700/60 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 disabled:opacity-50 disabled:cursor-not-allowed font-mono"></textarea>

                {{-- Status hints --}}
                <template x-if="!localPwd && !encNote.wrongPwd">
                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1">
                        <x-icon icon="alert-triangle" icon-set="lucide" class="w-3 h-3 flex-shrink-0" />
                        Задайте Локальный пароль в <a href="{{ route('settings') }}?tab=security" class="underline">Настройках</a> для шифрования
                    </p>
                </template>
                <template x-if="localPwd && !encNote.wrongPwd && !encNote.plain && form.encrypted_notes">
                    <p class="mt-1 text-xs text-green-600 dark:text-green-400 flex items-center gap-1">
                        <x-icon icon="shield-check" icon-set="lucide" class="w-3 h-3" />
                        Расшифровано · AES-256-GCM
                    </p>
                </template>
                <template x-if="localPwd && !encNote.wrongPwd && !form.encrypted_notes">
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
                        <x-icon icon="shield" icon-set="lucide" class="w-3 h-3" />
                        Шифруется AES-256-GCM · на сервере хранится только шифртекст
                    </p>
                </template>

                {{-- Wrong password warning --}}
                <template x-if="encNote.wrongPwd">
                    <div class="mt-1.5">
                        <p class="text-xs text-red-600 dark:text-red-400 flex items-center gap-1 mb-1.5">
                            <x-icon icon="key" icon-set="lucide" class="w-3 h-3" />
                            Зашифровано другим паролем
                            <button type="button" @click="encNote.showTempPwd = !encNote.showTempPwd"
                                class="ml-1 underline hover:no-underline">
                                Попробовать другой пароль
                            </button>
                        </p>
                        <div x-show="encNote.showTempPwd" class="flex gap-2">
                            <input type="password" x-model="encNote.tempPwd"
                                placeholder="Введите пароль для расшифровки"
                                @keydown.enter="decryptWithTempPwd()"
                                class="flex-1 px-2 py-1.5 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-400">
                            <button type="button" @click="decryptWithTempPwd()"
                                :disabled="!encNote.tempPwd"
                                class="px-3 py-1.5 text-xs bg-amber-500 hover:bg-amber-600 text-white rounded-lg disabled:opacity-50">
                                Расшифровать
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="checkbox" x-model="form.notifications_enabled" class="rounded">
                    Уведомления
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="checkbox" x-model="form.auto_renew" class="rounded">
                    Авто-продление
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="showServiceModal = false"
                    class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    Отмена
                </button>
                <button type="submit" :disabled="saving"
                    class="px-6 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50 transition">
                    <span x-text="saving ? 'Сохранение...' : (editingService ? 'Обновить' : 'Добавить')"></span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Group Modal --}}
<div x-show="showGroupModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
    @keydown.escape.window="showGroupModal = false">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md"
        @click.outside="showGroupModal = false" role="dialog">
        <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="font-semibold text-lg" x-text="editingGroup ? 'Редактировать группу' : 'Добавить группу'"></h2>
            <button @click="showGroupModal = false" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <x-icon icon="x" icon-set="lucide" class="w-5 h-5" />
            </button>
        </div>
        <form @submit.prevent="saveGroup()" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Название *</label>
                <input type="text" x-model="groupForm.name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Цвет</label>
                <div class="flex gap-2 flex-wrap">
                    @foreach(['#2563EB','#16A34A','#DC2626','#D97706','#7C3AED','#0891B2','#64748B','#EC4899','#0F766E','#C2410C','#1D4ED8','#15803D'] as $c)
                    <button type="button" @click="groupForm.color = '{{ $c }}'"
                        class="w-7 h-7 rounded-full border-2 transition"
                        :class="groupForm.color === '{{ $c }}' ? 'border-gray-900 dark:border-white scale-110' : 'border-transparent'"
                        style="background-color: {{ $c }}">
                    </button>
                    @endforeach
                    <input type="color" x-model="groupForm.color" class="w-7 h-7 rounded-full cursor-pointer border border-gray-300">
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" @click="showGroupModal = false"
                    class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg">Отмена</button>
                <button type="submit" :disabled="saving"
                    class="px-6 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    <span x-text="editingGroup ? 'Обновить' : 'Создать'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Renew Modal --}}
<div x-show="showRenewModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
    @keydown.escape.window="showRenewModal = false">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm"
        @click.outside="showRenewModal = false" role="dialog">
        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
            <h2 class="font-semibold">Продлить сервис</h2>
            <button @click="showRenewModal = false" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <x-icon icon="x" icon-set="lucide" class="w-5 h-5" />
            </button>
        </div>
        <div class="p-5 space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-400" x-text="renewingService?.name"></p>
            <div class="flex gap-2 flex-wrap">
                <button @click="renew({months: 1})" class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20">+1 мес</button>
                <button @click="renew({months: 3})" class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20">+3 мес</button>
                <button @click="renew({months: 6})" class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20">+6 мес</button>
                <button @click="renew({months: 12})" class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20">+12 мес</button>
                <template x-if="renewingService?.billing_cycle">
                    <button @click="renew({cycle: true})" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg">+1 период</button>
                </template>
            </div>
            <div class="flex gap-2">
                <input type="number" x-model="renewMonths" placeholder="N месяцев" min="1" max="120"
                    class="flex-1 px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button @click="renew({months: parseInt(renewMonths)})" :disabled="!renewMonths"
                    class="px-3 py-1.5 text-sm bg-gray-600 text-white rounded-lg disabled:opacity-50">Продлить</button>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Указать дату напрямую</label>
                <div class="flex gap-2">
                    <input type="date" x-model="renewDate"
                        class="flex-1 px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button @click="renew({expires_at: renewDate, record_payment: false})" :disabled="!renewDate"
                        class="px-3 py-1.5 text-sm bg-gray-600 text-white rounded-lg disabled:opacity-50">Задать</button>
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                <input type="checkbox" x-model="renewRecordPayment" class="rounded">
                Записать оплату
            </label>
        </div>
    </div>
</div>

</div>{{-- end x-data --}}

@push('scripts')
<script>
function dashboard() {
    return {
        showServiceModal: false,
        showGroupModal: false,
        showRenewModal: false,
        editingService: null,
        editingGroup: null,
        renewingService: null,
        renewMonths: '',
        renewDate: '',
        renewRecordPayment: true,
        saving: false,
        localPwd: null,
        encNote: { plain: '', wrongPwd: false, showTempPwd: false, tempPwd: '' },
        form: {
            name: '', url: '', ip: '', group_id: '', type_slug: '',
            expires_at: '', billing_cycle: '', cost: '', currency: 'RUB',
            provider_name: '', provider_url: '', notes: '', encrypted_notes: null,
            notifications_enabled: true, auto_renew: false,
        },
        groupForm: { name: '', color: '#2563EB' },

        init() {
            this.localPwd = window.SubCrypto.getPassword();
            this.initSortable();
        },

        initSortable() {
            if (typeof Sortable === 'undefined') return;
            // Groups sortable
            const container = document.getElementById('groups-container');
            if (!container) return;
            Sortable.create(container, {
                handle: '.drag-handle',
                animation: 150,
                onEnd: () => {
                    const ids = [...container.querySelectorAll('.group-block')].map(el => el.dataset.groupId);
                    this.apiFetch('{{ route("groups.sort") }}', 'POST', { ids });
                }
            });
            // Services sortable
            document.querySelectorAll('.services-container').forEach(el => {
                Sortable.create(el, {
                    handle: '.service-drag-handle',
                    group: 'services',
                    animation: 150,
                    onEnd: (evt) => {
                        const items = [];
                        document.querySelectorAll('.service-row').forEach((row, i) => {
                            items.push({ id: row.dataset.id, order: i, group_id: row.closest('.services-container')?.dataset.groupId || null });
                        });
                        this.apiFetch('{{ route("services.sort") }}', 'POST', { items });
                    }
                });
            });
        },

        resetForm() {
            this.form = { name: '', url: '', ip: '', group_id: '', type_slug: '', expires_at: '',
                billing_cycle: '', cost: '', currency: 'RUB', provider_name: '', provider_url: '',
                notes: '', encrypted_notes: null, notifications_enabled: true, auto_renew: false };
            this.encNote = { plain: '', wrongPwd: false, showTempPwd: false, tempPwd: '' };
        },

        async editService(serviceId) {
            const row = document.querySelector(`.service-row[data-id="${serviceId}"]`);
            if (!row) return;
            this.editingService = serviceId;
            this.form = JSON.parse(row.dataset.service || '{}');
            this.encNote = { plain: '', wrongPwd: false, showTempPwd: false, tempPwd: '' };
            if (this.form.encrypted_notes && this.localPwd) {
                try {
                    this.encNote.plain = await window.SubCrypto.decrypt(this.form.encrypted_notes, this.localPwd);
                } catch {
                    this.encNote.wrongPwd = true;
                }
            }
            this.showServiceModal = true;
        },

        async decryptWithTempPwd() {
            if (!this.encNote.tempPwd || !this.form.encrypted_notes) return;
            try {
                this.encNote.plain = await window.SubCrypto.decrypt(this.form.encrypted_notes, this.encNote.tempPwd);
                this.encNote.wrongPwd = false;
                this.encNote.showTempPwd = false;
                this.encNote.tempPwd = '';
            } catch {
                showToast('Неверный пароль', 'error');
            }
        },

        editGroup(id, name, color) {
            this.editingGroup = id;
            this.groupForm = { name, color };
            this.showGroupModal = true;
        },

        openRenew(service) {
            this.renewingService = service;
            this.renewMonths = '';
            this.renewDate = '';
            this.renewRecordPayment = true;
            this.showRenewModal = true;
        },

        async saveService() {
            this.saving = true;
            // Encrypt the secure notes field before sending to server
            if (this.encNote.plain) {
                if (!this.localPwd) {
                    showToast('Задайте Локальный пароль в Настройках → Профиль для шифрования', 'error');
                    this.saving = false;
                    return;
                }
                try {
                    this.form.encrypted_notes = await window.SubCrypto.encrypt(this.encNote.plain, this.localPwd);
                } catch {
                    showToast('Ошибка шифрования', 'error');
                    this.saving = false;
                    return;
                }
            } else {
                this.form.encrypted_notes = null;
            }
            const url = this.editingService ? `/services/${this.editingService}` : '/services';
            const method = this.editingService ? 'PUT' : 'POST';
            const res = await this.apiFetch(url, method, this.form);
            if (res?.success) {
                this.showServiceModal = false;
                this.resetForm();
                window.location.reload();
            }
            this.saving = false;
        },

        async saveGroup() {
            this.saving = true;
            const url = this.editingGroup ? `/groups/${this.editingGroup}` : '/groups';
            const method = this.editingGroup ? 'PUT' : 'POST';
            const res = await this.apiFetch(url, method, this.groupForm);
            if (res?.success) {
                this.showGroupModal = false;
                this.groupForm = { name: '', color: '#2563EB' };
                window.location.reload();
            }
            this.saving = false;
        },

        async deleteService(id) {
            if (!confirm('Удалить сервис?')) return;
            const res = await this.apiFetch(`/services/${id}`, 'DELETE');
            if (res?.success) {
                document.querySelector(`.service-row[data-id="${id}"]`)?.remove();
                showToast('Сервис удалён');
            }
        },

        async deleteGroup(id) {
            if (!confirm('Удалить группу? Сервисы перейдут в "Без группы"')) return;
            const res = await this.apiFetch(`/groups/${id}`, 'DELETE');
            if (res?.success) window.location.reload();
        },

        async renew(payload) {
            if (!this.renewingService) return;
            const res = await this.apiFetch(
                `/services/${this.renewingService.id}/renew`,
                'POST',
                { ...payload, record_payment: this.renewRecordPayment }
            );
            if (res?.success) {
                this.showRenewModal = false;
                showToast(`Продлено до ${res.data.expires_at}`);
                window.location.reload();
            }
        },

        async toggleGroupNotifications(id, btn) {
            const res = await this.apiFetch(`/groups/${id}/toggle-notifications`, 'PATCH');
            if (res?.success) window.location.reload();
        },

        async toggleServiceNotifications(id) {
            await this.apiFetch(`/services/${id}/toggle-notifications`, 'PATCH');
            window.location.reload();
        },

        async apiFetch(url, method, data = null) {
            try {
                const opts = {
                    method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
                };
                if (data && method !== 'GET') opts.body = JSON.stringify(data);
                const res = await fetch(url, opts);
                const json = await res.json();
                if (!json.success) showToast(json.message || 'Ошибка', 'error');
                return json;
            } catch (e) {
                showToast('Сетевая ошибка', 'error');
                return null;
            }
        }
    };
}

function catalogAutocomplete() {
    return {
        results: [],
        catalog: null,
        fuse: null,
        async init() {
            try {
                const res = await fetch('/catalog.json');
                const data = await res.json();
                this.catalog = data;
                this.fuse = new Fuse(data.presets || [], {
                    keys: ['name', 'aliases'],
                    threshold: 0.3,
                    ignoreLocation: true,
                    minMatchCharLength: 2,
                });
            } catch(e) {}
        },
        search() {
            const q = this.$parent.form.name;
            if (!this.fuse || q.length < 2) { this.results = []; return; }
            this.results = this.fuse.search(q).slice(0, 8).map(r => r.item);
        },
        select(item) {
            const parent = this.$parent;
            parent.form.name = item.name;
            if (item.type_slug) parent.form.type_slug = item.type_slug;
            if (item.default_url) parent.form.provider_url = item.default_url;
            if (item.color) parent.form.color = item.color;
            if (item.icon) parent.form.icon = item.icon;
            if (item.icon_set) parent.form.icon_set = item.icon_set;
            this.results = [];
        }
    };
}
</script>
@endpush

</x-app-layout>
