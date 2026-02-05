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
            ['Flasher Musical 12 V', 'Piece', 'High quality musical flasher', '150.00', '160.00', '170.00', '120.00', '5.00', '3.00', '4.00', '2.00', 'USN0001', '100', '115.00', '20', 'No'],
            ['Flasher Musical 24 V', 'Piece', 'High quality musical flasher 24V', '180.00', '190.00', '200.00', '145.00', '6.00', '4.00', '5.00', '3.00', 'USN0002', '50', '140.00', '15', 'No'],
            ['Flasher Electrical 12 V', 'Dozen', 'Electrical flasher 12V', '1200.00', '1250.00', '1300.00', '950.00', '50.00', '30.00', '40.00', '20.00', 'USN0003', '30', '920.00', '10', 'No'],
            ['Flasher Electrical 24 V', 'Bundle', 'Electrical flasher 24V bundle', '2400.00', '2500.00', '2600.00', '1900.00', '100.00', '60.00', '80.00', '40.00', 'USN0004', '25', '1850.00', '5', 'No'],
        ];
    }

    /**
     * Return column headings
     */
    public function headings(): array
    {
        return [
            'Product Name *',
            'Unit',
            'Description',
            'Rate',
            'Retail Price',
            'Wholesale Price',
            'Buy Rate',
            'Retail Cash Bonus',
            'Retail Credit Bonus',
            'Wholesale Cash Bonus',
            'Wholesale Credit Bonus',
            'Product Code *',
            'Opening Stock',
            'Opening Stock Rate',
            'Minimum Stock',
            'Is Service (Yes / No)',
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
