<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\VocationalReport;
use App\Services\ReportGeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function generate(Conversation $conversation, ReportGeneratorService $reportGeneratorService)
    {
        $report = $reportGeneratorService->generate($conversation);

        return redirect()->route('orientador.reports.show', $report)
            ->with('success', 'Reporte vocacional generado correctamente.');
    }

    public function show(VocationalReport $report)
    {
        $report->load(['student', 'conversation.messages']);

        return view('reports.show', compact('report'));
    }

    public function showForOrientador(VocationalReport $report)
    {
        $report->load(['student', 'conversation.messages']);

        return view('reports.show', compact('report'));
    }
    public function downloadPdf(VocationalReport $report)
    {
        $report->load(['student', 'conversation.messages']);

        $pdf = Pdf::loadView('reports.pdf', compact('report'))
            ->setPaper('letter', 'portrait');

        $fileName = 'reporte-vocacional-' . $report->id . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }
}
