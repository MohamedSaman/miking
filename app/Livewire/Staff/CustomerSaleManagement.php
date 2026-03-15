<?php

namespace App\Livewire\Staff;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

#[Layout('components.layouts.staff')]
#[Title('Customer Sale Management')]
class CustomerSaleManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = '';
    public $filterCustomerType = '';
    public $selectedSaleId = null;
    public $selectedSale = null;
    public $saleItems = [];

    public function mount()
    {
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterCustomerType' => ['except' => '']
    ];

    public function render()
    {
        $userId = Auth::id();

        // Convert this query to use Eloquent ORM
        $customerSales = Customer::select('customers.id', 'customers.name', 'customers.email', 'customers.phone', 'customers.type')
            ->withCount(['sales' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->withSum(['sales as total_sales' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }], 'total_amount')
            ->withSum(['sales as total_due' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }], 'due_amount')
            ->whereHas('sales', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->get();

        // Get all sales by this staff member
        $salesQuery = Sale::where('user_id', $userId)
            ->with('customer');

        if ($this->search) {
            $salesQuery->where(function ($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%')
                            ->orWhere('phone', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->filterStatus) {
            $salesQuery->where('payment_status', $this->filterStatus);
        }

        if ($this->filterCustomerType) {
            $salesQuery->where('customer_type', $this->filterCustomerType);
        }

        $sales = $salesQuery->orderBy('created_at', 'desc')
            ->paginate(10);

        // Calculate total sales and due amount
        $totals = Sale::where('user_id', $userId)
            ->select(
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('SUM(due_amount) as total_due'),
                DB::raw('COUNT(DISTINCT customer_id) as customer_count')
            )
            ->first();

        return view('livewire.staff.customer-sale-management', [
            'sales' => $sales,
            'customerSales' => $customerSales,
            'totals' => $totals
        ]);
    }

    public function viewSaleDetails($saleId)
    {
        $this->selectedSaleId = $saleId;
        $this->selectedSale = Sale::with([
            'customer',
            'staffReturns' => function ($q) {
                $q->where('status', 'approved')->with('product');
            }
        ])->find($saleId);

        $this->saleItems = SaleItem::with('product')
            ->where('sale_id', $saleId)
            ->get();

        $this->js("$('#saleDetailsModal').modal('show');");
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterStatus = '';
        $this->filterCustomerType = '';
    }

    public function downloadInvoice($saleId)
    {
        // Check web.php for the correct route name - should be 'receipts.download' not 'receipt.download'
        return $this->redirect(route('receipts.download', ['id' => $saleId]), navigate: false);
    }

    public function downloadReceipt()
    {
        if (!$this->selectedSale) {
            return;
        }

        return $this->redirect(route('receipts.download', ['id' => $this->selectedSale->id]), navigate: false);
    }

    public function printInvoice()
    {
        if (!$this->selectedSaleId) {
            return;
        }

        $printUrl = route('staff.print.sale', $this->selectedSaleId);
        $this->js("window.open('$printUrl', '_blank', 'width=800,height=600');");
    }

    public function sendWhatsApp($saleId)
    {
        $sale = Sale::with('customer')->findOrFail($saleId);
        
        if (!$sale->customer || !$sale->customer->phone) {
            $this->js("Swal.fire('error', 'Customer phone number not found.', 'error')");
            return;
        }

        $phone = $sale->customer->phone;
        // Basic cleanup of phone number if needed
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Ensure international format (assuming Sri Lanka +94 if it starts with 0)
        if (strlen($phone) === 10 && $phone[0] === '0') {
            $phone = '94' . substr($phone, 1);
        } elseif (strlen($phone) === 9 && $phone[0] !== '0') {
            $phone = '94' . $phone;
        }

        // Construct the message
        $invoiceLink = route('invoice.external_view', $sale->id);
        
        // Calculate customer total due accurately
        $salesDue = Sale::where('customer_id', $sale->customer_id)->sum('due_amount');
        $totalDue = $salesDue + ($sale->customer->opening_balance ?? 0) - ($sale->customer->overpaid_amount ?? 0);
        
        $message = "Hello " . ($sale->customer->name ?? 'Customer') . ",\n\n";
        $message .= "Your invoice (" . $sale->invoice_number . ") has been created.\n";
        $message .= "Total Amount: Rs." . number_format($sale->total_amount, 2) . "\n";
        $message .= "Paid Amount: Rs." . number_format($sale->total_amount - $sale->due_amount, 2) . "\n";
        $message .= "Current Sale Due: Rs." . number_format($sale->due_amount, 2) . "\n";
        $message .= "Total Outstanding Balance: Rs." . number_format($totalDue, 2) . "\n\n";
        $message .= "You can view and download your invoice online here: " . $invoiceLink . "\n\n";
        $message .= "Thank you for your business!";

        $whatsappUrl = "https://wa.me/" . $phone . "?text=" . urlencode($message);
        
        $this->js("window.open('$whatsappUrl', '_blank');");
    }
}
