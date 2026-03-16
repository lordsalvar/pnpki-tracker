<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class EmployeeExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize
{
    protected Collection $employees;

    public function __construct(Collection $employees)
    {
        $this->employees = $employees;
    }

    /**
     * Return the collection of employees to export.
     */
    public function collection(): Collection
    {
        return $this->employees;
    }

    /**
     * Column headings matching the table columns.
     */
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
        ];
    }

    /**
     * Map each employee record to the export row.
     */
    public function map($employee): array
    {
        $fullName = trim(
            $employee->firstname . ' ' .
            (($employee->middlename && $employee->middlename !== 'N/A')
                ? strtoupper(substr($employee->middlename, 0, 1)) . '. '
                : '') .
            $employee->lastname .
            (($employee->suffix && $employee->suffix !== 'N/A')
                ? ', ' . $employee->suffix
                : '')
        );

        return [
            $fullName,
            $employee->office?->acronym,
            $employee->email,
            (string) $employee->phone_number,
            $employee->gender?->value ?? $employee->gender,
            $employee->organizational_unit,
            (string) $employee->tin_number,
            (string) $employee->address?->house_no,
            $employee->address?->street,
            (string) $employee->address?->barangay,
            $employee->address?->municipality,
            $employee->address?->province,
            (string) $employee->address?->zip_code,
        ];
    }

    /**
     * Style the header row — bold and background color.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true],
                'fill'      => [
                    'fillType'   => 'solid',
                    'startColor' => ['rgb' => 'D9E1F2'],
                ],
            ],
        ];
    }
}