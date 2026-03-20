<?php

namespace App\Exports;

use App\Models\FormSubmission;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FormSubmissionExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize
{
    protected Collection $submissions;

    public function __construct(Collection $submissions)
    {
        $this->submissions = $submissions;
    }

    public function collection(): Collection
    {
        return $this->submissions;
    }

    public function headings(): array
    {
        return [
            'Full Name',
            'Office',
            'Email',
            'Phone',
            'Gender',
            'Org. Unit',
            'TIN',
            'House No.',
            'Street',
            'Barangay',
            'Municipality',
            'Province',
            'Zip Code',
            'Status',
        ];
    }

    public function map($submission): array
    {
        $fullName = trim(
            $submission->firstname . ' ' .
            (($submission->middlename && $submission->middlename !== 'N/A')
                ? strtoupper(substr($submission->middlename, 0, 1)) . '. '
                : '') .
            $submission->lastname .
            (($submission->suffix && $submission->suffix !== 'N/A')
                ? ', ' . $submission->suffix
                : '')
        );

        return [
            $fullName,
            $submission->office?->acronym,
            $submission->email,
            (string) $submission->phone_number,
            ucfirst($submission->gender?->value ?? $submission->gender),
            $submission->organizational_unit,
            (string) $submission->tin_number,
            (string) $submission->address?->house_no,
            $submission->address?->street,
            (string) $submission->address?->barangay,
            $submission->address?->municipality,
            $submission->address?->province,
            (string) $submission->address?->zip_code,
            $submission->status,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => 'solid',
                    'startColor' => ['rgb' => 'D9E1F2'],
                ],
            ],
        ];
    }
}