<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Экспорт сервисов — SubTracker</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 16px; font-weight: bold; margin-bottom: 4px; }
        .meta { font-size: 9px; color: #6b7280; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        thead { background: #f3f4f6; }
        th { padding: 6px 8px; text-align: left; font-weight: 600; font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; color: #374151; border-bottom: 1px solid #d1d5db; }
        td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        tr:hover td { background: #f9fafb; }
        .badge { display: inline-block; padding: 1px 5px; border-radius: 4px; font-size: 8px; font-weight: 600; }
        .badge-overdue { background: #fee2e2; color: #dc2626; }
        .badge-critical { background: #ffedd5; color: #ea580c; }
        .badge-soon { background: #fef9c3; color: #ca8a04; }
        .badge-ok { background: #dcfce7; color: #16a34a; }
        .badge-none { background: #f1f5f9; color: #64748b; }
        .footer { margin-top: 16px; font-size: 8px; color: #9ca3af; text-align: right; }
        .group-header { background: #eff6ff; }
        .group-header td { font-weight: 600; color: #1d4ed8; padding: 4px 8px; font-size: 9px; }
    </style>
</head>
<body>
    <h1>SubTracker — Экспорт сервисов</h1>
    <div class="meta">Сформировано: {{ now()->format('d.m.Y H:i') }} | Пользователь: {{ $user->name }} | Всего: {{ $services->count() }}</div>

    <table>
        <thead>
            <tr>
                <th style="width:25%">Название</th>
                <th style="width:12%">Дата</th>
                <th style="width:10%">Статус</th>
                <th style="width:12%">Стоимость</th>
                <th style="width:12%">Цикл</th>
                <th style="width:15%">Провайдер</th>
                <th style="width:14%">Группа</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $service)
            <tr>
                <td>
                    <strong>{{ $service->name }}</strong>
                    @if($service->ip) <br><span style="color:#6b7280;font-size:8px">{{ $service->ip }}</span> @endif
                    @if($service->url) <br><span style="color:#6b7280;font-size:8px">{{ $service->url }}</span> @endif
                </td>
                <td>{{ $service->expires_at?->format('d.m.Y') ?? '—' }}</td>
                <td>
                    @php $status = $service->status; @endphp
                    <span class="badge badge-{{ $status }}">
                        @switch($status)
                            @case('overdue') Просрочено @break
                            @case('critical') Критично @break
                            @case('soon') Скоро @break
                            @case('ok') Активен @break
                            @default Без даты
                        @endswitch
                    </span>
                </td>
                <td>{{ $service->cost ? number_format($service->cost, 2) . ' ' . $service->currency : '—' }}</td>
                <td>
                    @php $cycles = ['monthly' => 'Ежемесячно', 'quarterly' => 'Квартально', 'biannual' => 'Полугодие', 'annual' => 'Ежегодно', 'biennial' => 'Двухлетний', 'custom' => 'Особый']; @endphp
                    {{ $cycles[$service->billing_cycle] ?? '—' }}
                </td>
                <td>{{ $service->provider_name ?? '—' }}</td>
                <td>{{ $service->group?->name ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($services->isEmpty())
    <p style="text-align:center;color:#9ca3af;padding:24px">Нет сервисов для экспорта</p>
    @endif

    <div class="footer">SubTracker — {{ config('app.url') }}</div>
</body>
</html>
