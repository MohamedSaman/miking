<?php

namespace App\Imports;

use App\Models\ProductDetail;
use App\Models\StaffProduct;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Support\Facades\DB;

class StaffAllocationImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnFailure
{
    use SkipsFailures;

    private $staffId;
    private $successCount = 0;
    private $skipCount = 0;
    private $errors = [];

    public function __construct($staffId)
    {
        $this->staffId = $staffId;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Log the row data for debugging
        \Log::info('Import row data:', $row);
        
        // Get product code from row (support multiple variations)
        $productCode = $row['item_code'] 
            ?? $row['itemcode'] 
            ?? $row['item code'] 
            ?? $row['product_code'] 
            ?? $row['productcode'] 
            ?? $row['product code'] 
            ?? null;
        
        if (!$productCode) {
            $this->skipCount++;
            $this->errors[] = "Row skipped: Missing product code. Available keys: " . implode(', ', array_keys($row));
            \Log::warning('Missing product code', ['row' => $row]);
            return null;
        }

        // Find product by code
        $product = ProductDetail::where('code', $productCode)->first();

        if (!$product) {
            $this->skipCount++;
            $this->errors[] = "Product not found: {$productCode}";
            \Log::warning('Product not found', ['code' => $productCode]);
            return null;
        }

        // Get quantity (support multiple variations)
        $quantity = (int)($row['qty'] ?? $row['quantity'] ?? $row['Qty'] ?? $row['Quantity'] ?? 0);

        if ($quantity <= 0) {
            $this->skipCount++;
            $this->errors[] = "Invalid quantity for product: {$productCode}";
            \Log::warning('Invalid quantity', ['code' => $productCode, 'quantity' => $quantity]);
            return null;
        }

        // Check available stock
        $availableStock = DB::table('product_stocks')
            ->where('product_id', $product->id)
            ->sum('available_stock');

        if ($availableStock < $quantity) {
            $this->skipCount++;
            $this->errors[] = "Insufficient stock for product {$productCode}. Available: {$availableStock}, Required: {$quantity}";
            return null;
        }

        try {
            DB::beginTransaction();

            // Get product price
            $productPrice = DB::table('product_prices')
                ->where('product_id', $product->id)
                ->first();

            $unitPrice = $productPrice->selling_price ?? 0;
            $subtotal = $quantity * $unitPrice;

            // Check if this product is already allocated to this staff member
            $existingAllocation = StaffProduct::where('product_id', $product->id)
                ->where('staff_id', $this->staffId)
                ->first();

            if ($existingAllocation) {
                // Update existing allocation - add to quantity
                $newQuantity = $existingAllocation->quantity + $quantity;
                $newSubtotal = $newQuantity * $unitPrice;

                $existingAllocation->update([
                    'quantity' => $newQuantity,
                    'unit_price' => $unitPrice,
                    'total_value' => $newSubtotal - $existingAllocation->total_discount,
                ]);
            } else {
                // Create new staff product allocation
                StaffProduct::create([
                    'product_id' => $product->id,
                    'staff_id' => $this->staffId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_per_unit' => 0,
                    'total_discount' => 0,
                    'total_value' => $subtotal,
                    'sold_quantity' => 0,
                    'sold_value' => 0,
                    'status' => 'assigned',
                ]);
            }

            // Reduce admin stock using FIFO
            try {
                \App\Services\FIFOStockService::deductStock($product->id, $quantity);
            } catch (\Exception $e) {
                throw new \Exception("Failed to reduce admin stock for product {$productCode}: " . $e->getMessage());
            }

            DB::commit();
            $this->successCount++;

            return null; // Return null since we're handling creation manually

        } catch (\Exception $e) {
            DB::rollBack();
            $this->skipCount++;
            $this->errors[] = "Error allocating product {$productCode}: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Validation rules for each row
     */
    public function rules(): array
    {
        return [
            'item_code' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'qty' => 'required|integer|min:1',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'item_code.required' => 'Item code is required',
            'qty.required' => 'Quantity is required',
            'qty.integer' => 'Quantity must be a valid number',
            'qty.min' => 'Quantity must be at least 1',
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
     * Get all errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get heading row configuration
     */
    public function headingRow(): int
    {
        return 1; // First row contains headers
    }
}
