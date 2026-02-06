<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsTemplateExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    /**
     * Return sample data for the template
     */
    public function array(): array
    {
        return [
            ['LH-015', 'Boys 23 Denim Jogger (18,20,22,24,26)', 'Denim', '1700.00', '1625.00', '1565.00', '1450.00', '1250.00', '75.00', '100.00'],
            ['LH-048', 'Boys Black Denim', 'Denim', '1295.00', '1235.00', '1190.00', '1150.00', '950.00', '75.00', '100.00'],
            ['LH-070', 'Kids Boys Denim Jeans 1 to 5', 'Denim', '1570.00', '1495.00', '1450.00', '1350.00', '1150.00', '75.00', '100.00'],
            ['BC-501', 'Boys Cotton Printed Shirt (2-3, 3-4, 5-6, 7-8, 9-10)', 'Shirt', '1395.00', '1250.00', '1190.00', '1065.00', '852.96', '75.00', '100.00'],
        ];
    }

    /**
     * Return column headings
     */
    public function headings(): array
    {
        return [
            'Product Code',
            'Description',
            'Category',
            'Selling Price',
            'Credit Price',
            'Cash & Credit Price',
            'Cash Price',
            'Supplier Price',
            'Credit Sale Commission',
            'Cash Sale Commission',
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
                    'startColor' => ['rgb' => '4CAF50']
                ],
            ],
        ];
    }

    /**
     * Return the worksheet title
     */
    public function title(): string
    {
        return 'Products Import Template';
    }
}
