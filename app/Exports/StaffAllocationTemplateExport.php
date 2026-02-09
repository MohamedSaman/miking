<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StaffAllocationTemplateExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    /**
     * Return sample data for the template
     */
    public function array(): array
    {
        return [
            ['BC-501', 'Boys Cotton Printed Shirt (2-3, 3-4, 5-6, 7-8, 9-10)', '160'],
            ['BC-503', 'Boys Cotton Printed Shirt (2-3, 3-4, 5-6, 7-8, 9-10)', '87'],
            ['BC-505', 'Boys Cotton Printed Shirt (2-3, 3-4, 5-6, 7-8, 9-10)', '76'],
            ['BL-302', 'Boys Linen Shirt (2-3, 3-4, 5-6, 7-8, 9-10)', '77'],
            ['BO-402', 'Boys Oxford Shirt (2-3, 3-4, 5-6, 7-8, 9-10)', '91'],
            ['KC-502', 'Kids Cotton Printed Shirt (0-3, 3-6, 6-9, 9-12, 12-18, 18-24)', '49'],
        ];
    }

    /**
     * Return column headings
     */
    public function headings(): array
    {
        return [
            'Item Code',
            'Description',
            'Qty',
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (headers)
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2196F3']
                ],
            ],
        ];
    }

    /**
     * Return the worksheet title
     */
    public function title(): string
    {
        return 'Staff Allocation Template';
    }
}
