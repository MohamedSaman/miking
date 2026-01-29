<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductStock;
use App\Models\ProductSupplier;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\StaffBonus;
use App\Models\ReturnsProduct;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use App\Exports\SalaryReportExport;
use App\Exports\InventoryReportExport;
use App\Exports\StaffReportExport;
use App\Exports\PaymentsReportExport;
use App\Exports\AttendanceReportExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Concerns\WithDynamicLayout;
use Livewire\WithPagination;
use Carbon\Carbon;

#[Title('Reports')]
class Reports extends Component
{
    use WithDynamicLayout, WithPagination;

    // Main category selection
    public $activeCategory = 'sales';
    public $selectedReport = '';
    public $reportStartDate;
    public $reportEndDate;
    public $selectedMonth;
    public $selectedYear;

    // Separate date pickers for daily and monthly reports
    public $dailyMonth;
    public $dailyYear;
    public $monthlyYear;

    // Period type for P&L reports
    public $periodType = 'monthly'; // monthly, weekly, daily

    // Report data arrays
    public $salesReport = [];
    public $salaryReport = [];
    public $inventoryReport = [];
    public $staffReport = [];
    public $paymentsReport = [];
    public $attendanceReport = [];
    public $dailySalesReport = [];
    public $monthlySalesReport = [];
    public $dailyPurchasesReport = [];
    public $outstandingAccountsReport = [];
    public $reportStats = [];
    public $reportData = [];

    // Modal properties
    public $showDetailModal = false;
    public $selectedDetailType = null;
    public $selectedDetailData = null;

    // Report totals
    public $salesReportTotal = 0;
    public $salaryReportTotal = 0;
    public $inventoryReportTotal = 0;
    public $staffReportTotal = 0;
    public $paymentsReportTotal = 0;
    public $attendanceReportTotal = 0;
    public $dailySalesReportTotal = 0;
    public $monthlySalesReportTotal = 0;
    public $dailyPurchasesReportTotal = 0;

    public $perPage = 10;

    // Report categories and sub-reports structure
    public $reportCategories = [
        'sales' => [
            'label' => 'Sales Report',
            'icon' => 'bi-cart-check',
            'reports' => [
                'transaction-history' => 'Transaction History',
                'sales-payment' => 'Sales/Payment Report',
                'product-report' => 'Product Report',
                'sales-by-staff' => 'Sales by Staff - Top 5',
                'sales-by-product' => 'Sales by Product - Top 5',
                'invoice-aging' => 'Invoice Aging',
                'detailed-sales' => 'Detailed Sales Report',
                'sales-return' => 'Sales Return Report',
            ]
        ],
        'purchases' => [
            'label' => 'Purchases Report',
            'icon' => 'bi-bag-check',
            'reports' => [
                'purchases-payment' => 'Purchases/Payment Report',
                'detailed-purchases' => 'Detailed Purchases Report',
            ]
        ],
        'inventory' => [
            'label' => 'Inventory Valuation',
            'icon' => 'bi-box-seam',
            'reports' => [
                'product-wise-cogs' => 'Product Wise - COGS Method',
                'year-wise-cogs' => 'Year Wise - COGS Method',
            ]
        ],
        'profit-loss' => [
            'label' => 'Profit & Loss Report',
            'icon' => 'bi-graph-up-arrow',
            'reports' => [
                'pl-cogs' => 'P & L using COGS',
                'pl-opening-closing' => 'P & L using Opening/Closing Stock',
                'pl-period-cogs' => 'Monthly/Weekly/Daily P & L COGS',
                'pl-period-stock' => 'Monthly/Weekly/Daily P & L - Changes in Stock',
                'productwise-pl' => 'Productwise Profit/Loss',
                'invoicewise-pl' => 'Invoicewise Profit/Loss',
                'customerwise-pl' => 'Customerwise Profit/Loss',
            ]
        ],
        'other' => [
            'label' => 'Other Reports',
            'icon' => 'bi-file-earmark-text',
            'reports' => [
                'expense-report' => 'Expense Report',
                'commission-report' => 'Commission Report',
                'payment-mode-report' => 'Payment Mode Report',
            ]
        ],
    ];

