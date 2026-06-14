<x-app-layout>
<x-slot name="title">Подписки</x-slot>

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
@if($spendStats['total_monthly'] > 0 || !empty($spendStats['by_currency']))
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 mb-6"
     x-data="{ saving: false }" id="spend-widget">
    <div class="flex items-center justify-between mb-3 gap-3 flex-wrap">
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Расходы на подписки</h3>
        {{-- Currency selector --}}
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400">Валюта:</span>
            <select id="display-currency-select"
                class="text-xs border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500"
                @change="
                    saving = true;
                    fetch('{{ route('dashboard.currency') }}', {
                        method: 'PATCH',
                        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':window.csrfToken,'Accept':'application/json'},
                        body: JSON.stringify({currency: $el.value})
                    }).then(() => { saving = false; window.location.reload(); })
                ">
                @foreach($currencies as $cur)
                <option value="{{ $cur }}" {{ $displayCurrency === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                @endforeach
            </select>
            <span x-show="saving" class="inline-flex text-gray-400">
                <x-icon icon="loader-2" icon-set="lucide" class="w-3.5 h-3.5 animate-spin" />
            </span>
        </div>
    </div>

    <div class="flex flex-wrap gap-6">
        {{-- Total in display currency --}}
        <div>
            <div class="text-xs text-gray-500 dark:text-gray-400">В месяц</div>
            <div class="text-xl font-bold text-blue-600 dark:text-blue-400">
                {{ number_format($spendStats['total_monthly'], 2) }} {{ $displayCurrency }}
            </div>
        </div>
        <div>
            <div class="text-xs text-gray-500 dark:text-gray-400">В год</div>
            <div class="text-xl font-bold text-blue-600 dark:text-blue-400">
                {{ number_format($spendStats['total_yearly'], 2) }} {{ $displayCurrency }}
            </div>
        </div>

        {{-- Native breakdown --}}
        @if(count($spendStats['by_currency']) > 1 ||
            (count($spendStats['by_currency']) === 1 && !array_key_exists($displayCurrency, $spendStats['by_currency'])))
        <div class="border-l border-gray-200 dark:border-gray-700 pl-6">
            <div class="text-xs text-gray-400 mb-1">В оригинальных валютах (в месяц)</div>
            <div class="flex flex-wrap gap-3">
                @foreach($spendStats['by_currency'] as $cur => $amt)
                <span class="text-xs text-gray-600 dark:text-gray-300">
                    {{ number_format($amt, 2) }} <span class="font-medium">{{ $cur }}</span>
                </span>
                @endforeach
            </div>
        </div>
        @endif
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
        <option value="manual" {{ $sort==='manual'?'selected':'' }}>Свой порядок</option>
        <option value="expires_asc" {{ $sort==='expires_asc'?'selected':'' }}>По дате ↑</option>
        <option value="expires_desc" {{ $sort==='expires_desc'?'selected':'' }}>По дате ↓</option>
        <option value="name_asc" {{ $sort==='name_asc'?'selected':'' }}>По названию ↑</option>
        <option value="cost_desc" {{ $sort==='cost_desc'?'selected':'' }}>По стоимости ↓</option>
        <option value="created_desc" {{ $sort==='created_desc'?'selected':'' }}>Новые</option>
    </select>
    <form id="filter-form" method="GET" action="{{ route('dashboard') }}" class="hidden">
        <input type="hidden" name="q" value="{{ request('q') }}">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="hidden" name="mode" value="{{ $mode }}">
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
    <button @click="resetForm(); editingService = null; showServiceModal = true"
        class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 sm:px-4 py-2 rounded-lg transition">
        <x-icon icon="plus" icon-set="lucide" class="w-4 h-4 flex-shrink-0" />
        <span>Сервис</span>
    </button>
    <button @click="showGroupModal = true; editingGroup = null"
        class="flex items-center gap-1.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium px-3 sm:px-4 py-2 rounded-lg transition">
        <x-icon icon="plus" icon-set="lucide" class="w-4 h-4 flex-shrink-0" />
        <span class="hidden sm:inline">Группа</span>
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
    <button @click="resetForm(); editingService = null; showServiceModal = true" class="mt-4 bg-blue-600 text-white text-sm px-6 py-2 rounded-lg hover:bg-blue-700 transition">
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
    <div class="group-block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700"
        data-group-id="{{ $group?->id ?? 'null' }}"
        x-data="{ expanded: JSON.parse(localStorage.getItem('group_{{ $group?->id ?? 'null' }}') ?? 'true') }"
        x-init="$watch('expanded', v => localStorage.setItem('group_{{ $group?->id ?? 'null' }}', v))">

        {{-- Group header --}}
        @php
            $gSymbols = ['RUB'=>'₽','USD'=>'$','EUR'=>'€','GBP'=>'£','CNY'=>'¥','UAH'=>'₴','KZT'=>'₸','BYN'=>'Br','TRY'=>'₺'];
            $gSym     = $gSymbols[$displayCurrency] ?? $displayCurrency;
            $gKey     = $group ? $group->id : 'null';
            $gSpend   = $groupSpend[$gKey] ?? 0;
        @endphp
        <div class="flex items-center gap-2 px-4 py-3 cursor-pointer select-none" @click="expanded = !expanded">
            <x-icon icon="grip-vertical" icon-set="lucide" class="w-4 h-4 text-gray-400 drag-handle cursor-grab" />
            <button @click.stop="expanded = !expanded" class="flex items-center gap-1 text-sm" :aria-expanded="expanded">
                <span :class="expanded ? 'rotate-90' : ''" class="inline-flex transition-transform">
                    <x-icon icon="chevron-right" icon-set="lucide" class="w-4 h-4" />
                </span>
            </button>
            @if($group)
                <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $group->color ?? '#94a3b8' }}"></span>
                <span class="font-medium text-sm">{{ $group->name }}</span>
            @else
                <span class="font-medium text-sm text-gray-400">Без группы</span>
            @endif
            @if($group)
                <div class="ml-auto text-right flex-shrink-0">
                    @php $nearest = $group->nearest_expires_at; @endphp
                    @if($nearest)
                    <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($nearest)->format('d.m.Y') }}</div>
                    @endif
                    @if($gSpend > 0)
                    <div class="text-xs text-gray-400 dark:text-gray-500"
                         title="{{ number_format($gSpend * 12, 0, '.', ' ') }} {{ $gSym }}/год">
                        {{ number_format($gSpend, 0, '.', ' ') }} {{ $gSym }}/мес
                    </div>
                    @endif
                </div>
                <button wire:click.stop class="p-1 hover:text-red-500" title="{{ $group->notifications_enabled ? 'Отключить уведомления' : 'Включить уведомления' }}"
                    @click.stop="toggleGroupNotifications({{ $group->id }}, $el)">
                    <x-icon icon="{{ $group->notifications_enabled ? 'bell' : 'bell' }}" icon-set="lucide" class="w-4 h-4 {{ $group->notifications_enabled ? 'text-blue-500' : 'text-gray-300' }}" />
                </button>
                <button @click.stop="editGroup({{ $group->id }}, '{{ addslashes($group->name) }}', '{{ $group->color ?? '#94a3b8' }}')"
                    class="p-1 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                    <x-icon icon="settings" icon-set="lucide" class="w-4 h-4" />
                </button>
            @else
                <div class="ml-auto text-right flex-shrink-0">
                    @if($gSpend > 0)
                    <div class="text-xs text-gray-400 dark:text-gray-500"
                         title="{{ number_format($gSpend * 12, 0, '.', ' ') }} {{ $gSym }}/год">
                        {{ number_format($gSpend, 0, '.', ' ') }} {{ $gSym }}/мес
                    </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Services in group --}}
        <div x-show="expanded" x-collapse class="services-container divide-y divide-gray-100 dark:divide-gray-700/50" data-group-id="{{ $group?->id ?? '' }}">
            @forelse($groupServices as $service)
                @include('components.service-card', ['service' => $service])
            @empty
                <div class="empty-placeholder px-4 py-3 text-sm text-gray-400 text-center">Нет сервисов</div>
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
        role="dialog" aria-modal="true">
        <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="font-semibold text-lg" x-text="editingService ? 'Редактировать сервис' : 'Добавить сервис'"></h2>
            <button @click="showServiceModal = false" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <x-icon icon="x" icon-set="lucide" class="w-5 h-5" />
            </button>
        </div>
        @php
        $serviceIcons = ['server','database','hard-drive','cloud','network','globe','shield','lock','key','code','layers','box','archive','zap','activity','credit-bit','dollar-sign','mail','bell','settings','tag','sparkles','trending-up','link','webhook','server-cog','palette','waypoints'];
        $serviceIcons = array_filter($serviceIcons, fn($i) => file_exists(public_path("icons/lucide/{$i}.svg")));
        $serviceColors = ['#2563EB','#16A34A','#DC2626','#D97706','#7C3AED','#0891B2','#64748B','#EC4899','#0F766E','#C2410C','#1D4ED8','#15803D'];
        @endphp
        <form @submit.prevent="saveService()" class="p-4 sm:p-6 space-y-4">
            {{-- Autocomplete name --}}
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Название *</label>
                <input type="text" x-model="form.name" @input="catalogSearch()" @focus="catalogSearch()"
                    placeholder="Начните вводить название..."
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div x-show="catalogResults.length > 0" x-transition
                    class="absolute z-30 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg overflow-hidden max-h-48 overflow-y-auto">
                    <template x-for="item in catalogResults" :key="item.slug">
                        <button type="button" @click="catalogSelect(item)"
                            class="flex items-center gap-3 w-full px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm text-left">
                            <img :src="item.icon_url" class="w-5 h-5 object-contain" onerror="this.style.display='none'" :alt="item.name">
                            <span x-text="item.name"></span>
                            <span class="text-gray-400 text-xs ml-auto" x-text="item.type_slug"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Icon & Color picker --}}
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Иконка и цвет</label>

                {{-- Preview + Colors row --}}
                <div class="flex items-center gap-3">
                    {{-- Preview box: x-html copies SVG from clicked grid button --}}
                    <div class="w-12 h-12 rounded-xl flex-shrink-0 flex items-center justify-center border-2 border-gray-200 dark:border-gray-600 transition-colors"
                         :style="form.color ? 'background-color:' + form.color + '22; border-color:' + form.color + '55' : ''">
                        <div x-show="form.icon" style="display:none"
                             x-html="iconPreviewHtml"
                             :style="'color:' + (form.color || '#64748b')"
                             class="flex items-center justify-center"></div>
                        <div x-show="!form.icon" style="color:#cbd5e1">
                            <x-icon icon="tag" icon-set="lucide" class="w-6 h-6" />
                        </div>
                    </div>

                    {{-- Color swatches --}}
                    <div class="flex flex-wrap gap-2 items-center flex-1">
                        @foreach($serviceColors as $c)
                        <button type="button"
                            @click="form.color = form.color === '{{ $c }}' ? '' : '{{ $c }}'"
                            class="w-7 h-7 rounded-full flex-shrink-0 transition-all"
                            :style="form.color === '{{ $c }}'
                                ? 'background-color:{{ $c }}; outline:3px solid {{ $c }}; outline-offset:2px'
                                : 'background-color:{{ $c }}'"
                            title="{{ $c }}"></button>
                        @endforeach
                        {{-- Custom color: palette icon label triggers hidden input --}}
                        <label class="w-7 h-7 flex-shrink-0 cursor-pointer flex items-center justify-center rounded-full border-2 border-dashed border-gray-300 dark:border-gray-600 text-gray-400 hover:border-gray-400 hover:text-gray-600 transition-colors" title="Свой цвет">
                            <x-icon icon="palette" icon-set="lucide" class="w-3.5 h-3.5" />
                            <input type="color" x-model="form.color" class="sr-only">
                        </label>
                    </div>
                </div>

                {{-- Icon grid --}}
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(38px,1fr));gap:6px">
                    @foreach($serviceIcons as $ico)
                    <button type="button"
                        data-ico="{{ $ico }}"
                        @click="pickIcon('{{ $ico }}', $el)"
                        class="aspect-square flex items-center justify-center rounded-lg border-2 transition-colors"
                        :class="form.icon === '{{ $ico }}'
                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30'
                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-500'"
                        :style="'color:' + (form.icon === '{{ $ico }}' ? (form.color || '#2563eb') : (form.color ? form.color + 'aa' : '#94a3b8'))"
                        title="{{ $ico }}">
                        <x-icon icon="{{ $ico }}" icon-set="lucide" class="w-5 h-5" />
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL</label>
                    <input type="url" x-model="form.url" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">IP-адрес</label>
                    <input type="text" x-model="form.ip" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

