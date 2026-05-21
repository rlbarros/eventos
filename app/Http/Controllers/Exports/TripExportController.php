<?php

namespace App\Http\Controllers\Exports;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventTripParticipant;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TripExportController extends Controller
{
    public function index(Request $request)
    {
        $eventId = $request->query('eventId');
        $format = $request->query('format', 'xlsx'); // xlsx|pdf|print

        $event = Event::where('id', $eventId)->first();
        $eventDescriptor = $event ? $event->descriptor() . ' - ' . $event->event_site->descriptor() : 'all-events';
        $reportDescriptor = 'Viagens';

        $headers = ['ID', 'Viagem', 'Passageiro'];

        $collections = EventTripParticipant::with(['event_trip', 'person'])
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->get();

        $rows = $collections->map(function ($p) {
            return [
                $p->id,
                $p->event_trip->descriptor() ?? '',
                $p->person->descriptor() ?? '',
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
            $filename = 'trips-' . ($eventId ?? 'all') . '-' . date('Ymd_His') . '.xlsx';

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        if ($format === 'pdf') {
            if (class_exists('\\Dompdf\\Dompdf') && class_exists('\\PhpOffice\\PhpSpreadsheet\\Writer\\Pdf\\Dompdf')) {
                IOFactory::registerWriter('Pdf', \PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf::class);

                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf($spreadsheet);
                $filename = 'trips-' . ($eventId ?? 'all') . '-' . date('Ymd_His') . '.pdf';

                return response()->streamDownload(function () use ($writer) {
                    $writer->save('php://output');
                }, $filename, [
                    'Content-Type' => 'application/pdf',
                ]);
            }

            return response()->view('exports.table', compact('headers', 'rows', 'eventDescriptor', 'reportDescriptor'))
                ->header('Content-Type', 'text/html');
        }

        abort(400, 'Invalid format');
    }
}
