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
        $deductions  = [];
        $totalCost   = 0;

        // Get active batches in FIFO order (oldest first)
        $batches = ProductBatch::getActiveBatches($productId);

        // ----------------------------------------------------------------
        // FALLBACK: No active batches → use ProductStock available stock
        // ----------------------------------------------------------------
        if ($batches->isEmpty()) {
            // Use sum() across all stock rows for this product (matches addToCart logic)
            $totalAvailableStock = ProductStock::where('product_id', $productId)->sum('available_stock');

            if ($totalAvailableStock < $quantity) {
                throw new \Exception(
                    "Insufficient stock. Required: {$quantity}, Available: {$totalAvailableStock}"
                );
            }

            // Use the product's current price record as the selling price
            $productPrice  = ProductPrice::where('product_id', $productId)->first();
            $sellingPrice  = $productPrice ? (float) $productPrice->selling_price  : 0;
            $supplierPrice = $productPrice ? (float) $productPrice->supplier_price : 0;

            // Deduct from all ProductStock rows proportionally (FIFO across rows)
            $remainingToDeduct = $quantity;
            $stockRows = ProductStock::where('product_id', $productId)
                ->where('available_stock', '>', 0)
                ->orderBy('id', 'asc')
                ->get();

            foreach ($stockRows as $stockRow) {
                if ($remainingToDeduct <= 0) break;

                $deductFromRow = min($remainingToDeduct, $stockRow->available_stock);
                $stockRow->available_stock -= $deductFromRow;
                $stockRow->sold_count      += $deductFromRow;
                $stockRow->total_stock      = $stockRow->available_stock + $stockRow->damage_stock;
                $stockRow->save();

                $remainingToDeduct -= $deductFromRow;
            }

            $deductions[] = [
                'batch_id'       => null,
                'batch_number'   => 'DIRECT',
                'quantity'       => $quantity,
                'supplier_price' => $supplierPrice,
                'selling_price'  => $sellingPrice,
                'cost'           => $supplierPrice * $quantity,
            ];

            $totalCost = $supplierPrice * $quantity;

            Log::info("Direct stock deduction (no active batches) for Product #{$productId}", [
                'quantity'      => $quantity,
                'selling_price' => $sellingPrice,
                'remaining'     => $totalAvailableStock - $quantity,
            ]);

            return [
                'success'      => true,
                'deductions'   => $deductions,
                'total_cost'   => $totalCost,
                'average_cost' => $quantity > 0 ? $totalCost / $quantity : 0,
                'source'       => 'direct', // indicates no-batch path was used
            ];
        }

        // ----------------------------------------------------------------
        // NORMAL PATH: FIFO batch deduction
        // ----------------------------------------------------------------

        // Check if we have enough total stock across batches
        $totalAvailable = $batches->sum('remaining_quantity');
        if ($totalAvailable < $quantity) {
            throw new \Exception("Insufficient stock. Required: {$quantity}, Available: {$totalAvailable}");
        }

        $remainingQty  = $quantity;
        $batchDepleted = false;

        foreach ($batches as $batch) {
            if ($remainingQty <= 0) break;

            $deductQty = min($remainingQty, $batch->remaining_quantity);

            // Check if this batch will be depleted
            $willBeDepleted = ($deductQty == $batch->remaining_quantity);

            // Deduct from batch
            $batch->deduct($deductQty);

            if ($willBeDepleted) {
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

            $totalCost    += $batch->supplier_price * $deductQty;
            $remainingQty -= $deductQty;
        }

        // Update product stock totals
        $stock = ProductStock::where('product_id', $productId)->first();
        if ($stock) {
            $stock->available_stock -= $quantity;
            $stock->sold_count      += $quantity;
            $stock->total_stock      = $stock->available_stock + $stock->damage_stock;
            $stock->save();
        }

        // If any batch was depleted, update the main product price to the next batch
        if ($batchDepleted) {
            self::updateMainPrices($productId);
        }

        return [
            'success'      => true,
            'deductions'   => $deductions,
            'total_cost'   => $totalCost,
            'average_cost' => $quantity > 0 ? $totalCost / $quantity : 0,
            'source'       => 'fifo',
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
