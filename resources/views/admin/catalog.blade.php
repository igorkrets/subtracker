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

{{-- Tabs --}}
<div x-data="{ tab: 'types' }">
    <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700 mb-6">
        <button @click="tab = 'types'" :class="tab === 'types' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
            class="px-4 py-2 text-sm font-medium -mb-px">Типы ({{ $types->count() }})</button>
        <button @click="tab = 'presets'" :class="tab === 'presets' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
            class="px-4 py-2 text-sm font-medium -mb-px">Пресеты ({{ $presets->count() }})</button>
    </div>

    {{-- Types --}}
    <div x-show="tab === 'types'" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300 w-8">#</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Иконка</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Slug</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Название</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Пресетов</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Статус</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($types as $type)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-2 text-gray-400">{{ $type->sort_order }}</td>
                    <td class="px-4 py-2">
                        <x-icon :icon="$type->icon" icon-set="lucide" class="w-5 h-5 text-gray-600 dark:text-gray-300" />
                    </td>
                    <td class="px-4 py-2 font-mono text-xs">{{ $type->slug }}</td>
                    <td class="px-4 py-2">{{ $type->name_ru }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ $presets->where('type_slug', $type->slug)->count() }}</td>
                    <td class="px-4 py-2">
                        <span class="text-xs {{ $type->is_active ? 'text-green-600' : 'text-gray-400' }}">
                            {{ $type->is_active ? 'Активен' : 'Скрыт' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Presets --}}
    <div x-show="tab === 'presets'" x-data="{ search: '' }">
        <div class="mb-4">
            <input type="text" x-model="search" placeholder="Фильтр по имени или slug..."
                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-72">
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Иконка</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Название</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Slug</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Тип</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Регион</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Популярный</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($presets as $preset)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50"
                        x-show="!search || '{{ strtolower($preset->name) }}'.includes(search.toLowerCase()) || '{{ $preset->slug }}'.includes(search.toLowerCase())">
                        <td class="px-4 py-2">
                            @if($preset->icon)
                            <x-icon :icon="$preset->icon" :icon-set="$preset->icon_set ?? 'simple-icons'" class="w-5 h-5" :color="$preset->color" />
                            @endif
                        </td>
                        <td class="px-4 py-2 font-medium">{{ $preset->name }}</td>
                        <td class="px-4 py-2 font-mono text-xs text-gray-500">{{ $preset->slug }}</td>
                        <td class="px-4 py-2 text-xs text-gray-500">{{ $preset->type_slug }}</td>
                        <td class="px-4 py-2 text-xs">
                            <span class="px-1.5 py-0.5 rounded {{ $preset->region === 'ru' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                {{ $preset->region }}
                            </span>
                        </td>
                        <td class="px-4 py-2">
                            @if($preset->is_popular)
                            <x-icon icon="star" icon-set="lucide" class="w-4 h-4 text-amber-500" />
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