    public function mount()
    {
        // Set default to current month and year if not set
        if (!$this->selectedMonth) {
            $this->selectedMonth = now()->month;
        }
        if (!$this->selectedYear) {
            $this->selectedYear = now()->year;
        }

        // Initialize separate date pickers
        $this->dailyMonth = now()->month;
        $this->dailyYear = now()->year;
        $this->monthlyYear = now()->year;

        // Set default dates
        $this->reportStartDate = now()->startOfMonth()->format('Y-m-d');
        $this->reportEndDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function setCategory($category)
    {
        $this->activeCategory = $category;
        $this->selectedReport = '';
        $this->reportData = [];
    }

    public function selectReport($report)
    {
        $this->selectedReport = $report;
        $this->generateReport();
    }

    public function updatedSelectedReport()
    {
        $this->generateReport();
    }

    public function updatedReportStartDate()
    {
        $this->generateReport();
    }

    public function updatedReportEndDate()
    {
        $this->generateReport();
    }

    public function updatedSelectedMonth()
    {
        $this->generateReport();
    }

    public function updatedSelectedYear()
    {
        $this->generateReport();
    }

    public function updatedDailyMonth()
    {
        $this->generateReport();
    }

    public function updatedDailyYear()
    {
        $this->generateReport();
    }

    public function updatedMonthlyYear()
    {
        $this->generateReport();
    }

    public function updatedPeriodType()
    {
        $this->generateReport();
    }

    public function generateReport()
    {
        // Validate date range
        if ($this->reportStartDate && $this->reportEndDate && $this->reportStartDate > $this->reportEndDate) {
            $this->addError('reportEndDate', 'End date must be after start date.');
            return;
        }

        // Clear previous errors
        $this->resetErrorBag();

        // Set default dates if not set
        if (!$this->reportStartDate) {
            $this->reportStartDate = now()->startOfMonth()->format('Y-m-d');
        }
        if (!$this->reportEndDate) {
            $this->reportEndDate = now()->endOfMonth()->format('Y-m-d');
        }

        // Generate report based on selection
        switch ($this->selectedReport) {
            // Sales Reports
            case 'transaction-history':
                $this->reportData = $this->getTransactionHistory();
                break;
            case 'sales-payment':
                $this->reportData = $this->getSalesPaymentReport();
                break;
            case 'product-report':
                $this->reportData = $this->getProductReport();
                break;
            case 'sales-by-staff':
                $this->reportData = $this->getSalesByStaff();
                break;
            case 'sales-by-product':
                $this->reportData = $this->getSalesByProduct();
                break;
            case 'invoice-aging':
                $this->reportData = $this->getInvoiceAging();
                break;
            case 'detailed-sales':
                $this->reportData = $this->getDetailedSalesReport();
                break;
            case 'sales-return':
                $this->reportData = $this->getSalesReturnReport();
                break;

            // Purchases Reports
            case 'purchases-payment':
                $this->reportData = $this->getPurchasesPaymentReport();
                break;
            case 'detailed-purchases':
                $this->reportData = $this->getDetailedPurchasesReport();
                break;

            // Inventory Valuation Reports
            case 'product-wise-cogs':
                $this->reportData = $this->getProductWiseCOGS();
                break;
            case 'year-wise-cogs':
                $this->reportData = $this->getYearWiseCOGS();
                break;

            // Profit & Loss Reports
            case 'pl-cogs':
                $this->reportData = $this->getProfitLossCOGS();
                break;
            case 'pl-opening-closing':
                $this->reportData = $this->getProfitLossOpeningClosing();
                break;
            case 'pl-period-cogs':
                $this->reportData = $this->getPeriodProfitLossCOGS();
                break;
            case 'pl-period-stock':
                $this->reportData = $this->getPeriodProfitLossStock();
                break;
            case 'productwise-pl':
                $this->reportData = $this->getProductwiseProfitLoss();
                break;
            case 'invoicewise-pl':
                $this->reportData = $this->getInvoicewiseProfitLoss();
                break;
            case 'customerwise-pl':
                $this->reportData = $this->getCustomerwiseProfitLoss();
                break;

            // Other Reports
            case 'expense-report':
                $this->reportData = $this->getExpenseReport();
                break;
            case 'commission-report':
                $this->reportData = $this->getCommissionReport();
                break;
            case 'payment-mode-report':
                $this->reportData = $this->getPaymentModeReport();
                break;

            default:
                $this->reportData = [];
        }
    }

    // ==================== SALES REPORTS ====================

    public function getTransactionHistory()
    {
        return Sale::with(['customer', 'items', 'payments', 'user'])
            ->whereBetween('created_at', [$this->reportStartDate, $this->reportEndDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getSalesPaymentReport()
    {
        $sales = Sale::with(['customer', 'payments'])
            ->whereBetween('created_at', [$this->reportStartDate, $this->reportEndDate . ' 23:59:59'])
            ->get();

        $totalSales = $sales->sum('total_amount');
        $totalPaid = $sales->flatMap->payments->sum('amount');
        $totalDue = $sales->sum('due_amount');

        return [
            'sales' => $sales,
            'summary' => [
                'total_sales' => $totalSales,
                'total_paid' => $totalPaid,
                'total_due' => $totalDue,
            ]
        ];
    }

    public function getProductReport()
    {
        return SaleItem::with(['product.brand', 'product.category', 'sale'])
            ->whereHas('sale', function($q) {
                $q->whereBetween('created_at', [$this->reportStartDate, $this->reportEndDate . ' 23:59:59']);
            })
            ->select('product_id', 'product_name', 'product_code',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('AVG(unit_price) as avg_price')
            )
            ->groupBy('product_id', 'product_name', 'product_code')
            ->orderByDesc('total_revenue')
            ->get();
    }

    public function getSalesByStaff()
    {
        return Sale::with('user')
            ->whereBetween('created_at', [$this->reportStartDate, $this->reportEndDate . ' 23:59:59'])
            ->select('user_id',
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('AVG(total_amount) as avg_sale')
            )
            ->groupBy('user_id')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $item->user = User::find($item->user_id);
                return $item;
            });
    }

    public function getSalesByProduct()
    {
        return SaleItem::with(['product.brand'])
            ->whereHas('sale', function($q) {
                $q->whereBetween('created_at', [$this->reportStartDate, $this->reportEndDate . ' 23:59:59']);
            })
            ->select('product_id', 'product_name',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();
    }

    public function getInvoiceAging()
    {
        $sales = Sale::with('customer')
            ->where('due_amount', '>', 0)
            ->get()
            ->map(function ($sale) {
                $daysOverdue = Carbon::parse($sale->created_at)->diffInDays(now());
                $sale->days_overdue = $daysOverdue;
                $sale->aging_bucket = match(true) {
                    $daysOverdue <= 30 => '0-30 days',
                    $daysOverdue <= 60 => '31-60 days',
                    $daysOverdue <= 90 => '61-90 days',
                    default => '90+ days',
                };
                return $sale;
            });

        return [
            'invoices' => $sales,
            'buckets' => [
                '0-30 days' => $sales->where('aging_bucket', '0-30 days')->sum('due_amount'),
                '31-60 days' => $sales->where('aging_bucket', '31-60 days')->sum('due_amount'),
                '61-90 days' => $sales->where('aging_bucket', '61-90 days')->sum('due_amount'),
                '90+ days' => $sales->where('aging_bucket', '90+ days')->sum('due_amount'),
            ]
        ];
    }

    public function getDetailedSalesReport()
    {
        return Sale::with(['customer', 'items.product', 'payments', 'user'])
            ->whereBetween('created_at', [$this->reportStartDate, $this->reportEndDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getSalesReturnReport()
    {
        return ReturnsProduct::with(['sale.customer', 'product'])
            ->whereHas('sale', function($q) {
                $q->whereBetween('created_at', [$this->reportStartDate, $this->reportEndDate . ' 23:59:59']);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // ==================== PURCHASES REPORTS ====================

    public function getPurchasesPaymentReport()
    {
        $purchases = PurchaseOrder::with(['supplier', 'items'])
            ->whereBetween('order_date', [$this->reportStartDate, $this->reportEndDate])
            ->get();

        $totalPurchases = $purchases->sum('total_amount');
        $totalPaid = $totalPurchases - $purchases->sum('due_amount');
        $totalDue = $purchases->sum('due_amount');

        return [
            'purchases' => $purchases,
            'summary' => [
                'total_purchases' => $totalPurchases,
                'total_paid' => $totalPaid,
                'total_due' => $totalDue,
            ]
        ];
    }

    public function getDetailedPurchasesReport()
    {
        return PurchaseOrder::with(['supplier', 'items.product'])
            ->whereBetween('order_date', [$this->reportStartDate, $this->reportEndDate])
            ->orderBy('order_date', 'desc')
            ->get();
    }

    // ==================== INVENTORY VALUATION REPORTS ====================

    public function getProductWiseCOGS()
    {
        return ProductDetail::with(['price', 'stock', 'brand', 'category'])
            ->whereHas('stock', function($q) {
                $q->where('available_stock', '>', 0);
            })
            ->get()
            ->map(function ($product) {
                $costPrice = $product->price->supplier_price ?? 0;
                $availableStock = $product->stock->available_stock ?? 0;
                $inventoryValue = $costPrice * $availableStock;

                return [
                    'product' => $product,
                    'cost_price' => $costPrice,
                    'available_stock' => $availableStock,
                    'inventory_value' => $inventoryValue,
                ];
            });
    }

    public function getYearWiseCOGS()
    {
        $years = range(now()->year - 4, now()->year);
        $yearlyData = [];

        foreach ($years as $year) {
            $startOfYear = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endOfYear = Carbon::createFromDate($year, 12, 31)->endOfDay();

            // Calculate COGS for the year
            $salesItems = SaleItem::whereHas('sale', function($q) use ($startOfYear, $endOfYear) {
                $q->whereBetween('created_at', [$startOfYear, $endOfYear]);
            })->get();

            $totalCOGS = 0;
            foreach ($salesItems as $item) {
                $productPrice = ProductPrice::where('product_id', $item->product_id)->first();
                $costPrice = $productPrice->supplier_price ?? 0;
                $totalCOGS += $costPrice * $item->quantity;
            }

            $totalSales = Sale::whereBetween('created_at', [$startOfYear, $endOfYear])->sum('total_amount');

            $yearlyData[] = [
                'year' => $year,
                'total_sales' => $totalSales,
                'total_cogs' => $totalCOGS,
                'gross_profit' => $totalSales - $totalCOGS,
                'margin_percentage' => $totalSales > 0 ? (($totalSales - $totalCOGS) / $totalSales) * 100 : 0,
            ];
        }

        return collect($yearlyData);
    }

    // ==================== PROFIT & LOSS REPORTS ====================

    public function getProfitLossCOGS()
    {
        $salesItems = SaleItem::whereHas('sale', function($q) {
            $q->whereBetween('created_at', [$this->reportStartDate, $this->reportEndDate . ' 23:59:59']);
        })->get();

        $totalCOGS = 0;
        foreach ($salesItems as $item) {
            $productPrice = ProductPrice::where('product_id', $item->product_id)->first();
            $costPrice = $productPrice->supplier_price ?? 0;
            $totalCOGS += $costPrice * $item->quantity;
        }

        $totalSales = Sale::whereBetween('created_at', [$this->reportStartDate, $this->reportEndDate . ' 23:59:59'])
            ->sum('total_amount');

        $totalExpenses = Expense::whereBetween('date', [$this->reportStartDate, $this->reportEndDate])
            ->sum('amount');

        $returns = ReturnsProduct::whereHas('sale', function($q) {
            $q->whereBetween('created_at', [$this->reportStartDate, $this->reportEndDate . ' 23:59:59']);
        })->sum('total_amount');

        $grossProfit = $totalSales - $totalCOGS - $returns;
        $netProfit = $grossProfit - $totalExpenses;

        return [
            'total_sales' => $totalSales,
            'total_cogs' => $totalCOGS,
            'total_returns' => $returns,
            'gross_profit' => $grossProfit,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'gross_margin' => $totalSales > 0 ? ($grossProfit / $totalSales) * 100 : 0,
            'net_margin' => $totalSales > 0 ? ($netProfit / $totalSales) * 100 : 0,
        ];
    }

    public function getProfitLossOpeningClosing()
    {
        // Get opening stock value (stock at start date)
        $openingStock = ProductStock::sum('available_stock');
        $openingValue = 0;
        $products = ProductDetail::with(['price', 'stock'])->get();
        foreach ($products as $product) {
            $costPrice = $product->price->supplier_price ?? 0;
            $openingValue += $costPrice * ($product->stock->available_stock ?? 0);
        }

        // Calculate purchases during period
        $purchases = PurchaseOrder::whereBetween('order_date', [$this->reportStartDate, $this->reportEndDate])
            ->sum('total_amount');

        // For simplicity, using current stock as closing stock
        $closingValue = $openingValue;

        // Total sales
        $totalSales = Sale::whereBetween('created_at', [$this->reportStartDate, $this->reportEndDate . ' 23:59:59'])
            ->sum('total_amount');

        // Calculate COGS using Opening + Purchases - Closing
        $cogs = $openingValue + $purchases - $closingValue;

        $grossProfit = $totalSales - $cogs;
        $expenses = Expense::whereBetween('date', [$this->reportStartDate, $this->reportEndDate])->sum('amount');
        $netProfit = $grossProfit - $expenses;

        return [
            'opening_stock_value' => $openingValue,
            'purchases' => $purchases,
            'closing_stock_value' => $closingValue,
            'cogs' => $cogs,
            'total_sales' => $totalSales,
            'gross_profit' => $grossProfit,
            'expenses' => $expenses,
            'net_profit' => $netProfit,
        ];
    }

    public function getPeriodProfitLossCOGS()
    {
        $data = [];
        $startDate = Carbon::parse($this->reportStartDate);
        $endDate = Carbon::parse($this->reportEndDate);

        if ($this->periodType === 'daily') {
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                $dayStart = $currentDate->copy()->startOfDay();
                $dayEnd = $currentDate->copy()->endOfDay();

                $data[] = $this->calculatePeriodPL($dayStart, $dayEnd, $currentDate->format('Y-m-d'));
                $currentDate->addDay();
            }
        } elseif ($this->periodType === 'weekly') {
            $currentDate = $startDate->copy()->startOfWeek();
            while ($currentDate->lte($endDate)) {
                $weekStart = $currentDate->copy();
                $weekEnd = $currentDate->copy()->endOfWeek();
                if ($weekEnd->gt($endDate)) $weekEnd = $endDate->copy();

                $data[] = $this->calculatePeriodPL($weekStart, $weekEnd, 'Week ' . $currentDate->weekOfYear);
                $currentDate->addWeek();
            }
        } else { // monthly
            $currentDate = $startDate->copy()->startOfMonth();
            while ($currentDate->lte($endDate)) {
                $monthStart = $currentDate->copy()->startOfMonth();
                $monthEnd = $currentDate->copy()->endOfMonth();
                if ($monthEnd->gt($endDate)) $monthEnd = $endDate->copy();

                $data[] = $this->calculatePeriodPL($monthStart, $monthEnd, $currentDate->format('F Y'));
                $currentDate->addMonth();
            }
        }

        return collect($data);
    }

    private function calculatePeriodPL($start, $end, $label)
    {
        $salesItems = SaleItem::whereHas('sale', function($q) use ($start, $end) {
            $q->whereBetween('created_at', [$start, $end]);
        })->get();

        $totalCOGS = 0;
        foreach ($salesItems as $item) {
            $productPrice = ProductPrice::where('product_id', $item->product_id)->first();
            $costPrice = $productPrice->supplier_price ?? 0;
            $totalCOGS += $costPrice * $item->quantity;
        }

        $totalSales = Sale::whereBetween('created_at', [$start, $end])->sum('total_amount');
        $expenses = Expense::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->sum('amount');

        return [
            'period' => $label,
            'sales' => $totalSales,
            'cogs' => $totalCOGS,
            'gross_profit' => $totalSales - $totalCOGS,
            'expenses' => $expenses,
            'net_profit' => $totalSales - $totalCOGS - $expenses,
        ];
    }

    public function getPeriodProfitLossStock()
    {
        // Similar to period COGS but with stock changes
        return $this->getPeriodProfitLossCOGS();
    }

    public function getProductwiseProfitLoss()
    {
        return SaleItem::with(['product.price', 'product.brand'])
            ->whereHas('sale', function($q) {
                $q->whereBetween('created_at', [$this->reportStartDate, $this->reportEndDate . ' 23:59:59']);
            })
            ->get()
            ->groupBy('product_id')
            ->map(function ($items, $productId) {
                $product = $items->first()->product;
                $totalQuantity = $items->sum('quantity');
                $totalRevenue = $items->sum('total');
                $costPrice = $product->price->supplier_price ?? 0;
                $totalCost = $costPrice * $totalQuantity;
                $profit = $totalRevenue - $totalCost;

                return [
                    'product' => $product,
                    'quantity_sold' => $totalQuantity,
                    'total_revenue' => $totalRevenue,
                    'total_cost' => $totalCost,
                    'profit' => $profit,
                    'margin' => $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0,
                ];
            })->sortByDesc('profit')->values();
    }

    public function getInvoicewiseProfitLoss()
    {
        return Sale::with(['items.product.price', 'customer'])
            ->whereBetween('created_at', [$this->reportStartDate, $this->reportEndDate . ' 23:59:59'])
            ->get()
            ->map(function ($sale) {
                $totalCost = 0;
                foreach ($sale->items as $item) {
                    $costPrice = $item->product->price->supplier_price ?? 0;
                    $totalCost += $costPrice * $item->quantity;
                }
                $profit = $sale->total_amount - $totalCost;

                return [
                    'sale' => $sale,
                    'total_revenue' => $sale->total_amount,
                    'total_cost' => $totalCost,
                    'profit' => $profit,
                    'margin' => $sale->total_amount > 0 ? ($profit / $sale->total_amount) * 100 : 0,
                ];
            })->sortByDesc('profit');
    }

    public function getCustomerwiseProfitLoss()
    {
        return Sale::with(['items.product.price', 'customer'])
            ->whereBetween('created_at', [$this->reportStartDate, $this->reportEndDate . ' 23:59:59'])
            ->get()
            ->groupBy('customer_id')
            ->map(function ($sales, $customerId) {
                $customer = $sales->first()->customer;
                $totalRevenue = $sales->sum('total_amount');
                $totalCost = 0;

                foreach ($sales as $sale) {
                    foreach ($sale->items as $item) {
                        $costPrice = $item->product->price->supplier_price ?? 0;
                        $totalCost += $costPrice * $item->quantity;
                    }
                }

                $profit = $totalRevenue - $totalCost;

                return [
                    'customer' => $customer,
                    'total_transactions' => $sales->count(),
                    'total_revenue' => $totalRevenue,
                    'total_cost' => $totalCost,
                    'profit' => $profit,
                    'margin' => $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0,
                ];
            })->sortByDesc('profit')->values();
    }

    // ==================== OTHER REPORTS ====================

    public function getExpenseReport()
    {
        $expenses = Expense::whereBetween('date', [$this->reportStartDate, $this->reportEndDate])
            ->orderBy('date', 'desc')
            ->get();

        $byCategory = $expenses->groupBy('category')->map(function ($items, $category) {
            return [
                'category' => $category,
                'total' => $items->sum('amount'),
                'count' => $items->count(),
            ];
        });

        return [
            'expenses' => $expenses,
            'by_category' => $byCategory,
            'total' => $expenses->sum('amount'),
        ];
    }

    public function getCommissionReport()
    {
        return StaffBonus::with(['staff', 'product', 'sale'])
            ->whereHas('sale', function($q) {
                $q->whereBetween('created_at', [$this->reportStartDate, $this->reportEndDate . ' 23:59:59']);
            })
            ->get()
            ->groupBy('staff_id')
            ->map(function ($bonuses, $staffId) {
                $staff = $bonuses->first()->staff;
                return [
                    'staff' => $staff,
                    'total_commission' => $bonuses->sum('total_bonus'),
                    'transactions' => $bonuses->count(),
                    'bonuses' => $bonuses,
                ];
            })->sortByDesc('total_commission')->values();
    }

    public function getPaymentModeReport()
    {
        $payments = Payment::whereBetween('payment_date', [$this->reportStartDate, $this->reportEndDate . ' 23:59:59'])
            ->get();

        $byMode = $payments->groupBy('payment_method')->map(function ($items, $method) {
            return [
                'method' => $method ?: 'Unknown',
                'total' => $items->sum('amount'),
                'count' => $items->count(),
            ];
        });

        return [
            'payments' => $payments,
            'by_mode' => $byMode,
            'total' => $payments->sum('amount'),
        ];
    }

    public function downloadReport()
    {
        $filename = $this->selectedReport . '_report_' . now()->format('Y_m_d') . '.xlsx';

        switch ($this->selectedReport) {
            case 'sales':
                $export = new SalesReportExport($this->salesReport, $this->salesReportTotal);
                break;
            case 'salary':
                $export = new SalaryReportExport($this->salaryReport, $this->salaryReportTotal);
                break;
            case 'inventory':
                $export = new InventoryReportExport($this->inventoryReport, $this->inventoryReportTotal);
                break;
            case 'staff':
                $export = new StaffReportExport($this->staffReport, $this->staffReportTotal);
                break;
            case 'payments':
                $export = new PaymentsReportExport($this->paymentsReport, $this->paymentsReportTotal);
                break;
            case 'attendance':
                $export = new AttendanceReportExport($this->attendanceReport, $this->attendanceReportTotal);
                break;
            case 'daily-sales':
                $export = new SalesReportExport($this->dailySalesReport, $this->dailySalesReportTotal);
                break;
            case 'monthly-sales':
                $export = new SalesReportExport($this->monthlySalesReport, $this->monthlySalesReportTotal);
                break;
            default:
                return;
        }

        return Excel::download($export, $filename);
    }

    public function printReport()
    {
        $this->dispatch('print-report', reportType: $this->selectedReport);
    }

    // Report data methods
    public function getSalesReport($start = null, $end = null)
    {
        $query = Sale::with('items', 'customer', 'payments')->orderBy('created_at', 'desc');

        // Filter by user for staff
        if ($this->isStaff()) {
            $query->where('user_id', Auth::id())->where('sale_type', 'staff');
        }

        if ($start) $query->whereDate('created_at', '>=', $start);
        if ($end) $query->whereDate('created_at', '<=', $end);

        return $query->limit(100)->get();
    }

    public function getSalaryReport($start = null, $end = null)
    {
        $query = DB::table('salaries')
            ->join('users', 'salaries.user_id', '=', 'users.id')
            ->select('users.name', 'salaries.net_salary', 'salaries.salary_month', 'salaries.payment_status')
            ->orderBy('salaries.salary_month', 'desc');

        if ($start) $query->whereDate('salaries.salary_month', '>=', $start);
        if ($end) $query->whereDate('salaries.salary_month', '<=', $end);

        return $query->limit(100)->get();
    }

    public function getInventoryReport($start = null, $end = null)
    {
        $query = DB::table('product_details')
            ->join('product_stocks', 'product_details.id', '=', 'product_stocks.product_id')
            ->join('brand_lists', 'product_details.brand_id', '=', 'brand_lists.id')
            ->select(
                'product_details.name',
                'product_details.model',
                'brand_lists.brand_name as brand',
                'product_stocks.total_stock',
                'product_stocks.available_stock',
                'product_stocks.sold_count',
                'product_stocks.damage_stock'
            )
            ->orderBy('product_stocks.available_stock', 'desc');

        return $query->get();
    }

    public function getStaffReport($start = null, $end = null)
    {
        $query = DB::table('users')
            ->where('role', 'staff')
            ->leftJoin('staff_sales', 'users.id', '=', 'staff_sales.staff_id')
            ->select(
                'users.name',
                'users.email',
                DB::raw('COALESCE(SUM(staff_sales.sold_value), 0) as total_sales'),
                DB::raw('COALESCE(SUM(staff_sales.sold_quantity), 0) as total_quantity')
            )
            ->groupBy('users.id', 'users.name', 'users.email');

        return $query->get();
    }

    public function getPaymentsReport($start = null, $end = null)
    {
        // Fetch all customer payments with sale and customer relationships
        $customerPayments = Payment::with(['sale' => function ($query) {
            $query->with('customer');
        }])
            ->orderBy('payment_date', 'desc');

        // Fetch all supplier payments with purchaseOrder and supplier relationships
        $supplierPayments = \App\Models\PurchasePayment::with(['purchaseOrder' => function ($query) {
            $query->with('supplier');
        }])
            ->orderBy('payment_date', 'desc');

        if ($start) {
            $customerPayments->whereDate('payment_date', '>=', $start);
            $supplierPayments->whereDate('payment_date', '>=', $start);
        }
        if ($end) {
            $customerPayments->whereDate('payment_date', '<=', $end);
            $supplierPayments->whereDate('payment_date', '<=', $end);
        }

        return [
            'customer' => $customerPayments->get(),
            'supplier' => $supplierPayments->get()
        ];
    }

    public function getAttendanceReport($start = null, $end = null)
    {
        $query = DB::table('attendances')
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->select(
                'users.name',
                'attendances.date',
                'attendances.check_in',
                'attendances.check_out',
                'attendances.status'
            )
            ->orderBy('attendances.date', 'desc');

        if ($start) $query->whereDate('attendances.date', '>=', $start);
        if ($end) $query->whereDate('attendances.date', '<=', $end);

        return $query->limit(100)->get();
    }

    public function getDailySalesReport($start = null, $end = null)
    {
        // Determine date range - keep only the selected month
        $startDate = $start ? \Carbon\Carbon::parse($start) : \Carbon\Carbon::now()->startOfMonth();
        $endDate = $end ? \Carbon\Carbon::parse($end) : \Carbon\Carbon::now()->endOfMonth();

        // Don't allow future dates - cap at today
        $today = \Carbon\Carbon::now()->endOfDay();
        if ($endDate->gt($today)) {
            $endDate = $today->copy();
        }

        // Store the original month boundaries for filtering
        $monthStartDate = $startDate->copy();
        $monthEndDate = $endDate->copy();

        // Get actual sales data
        $salesQuery = DB::table('sales')
            ->select(
                DB::raw('DATE(created_at) as sale_date'),
                DB::raw('SUM(total_amount) as grand_total'),
                DB::raw('COUNT(*) as total_sales')
            )
            ->whereDate('created_at', '>=', $monthStartDate)
            ->whereDate('created_at', '<=', $monthEndDate);

        // Filter by user for staff
        if ($this->isStaff()) {
            $salesQuery->where('user_id', Auth::id())->where('sale_type', 'staff');
        }

        $salesData = $salesQuery->groupBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        // Get return data from returns_products table
        $returnsQuery = DB::table('returns_products')
            ->join('sales', 'returns_products.sale_id', '=', 'sales.id')
            ->select(
                DB::raw('DATE(sales.created_at) as sale_date'),
                DB::raw('SUM(returns_products.total_amount) as return_total')
            )
            ->whereDate('sales.created_at', '>=', $monthStartDate)
            ->whereDate('sales.created_at', '<=', $monthEndDate);

        // Filter by user for staff
        if ($this->isStaff()) {
            $returnsQuery->where('sales.user_id', Auth::id())->where('sales.sale_type', 'staff');
        }

        $returnsData = $returnsQuery->groupBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        // Generate all days in the month only
        $allDays = [];
        $currentDate = $monthStartDate->copy();

        while ($currentDate->lte($monthEndDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            $dayData = $salesData->get($dateStr);
            $returnData = $returnsData->get($dateStr);

            // Only add days up to today
            if ($currentDate->lte($today)) {
                $allDays[] = (object)[
                    'sale_date' => $dateStr,
                    'day_name' => $currentDate->format('l'),
                    'grand_total' => $dayData ? $dayData->grand_total : 0,
                    'return_total' => $returnData ? $returnData->return_total : 0,
                    'total_sales' => $dayData ? $dayData->total_sales : 0,
                ];
            }

            $currentDate->addDay();
        }

        return collect($allDays);
    }

    public function getMonthlySalesReport($start = null, $end = null)
    {
        $query = DB::table('sales')
            ->select(
                DB::raw('YEAR(sales.created_at) as year'),
                DB::raw('MONTH(sales.created_at) as month'),
                DB::raw('MONTHNAME(sales.created_at) as month_name'),
                DB::raw('SUM(sales.total_amount) as grand_total'),
                DB::raw('SUM(sales.discount_amount) as total_discount'),
                DB::raw('COUNT(DISTINCT sales.id) as total_sales')
            )
            ->groupBy('year', 'month', 'month_name')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'asc');

        // Filter by user for staff
        if ($this->isStaff()) {
            $query->where('sales.user_id', Auth::id())->where('sales.sale_type', 'staff');
        }

        if ($start) $query->whereDate('sales.created_at', '>=', $start);
        if ($end) $query->whereDate('sales.created_at', '<=', $end);

        $monthlyData = $query->get();

        // Get return totals from returns_products table for each month
        foreach ($monthlyData as $monthData) {
            $returnQuery = DB::table('returns_products')
                ->join('sales', 'returns_products.sale_id', '=', 'sales.id')
                ->whereYear('sales.created_at', $monthData->year)
                ->whereMonth('sales.created_at', $monthData->month);

            if ($this->isStaff()) {
                $returnQuery->where('sales.user_id', Auth::id())->where('sales.sale_type', 'staff');
            }

            $returnTotal = $returnQuery->sum('returns_products.total_amount');

            // Calculate payment adjustment (price difference between product price and sale price)
            $adjustmentQuery = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('product_prices', 'sale_items.product_id', '=', 'product_prices.product_id')
                ->whereYear('sales.created_at', $monthData->year)
                ->whereMonth('sales.created_at', $monthData->month);

            if ($this->isStaff()) {
                $adjustmentQuery->where('sales.user_id', Auth::id())->where('sales.sale_type', 'staff');
            }

            $paymentAdjustment = $adjustmentQuery->selectRaw('SUM((product_prices.selling_price - sale_items.unit_price) * sale_items.quantity) as adjustment')
                ->value('adjustment');

            $monthData->return_total = $returnTotal ?? 0;
            $monthData->payment_adjustment = abs($paymentAdjustment ?? 0);
        }

        return $monthlyData;
    }

    public function getCurrentReportData()
    {
        // For daily-purchases, generate paginated data on the fly
        if ($this->selectedReport === 'daily-purchases') {
            return $this->getDailyPurchasesReport($this->reportStartDate, $this->reportEndDate);
        }

        // For inventory-stock, generate paginated data on the fly
        if ($this->selectedReport === 'inventory-stock') {
            return $this->getInventoryStockReport();
        }

        return match ($this->selectedReport) {
            'sales' => $this->salesReport,
            'salary' => $this->salaryReport,
            'inventory' => $this->inventoryReport,
            'staff' => $this->staffReport,
            'payments' => $this->paymentsReport,
            'attendance' => $this->attendanceReport,
            'daily-sales' => $this->dailySalesReport,
            'monthly-sales' => $this->monthlySalesReport,
            'outstanding-accounts' => $this->outstandingAccountsReport,
            default => [],
        };
    }

    public function getCurrentReportTotal()
    {
        return match ($this->selectedReport) {
            'sales' => $this->salesReportTotal,
            'salary' => $this->salaryReportTotal,
            'inventory' => $this->inventoryReportTotal,
            'staff' => $this->staffReportTotal,
            'payments' => $this->paymentsReportTotal,
            'attendance' => $this->attendanceReportTotal,
            'daily-sales' => $this->dailySalesReportTotal,
            'monthly-sales' => $this->monthlySalesReportTotal,
            'daily-purchases' => $this->dailyPurchasesReportTotal,
            'inventory-stock' => 0,
            'outstanding-accounts' => 0,
            default => 0,
        };
    }

    public function getReportTitle()
    {
        return match ($this->selectedReport) {
            'sales' => 'Sales Report',
            'salary' => 'Salary Report',
            'inventory' => 'Inventory Report',
            'staff' => 'Staff Performance Report',
            'payments' => 'Payments Report',
            'attendance' => 'Attendance Report',
            'daily-sales' => 'Daily Sales Report',
            'monthly-sales' => 'Monthly Sales Report',
            default => 'Report',
        };
    }

    public function render()
    {
        return view('livewire.admin.reports', [
            'reportCategories' => $this->reportCategories,
            'reportData' => $this->reportData,
            'activeCategory' => $this->activeCategory,
            'selectedReport' => $this->selectedReport,
            'periodType' => $this->periodType,
        ])->layout($this->layout);
    }

    public function clearFilters()
    {
        $this->reportStartDate = now()->startOfMonth()->format('Y-m-d');
        $this->reportEndDate = now()->endOfMonth()->format('Y-m-d');
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
        $this->periodType = 'monthly';
        $this->generateReport();
    }
}
