<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'permission_key',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the permission.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Available permission keys and their descriptions
     */
    public static function availablePermissions()
    {
        return [
            // Products Menu
            'menu_products' => 'Products Parent Menu',
            'menu_stock' => 'Stock Overview',
            'staff_my_allocated_products' => 'My Allocated Products',

            // Sales Menu
            'menu_sales' => 'Sales Parent Menu',
            'menu_sales_add' => 'Staff POS Sale',
            'menu_sales_list' => 'Sales List',
            'menu_sales_system' => 'Add Sales',
            'menu_pos_sales' => 'POS Sales',
            'menu_total_sales' => 'Total Sales',

            // Quotation Menu
            'menu_quotation' => 'Quotation Parent Menu',
            'menu_quotation_add' => 'Add Quotation',
            'menu_quotation_list' => 'Quotation List',

            // Return Menu
            'menu_return' => 'Return Parent Menu',
            'menu_return_customer_add' => 'Add Customer Return',
            'menu_return_customer_list' => 'Customer Return List',
            'menu_return_supplier_add' => 'Add Supplier Return',
            'menu_return_supplier_list' => 'Supplier Return List',

            // Payment Management Menu
            'menu_payment' => 'Payment Management',
            'menu_payment_add' => 'Add Payment',
            'menu_payment_list' => 'Payment List',

            // Cheque/Banks Menu
            'menu_banks' => 'Cheque / Banks',
            'menu_banks_cheque_list' => 'Cheque List',

            // Expenses Menu
            'menu_expenses' => 'Expenses Menu',
            'menu_expenses_list' => 'List Expenses',

            // People Menu
            'menu_people' => 'People Parent Menu',
            'menu_people_customers' => 'List Customer',

            // Reports
            'menu_reports' => 'Reports Access',
            'report_sales_transaction_history' => 'Report: Sales Transactions',
            'report_sales_payment' => 'Report: Sales/Payments',
            'report_sales_product' => 'Report: Sales by Item',
            'report_sales_by_staff' => 'Report: Sales by Staff',
            'report_sales_by_product' => 'Report: Detailed Product Sales',
            'report_sales_invoice_aging' => 'Report: Invoice Aging',
            'report_sales_detailed' => 'Report: Detailed Sales',
            'report_sales_return' => 'Report: Sales Returns',

            'report_purchases_payment' => 'Report: Purchase Payments',
            'report_purchases_detailed' => 'Report: Detailed Purchases',

            'report_inventory_product_wise' => 'Report: Product COGS',
            'report_inventory_year_wise' => 'Report: Yearly COGS',

            'report_pl_cogs' => 'Report: P&L (COGS)',
            'report_pl_opening_closing' => 'Report: P&L (Stock Change)',
            'report_pl_period_cogs' => 'Report: Period COGS',
            'report_pl_period_stock' => 'Report: Period Stock',
            'report_pl_product_wise' => 'Report: Product P&L',
            'report_pl_invoice_wise' => 'Report: Invoice P&L',
            'report_pl_customer_wise' => 'Report: Customer P&L',

            'report_other_expense' => 'Report: Expenses Breakdown',
            'report_other_commission' => 'Report: Staff Commissions',
            'report_other_payment_mode' => 'Report: Payment Modes',

            // Analytics
            'menu_analytics' => 'Analytics Dashboard',
        ];
    }

    /**
     * Permission categories for organized display
     */
    public static function permissionCategories()
    {
        return [
            'Staff POS Sale' => [
                'menu_sales_add',
            ],
            'Products Management' => [
                'menu_products',
                'menu_stock',
                'staff_my_allocated_products',
            ],
            'Sales Management' => [
                'menu_sales',
                'menu_sales_list',
                'menu_sales_system',
                'menu_pos_sales',
                'menu_total_sales',
            ],
            'Quotation Management' => [
                'menu_quotation',
                'menu_quotation_add',
                'menu_quotation_list',
            ],
            'Return Management' => [
                'menu_return',
                'menu_return_customer_add',
                'menu_return_customer_list',
                'menu_return_supplier_add',
                'menu_return_supplier_list',
            ],
            'Payment Management' => [
                'menu_payment',
                'menu_payment_add',
                'menu_payment_list',
            ],
            'Cheque & Banks' => [
                'menu_banks',
                'menu_banks_cheque_list',
            ],
            'Expenses Management' => [
                'menu_expenses',
                'menu_expenses_list',
            ],
            'People Management' => [
                'menu_people',
                'menu_people_customers',
            ],
            'Reports Management' => [
                'menu_reports',
                'report_sales_transaction_history',
                'report_sales_payment',
                'report_sales_product',
                'report_sales_by_staff',
                'report_sales_by_product',
                'report_sales_invoice_aging',
                'report_sales_detailed',
                'report_sales_return',
                'report_purchases_payment',
                'report_purchases_detailed',
                'report_inventory_product_wise',
                'report_inventory_year_wise',
                'report_pl_cogs',
                'report_pl_opening_closing',
                'report_pl_period_cogs',
                'report_pl_period_stock',
                'report_pl_product_wise',
                'report_pl_invoice_wise',
                'report_pl_customer_wise',
                'report_other_expense',
                'report_other_commission',
                'report_other_payment_mode',
            ],
            'Analytics' => [
                'menu_analytics',
            ],
        ];
    }

    /**
     * Check if a user has a specific permission
     */
    public static function hasPermission($userId, $permissionKey)
    {
        return self::where('user_id', $userId)
            ->where('permission_key', $permissionKey)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get all permissions for a user
     */
    public static function getUserPermissions($userId)
    {
        return self::where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('permission_key')
            ->toArray();
    }

    /**
     * Sync permissions for a user
     */
    public static function syncPermissions($userId, array $permissions)
    {
        // Delete existing permissions
        self::where('user_id', $userId)->delete();

        // Create new permissions
        foreach ($permissions as $permission) {
            self::create([
                'user_id' => $userId,
                'permission_key' => $permission,
                'is_active' => true,
            ]);
        }
    }
}
