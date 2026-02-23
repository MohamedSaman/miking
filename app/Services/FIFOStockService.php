<?php

namespace App\Services;

use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Models\ProductPrice;
use App\Models\ProductDetail;
use Illuminate\Support\Facades\Log;

class FIFOStockService
{
    /**
     * Deduct stock from batches using FIFO method.
     * Falls back to ProductStock direct deduction when no active batches exist.
     * Returns array with deduction details.
     */
    public static function deductStock($productId, $quantity)
    {
        $deductions = [];
        $totalCost = 0;

        // 1. Get ultimate capacity from ProductStock (Source of truth for total quantity)
        // This ensures UI consistency as UI shows stock from this table.
        $totalStockAvailable = ProductStock::where('product_id', $productId)->sum('available_stock');

        if ($totalStockAvailable < $quantity) {
            throw new \Exception("Insufficient stock. Required: {$quantity}, Available: {$totalStockAvailable}");
        }

        // 2. Get active batches (for FIFO cost and accounting)
        $batches = ProductBatch::getActiveBatches($productId);
        $remainingToDeductFromAccounting = $quantity;
        $batchDepleted = false;

        // 3. Deduct from batches first (FIFO Accounting)
        foreach ($batches as $batch) {
            if ($remainingToDeductFromAccounting <= 0) break;

            $deductQty = min($remainingToDeductFromAccounting, $batch->remaining_quantity);
            $batch->deduct($deductQty);

            if ($batch->remaining_quantity == 0) {
                $batchDepleted = true;
            }

            $deductions[] = [
                'batch_id'       => $batch->id,
                'batch_number'   => $batch->batch_number,
                'quantity'       => $deductQty,
                'supplier_price' => $batch->supplier_price,
                'selling_price'  => $batch->selling_price,
                'cost'           => $batch->supplier_price * $deductQty,
            ];

            $totalCost += (float) $batch->supplier_price * $deductQty;
            $remainingToDeductFromAccounting -= $deductQty;
        }

        // 4. Handle cases where ProductStock > Batches (Direct Deduction for out-of-sync stock)
        if ($remainingToDeductFromAccounting > 0) {
            $productPrice = ProductPrice::where('product_id', $productId)->first();
            $sellingPrice = $productPrice ? (float) $productPrice->selling_price : 0;
            $supplierPrice = $productPrice ? (float) $productPrice->supplier_price : 0;

            $deductions[] = [
                'batch_id'       => null,
                'batch_number'   => 'DIRECT',
                'quantity'       => $remainingToDeductFromAccounting,
                'supplier_price' => $supplierPrice,
                'selling_price'  => $sellingPrice,
                'cost'           => $supplierPrice * $remainingToDeductFromAccounting,
            ];

            $totalCost += $supplierPrice * $remainingToDeductFromAccounting;
            
            Log::info("Mixed stock deduction for Product #{$productId}. Direct part: {$remainingToDeductFromAccounting}");
        }

        // 5. Update ProductStock table rows (FIFO across rows if multiple)
        $stockRows = ProductStock::where('product_id', $productId)
            ->where('available_stock', '>', 0)
            ->orderBy('id', 'asc')
            ->get();

        $remainingToDeductFromRows = $quantity;
        foreach ($stockRows as $row) {
            if ($remainingToDeductFromRows <= 0) break;
            
            $deductFromRow = min($remainingToDeductFromRows, $row->available_stock);
            
            $row->available_stock -= $deductFromRow;
            $row->sold_count      += $deductFromRow;
            $row->total_stock      = $row->available_stock + $row->damage_stock;
            $row->save();
            
            $remainingToDeductFromRows -= $deductFromRow;
        }

        // 6. Update main prices if any batch was emptied
        if ($batchDepleted) {
            self::updateMainPrices($productId);
        }

        return [
            'success'      => true,
            'deductions'   => $deductions,
            'total_cost'   => (float) $totalCost,
            'average_cost' => $quantity > 0 ? (float) ($totalCost / $quantity) : 0,
            'source'       => count($batches) > 0 ? ($remainingToDeductFromAccounting > 0 ? 'mixed' : 'fifo') : 'direct',
        ];
    }

    /**
     * Update main product prices when a batch is depleted
     * Uses the oldest active batch prices (next in FIFO queue)
     */
    public static function updateMainPrices($productId)
    {
        // Get the next active batch (if any)
        $nextBatch = ProductBatch::where('product_id', $productId)
            ->where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->orderBy('received_date', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        if ($nextBatch) {
            // Update main product prices to reflect the next batch
            $productPrice = ProductPrice::where('product_id', $productId)->first();

            if ($productPrice) {
                $oldSupplierPrice = $productPrice->supplier_price;
                $oldSellingPrice = $productPrice->selling_price;

                $productPrice->supplier_price = $nextBatch->supplier_price;
                $productPrice->selling_price = $nextBatch->selling_price;
                $productPrice->save();

                Log::info("Product #{$productId} prices updated", [
                    'old_supplier_price' => $oldSupplierPrice,
                    'new_supplier_price' => $nextBatch->supplier_price,
                    'old_selling_price' => $oldSellingPrice,
                    'new_selling_price' => $nextBatch->selling_price,
                    'batch_number' => $nextBatch->batch_number,
                ]);
            } else {
                // Create price record if doesn't exist
                ProductPrice::create([
                    'product_id' => $productId,
                    'supplier_price' => $nextBatch->supplier_price,
                    'selling_price' => $nextBatch->selling_price,
                    'cash_credit_price' => 0,
                ]);

                Log::info("Product #{$productId} price record created", [
                    'supplier_price' => $nextBatch->supplier_price,
                    'selling_price' => $nextBatch->selling_price,
                    'batch_number' => $nextBatch->batch_number,
                ]);
            }

            return true;
        }

        Log::info("No active batches found for Product #{$productId} to update prices");
        return false;
    }

    /**
     * Get current active batch prices for a product
     */
    public static function getCurrentBatchPrices($productId)
    {
        $batch = ProductBatch::where('product_id', $productId)
            ->where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->orderBy('received_date', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        if ($batch) {
            return [
                'supplier_price' => $batch->supplier_price,
                'selling_price' => $batch->selling_price,
                'batch_number' => $batch->batch_number,
                'remaining_quantity' => $batch->remaining_quantity,
            ];
        }

        return null;
    }

    /**
     * Check available stock across all active batches
     */
    public static function getAvailableStock($productId)
    {
        return ProductBatch::where('product_id', $productId)
            ->where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->sum('remaining_quantity');
    }

    /**
     * Get batch details for a product
     */
    public static function getBatchDetails($productId)
    {
        return ProductBatch::where('product_id', $productId)
            ->where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->orderBy('received_date', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($batch) {
                return [
                    'batch_number' => $batch->batch_number,
                    'remaining_quantity' => $batch->remaining_quantity,
                    'supplier_price' => $batch->supplier_price,
                    'selling_price' => $batch->selling_price,
                    'received_date' => $batch->received_date->format('Y-m-d'),
                ];
            });
    }
}
