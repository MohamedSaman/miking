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
        $productCode = $row['product_code'] ?? $row['product_code_'] ?? null;
        
        if (!$productCode) {
            $this->skipCount++;
            return null;
        }
        
        $existingProduct = ProductDetail::where('code', $productCode)->first();

        try {
            DB::beginTransaction();

            if ($existingProduct) {
                // UPDATE existing product
                $existingProduct->update([
                    'name' => $row['description'],
                    'model' => $row['category'] ?? $existingProduct->model,
                    'cash_sale_commission' => $row['cash_sale_commission'] ?? $existingProduct->cash_sale_commission,
                    'credit_sale_commission' => $row['credit_sale_commission'] ?? $existingProduct->credit_sale_commission,
                ]);

                // Update or create price record
                ProductPrice::updateOrCreate(
                    ['product_id' => $existingProduct->id],
                    [
                        'supplier_price' => $row['supplier_price'] ?? 0.00,
                        'selling_price' => $row['selling_price'] ?? 0.00,
                        'cash_price' => $row['cash_price'] ?? null,
                        'credit_price' => $row['credit_price'] ?? null,
                        'cash_credit_price' => $row['cash_credit_price'] ?? null,
                    ]
                );

                // Create stock if not exists
                ProductStock::firstOrCreate(
                    ['product_id' => $existingProduct->id],
                    [
                        'available_stock' => 0,
                        'opening_stock_rate' => 0.00,
                        'damage_stock' => 0,
                        'restocked_quantity' => 0,
                    ]
                );

                DB::commit();
                $this->successCount++;
                return null; // Return null since we're updating, not creating
            }

            // Create NEW product detail
            $product = ProductDetail::create([
                'code' => $productCode,
                'name' => $row['description'],
                'model' => $row['category'] ?? null,
                'image' => null,
                'description' => null,
                'barcode' => null,
                'status' => 'active',
                'unit' => 'Piece',
                'cash_sale_commission' => $row['cash_sale_commission'] ?? 0.00,
                'credit_sale_commission' => $row['credit_sale_commission'] ?? 0.00,
                'brand_id' => $this->defaultBrandId,
                'category_id' => $this->defaultCategoryId,
                'supplier_id' => $this->defaultSupplierId,
            ]);

            // Create price record
            ProductPrice::create([
                'product_id' => $product->id,
                'supplier_price' => $row['supplier_price'] ?? 0.00,
                'selling_price' => $row['selling_price'] ?? 0.00,
                'cash_price' => $row['cash_price'] ?? null,
                'credit_price' => $row['credit_price'] ?? null,
                'cash_credit_price' => $row['cash_credit_price'] ?? null,
            ]);

            // Create stock record
            ProductStock::create([
                'product_id' => $product->id,
                'available_stock' => 0,
                'opening_stock_rate' => 0.00,
                'damage_stock' => 0,
                'restocked_quantity' => 0,
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
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'selling_price' => 'nullable|numeric|min:0',
            'credit_price' => 'nullable|numeric|min:0',
            'cash_credit_price' => 'nullable|numeric|min:0',
            'cash_price' => 'nullable|numeric|min:0',
            'supplier_price' => 'nullable|numeric|min:0',
            'credit_sale_commission' => 'nullable|numeric|min:0',
            'cash_sale_commission' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'product_code.required' => 'Product code is required',
            'description.required' => 'Description is required',
            'selling_price.numeric' => 'Selling price must be a valid number',
            'cash_price.numeric' => 'Cash price must be a valid number',
            'credit_price.numeric' => 'Credit price must be a valid number',
            'cash_credit_price.numeric' => 'Cash & Credit price must be a valid number',
            'supplier_price.numeric' => 'Supplier price must be a valid number',
            'cash_sale_commission.numeric' => 'Cash Sale Commission must be a valid number',
            'credit_sale_commission.numeric' => 'Credit Sale Commission must be a valid number',
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
