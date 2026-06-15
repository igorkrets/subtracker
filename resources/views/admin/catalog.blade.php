@extends('layouts.admin')

@section('title', 'Каталог')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-semibold">Каталог сервисов</h1>
    <div class="flex gap-2">
        <form method="POST" action="{{ route('admin.catalog.rebuild') }}" class="inline">
            @csrf
            <button type="submit" class="px-4 py-2 text-sm bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-lg hover:bg-amber-200 dark:hover:bg-amber-900/50">
                Пересобрать catalog.json
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 rounded-lg text-sm">
    {{ session('success') }}
</div>
@endif

<div x-data="catalogAdmin()" x-init="init()">

{{-- Tabs --}}
<div class="flex gap-1 border-b border-gray-200 dark:border-gray-700 mb-6">
    <button @click="tab = 'types'" :class="tab === 'types' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600 dark:text-gray-300'"
        class="px-4 py-2 text-sm font-medium -mb-px">Типы ({{ $types->count() }})</button>
    <button @click="tab = 'presets'" :class="tab === 'presets' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600 dark:text-gray-300'"
        class="px-4 py-2 text-sm font-medium -mb-px">Пресеты ({{ $presets->count() }})</button>
</div>

{{-- =================== TYPES =================== --}}
<div x-show="tab === 'types'">
    <div class="flex justify-end mb-3">
        <button @click="openTypeForm(null)"
            class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Добавить тип</button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300 w-12">#</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Slug</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Название</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Пресетов</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Статус</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Действия</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($types as $type)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-2 text-gray-400">{{ $type->sort_order }}</td>
                    <td class="px-4 py-2 font-mono text-xs">{{ $type->slug }}</td>
                    <td class="px-4 py-2">{{ $type->name_ru }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ $presets->where('type_slug', $type->slug)->count() }}</td>
                    <td class="px-4 py-2">
                        <span class="text-xs {{ $type->is_active ? 'text-green-600' : 'text-gray-400' }}">
                            {{ $type->is_active ? 'Активен' : 'Скрыт' }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right">
                        <button @click="openTypeForm({{ $type->toJson() }})"
                            class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded mr-1">Изменить</button>
                        <button @click="deleteType({{ $type->id }}, '{{ $type->name_ru }}')"
                            class="text-xs px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 hover:bg-red-200 rounded">Удалить</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- =================== PRESETS =================== --}}
<div x-show="tab === 'presets'" x-data="{ search: '' }">
    <div class="flex items-center justify-between mb-3">
        <input type="text" x-model="search" placeholder="Фильтр по имени или slug..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-72">
        <button @click="openPresetForm(null)"
            class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Добавить пресет</button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Название</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Slug</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Тип</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Регион</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">★</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Действия</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($presets as $preset)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50"
                    x-show="!search || '{{ strtolower($preset->name) }}'.includes(search.toLowerCase()) || '{{ $preset->slug }}'.includes(search.toLowerCase())">
                    <td class="px-4 py-2 font-medium">{{ $preset->name }}</td>
                    <td class="px-4 py-2 font-mono text-xs text-gray-500">{{ $preset->slug }}</td>
                    <td class="px-4 py-2 text-xs text-gray-500">{{ $preset->type_slug }}</td>
                    <td class="px-4 py-2 text-xs">
                        <span class="px-1.5 py-0.5 rounded {{ $preset->region === 'ru' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                            {{ $preset->region ?? 'global' }}
                        </span>
                    </td>
                    <td class="px-4 py-2">
                        @if($preset->is_popular)
                        <x-icon icon="star" icon-set="lucide" class="w-4 h-4 text-amber-500" />
                        @endif
                    </td>
                    <td class="px-4 py-2 text-right">
                        <button @click="openPresetForm({{ $preset->toJson() }})"
                            class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded mr-1">Изменить</button>
                        <button @click="deletePreset({{ $preset->id }}, '{{ $preset->name }}')"
                            class="text-xs px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 hover:bg-red-200 rounded">Удалить</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- =================== TYPE MODAL =================== --}}
<div x-show="typeModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" @click="typeModal = false"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6" @click.stop>
        <h2 class="text-lg font-semibold mb-4" x-text="typeForm.id ? 'Редактировать тип' : 'Новый тип'"></h2>
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Slug <span class="text-red-500">*</span></label>
                <input x-model="typeForm.slug" :disabled="typeForm.id" type="text"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Название EN <span class="text-red-500">*</span></label>
                    <input x-model="typeForm.name" type="text"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Название RU <span class="text-red-500">*</span></label>
                    <input x-model="typeForm.name_ru" type="text"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Иконка (lucide)</label>
                    <input x-model="typeForm.icon" type="text"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Цвет (hex)</label>
                    <input x-model="typeForm.color" type="text" placeholder="#3b82f6"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Порядок</label>
                    <input x-model="typeForm.sort_order" type="number"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-end pb-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input x-model="typeForm.is_active" type="checkbox" class="rounded">
                        <span class="text-sm">Активен</span>
                    </label>
                </div>
            </div>
        </div>
        <div x-show="typeError" class="mt-3 text-sm text-red-600 dark:text-red-400" x-text="typeError"></div>
        <div class="flex gap-3 mt-5">
            <button @click="typeModal = false" class="flex-1 px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Отмена</button>
            <button @click="saveType()" :disabled="typeSaving"
                class="flex-1 px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                <span x-text="typeSaving ? 'Сохранение…' : 'Сохранить'"></span>
            </button>
        </div>
    </div>
