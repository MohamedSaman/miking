<?php

namespace App\Services;

use App\Models\StaffBonus;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ProductDetail;
use Illuminate\Support\Facades\Log;

class StaffBonusService
{
    /**
     * Calculate and record staff bonuses for a sale
     *
     * @param Sale $sale
     * @return void
     */
    public static function calculateBonusesForSale(Sale $sale)
    {
        try {
            // Get sale type (wholesale/retail) and payment method (cash/credit)
            $saleType = $sale->customer_type_sale ?? 'retail';
            $paymentMethod = $sale->payment_method ?? 'cash';
            $staffId = $sale->user_id;

            // Get all sale items
            $saleItems = $sale->items;

            foreach ($saleItems as $item) {
                // Get product details with bonus information
                $product = ProductDetail::find($item->product_id);
                
                if (!$product) {
                    continue;
                }

                // Determine which bonus field to use based on sale type and payment method
                $bonusPerUnit = self::getBonusAmount($product, $saleType, $paymentMethod);
                
                // Calculate total bonus for this item
                $totalBonus = $bonusPerUnit * $item->quantity;

                // Record the staff bonus
                StaffBonus::create([
                    'sale_id' => $sale->id,
                    'staff_id' => $staffId,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'sale_type' => $saleType,
                    'payment_method' => $paymentMethod,
                    'bonus_per_unit' => $bonusPerUnit,
                    'total_bonus' => $totalBonus,
                ]);

                Log::info("Staff Bonus Recorded", [
                    'sale_id' => $sale->id,
                    'staff_id' => $staffId,
                    'product_id' => $product->id,
                    'sale_type' => $saleType,
                    'payment_method' => $paymentMethod,
                    'bonus_per_unit' => $bonusPerUnit,
                    'total_bonus' => $totalBonus,
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to calculate staff bonuses for sale {$sale->id}: " . $e->getMessage());
        }
    }

    /**
     * Get the appropriate bonus amount based on sale type and payment method
     *
     * @param ProductDetail $product
     * @param string $saleType (wholesale/retail)
     * @param string $paymentMethod (cash/credit)
     * @return float
     */
    private static function getBonusAmount(ProductDetail $product, string $saleType, string $paymentMethod): float
    {
        // Map sale type and payment method to the correct bonus field
        if ($saleType === 'wholesale') {
            if ($paymentMethod === 'cash') {
                return $product->wholesale_cash_bonus ?? 0;
            } else {
                return $product->wholesale_credit_bonus ?? 0;
            }
        } else { // retail
            if ($paymentMethod === 'cash') {
                return $product->retail_cash_bonus ?? 0;
            } else {
                return $product->retail_credit_bonus ?? 0;
            }
        }
    }

    /**
     * Get total bonuses for a staff member within a date range
     *
     * @param int $staffId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return float
     */
    public static function getTotalBonusForStaff(int $staffId, ?string $startDate = null, ?string $endDate = null): float
    {
        $query = StaffBonus::where('staff_id', $staffId);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query->sum('total_bonus');
    }

    /**
     * Get bonus breakdown by sale type and payment method for a staff member
     *
     * @param int $staffId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public static function getBonusBreakdown(int $staffId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = StaffBonus::where('staff_id', $staffId);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $bonuses = $query->get();

        return [
            'wholesale_cash' => $bonuses->where('sale_type', 'wholesale')->where('payment_method', 'cash')->sum('total_bonus'),
            'wholesale_credit' => $bonuses->where('sale_type', 'wholesale')->where('payment_method', 'credit')->sum('total_bonus'),
            'retail_cash' => $bonuses->where('sale_type', 'retail')->where('payment_method', 'cash')->sum('total_bonus'),
            'retail_credit' => $bonuses->where('sale_type', 'retail')->where('payment_method', 'credit')->sum('total_bonus'),
            'total' => $bonuses->sum('total_bonus'),
        ];
    }
}
