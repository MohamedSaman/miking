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
            // Dashboard
            'menu_dashboard' => 'Dashboard Overview Access',


            // Stock
            'menu_stock' => 'Stock Status View',

            // Sales Menu
            'menu_sales' => 'Sales Parent Menu',
            'menu_sales_add' => 'Staff POS Sale Entry',
            'menu_sales_list' => 'View Sales Records',
            'menu_sales_system' => 'Add Sales (Sales System)',
            'menu_pos_sales' => 'POS Sales Records',
            'sales_distribution_access' => 'Sales Distribution Page',

            // Quotation Menu
            'menu_quotation' => 'Quotation Parent Menu',
            'menu_quotation_add' => 'Add New Quotation',
            'menu_quotation_list' => 'View All Quotations',

            // Payment Management Menu
            'menu_payment' => 'Payments Parent Menu',
            'menu_payment_add' => 'Record Due Payment',
            'menu_payment_list' => 'View Payment List',

            // Products Menu
            'menu_products' => 'Products Parent Menu',
            'menu_products_list' => 'View Product List',
            'menu_products_brand' => 'Manage Product Brands',
            'menu_products_category' => 'Manage Categories',

            // Purchase Menu
            'menu_purchase' => 'Purchase Parent Menu',
            'menu_purchase_order' => 'Purchase Order Management',
            'menu_purchase_grn' => 'GRN Processing',

            // Return Menu
            'menu_return' => 'Returns Parent Menu',
            'menu_return_customer_add' => 'Process Customer Return',
            'menu_return_customer_list' => 'View Customer Returns',
            'menu_return_supplier_add' => 'Process Supplier Return',
            'menu_return_supplier_list' => 'View Supplier Returns',

            // Cheque/Banks Menu
            'menu_banks' => 'Banking Parent Menu',
            'menu_banks_deposit' => 'Cash Deposit Entry',
            'menu_banks_cheque_list' => 'View Cheque List',
            'menu_banks_return_cheque' => 'Cheque Return Processing',

            // Expenses Menu
            'menu_expenses' => 'Expenses Menu Access',
            'menu_expenses_list' => 'View Expense List',

            // People Menu
            'menu_people' => 'People Parent Menu',
            'menu_people_suppliers' => 'Supplier Management',
            'menu_people_customers' => 'Customer Management',
            'menu_people_staff' => 'Staff Records',

            // Reports
            'menu_reports' => 'Reports Main Access',
            'report_sales_transaction_history' => 'Report: Sales Transactions',
            'report_sales_payment' => 'Report: Sales/Payments',
            'report_sales_product' => 'Report: Sales by Item',
            'report_sales_by_staff' => 'Report: Sales by Staff',
            'report_sales_by_product' => 'Report: Detailed Product Sales',
            'report_sales_invoice_aging' => 'Report: Invoice Aging',
            'report_sales_detailed' => 'Report: Detailed Sales Info',
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
            
            // Staff Product Management
            'staff_product_return' => 'Return Assigned Stock',
            'staff_my_allocated_products' => 'My Allocated Stock',
            // 'menu_staff_allocation_list' => 'Staff Allocation List',
            // 'menu_staff_return_requests' => 'Staff Return Requests',
            // // 'menu_staff_attendance' => 'Staff Attendance',
            // // 'menu_staff_salary' => 'Staff Salary (Calculations)',
            // 'menu_staff_salary_management' => 'Staff Salary Management',
            // 'menu_loan_management' => 'Loan Management',
        ];
    }

    /**
     * Permission categories for organized display
     */
    public static function permissionCategories()
    {
        return [
            'Overview' => [
                'menu_dashboard',
                'menu_sales_add',
            ],
            'Purchase Management' => [
                'menu_purchase',
                'menu_purchase_order',
                'menu_purchase_grn',
            ],
            'Products Management' => [
                'menu_products',
                'menu_products_list',
                'menu_products_brand',
                'menu_products_category',
                'menu_stock',
                'staff_my_allocated_products',
            ],
            'Sales Management' => [
                'menu_sales',
                'menu_sales_list',
                'menu_sales_system',
                'menu_pos_sales',
                'sales_distribution_access',
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
                'menu_banks_deposit',
                'menu_banks_cheque_list',
                'menu_banks_return_cheque',
            ],
            'People Management' => [
                'menu_people',
                'menu_people_suppliers',
                'menu_people_customers',
                'menu_people_staff',
            ],
            'Staff Management' => [
                'staff_product_return',
            ],
            'Expenses Management' => [
                'menu_expenses',
                'menu_expenses_list',
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
            'Analytics Management' => [
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
