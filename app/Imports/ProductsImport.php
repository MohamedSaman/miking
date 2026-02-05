<?php

namespace App\Imports;

use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\ProductSupplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Support\Facades\DB;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnFailure
{
    use SkipsFailures;

    private $defaultBrandId;
    private $defaultCategoryId;
    private $defaultSupplierId;
    private $successCount = 0;
    private $skipCount = 0;

    public function __construct()
    {
        // Set default IDs
        $this->setDefaultIds();
    }

    /**
     * Set default IDs for brand, category, and supplier
     */
    private function setDefaultIds()
    {
        // Get or create default brand
        $defaultBrand = BrandList::firstOrCreate(
            ['brand_name' => 'Default Brand'],
            ['status' => 'active']
        );
        $this->defaultBrandId = $defaultBrand->id;

        // Get or create default category
        $defaultCategory = CategoryList::firstOrCreate(
            ['category_name' => 'Default Category'],
            ['status' => 'active']
        );
        $this->defaultCategoryId = $defaultCategory->id;

        // Get or create default supplier
        $defaultSupplier = ProductSupplier::firstOrCreate(
            ['name' => 'Default Supplier'],
            [
                'phone' => '0000000000',
                'email' => 'default@supplier.com',
                'address' => 'Default Address',
                'status' => 'active'
            ]
        );
        $this->defaultSupplierId = $defaultSupplier->id;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Check if product with same code already exists
        $existingProduct = ProductDetail::where('code', $row['product_code'])->first();
        
        if ($existingProduct) {
            $this->skipCount++;
            return null; // Skip duplicate products
        }

        try {
            DB::beginTransaction();

            // Map 'Is Service' field to status
            $isService = strtolower(trim($row['is_service_yes_no'] ?? 'no'));
            $status = ($isService === 'yes' || $isService === 'service') ? 'inactive' : 'active';

            // Map 'Unit' field - default to 'Piece' if not provided or invalid
            $unit = ucfirst(strtolower(trim($row['unit'] ?? 'piece')));
            if (!in_array($unit, ['Bundle', 'Dozen', 'Piece'])) {
                $unit = 'Piece';
            }

            // Create product detail
            $product = ProductDetail::create([
                'code' => $row['product_code'],                    // Product Code → code
                'name' => $row['product_name'],                    // Product Name → name
                'model' => null,
                'image' => null,
                'description' => $row['description'] ?? null,      // Description
                'barcode' => null,
                'status' => $status,                               // Is Service → status
                'unit' => $unit,                                   // Unit (Bundle/Dozen/Piece)
                'retail_cash_bonus' => $row['retail_cash_bonus'] ?? 0.00,
                'retail_credit_bonus' => $row['retail_credit_bonus'] ?? 0.00,
                'wholesale_cash_bonus' => $row['wholesale_cash_bonus'] ?? 0.00,
                'wholesale_credit_bonus' => $row['wholesale_credit_bonus'] ?? 0.00,
                'brand_id' => $this->defaultBrandId,
                'category_id' => $this->defaultCategoryId,
                'supplier_id' => $this->defaultSupplierId,
            ]);

            // Create price record
            ProductPrice::create([
                'product_id' => $product->id,
                'supplier_price' => $row['buy_rate'] ?? 0.00,     // Buy Rate → supplier_price
                'selling_price' => $row['rate'] ?? 0.00,          // Rate → selling_price
                'retail_price' => $row['retail_price'] ?? null,   // Retail Price → retail_price
                'wholesale_price' => $row['wholesale_price'] ?? null, // Wholesale Price → wholesale_price
                'discount_price' => 0.00,
            ]);

            // Create stock record
            ProductStock::create([
                'product_id' => $product->id,
                'available_stock' => $row['opening_stock'] ?? 0,  // Opening Stock → available_stock
                'opening_stock_rate' => $row['opening_stock_rate'] ?? 0.00, // Opening Stock Rate
                'damage_stock' => 0,
                'restocked_quantity' => $row['minimum_stock'] ?? 0, // Minimum Stock → restocked_quantity
            ]);

            DB::commit();
            $this->successCount++;

            return $product;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->skipCount++;
            return null;
        }
    }

    /**
     * Validation rules for each row
     */
    public function rules(): array
    {
        return [
            'product_code' => 'required|string|max:255',
            'product_name' => 'required|string|max:255',
            'unit' => 'nullable|string|in:Bundle,Dozen,Piece,bundle,dozen,piece',
            'description' => 'nullable|string',
            'rate' => 'nullable|numeric|min:0',
            'retail_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'buy_rate' => 'nullable|numeric|min:0',
            'retail_cash_bonus' => 'nullable|numeric|min:0',
            'retail_credit_bonus' => 'nullable|numeric|min:0',
            'wholesale_cash_bonus' => 'nullable|numeric|min:0',
            'wholesale_credit_bonus' => 'nullable|numeric|min:0',
            'opening_stock' => 'nullable|integer|min:0',
            'opening_stock_rate' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
            'is_service_yes_no' => 'nullable|string',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'product_code.required' => 'Product code is required',
            'product_name.required' => 'Product name is required',
            'unit.in' => 'Unit must be Bundle, Dozen, or Piece',
            'rate.numeric' => 'Rate must be a valid number',
            'retail_price.numeric' => 'Retail price must be a valid number',
            'wholesale_price.numeric' => 'Wholesale price must be a valid number',
            'buy_rate.numeric' => 'Buy rate must be a valid number',
            'retail_cash_bonus.numeric' => 'Retail Cash bonus must be a valid number',
            'retail_credit_bonus.numeric' => 'Retail Credit bonus must be a valid number',
            'wholesale_cash_bonus.numeric' => 'Wholesale Cash bonus must be a valid number',
            'wholesale_credit_bonus.numeric' => 'Wholesale Credit bonus must be a valid number',
            'opening_stock.integer' => 'Opening stock must be a whole number',
            'opening_stock_rate.numeric' => 'Opening stock rate must be a valid number',
            'minimum_stock.integer' => 'Minimum stock must be a whole number',
        ];
    }

    /**
     * Get the count of successfully imported products
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * Get the count of skipped products
     */
    public function getSkipCount(): int
    {
        return $this->skipCount;
    }

    /**
     * Get heading row configuration
     */
    public function headingRow(): int
    {
        return 1; // First row contains headers
    }
}
