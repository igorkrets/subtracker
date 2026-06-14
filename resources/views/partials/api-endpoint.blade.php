@php
$methodColors = [
    'GET'    => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
    'POST'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    'PUT'    => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    'PATCH'  => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
    'DELETE' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
];

preg_match_all('/\{(\w+)\}/', $path, $m);
$pathParams = $m[1] ?? [];
$params     = $params ?? [];
$isBody     = in_array($method, ['POST', 'PUT', 'PATCH']);

$bodyObj = [];
if ($isBody && !empty($params)) {
    foreach ($params as $p) {
        $bodyObj[rtrim($p['name'], '*')] = '';
    }
}
$bodyDefault = empty($bodyObj)
    ? "{\n\n}"
    : json_encode($bodyObj, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$queryParamNames = [];
if (!$isBody && !empty($params)) {
    foreach ($params as $p) {
        $queryParamNames[] = rtrim($p['name'], '*');
    }
}

$endpointCfg = [
    'method'      => $method,
    'path'        => $path,
    'isBody'      => $isBody,
    'pathParams'  => array_fill_keys($pathParams, ''),
    'queryParams' => array_fill_keys($queryParamNames, ''),
    'bodyDefault' => $bodyDefault,
    'baseUrl'     => config('app.url'),
];
@endphp

{{--
  apiEndpoint() defined in api-docs.blade.php via @push('scripts').
  Config passed via data-config attribute. Blade {{ }} applies htmlspecialchars
  automatically — do NOT wrap json_encode in manual htmlspecialchars (double-escaping).
--}}
<div class="mb-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
     x-data="apiEndpoint()"
     data-config="{{ json_encode($endpointCfg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}">

    {{-- Header --}}
    <div class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 select-none"
         @click="open = !open">
        <span class="text-xs font-bold font-mono px-2 py-1 rounded flex-shrink-0 {{ $methodColors[$method] ?? '' }}">{{ $method }}</span>
        <code class="text-sm font-mono text-gray-800 dark:text-gray-200 flex-1 truncate">{{ $path }}</code>
        <span class="text-xs text-gray-500 hidden sm:block flex-shrink-0">{{ $desc }}</span>
        <span :class="open ? 'rotate-180' : ''" class="transition-transform inline-flex flex-shrink-0 ml-1">
            <x-icon icon="chevron-down" icon-set="lucide" class="w-4 h-4 text-gray-400" />
        </span>
    </div>

    {{-- Expanded body --}}
    <div x-show="open" class="border-t border-gray-200 dark:border-gray-700">

        {{-- Description on mobile (hidden in header) --}}
        <div class="sm:hidden px-4 pt-3 text-xs text-gray-500">{{ $desc }}</div>

        {{-- Params table --}}
        @if(!empty($params))
        <div class="p-4 border-b border-gray-100 dark:border-gray-700/60">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Параметры</div>
            <table class="w-full text-xs">
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach($params as $p)
                    <tr>
                        <td class="py-1.5 pr-3 font-mono font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap">{{ $p['name'] }}</td>
                        <td class="py-1.5 pr-3 text-gray-400 whitespace-nowrap">{{ $p['type'] }}</td>
                        <td class="py-1.5 text-gray-600 dark:text-gray-400">{{ $p['desc'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Response example --}}
        @if(!empty($response))
        <div class="p-4 border-b border-gray-100 dark:border-gray-700/60">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Пример ответа</div>
            <pre class="bg-gray-900 text-green-400 rounded-lg p-3 text-xs overflow-x-auto whitespace-pre-wrap">{{ $response }}</pre>
        </div>
        @endif

        {{-- Test panel (always visible, no toggle) --}}
        <div class="p-4 space-y-4">

            {{-- API key warning --}}
            <div x-show="!$store.apiKey"
                class="flex items-center gap-2 text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 rounded-lg px-3 py-2">
                <span class="inline-flex flex-shrink-0">
                    <x-icon icon="alert-triangle" icon-set="lucide" class="w-3.5 h-3.5" />
                </span>
                Введите API-ключ в поле выше для тестирования
            </div>

            {{-- Path params --}}
            @if(!empty($pathParams))
            <div>
                <div class="text-xs font-semibold text-gray-500 mb-2">Параметры пути</div>
                <div class="space-y-2">
                    @foreach($pathParams as $pp)
                    <div class="flex items-center gap-3">
                        <label class="text-xs font-mono text-gray-500 w-16 flex-shrink-0">{{ '{' . $pp . '}' }}</label>
                        <input type="text" x-model="pathVals.{{ $pp }}" placeholder="1"
                            class="flex-1 px-2 py-1.5 text-xs font-mono border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Query params (GET/DELETE) --}}
            @if(!$isBody && !empty($params))
            <div>
                <div class="text-xs font-semibold text-gray-500 mb-2">Query-параметры <span class="font-normal text-gray-400">(необязательно)</span></div>
                <div class="space-y-2">
                    @foreach($params as $p)
                    @php $pname = rtrim($p['name'], '*'); @endphp
                    <div class="flex items-center gap-3">
                        <label class="text-xs font-mono text-gray-500 w-20 flex-shrink-0 truncate">{{ $pname }}</label>
                        <input type="text" x-model="queryVals.{{ $pname }}"
                            placeholder="{{ addslashes($p['desc']) }}"
                            class="flex-1 px-2 py-1.5 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Body (POST/PUT/PATCH) --}}
            @if($isBody)
            <div>
                <div class="text-xs font-semibold text-gray-500 mb-2">Тело запроса (JSON)</div>
                <textarea x-model="bodyText" rows="8" spellcheck="false"
                    class="w-full px-3 py-2 text-xs font-mono border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-900 text-green-400 focus:outline-none focus:ring-1 focus:ring-blue-500 resize-y"></textarea>
            </div>
            @endif

            {{-- Send button --}}
            <button @click="send()"
                :disabled="loading"
                class="flex items-center gap-2 px-5 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-xs font-semibold rounded-lg transition">
                <span x-show="!loading" class="inline-flex">
                    <x-icon icon="send" icon-set="lucide" class="w-3.5 h-3.5" />
                </span>
                <span x-show="loading" class="inline-flex">
                    <x-icon icon="loader-2" icon-set="lucide" class="w-3.5 h-3.5 animate-spin" />
                </span>
                <span x-text="loading ? 'Отправка...' : 'Отправить запрос'"></span>
            </button>

            {{-- Response --}}
            <div x-show="result !== null" class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-500">Ответ</span>
                    <span x-show="result?.status"
                        class="text-xs font-mono font-bold px-2 py-0.5 rounded"
                        :class="result?.ok
                            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                            : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'"
                        x-text="result?.status + (result?.ok ? ' OK' : ' Error')"></span>
                </div>
                <div x-show="result?.error" class="text-xs text-red-600 dark:text-red-400" x-text="result?.msg"></div>
                <pre x-show="result?.data !== undefined"
                    :class="result?.ok ? 'text-green-400' : 'text-red-400'"
                    class="bg-gray-900 rounded-lg p-3 text-xs overflow-x-auto whitespace-pre-wrap max-h-72"
                    x-text="pretty(result?.data)"></pre>
            </div>

        </div>
    </div>
</div>