</div>

{{-- =================== PRESET MODAL =================== --}}
<div x-show="presetModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" @click="presetModal = false"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg p-6" @click.stop>
        <h2 class="text-lg font-semibold mb-4" x-text="presetForm.id ? 'Редактировать пресет' : 'Новый пресет'"></h2>
        <div class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Название <span class="text-red-500">*</span></label>
                    <input x-model="presetForm.name" type="text"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Slug <span class="text-red-500">*</span></label>
                    <input x-model="presetForm.slug" :disabled="presetForm.id" type="text"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Тип <span class="text-red-500">*</span></label>
                <select x-model="presetForm.type_slug"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— выберите тип —</option>
                    @foreach($types as $t)
                    <option value="{{ $t->slug }}">{{ $t->name_ru }} ({{ $t->slug }})</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Иконка</label>
                    <input x-model="presetForm.icon" type="text"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Icon set</label>
                    <select x-model="presetForm.icon_set"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="simple-icons">simple-icons</option>
                        <option value="lucide">lucide</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Цвет (hex)</label>
                    <input x-model="presetForm.color" type="text" placeholder="#3b82f6"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Регион</label>
                    <select x-model="presetForm.region"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="global">global</option>
                        <option value="ru">ru</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">URL по умолчанию</label>
                    <input x-model="presetForm.default_url" type="text"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input x-model="presetForm.is_popular" type="checkbox" class="rounded">
                    <span class="text-sm">Популярный</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input x-model="presetForm.is_active" type="checkbox" class="rounded">
                    <span class="text-sm">Активен</span>
                </label>
            </div>
        </div>
        <div x-show="presetError" class="mt-3 text-sm text-red-600 dark:text-red-400" x-text="presetError"></div>
        <div class="flex gap-3 mt-5">
            <button @click="presetModal = false" class="flex-1 px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Отмена</button>
            <button @click="savePreset()" :disabled="presetSaving"
                class="flex-1 px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                <span x-text="presetSaving ? 'Сохранение…' : 'Сохранить'"></span>
            </button>
        </div>
    </div>
</div>

</div>{{-- end x-data --}}
@endsection

@push('scripts')
<script>
function catalogAdmin() {
    return {
        tab: 'types',
        typeModal: false,
        typeForm: {},
        typeError: '',
        typeSaving: false,
        presetModal: false,
        presetForm: {},
        presetError: '',
        presetSaving: false,

        init() {},

        openTypeForm(type) {
            this.typeError = '';
            this.typeForm = type ? { ...type } : { slug: '', name: '', name_ru: '', icon: '', color: '', sort_order: 0, is_active: true };
            this.typeModal = true;
        },

        async saveType() {
            this.typeError = '';
            this.typeSaving = true;
            const isNew = !this.typeForm.id;
            const url = isNew
                ? '/admin/catalog/types'
                : `/admin/catalog/types/${this.typeForm.id}`;
            const method = isNew ? 'POST' : 'PUT';
            try {
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
                    body: JSON.stringify(this.typeForm),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.typeError = Object.values(data.errors ?? {}).flat().join(', ') || data.message || 'Ошибка';
                    return;
                }
                this.typeModal = false;
                location.reload();
            } catch (e) {
                this.typeError = 'Ошибка сети';
            } finally {
                this.typeSaving = false;
            }
        },

        async deleteType(id, name) {
            if (!confirm(`Удалить тип «${name}»?`)) return;
            await fetch(`/admin/catalog/types/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': window.csrfToken },
            });
            location.reload();
        },

        openPresetForm(preset) {
            this.presetError = '';
            this.presetForm = preset ? { ...preset } : {
                name: '', slug: '', type_slug: '', icon: '', icon_set: 'simple-icons',
                color: '', region: 'global', default_url: '', is_popular: false, is_active: true,
            };
            this.presetModal = true;
        },

        async savePreset() {
            this.presetError = '';
            this.presetSaving = true;
            const isNew = !this.presetForm.id;
            const url = isNew
                ? '/admin/catalog/presets'
                : `/admin/catalog/presets/${this.presetForm.id}`;
            const method = isNew ? 'POST' : 'PUT';
            try {
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
                    body: JSON.stringify(this.presetForm),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.presetError = Object.values(data.errors ?? {}).flat().join(', ') || data.message || 'Ошибка';
                    return;
                }
                this.presetModal = false;
                location.reload();
            } catch (e) {
                this.presetError = 'Ошибка сети';
            } finally {
                this.presetSaving = false;
            }
        },

        async deletePreset(id, name) {
            if (!confirm(`Удалить пресет «${name}»?`)) return;
            await fetch(`/admin/catalog/presets/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': window.csrfToken },
            });
            location.reload();
        },
    };
}
</script>
@endpush
