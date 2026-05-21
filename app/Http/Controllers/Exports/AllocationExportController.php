<?php

namespace App\Http\Controllers\Exports;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventParticipantAllocation;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AllocationExportController extends Controller
{
    public function index(Request $request)
    {
        $eventId = $request->query('eventId');
        $format = $request->query('format', 'xlsx'); // xlsx|pdf|print

        $event = Event::where('id', $eventId)->first();
        $eventDescriptor = $event ? $event->descriptor() . ' - ' . $event->event_site->descriptor() : 'all-events';
        $reportDescriptor = 'Alocações de Participantes';

        $headers = ['Participante', 'Tipo de Quarto', 'Quarto'];

        $collections = EventParticipantAllocation::with(['person.church', 'event_site_room_type', 'event_site_room'])
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->whereNotNull('event_site_room_id')
            ->orderBy('event_site_room_type_id')
            ->orderBy('event_site_room_id')
            ->orderBy('person_id')
            ->get();

        $rows = $collections->map(function ($participant) {
            return [
                $participant->descriptor(),
                $participant->event_site_room_type->name ?? '',
                $participant->event_site_room->name ?? '',
            ];
        })->toArray();

        if ($format === 'print') {
            return response()->view('exports.table', compact('headers', 'rows', 'eventDescriptor', 'reportDescriptor'))
                ->header('Content-Type', 'text/html');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        if ($format === 'xlsx') {
            $writer = new Xlsx($spreadsheet);
            $filename = 'allocations-' . ($eventId ?? 'all') . '-' . date('Ymd_His') . '.xlsx';

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        if ($format === 'pdf') {
            // Use PhpSpreadsheet Dompdf writer only if Dompdf is installed.
            if (class_exists('\\Dompdf\\Dompdf') && class_exists('\\PhpOffice\\PhpSpreadsheet\\Writer\\Pdf\\Dompdf')) {
                // Register writer and set renderer
                $className = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf::class;
                IOFactory::registerWriter('Pdf', $className);

                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf($spreadsheet);
                $filename = 'allocations-' . ($eventId ?? 'all') . '-' . date('Ymd_His') . '.pdf';

                return response()->streamDownload(function () use ($writer) {
                    $writer->save('php://output');
                }, $filename, [
                    'Content-Type' => 'application/pdf',
                ]);
            }

            // Fallback: render HTML printable view (user can Save as PDF)
            return response()->view('exports.table', compact('headers', 'rows', 'eventDescriptor', 'reportDescriptor'))
                ->header('Content-Type', 'text/html');
        }

        abort(400, 'Invalid format');
    }
}
