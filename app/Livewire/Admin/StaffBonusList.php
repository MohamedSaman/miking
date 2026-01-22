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
        $query = StaffBonus::with(['staff', 'product', 'sale'])
            ->when($this->search, function ($q) {
                $q->whereHas('staff', function ($sq) {
                    $sq->where('name', 'like', '%' . $this->search . '%');
                })->orWhereHas('product', function ($pq) {
                    $pq->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('code', 'like', '%' . $this->search . '%');
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
            ->orderBy('created_at', 'desc');

        return $query->paginate($this->perPage);
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
