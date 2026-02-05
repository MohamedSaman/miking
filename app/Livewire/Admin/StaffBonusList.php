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
    public $saleTypeFilter = '';
    public $paymentMethodFilter = '';
    public $dateFrom = '';
    public $dateTo = '';

    // Summary data
    public $totalBonus = 0;
    public $wholesaleCashBonus = 0;
    public $wholesaleCreditBonus = 0;
    public $retailCashBonus = 0;
    public $retailCreditBonus = 0;
    
    // Modal properties
    public $showBonusDetailModal = false;
    public $selectedSaleBonuses = [];
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

    public function updatingSaleTypeFilter()
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
                'sale_type',
                'payment_method',
                'created_at',
                DB::raw('SUM(total_bonus) as total_sale_bonus'),
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
            ->when($this->saleTypeFilter, function ($q) {
                $q->where('sale_type', $this->saleTypeFilter);
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
            ->groupBy('sale_id', 'staff_id', 'sale_type', 'payment_method', 'created_at')
            ->orderBy('created_at', 'desc');

        return $query->paginate($this->perPage);
    }

    public function viewSaleBonus($saleId)
    {
        $this->selectedSaleInfo = \App\Models\Sale::with(['customer', 'user'])->find($saleId);
        $this->selectedSaleBonuses = StaffBonus::with(['product'])
            ->where('sale_id', $saleId)
            ->get();
        
        $this->showBonusDetailModal = true;
    }

    public function closeBonusDetailModal()
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
            ->when($this->saleTypeFilter, function ($q) {
                $q->where('sale_type', $this->saleTypeFilter);
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

        // Get summary by staff
        return StaffBonus::select(
                'staff_id',
                DB::raw('SUM(total_bonus) as total_bonus'),
                DB::raw('SUM(CASE WHEN sale_type = "wholesale" AND payment_method = "cash" THEN total_bonus ELSE 0 END) as wholesale_cash'),
                DB::raw('SUM(CASE WHEN sale_type = "wholesale" AND payment_method = "credit" THEN total_bonus ELSE 0 END) as wholesale_credit'),
                DB::raw('SUM(CASE WHEN sale_type = "retail" AND payment_method = "cash" THEN total_bonus ELSE 0 END) as retail_cash'),
                DB::raw('SUM(CASE WHEN sale_type = "retail" AND payment_method = "credit" THEN total_bonus ELSE 0 END) as retail_credit'),
                DB::raw('COUNT(*) as total_sales')
            )
            ->with('staff')
            ->whereHas('staff', function($q) {
                $q->where('role', 'staff');
            })
            ->when($this->staffFilter, function ($q) {
                $q->where('staff_id', $this->staffFilter);
            })
            ->when($this->saleTypeFilter, function ($q) {
                $q->where('sale_type', $this->saleTypeFilter);
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
            ->when($this->saleTypeFilter, function ($q) {
                $q->where('sale_type', $this->saleTypeFilter);
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
                DB::raw('SUM(CASE WHEN sale_type = "wholesale" AND payment_method = "cash" THEN total_bonus ELSE 0 END) as wholesale_cash'),
                DB::raw('SUM(CASE WHEN sale_type = "wholesale" AND payment_method = "credit" THEN total_bonus ELSE 0 END) as wholesale_credit'),
                DB::raw('SUM(CASE WHEN sale_type = "retail" AND payment_method = "cash" THEN total_bonus ELSE 0 END) as retail_cash'),
                DB::raw('SUM(CASE WHEN sale_type = "retail" AND payment_method = "credit" THEN total_bonus ELSE 0 END) as retail_credit')
            )
            ->first();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->staffFilter = '';
        $this->saleTypeFilter = '';
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
