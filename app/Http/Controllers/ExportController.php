<?php

namespace App\Http\Controllers;

use App\Services\ExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function export(Request $request, ExportService $exportService)
    {
        $user = Auth::user();
        $format = $request->get('format', 'html');
        $filters = $request->only(['group_id', 'status', 'search']);

        $services = $exportService->getServicesQuery($user, $filters)->get();
        $filename = 'subtracker-export-' . now()->format('Y-m-d');

        return match ($format) {
            'pdf' => Pdf::loadView('exports.pdf', compact('services', 'user'))
                ->download("{$filename}.pdf"),
            'xlsx' => Excel::download(
                new \App\Exports\ServicesExport($services),
                "{$filename}.xlsx"
            ),
            default => response($exportService->toHtml($user, $filters), 200, [
                'Content-Type' => 'text/html',
                'Content-Disposition' => "attachment; filename=\"{$filename}.html\"",
            ]),
        };
    }
}
