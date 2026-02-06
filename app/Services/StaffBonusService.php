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
            // All sales are wholesale - only payment method matters
            $paymentMethod = $sale->payment_method ?? 'cash';
            $staffId = $sale->user_id;
            
            // Check if the user is a staff member. Admins do not get bonuses.
            $user = \App\Models\User::find($staffId);
            if (!$user || $user->role !== 'staff') {
                Log::info("Staff Commission skipped: User is not staff or not found.", ['user_id' => $staffId]);
                return;
            }

            // Get all sale items
            $saleItems = $sale->items;

            foreach ($saleItems as $item) {
                // Get product details with commission information
                $product = ProductDetail::find($item->product_id);
                
                if (!$product) {
                    continue;
                }

                // Determine which commission field to use based on payment method
                $commissionPerUnit = self::getCommissionAmount($product, $paymentMethod);
                
                // Calculate total commission for this item
                $totalCommission = $commissionPerUnit * $item->quantity;

                // Record the staff commission (bonus)
                StaffBonus::create([
                    'sale_id' => $sale->id,
                    'staff_id' => $staffId,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'sale_type' => 'wholesale', // All sales are wholesale
                    'payment_method' => $paymentMethod,
                    'bonus_per_unit' => $commissionPerUnit,
                    'total_bonus' => $totalCommission,
                ]);

                Log::info("Staff Commission Recorded", [
                    'sale_id' => $sale->id,
                    'staff_id' => $staffId,
                    'product_id' => $product->id,
                    'payment_method' => $paymentMethod,
                    'commission_per_unit' => $commissionPerUnit,
                    'total_commission' => $totalCommission,
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to calculate staff commissions for sale {$sale->id}: " . $e->getMessage());
        }
    }

    /**
     * Get the appropriate commission amount based on payment method
     * Cash-based: cash, bank_transfer, cheque → use cash_sale_commission
     * Credit-based: credit → use credit_sale_commission
     * 
     * For sale_price_type = 'cash_credit', we use cash commission (same as cash)
     *
     * @param ProductDetail $product
     * @param string $paymentMethod (cash/bank_transfer/cheque/credit)
     * @return float
     */
    private static function getCommissionAmount(ProductDetail $product, string $paymentMethod): float
    {
        // Cash-based payment methods: cash, bank_transfer, cheque
        // Note: cash_credit price type is treated as cash for commission purposes
        $cashBasedMethods = ['cash', 'bank_transfer', 'cheque'];
        
        if (in_array($paymentMethod, $cashBasedMethods)) {
            return $product->cash_sale_commission ?? 0;
        } else {
            // Credit-based payment
            return $product->credit_sale_commission ?? 0;
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
     * Get bonus breakdown by payment method for a staff member
     * Note: All sales are wholesale
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
            'cash_commission' => $bonuses->where('payment_method', 'cash')->sum('total_bonus'),
            'credit_commission' => $bonuses->where('payment_method', 'credit')->sum('total_bonus'),
            'total' => $bonuses->sum('total_bonus'),
        ];
    }
}
