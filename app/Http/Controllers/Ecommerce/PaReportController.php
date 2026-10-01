<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\UnprocessedPaReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Reports module (admin panel). For now: the Unprocessed PA report the
 * Purchasing Officer files every 15th and 30th — see App\Services\UnprocessedPaReport.
 */
class PaReportController extends Controller
{
    /** Admin, MCD Planner, Purchasing Officer, MCD Verifier, MCD Approver. */
    const ROLES = [1, 4, 5, 7, 8];

    public function unprocessed(Request $request)
    {
        $this->authorizeRole();

        $report = UnprocessedPaReport::fromRequest($request);

        $rows = $report->ordered()->paginate(20)->withQueryString();
        $purchaserNames = UnprocessedPaReport::namesFor($rows->items());

        return view('admin.reports.unprocessed-pa', [
            'report'         => $report,
            'filters'        => $report->filters,
            'rows'           => $rows,
            'summary'        => $report->summary(),
            'purchaserNames' => $purchaserNames,
            'departments'    => Department::inUse(),
            'purchasers'     => $report->purchasers(),
            'buckets'        => array_keys(UnprocessedPaReport::AGING_BUCKETS),
        ]);
    }

    public function unprocessedExport(Request $request)
    {
        $this->authorizeRole();

        $report = UnprocessedPaReport::fromRequest($request);
        $entries = $report->withLines();
        $names = UnprocessedPaReport::namesFor(array_column($entries, 'row'));

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Unprocessed PA');

        $headers = [
            'PA Type', 'MRS No.', 'PA No.', 'Posted Date', 'Department', 'Purchaser', 'Purchasing Date Received',
            'Aging (days)', 'PA Balance', 'PA On Hold',
            'Stock Code', 'Stock Description', 'OEM ID', 'UoM', 'Frequency', 'PAR To', 'Previous PO#', 'Current PO#',
            'PO Date Released', 'QTY to Order', 'QTY Ordered', 'Balance QTY for PO', 'Line Status',
        ];
        $sheet->fromArray($headers, null, 'A1');
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
        ]);

        $rowNo = 2;
        $colors = ['D9D9D9', 'B3D9FF'];
        foreach ($entries as $i => $entry) {
            $pa = $entry['row'];
            $paCells = [
                $pa->pa_type,
                $pa->order_number ?: 'N/A',
                $pa->pa_number ?: 'N/A',
                $this->date($pa->posted_at),
                $pa->department ?: ($pa->pa_type === 'SR' ? 'Stock Replenishment' : ''),
                $names->get($pa->received_by, ''),
                $this->date($pa->received_at) ?: 'N/A',
                $pa->aging_days === null ? 'N/A' : (int) $pa->aging_days,
                (float) $pa->balance,
                (int) $pa->is_hold === 1 ? 'Yes' : '',
            ];

            // A PA with no lines left to show still gets one row, so it's counted.
            $lines = $entry['lines'] ?: [null];
            $start = $rowNo;
            foreach ($lines as $line) {
                $lineCells = $line === null ? array_fill(0, 13, '') : [
                    $line['code'],
                    $line['description'],
                    $line['oem'],
                    $line['uom'],
                    $line['frequency'],
                    $line['par_to'],
                    $line['previous_po'],
                    $line['current_po'],
                    $this->date($line['po_released']),
                    $line['qty_to_order'],
                    $line['qty_ordered'],
                    $line['balance'],
                    $line['status'],
                ];
                $sheet->fromArray(array_merge($paCells, $lineCells), null, 'A' . $rowNo, true);
                if ($line !== null && $line['status'] === 'On hold') {
                    $sheet->getStyle("K{$rowNo}:{$lastCol}{$rowNo}")->getFont()->getColor()->setRGB('C00000');
                }
                $rowNo++;
            }
            $sheet->getStyle("A{$start}:{$lastCol}" . ($rowNo - 1))->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors[$i % 2]);
        }

        $sheet->setAutoFilter("A1:{$lastCol}" . max(1, $rowNo - 1));
        $sheet->freezePane('A2');
        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $fileName = 'IMP-UNPROCESSED-PA-' . date('Ymd_His') . '.xlsx';
        $filePath = storage_path($fileName);
        (new Xlsx($spreadsheet))->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    protected function authorizeRole()
    {
        abort_unless(in_array((int) optional(Auth::user())->role_id, self::ROLES, true), 403);
    }

    protected function date($value)
    {
        return $value ? Carbon::parse($value)->format('m/d/Y') : '';
    }
}
