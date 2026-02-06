<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StaffBonus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Title("Staff Bonus Management")]
#[Layout("components.layouts.admin")]
class StaffBonusList extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $staffFilter = '';
    public $paymentMethodFilter = '';
    public $dateFrom = '';
    public $dateTo = '';

    // Summary data
    public $totalCommission = 0;
    public $cashCommission = 0;
    public $creditCommission = 0;
    
    // Modal properties
    public $showBonusDetailModal = false;
    public $selectedSaleCommissions = [];
    public $selectedSaleInfo = null;
    
    // Sale Invoice Modal
    public $showSaleInvoiceModal = false;
    public $selectedSaleForInvoice = null;

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStaffFilter()
    {
        $this->resetPage();
    }

    public function updatingPaymentMethodFilter()
    {
        $this->resetPage();
    }

    public function getStaffListProperty()
    {
        return User::where('role', 'staff')->orderBy('name')->get();
    }

    public function getBonusesProperty()
    {
        $query = StaffBonus::select(
                'sale_id',
                'staff_id',
                'payment_method',
                'created_at',
                DB::raw('SUM(total_bonus) as total_sale_commission'),
                DB::raw('COUNT(*) as items_count')
            )
            ->with(['staff', 'sale'])
            ->whereHas('staff', function($q) {
                $q->where('role', 'staff');
            })
            ->when($this->search, function ($q) {
                $q->whereHas('staff', function ($sq) {
                    $sq->where('name', 'like', '%' . $this->search . '%');
                })->orWhereHas('sale', function ($sq) {
                    $sq->where('invoice_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->staffFilter, function ($q) {
                $q->where('staff_id', $this->staffFilter);
            })
            ->when($this->paymentMethodFilter, function ($q) {
                $q->where('payment_method', $this->paymentMethodFilter);
            })
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('created_at', '<=', $this->dateTo);
            })
            ->groupBy('sale_id', 'staff_id', 'payment_method', 'created_at')
            ->orderBy('created_at', 'desc');

        return $query->paginate($this->perPage);
    }

    public function viewSaleCommission($saleId)
    {
        $this->selectedSaleInfo = \App\Models\Sale::with(['customer', 'user'])->find($saleId);
        $this->selectedSaleCommissions = StaffBonus::with(['product'])
            ->where('sale_id', $saleId)
            ->get();
        
        $this->showBonusDetailModal = true;
    }

    public function closeCommissionDetailModal()
    {
        $this->showBonusDetailModal = false;
        $this->selectedSaleBonuses = [];
        $this->selectedSaleInfo = null;
    }

    public function viewSaleInvoice($saleId)
    {
        $this->selectedSaleForInvoice = \App\Models\Sale::with([
            'customer', 
            'user', 
            'items.product'
        ])->find($saleId);
        
        $this->showSaleInvoiceModal = true;
    }

    public function closeSaleInvoiceModal()
    {
        $this->showSaleInvoiceModal = false;
        $this->selectedSaleForInvoice = null;
    }

    public function getStaffBonusSummaryProperty()
    {
        $query = StaffBonus::query()
            ->when($this->staffFilter, function ($q) {
                $q->where('staff_id', $this->staffFilter);
            })
            ->when($this->paymentMethodFilter, function ($q) {
                $q->where('payment_method', $this->paymentMethodFilter);
            })
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('created_at', '<=', $this->dateTo);
            });

        // Get summary by staff - cash-based includes: cash, bank_transfer, cheque
        return StaffBonus::select(
                'staff_id',
                DB::raw('SUM(total_bonus) as total_commission'),
                DB::raw('SUM(CASE WHEN payment_method IN ("cash", "bank_transfer", "cheque") THEN total_bonus ELSE 0 END) as cash_commission'),
                DB::raw('SUM(CASE WHEN payment_method = "credit" THEN total_bonus ELSE 0 END) as credit_commission'),
                DB::raw('COUNT(*) as total_sales')
            )
            ->with('staff')
            ->whereHas('staff', function($q) {
                $q->where('role', 'staff');
            })
            ->when($this->staffFilter, function ($q) {
                $q->where('staff_id', $this->staffFilter);
            })
            ->when($this->paymentMethodFilter, function ($q) {
                $q->where('payment_method', $this->paymentMethodFilter);
            })
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('created_at', '<=', $this->dateTo);
            })
            ->groupBy('staff_id')
            ->get();
    }

    public function getTotalStatsProperty()
    {
        return StaffBonus::query()
            ->whereHas('staff', function($q) {
                $q->where('role', 'staff');
            })
            ->when($this->staffFilter, function ($q) {
                $q->where('staff_id', $this->staffFilter);
            })
            ->when($this->paymentMethodFilter, function ($q) {
                $q->where('payment_method', $this->paymentMethodFilter);
            })
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('created_at', '<=', $this->dateTo);
            })
            ->select(
                DB::raw('SUM(total_bonus) as total'),
                DB::raw('SUM(CASE WHEN payment_method IN ("cash", "bank_transfer", "cheque") THEN total_bonus ELSE 0 END) as cash_commission'),
                DB::raw('SUM(CASE WHEN payment_method = "credit" THEN total_bonus ELSE 0 END) as credit_commission')
            )
            ->first();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->staffFilter = '';
        $this->paymentMethodFilter = '';
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.staff-bonus-list', [
            'bonuses' => $this->bonuses,
            'staffList' => $this->staffList,
            'staffBonusSummary' => $this->staffBonusSummary,
            'totalStats' => $this->totalStats,
        ]);
    }
}
