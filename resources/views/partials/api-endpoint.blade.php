@php
$methodColors = [
    'GET' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
    'POST' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    'PUT' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    'PATCH' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
    'DELETE' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
];
@endphp
<div class="mb-8 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden" x-data="{ open: false }">
    <div class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50" @click="open = !open">
        <span class="text-xs font-bold font-mono px-2 py-1 rounded {{ $methodColors[$method] ?? '' }}">{{ $method }}</span>
        <code class="text-sm font-mono text-gray-800 dark:text-gray-200">{{ $path }}</code>
        <span class="text-xs text-gray-500 ml-2">{{ $desc }}</span>
        <x-icon icon="chevron-down" icon-set="lucide" class="w-4 h-4 text-gray-400 ml-auto transition-transform" x-bind:class="open ? 'rotate-180' : ''"/>
    </div>
    <div x-show="open" class="border-t border-gray-200 dark:border-gray-700 p-4 space-y-4">
        @if(!empty($params))
        <div>
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Параметры</div>
            <table class="w-full text-xs">
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($params as $p)
                    <tr>
                        <td class="py-1.5 pr-3 font-mono font-medium">{{ $p['name'] }}</td>
                        <td class="py-1.5 pr-3 text-gray-500">{{ $p['type'] }}</td>
                        <td class="py-1.5 text-gray-600 dark:text-gray-400">{{ $p['desc'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        @if(!empty($response))
        <div>
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Ответ</div>
            <pre class="bg-gray-900 text-green-400 rounded-lg p-3 text-xs overflow-x-auto whitespace-pre-wrap">{{ $response }}</pre>
        </div>
        @endif
    </div>
</div>
