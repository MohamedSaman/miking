<?php

namespace App\Exports;

use App\Models\ProductDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsListExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $search;
    protected $stockFilter;
    protected $sortBy;
    protected $sortDirection;

    public function __construct($search = '', $stockFilter = 'all', $sortBy = 'code', $sortDirection = 'asc')
    {
        $this->search = $search;
        $this->stockFilter = $stockFilter;
        $this->sortBy = $sortBy;
        $this->sortDirection = $sortDirection;
    }

    /**
     * Get sort column for query
     */
    private function getSortColumn()
    {
        $sortMap = [
            'code' => 'product_details.code',
            'name' => 'product_details.name',
            'price' => 'product_prices.selling_price',
            'stock' => 'product_stocks.available_stock',
            'brand' => 'brand_lists.brand_name',
            'category' => 'category_lists.category_name',
        ];
        
        return $sortMap[$this->sortBy] ?? 'product_details.code';
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $products = ProductDetail::join('product_prices', 'product_details.id', '=', 'product_prices.product_id')
            ->join('product_stocks', 'product_details.id', '=', 'product_stocks.product_id')
            ->leftJoin('brand_lists', 'product_details.brand_id', '=', 'brand_lists.id')
            ->leftJoin('category_lists', 'product_details.category_id', '=', 'category_lists.id')
            ->select(
                'product_details.code',
                'product_details.name as product_name',
                'brand_lists.brand_name as brand',
                'category_lists.category_name as category',
                'product_prices.supplier_price',
                'product_prices.selling_price',
                'product_prices.cash_price',
                'product_prices.credit_price',
                'product_prices.cash_credit_price',
                'product_details.cash_sale_commission',
                'product_details.credit_sale_commission',
                'product_stocks.available_stock',
                'product_stocks.damage_stock',
                'product_details.status'
            )
            ->where(function ($query) {
                $query->where('product_details.name', 'like', '%' . $this->search . '%')
                    ->orWhere('product_details.code', 'like', '%' . $this->search . '%')
                    ->orWhere('product_details.model', 'like', '%' . $this->search . '%');
            })
            ->when($this->stockFilter === 'low', function ($query) {
                $query->where('product_stocks.available_stock', '>', 0)
                      ->where('product_stocks.available_stock', '<', 5);
            })
            ->when($this->stockFilter === 'out', function ($query) {
                $query->where('product_stocks.available_stock', '=', 0);
            })
            ->when($this->stockFilter === 'in_stock', function ($query) {
                $query->where('product_stocks.available_stock', '>', 0);
            })
            ->orderBy($this->getSortColumn(), $this->sortDirection)
            ->get();

        return $products;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Product Code',
            'Product Name',
            'Brand',
            'Category',
            'Cost Price',
            'Selling Price',
            'Cash Price',
            'Credit Price',
            'Cash & Credit Price',
            'Cash Sale Bonus',
            'Credit Sale Bonus',
            'Available Stock',
            'Damage Stock',
            'Status',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB']
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 40,
            'C' => 15,
            'D' => 15,
            'E' => 12,
            'F' => 12,
            'G' => 12,
            'H' => 12,
            'I' => 18,
            'J' => 15,
            'K' => 16,
            'L' => 15,
            'M' => 15,
            'N' => 12,
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Products List';
    }
}
