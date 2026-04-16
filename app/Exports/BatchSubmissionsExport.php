<?php

namespace App\Exports;

use App\Models\Batch;
use App\Services\PsgcService;
use BackedEnum;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BatchSubmissionsExport
{
    public function __construct(protected Batch $batch) {}

    protected function formatTinForExport(string|int|null $tinNumber): string
    {
        $digits = preg_replace('/\D+/', '', (string) $tinNumber);

        if ($digits === '' || strlen($digits) < 9) {
            return (string) $tinNumber;
        }

        $baseTin = substr($digits, 0, 9);

        return substr($baseTin, 0, 3).'-'.substr($baseTin, 3, 3).'-'.substr($baseTin, 6, 3).'-000';
    }

    public function headers(): array
    {
        return [
            'No.',
            'First Name',
            'Last Name',
            'Middle Name',
            'Suffix',
            'Email',
            'Phone Number',
            'Sex',
            'TIN Number',
            'Organization',
            'Organizational Unit',
            'Office',
            'Address',
            'Status',
            'ID Combination',
        ];
    }

    public function rows(): Collection
    {
        $psgc = app(PsgcService::class);
        $submissions = $this->batch->formSubmissions()->with(['address', 'attachments', 'office'])->get();

        return $submissions->map(function ($submission, $index) use ($psgc) {
            $address = $submission->address;
            $provinceName = $address ? ($psgc->provinces()[$address->province] ?? $address->province) : '';
            $municipalityName = $address ? ($psgc->municipalities($address->province)[$address->municipality] ?? $address->municipality) : '';
            $barangayName = $address ? ($psgc->barangays($address->municipality)[$address->barangay] ?? $address->barangay) : '';

            $fullAddress = collect([
                $address->house_no ?? '',
                $address->street ?? '',
                $barangayName,
                $address->zip_code ?? '',
                $municipalityName,
                $provinceName,
            ])->filter()->implode(', ');

            $fileTypes = $submission->attachments->pluck('file_type')->toArray();

            if (in_array('NationalID', $fileTypes)) {
                $attachmentList = 'PNPKI form & National ID';
            } elseif (in_array('Passport', $fileTypes) && ! in_array('UMID', $fileTypes) && ! in_array('ID1', $fileTypes)) {
                $attachmentList = 'PNPKI form, Philippine Passport';
            } elseif (in_array('UMID', $fileTypes) && ! in_array('BirthCert', $fileTypes) && ! in_array('Passport', $fileTypes)) {
                $attachmentList = 'PNPKI form, SSS Unified Multi-Purpose ID (UMID)';
            } elseif (in_array('DriversLicense', $fileTypes)) {
                $attachmentList = "PNPKI form, LTO Driver's License";
            } elseif (in_array('PRCID', $fileTypes)) {
                $attachmentList = 'PNPKI form, Professional Regulation Commission (PRC)';
            } elseif (in_array('PostalID', $fileTypes)) {
                $attachmentList = 'PNPKI form, ID Postal Identity Card';
            } elseif (in_array('UMID', $fileTypes) && in_array('BirthCert', $fileTypes)) {
                $attachmentList = 'PNPKI form, Birth Cert & UMID';
            } elseif (in_array('UMID', $fileTypes) && in_array('Passport', $fileTypes)) {
                $attachmentList = 'PNPKI form, Passport & UMID';
            } elseif (in_array('ID1', $fileTypes) && in_array('BirthCert', $fileTypes)) {
                $attachmentList = 'PNPKI form, Birth Cert & 2 Valid IDs';
            } elseif (in_array('ID1', $fileTypes) && in_array('Passport', $fileTypes)) {
                $attachmentList = 'PNPKI form, Passport & 2 valid IDs';
            } else {
                $attachmentList = 'N/A';
            }

            return [
                $index + 1,
                $submission->firstname,
                $submission->lastname,
                $submission->middlename,
                $submission->suffix,
                $submission->email,
                (string) $submission->phone_number,
                $submission->sex instanceof BackedEnum ? ucfirst($submission->sex->value) : ucfirst((string) $submission->sex),
                $this->formatTinForExport($submission->tin_number),
                'PROVINCIAL GOVERNMENT OF DAVAO DEL SUR',
                'LOCAL GOVERNMENT UNIT',
                $submission->office?->name ?? '',
                $fullAddress,
                $submission->status instanceof BackedEnum ? ucfirst($submission->status->value) : ucfirst($submission->status),
                $attachmentList,
            ];
        });
    }

    public function download(): StreamedResponse
    {
        $filename = $this->batch->batch_name.'_submissions.xlsx';
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Submissions');

        $headers = $this->headers();
        $rows = $this->rows();
        $totalCols = count($headers);
        $totalRows = count($rows);
        $lastCol = Coordinate::stringFromColumnIndex($totalCols);

        // ── Title row (row 1) ──────────────────────────────────────────
        $batchName = $this->batch->batch_name;
        $officeName = $this->batch->office?->name ?? '';
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', strtoupper($batchName).' — Batch Submission Report');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ── Subtitle row (row 2) ───────────────────────────────────────
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'Generated: '.now('Asia/Manila')->format('F d, Y h:i A'));
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E5090']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(18);

        // ── Header row (row 3) ─────────────────────────────────────────
        foreach ($headers as $colIndex => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue("{$colLetter}3", $header);
        }
        $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF3B6EA5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBFCFE0']],
            ],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(20);

        // String-type columns (0-based): Phone Number = 6, TIN Number = 8
        $stringColumns = [6, 8];

        // Pre-format phone and TIN columns as text
        foreach ($stringColumns as $colIndex) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->getStyle("{$colLetter}3:{$colLetter}10000")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_TEXT);
        }

        // ── Data rows (starting row 4) ─────────────────────────────────
        foreach ($rows as $rowIndex => $row) {
            $excelRow = $rowIndex + 4;
            $isEvenRow = $rowIndex % 2 === 0;

            foreach ($row as $colIndex => $value) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                $cell = $sheet->getCell("{$colLetter}{$excelRow}");

                if (in_array($colIndex, $stringColumns, true)) {
                    $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
                } else {
                    $cell->setValue($value);
                }
            }

            // Alternating row background
            $rowBg = $isEvenRow ? 'FFF0F4FA' : 'FFFFFFFF';
            $sheet->getStyle("A{$excelRow}:{$lastCol}{$excelRow}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $rowBg]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD6E0EE']],
                ],
            ]);

            // Center-align No. column
            $sheet->getStyle("A{$excelRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getRowDimension($excelRow)->setRowHeight(16);
        }

        // ── Summary row ────────────────────────────────────────────────
        $summaryRow = $totalRows + 4;
        $sheet->mergeCells("A{$summaryRow}:{$lastCol}{$summaryRow}");
        $sheet->setCellValue("A{$summaryRow}", 'Total Submissions: '.$totalRows);
        $sheet->getStyle("A{$summaryRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF1E3A5F']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD6E4F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF3B6EA5']],
            ],
        ]);
        $sheet->getRowDimension($summaryRow)->setRowHeight(18);

        // ── Auto-size columns ──────────────────────────────────────────
        foreach (range(1, $totalCols) as $col) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Freeze header row
        $sheet->freezePane('A4');

        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(
            function () use ($writer): void {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ]
        );
    }
}
