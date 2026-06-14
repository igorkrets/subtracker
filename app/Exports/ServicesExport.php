<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ServicesExport implements FromCollection, WithHeadings
{
    public function __construct(private $services) {}

    public function collection()
    {
        return $this->services->map(fn($s) => [
            $s->name,
            $s->ip,
            $s->url,
            $s->provider_name,
            $s->expires_at?->format('Y-m-d'),
            $s->cost,
            $s->currency,
            $s->billing_cycle,
            $s->group?->name,
            $s->notes,
        ]);
    }

    public function headings(): array
    {
        return ['Название', 'IP', 'URL', 'Провайдер', 'Истекает', 'Стоимость', 'Валюта', 'Периодичность', 'Группа', 'Заметки'];
    }
}