{{-- View Service Modal --}}
<div x-show="showViewModal" x-transition.opacity
     class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4 bg-black/50"
     @keydown.escape.window="showViewModal = false">
    <div class="bg-white dark:bg-gray-800 rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-lg max-h-[90vh] flex flex-col"
         @click.outside="showViewModal = false" role="dialog">

        {{-- Header: icon + name + action buttons --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            {{-- Service icon --}}
            <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center"
                 :style="viewingService?.color ? 'background-color:' + viewingService.color + '22' : 'background-color:#64748b22'">
                <div x-html="viewIconHtml" :style="'color:' + (viewingService?.color || '#64748b')" class="w-5 h-5 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full"></div>
            </div>

            {{-- Name (click to edit) --}}
            <div class="flex-1 min-w-0">
                <h2 class="font-semibold text-base truncate" x-text="viewingService?.name"></h2>
                <button @click="showViewModal = false; $nextTick(() => editService(viewingService.id))"
                    class="text-xs text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1 mt-0.5">
                    <x-icon icon="edit-2" icon-set="lucide" class="w-3 h-3" />
                    Редактировать
                </button>
            </div>

            {{-- Action buttons --}}
            <div class="flex items-center gap-1 flex-shrink-0">
                <button @click="showViewModal = false; $nextTick(() => editService(viewingService.id))"
                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-blue-600 transition-colors"
                    title="Редактировать">
                    <x-icon icon="edit-2" icon-set="lucide" class="w-4 h-4" />
                </button>
                <button @click="apiFetch(`/services/${viewingService.id}/toggle-notifications`, 'PATCH'); viewingService.notifications_enabled = !viewingService.notifications_enabled"
                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    :class="viewingService?.notifications_enabled ? 'text-blue-500' : 'text-gray-300'"
                    title="Уведомления">
                    <x-icon icon="bell" icon-set="lucide" class="w-4 h-4" />
                </button>
                <button @click="showViewModal = false"
                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 hover:text-gray-600 transition-colors"
                    title="Закрыть">
                    <x-icon icon="x" icon-set="lucide" class="w-4 h-4" />
                </button>
            </div>
        </div>

        {{-- Body: service details --}}
        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3">

            {{-- Status badge + expires --}}
            <template x-if="viewingService?.expires_at">
                <div class="flex items-center gap-3">
                    <span class="text-xs px-2 py-1 rounded-lg font-medium"
                          :class="{
                            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300': viewDaysLeft < 0,
                            'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300': viewDaysLeft >= 0 && viewDaysLeft <= 3,
                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300': viewDaysLeft > 3 && viewDaysLeft <= 14,
                            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300': viewDaysLeft > 14,
                          }">
                        <span x-text="viewDaysLeft < 0 ? 'Просрочено ' + Math.abs(viewDaysLeft) + ' дн.' : (viewDaysLeft === 0 ? 'Истекает сегодня' : 'Осталось ' + viewDaysLeft + ' дн.')"></span>
                    </span>
                    <span class="text-sm text-gray-500" x-text="viewingService.expires_at ? new Date(viewingService.expires_at).toLocaleDateString('ru-RU', {day:'2-digit',month:'2-digit',year:'numeric'}) : ''"></span>
                </div>
            </template>

            {{-- Details rows --}}
            <dl class="space-y-2.5 text-sm">
                <template x-if="viewingService?.ip">
                    <div class="flex items-start gap-3">
                        <dt class="w-28 flex-shrink-0 text-gray-400 dark:text-gray-500 flex items-center gap-1.5">
                            <x-icon icon="monitor" icon-set="lucide" class="w-3.5 h-3.5" />IP-адрес
                        </dt>
                        <dd class="flex-1 font-mono text-gray-800 dark:text-gray-200 break-all" x-text="viewingService.ip"></dd>
                    </div>
                </template>
                <template x-if="viewingService?.url">
                    <div class="flex items-start gap-3">
                        <dt class="w-28 flex-shrink-0 text-gray-400 dark:text-gray-500 flex items-center gap-1.5">
                            <x-icon icon="link" icon-set="lucide" class="w-3.5 h-3.5" />URL
                        </dt>
                        <dd class="flex-1 break-all">
                            <a :href="viewingService.url" target="_blank" x-text="viewingService.url"
                               class="text-blue-600 dark:text-blue-400 hover:underline"></a>
                        </dd>
                    </div>
                </template>
                <template x-if="viewingService?.provider_name">
                    <div class="flex items-start gap-3">
                        <dt class="w-28 flex-shrink-0 text-gray-400 dark:text-gray-500 flex items-center gap-1.5">
                            <x-icon icon="building-2" icon-set="lucide" class="w-3.5 h-3.5" />Провайдер
                        </dt>
                        <dd class="flex-1 text-gray-800 dark:text-gray-200">
                            <template x-if="viewingService.provider_url">
                                <a :href="viewingService.provider_url" target="_blank" x-text="viewingService.provider_name"
                                   class="text-blue-600 dark:text-blue-400 hover:underline"></a>
                            </template>
                            <template x-if="!viewingService.provider_url">
                                <span x-text="viewingService.provider_name"></span>
                            </template>
                        </dd>
                    </div>
                </template>
                <template x-if="viewingService?.cost">
                    <div class="flex items-start gap-3">
                        <dt class="w-28 flex-shrink-0 text-gray-400 dark:text-gray-500 flex items-center gap-1.5">
                            <x-icon icon="credit-card" icon-set="lucide" class="w-3.5 h-3.5" />Стоимость
                        </dt>
                        <dd class="flex-1 text-gray-800 dark:text-gray-200">
                            <span x-text="viewingService.cost + ' ' + (viewingService.currency || '')"></span>
                            <span x-show="viewingService.billing_cycle" class="text-gray-400 ml-1 text-xs"
                                  x-text="({monthly:'/ мес',quarterly:'/ квартал',semiannual:'/ полгода',yearly:'/ год',one_time:'разово'}[viewingService.billing_cycle] || '')"></span>
                        </dd>
                    </div>
                </template>
                <template x-if="viewingService?.group_name">
                    <div class="flex items-start gap-3">
                        <dt class="w-28 flex-shrink-0 text-gray-400 dark:text-gray-500 flex items-center gap-1.5">
                            <x-icon icon="folder" icon-set="lucide" class="w-3.5 h-3.5" />Группа
                        </dt>
                        <dd class="flex-1 text-gray-800 dark:text-gray-200" x-text="viewingService.group_name"></dd>
                    </div>
                </template>
                <template x-if="viewingService?.notes">
                    <div class="flex items-start gap-3">
                        <dt class="w-28 flex-shrink-0 text-gray-400 dark:text-gray-500 flex items-center gap-1.5">
                            <x-icon icon="file-text" icon-set="lucide" class="w-3.5 h-3.5" />Заметка
                        </dt>
                        <dd class="flex-1 text-gray-800 dark:text-gray-200 whitespace-pre-wrap text-xs" x-text="viewingService.notes"></dd>
                    </div>
                </template>
                <template x-if="viewingService?.encrypted_notes">
                    <div class="flex items-start gap-3">
                        <dt class="w-28 flex-shrink-0 text-gray-400 dark:text-gray-500 flex items-center gap-1.5 pt-0.5">
                            <x-icon icon="lock" icon-set="lucide" class="w-3.5 h-3.5" />Заметка (шифр.)
                        </dt>
                        <dd class="flex-1 text-xs">
                            {{-- Decrypted successfully --}}
                            <template x-if="viewEncNote.plain && !viewEncNote.wrongPwd">
                                <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap" x-text="viewEncNote.plain"></p>
                            </template>
                            {{-- Wrong password / no local pwd --}}
                            <template x-if="!viewEncNote.plain">
                                <div class="space-y-2">
                                    <p class="text-gray-400 italic flex items-center gap-1.5">
                                        <x-icon icon="lock" icon-set="lucide" class="w-3.5 h-3.5" />
                                        <span x-text="viewEncNote.wrongPwd ? 'Неверный пароль' : 'Требуется локальный пароль'"></span>
                                    </p>
                                    <div x-show="!viewEncNote.showTempPwd">
                                        <button @click="viewEncNote.showTempPwd = true"
                                            class="text-blue-600 dark:text-blue-400 hover:underline text-xs">
                                            Ввести пароль вручную
                                        </button>
                                    </div>
                                    <div x-show="viewEncNote.showTempPwd" class="flex gap-2">
                                        <input type="password" x-model="viewEncNote.tempPwd"
                                            placeholder="Локальный пароль"
                                            @keydown.enter="(async () => {
                                                try {
                                                    viewEncNote.plain = await window.SubCrypto.decrypt(viewingService.encrypted_notes, viewEncNote.tempPwd);
                                                    viewEncNote.wrongPwd = false;
                                                    viewEncNote.showTempPwd = false;
                                                    viewEncNote.tempPwd = '';
                                                } catch { viewEncNote.wrongPwd = true; viewEncNote.plain = ''; }
                                            })()"
                                            class="flex-1 text-xs border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <button @click="(async () => {
                                                try {
                                                    viewEncNote.plain = await window.SubCrypto.decrypt(viewingService.encrypted_notes, viewEncNote.tempPwd);
                                                    viewEncNote.wrongPwd = false;
                                                    viewEncNote.showTempPwd = false;
                                                    viewEncNote.tempPwd = '';
                                                } catch { viewEncNote.wrongPwd = true; viewEncNote.plain = ''; }
                                            })()"
                                            class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs hover:bg-blue-700">
                                            Расшифровать
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </dd>
                    </div>
                </template>
            </dl>
        </div>

        {{-- Footer: delete --}}
        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
            <button @click="showViewModal = false; deleteService(viewingService.id)"
                class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                <x-icon icon="trash-2" icon-set="lucide" class="w-4 h-4" />
                Удалить сервис
            </button>
        </div>
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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Цвет</label>
                <div class="flex gap-2 flex-wrap items-center">
                    @foreach(['#2563EB','#16A34A','#DC2626','#D97706','#7C3AED','#0891B2','#64748B','#EC4899','#0F766E','#C2410C','#1D4ED8','#15803D'] as $c)
                    <button type="button" @click="groupForm.color = groupForm.color === '{{ $c }}' ? '' : '{{ $c }}'"
                        class="w-7 h-7 rounded-full flex-shrink-0 transition-all"
                        :style="groupForm.color === '{{ $c }}'
                            ? 'background-color:{{ $c }}; outline:3px solid {{ $c }}; outline-offset:2px'
                            : 'background-color:{{ $c }}'">
                    </button>
                    @endforeach
                    <label class="w-7 h-7 flex-shrink-0 cursor-pointer flex items-center justify-center rounded-full border-2 border-dashed border-gray-300 dark:border-gray-600 text-gray-400 hover:border-gray-400 transition-colors" title="Свой цвет">
                        <x-icon icon="palette" icon-set="lucide" class="w-3.5 h-3.5" />
                        <input type="color" x-model="groupForm.color" class="sr-only">
                    </label>
                </div>
            </div>
            <div class="flex items-center justify-between gap-3">
                <button x-show="editingGroup" type="button"
                    @click="showGroupModal = false; deleteGroup(editingGroup)"
                    class="whitespace-nowrap px-4 py-2 text-sm text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                    Удалить группу
                </button>
                <div class="flex gap-3 ml-auto">
                    <button type="button" @click="showGroupModal = false"
                        class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg">Отмена</button>
                    <button type="submit" :disabled="saving"
                        class="px-6 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        <span x-text="editingGroup ? 'Обновить' : 'Создать'"></span>
                    </button>
                </div>
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
        showViewModal: false,
        editingService: null,
        editingGroup: null,
        renewingService: null,
        viewingService: null,
        viewIconHtml: '',
        viewDaysLeft: null,
        renewMonths: '',
        renewDate: '',
        renewRecordPayment: true,
        saving: false,
        localPwd: null,
        encNote: { plain: '', wrongPwd: false, showTempPwd: false, tempPwd: '' },
        viewEncNote: { plain: '', wrongPwd: false, showTempPwd: false, tempPwd: '' },
        iconPreviewHtml: '',
        form: {
            name: '', url: '', ip: '', group_id: '', type_slug: '',
            expires_at: '', billing_cycle: '', cost: '', currency: 'RUB',
            provider_name: '', provider_url: '', notes: '', encrypted_notes: null,
            notifications_enabled: true, auto_renew: false,
            icon: '', icon_set: 'lucide', color: '',
        },
        groupForm: { name: '', color: '#2563EB' },
        catalogResults: [],
        _catalogFuse: null,

        async init() {
            this.localPwd = window.SubCrypto.getPassword();
            this.initSortable();
            try {
                const res = await fetch('/catalog.json');
                const data = await res.json();
                this._catalogFuse = new Fuse(data.presets || [], {
                    keys: ['name', 'aliases'],
                    threshold: 0.3,
                    ignoreLocation: true,
                    minMatchCharLength: 2,
                });
            } catch(e) {}
        },

        catalogSearch() {
            const q = this.form.name;
            if (!this._catalogFuse || q.length < 2) { this.catalogResults = []; return; }
            this.catalogResults = this._catalogFuse.search(q).slice(0, 8).map(r => r.item);
        },

        catalogSelect(item) {
            this.form.name = item.name;
            if (item.type_slug) this.form.type_slug = item.type_slug;
            if (item.default_url) this.form.provider_url = item.default_url;
            if (item.color) this.form.color = item.color;
            if (item.icon) this.form.icon = item.icon;
            if (item.icon_set) this.form.icon_set = item.icon_set;
            this.catalogResults = [];
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
                    onAdd: () => this.updateEmptyPlaceholders(),
                    onRemove: () => this.updateEmptyPlaceholders(),
                    onEnd: (evt) => {
                        this.updateEmptyPlaceholders();
                        const items = [];
                        document.querySelectorAll('.service-row').forEach((row, i) => {
                            items.push({ id: row.dataset.id, order: i, group_id: row.closest('.services-container')?.dataset.groupId || null });
                        });
                        this.apiFetch('{{ route("services.sort") }}', 'POST', { items });
                    }
                });
            });
        },

        updateEmptyPlaceholders() {
            document.querySelectorAll('.services-container').forEach(container => {
                const placeholder = container.querySelector('.empty-placeholder');
                if (!placeholder) return;
                const hasServices = container.querySelector('.service-row');
                placeholder.style.display = hasServices ? 'none' : '';
            });
        },

        pickIcon(ico, el) {
            if (this.form.icon === ico) {
                this.form.icon = '';
                this.iconPreviewHtml = '';
            } else {
                this.form.icon = ico;
                this.form.icon_set = 'lucide';
                this.iconPreviewHtml = el.innerHTML;
            }
        },

        syncIconPreview() {
            if (!this.form.icon) { this.iconPreviewHtml = ''; return; }
            this.$nextTick(() => {
                const btn = this.$el.querySelector(`[data-ico="${this.form.icon}"]`);
                if (btn) this.iconPreviewHtml = btn.innerHTML;
            });
        },

        resetForm() {
            this.form = { name: '', url: '', ip: '', group_id: '', type_slug: '', expires_at: '',
                billing_cycle: '', cost: '', currency: 'RUB', provider_name: '', provider_url: '',
                notes: '', encrypted_notes: null, notifications_enabled: true, auto_renew: false,
                icon: '', icon_set: 'lucide', color: '' };
            this.iconPreviewHtml = '';
            this.encNote = { plain: '', wrongPwd: false, showTempPwd: false, tempPwd: '' };
            this.catalogResults = [];
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
            this.syncIconPreview();
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

        async viewService(serviceId) {
            const row = document.querySelector(`.service-row[data-id="${serviceId}"]`);
            if (!row) return;
            const data = JSON.parse(row.dataset.service || '{}');
            data.id = parseInt(serviceId);
            if (data.expires_at) {
                const today = new Date(); today.setHours(0, 0, 0, 0);
                const exp = new Date(data.expires_at + 'T00:00:00');
                this.viewDaysLeft = Math.round((exp - today) / 86400000);
            } else {
                this.viewDaysLeft = null;
            }
            const iconWrap = row.querySelector('div.rounded-lg');
            this.viewIconHtml = iconWrap ? iconWrap.innerHTML : '';
            this.viewEncNote = { plain: '', wrongPwd: false, showTempPwd: false, tempPwd: '' };
            if (data.encrypted_notes && this.localPwd) {
                try {
                    this.viewEncNote.plain = await window.SubCrypto.decrypt(data.encrypted_notes, this.localPwd);
                } catch {
                    this.viewEncNote.wrongPwd = true;
                }
            }
            this.viewingService = data;
            this.showViewModal = true;
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

</script>
@endpush

</x-app-layout>
