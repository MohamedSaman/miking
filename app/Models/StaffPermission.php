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
            'menu_dashboard' => 'Dashboard/Overview',

            // POS
            'menu_pos' => 'POS (Point of Sale)',

            // Stock Management
            'menu_stock' => 'Stock Overview',

            // Sales Menu
            'menu_sales' => 'Sales Menu',
            'menu_sales_add' => 'Staff POS Sale', // Updated description
            'menu_sales_list' => 'List Sales',

            // Quotation Menu
            'menu_quotation' => 'Quotation Menu',
            'menu_quotation_add' => 'Add Quotation',
            'menu_quotation_list' => 'List Quotations',

            // Payment Management Menu
            'menu_payment' => 'Payment Management Menu',
            'menu_payment_add' => 'Add Payment (Due Payments)',
            'menu_payment_list' => 'Payment List',

            // Products Menu (if staff needs to view products)
            'menu_products' => 'Products Menu',
            'menu_products_list' => 'List Products',
            'menu_products_brand' => 'Product Brands',
            'menu_products_category' => 'Product Categories',

            // Purchase Menu
            'menu_purchase' => 'Purchase Menu',
            'menu_purchase_order' => 'Purchase Order List',
            'menu_purchase_grn' => 'GRN (Goods Received Note)',

            // Return Menu
            'menu_return' => 'Return Menu',
            'menu_return_customer_add' => 'Add Customer Return',
            'menu_return_customer_list' => 'List Customer Returns',
            'menu_return_supplier_add' => 'Add Supplier Return',
            'menu_return_supplier_list' => 'List Supplier Returns',

            // Cheque/Banks Menu
            'menu_banks' => 'Cheque/Banks Menu',
            'menu_banks_deposit' => 'Deposit By Cash',
            'menu_banks_cheque_list' => 'Cheque List',
            'menu_banks_return_cheque' => 'Return Cheque',

            // Expenses Menu
            'menu_expenses' => 'Expenses Menu',
            'menu_expenses_list' => 'List Expenses',

            // People Menu
            'menu_people' => 'People Menu',
            'menu_people_suppliers' => 'Manage Suppliers',
            'menu_people_customers' => 'Customer Management', // Updated description
            'menu_people_staff' => 'List Staff',

            // Reports
            'menu_reports' => 'Reports Menu Access',
            'report_sales_transaction_history' => 'Report: Sales - Transaction History',
            'report_sales_payment' => 'Report: Sales - Sales/Payment',
            'report_sales_product' => 'Report: Sales - Product',
            'report_sales_by_staff' => 'Report: Sales - By Staff',
            'report_sales_by_product' => 'Report: Sales - By Product',
            'report_sales_invoice_aging' => 'Report: Sales - Invoice Aging',
            'report_sales_detailed' => 'Report: Sales - Detailed',
            'report_sales_return' => 'Report: Sales - Sales Return',
            
            'report_purchases_payment' => 'Report: Purchases - Purchases/Payment',
            'report_purchases_detailed' => 'Report: Purchases - Detailed',
            
            'report_inventory_product_wise' => 'Report: Inventory - Product Wise COGS',
            'report_inventory_year_wise' => 'Report: Inventory - Year Wise COGS',
            
            'report_pl_cogs' => 'Report: P&L - COGS Method',
            'report_pl_opening_closing' => 'Report: P&L - Opening/Closing Method',
            'report_pl_period_cogs' => 'Report: P&L - Period COGS',
            'report_pl_period_stock' => 'Report: P&L - Period Stock',
            'report_pl_product_wise' => 'Report: P&L - Product Wise',
            'report_pl_invoice_wise' => 'Report: P&L - Invoice Wise',
            'report_pl_customer_wise' => 'Report: P&L - Customer Wise',
            
            'report_other_expense' => 'Report: Other - Expense Report',
            'report_other_commission' => 'Report: Other - Commission Report',
            'report_other_payment_mode' => 'Report: Other - Payment Mode Report',

            // Analytics
            'menu_analytics' => 'Analytics',

            // Sales Distribution
            'sales_distribution_access' => 'Sales Distribution Page Access',
            
            // Staff Product Management
            'staff_product_return' => 'Return Allocated Products',
            'staff_my_allocated_products' => 'Allocated Products',
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
            'Dashboard' => [
                'menu_dashboard',
            ],
            'Point of Sale' => [
                'menu_pos',
            ],
            'Stock Management' => [
                'menu_stock',
            ],
            'Sales Management' => [
                'menu_sales',
                'menu_sales_add',
                'menu_sales_list',
            ],
            'Quotation Management' => [
                'menu_quotation',
                'menu_quotation_add',
                'menu_quotation_list',
            ],
            'Payment Management' => [
                'menu_payment',
                'menu_payment_add',
                'menu_payment_list',
            ],
            'Products Management' => [
                'menu_products',
                'menu_products_list',
                'menu_products_brand',
                'menu_products_category',
            ],
            'Purchase Management' => [
                'menu_purchase',
                'menu_purchase_order',
                'menu_purchase_grn',
            ],
            'Return Management' => [
                'menu_return',
                'menu_return_customer_add',
                'menu_return_customer_list',
                'menu_return_supplier_add',
                'menu_return_supplier_list',
            ],
            'Cheque & Banks' => [
                'menu_banks',
                'menu_banks_deposit',
                'menu_banks_cheque_list',
                'menu_banks_return_cheque',
            ],
            'Expenses Management' => [
                'menu_expenses',
                'menu_expenses_list',
            ],
            'People Management' => [
                'menu_people',
                'menu_people_suppliers',
                'menu_people_customers',
                'menu_people_staff',
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
            'Distribution Management' => [
                'sales_distribution_access',
            ],
            'Staff Product Management' => [
                'staff_product_return',
                'staff_my_allocated_products',
                // 'menu_staff_allocation_list',
                // 'menu_staff_return_requests',
                // // 'menu_staff_attendance',
                // // 'menu_staff_salary',
                // 'menu_staff_salary_management',
                // 'menu_loan_management',
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
