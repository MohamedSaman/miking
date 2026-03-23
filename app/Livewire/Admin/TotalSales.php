<?php

namespace App\Livewire\Admin;

use App\Models\Cheque;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\Deposit;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Total Sales Summary')]
class TotalSales extends Component
{
    use WithDynamicLayout, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $dateFrom = '';
    public $dateTo   = '';
    public $activeTab = 'overview'; // overview | cash | credit | cheque | collection | total-income

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function updatedDateFrom() { $this->resetPage(); }
    public function updatedDateTo()   { $this->resetPage(); }
    public function updatedActiveTab(){ $this->resetPage(); }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function filterThisMonth()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function filterToday()
    {
        $this->dateFrom = now()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function filterAllTime()
    {
        $this->dateFrom = '';
        $this->dateTo   = '';
        $this->resetPage();
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------
    protected function isStaff(): bool
    {
        return auth()->user()?->role === 'staff';
    }

    protected function applyStaffFilter($query, string $userIdColumn = 'user_id')
    {
        if ($this->isStaff()) {
            $query->where($userIdColumn, auth()->id());
        }
        return $query;
    }

    protected function applyStaffFilterViaSale($query)
    {
        if ($this->isStaff()) {
            $query->whereHas('sale', fn($q) => $q->where('user_id', auth()->id()));
        }
        return $query;
    }

    // ---------------------------------------------------------------
    // Summary numbers
    // ---------------------------------------------------------------
    protected function dateRange($query)
    {
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }
        return $query;
    }

    protected function paymentDateRange($query)
    {
        if ($this->dateFrom) {
            $query->whereDate('payment_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('payment_date', '<=', $this->dateTo);
        }
        return $query;
    }

    public function getSummaryProperty()
    {
        // Total sales count & amount from Sales table
        $salesQuery = Sale::query();
        $this->applyStaffFilter($salesQuery);
        $this->dateRange($salesQuery);

        $totalSalesCount  = (clone $salesQuery)->count();
        $totalSalesAmount = (clone $salesQuery)->sum('total_amount');

        // Cash sales (sales where payment_method = cash)
        $cashSalesAmount = (clone $salesQuery)
            ->whereHas('payments', fn($q) => $q->where('payment_method', 'cash'))
            ->orWhereHas('payments', fn($q) => $q->where('payment_method', 'cash'))
            ->sum('total_amount');

        // Easier: use payments table for method breakdown
        $payQ = Payment::query()->whereNotNull('sale_id');
        $this->applyStaffFilterViaSale($payQ);
        $this->paymentDateRange($payQ);

        $cashCollected   = (clone $payQ)->where('payment_method', 'cash')->sum('amount');
        $chequeCollected = (clone $payQ)->where('payment_method', 'cheque')->sum('amount');
        $creditCollected = (clone $payQ)->where('payment_method', 'credit')->sum('amount');
        $bankCollected   = (clone $payQ)->where('payment_method', 'bank_transfer')->sum('amount');
        $totalCollected  = (clone $payQ)->sum('amount');

        // Sales by type counting invoices
        $cashSaleCount   = Sale::whereHas('payments', fn($q) => $q->where('payment_method', 'cash'));
        $this->applyStaffFilter($cashSaleCount);
        $this->dateRange($cashSaleCount);
        $cashSaleCount   = $cashSaleCount->count();

        $chequeSaleCount = Sale::whereHas('payments', fn($q) => $q->where('payment_method', 'cheque'));
        $this->applyStaffFilter($chequeSaleCount);
        $this->dateRange($chequeSaleCount);
        $chequeSaleCount = $chequeSaleCount->count();

        $creditSaleCount = Sale::where('payment_method', 'credit');
        $this->applyStaffFilter($creditSaleCount);
        $this->dateRange($creditSaleCount);
        $creditSaleCount = $creditSaleCount->count();

        // Total due — only from credit sales
        $totalDue = (clone $salesQuery)->where('payment_method', 'credit')->sum('due_amount');

        // ---- Actual company income (no pending cheques, no credit) ----
        // Cash approved payments
        $incomeCash = Payment::whereNotNull('sale_id')
            ->where('payment_method', 'cash')
            ->whereIn('status', ['approved', 'paid']);
        $this->applyStaffFilterViaSale($incomeCash);
        $this->paymentDateRange($incomeCash);
        $incomeCash = $incomeCash->sum('amount');

        // Completed cheques only
        $incomeCheque = Payment::whereNotNull('sale_id')
            ->where('payment_method', 'cheque')
            ->whereHas('cheques', fn($q) => $q->where('status', 'complete'));
        $this->applyStaffFilterViaSale($incomeCheque);
        $this->paymentDateRange($incomeCheque);
        $incomeCheque = $incomeCheque->sum('amount');

        // Bank transfers approved
        $incomeBank = Payment::whereNotNull('sale_id')
            ->where('payment_method', 'bank_transfer')
            ->whereIn('status', ['approved', 'paid']);
        $this->applyStaffFilterViaSale($incomeBank);
        $this->paymentDateRange($incomeBank);
        $incomeBank = $incomeBank->sum('amount');

        // Customer Receipt (Standalone Payments)
        $incomeReceipts = Payment::whereNull('sale_id')
            ->whereIn('status', ['approved', 'paid']);
        if ($this->isStaff()) {
            $incomeReceipts->where('created_by', auth()->id());
        }
        $this->paymentDateRange($incomeReceipts);
        $incomeReceipts = $incomeReceipts->sum('amount');

        // Other Income / Deposits
        $incomeOther = Deposit::query();
        if ($this->dateFrom) {
            $incomeOther->whereDate('date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $incomeOther->whereDate('date', '<=', $this->dateTo);
        }
        $incomeOther = $incomeOther->sum('amount');

        $totalIncome = $incomeCash + $incomeCheque + $incomeBank + $incomeReceipts + $incomeOther;

        return compact(
            'totalSalesCount', 'totalSalesAmount', 'totalDue',
            'cashCollected', 'chequeCollected', 'creditCollected', 'bankCollected', 'totalCollected',
            'cashSaleCount', 'chequeSaleCount', 'creditSaleCount',
            'incomeCash', 'incomeCheque', 'incomeBank', 'incomeReceipts', 'incomeOther', 'totalIncome'
        );
    }

    // ---------------------------------------------------------------
    // Paginated detail tables
    // ---------------------------------------------------------------
    public function getCashSalesProperty()
    {
        $q = Payment::with(['sale.customer'])
            ->whereNotNull('sale_id')
            ->where('payment_method', 'cash')
            ->when($this->dateFrom, fn($q) => $q->whereDate('payment_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('payment_date', '<=', $this->dateTo));
        $this->applyStaffFilterViaSale($q);
        return $q->orderByDesc('payment_date')->paginate(15);
    }

    public function getCreditSalesProperty()
    {
        $q = Sale::with('customer')
            ->where('payment_method', 'credit')
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at', '<=', $this->dateTo));
        $this->applyStaffFilter($q);
        return $q->orderByDesc('created_at')->paginate(15);
    }

    public function getChequeSalesProperty()
    {
        $q = Payment::with(['sale.customer', 'cheques'])
            ->whereNotNull('sale_id')
            ->where('payment_method', 'cheque')
            ->when($this->dateFrom, fn($q) => $q->whereDate('payment_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('payment_date', '<=', $this->dateTo));
        $this->applyStaffFilterViaSale($q);
        return $q->orderByDesc('payment_date')->paginate(15);
    }

    public function getBankSalesProperty()
    {
        $q = Payment::with(['sale.customer'])
            ->whereNotNull('sale_id')
            ->where('payment_method', 'bank_transfer')
            ->when($this->dateFrom, fn($q) => $q->whereDate('payment_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('payment_date', '<=', $this->dateTo));
        $this->applyStaffFilterViaSale($q);
        return $q->orderByDesc('payment_date')->paginate(15);
    }

    public function getTotalIncomeProperty()
    {
        // Actual realised income: approved cash + cleared cheques + approved bank transfers
        // Excludes: pending cheques, credit/due, rejected
        $cashPayments = Payment::with(['sale.customer'])
            ->whereNotNull('sale_id')
            ->where('payment_method', 'cash')
            ->whereIn('status', ['approved', 'paid'])
            ->when($this->isStaff(), fn($q) => $q->whereHas('sale', fn($s) => $s->where('user_id', auth()->id())))
            ->when($this->dateFrom, fn($q) => $q->whereDate('payment_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('payment_date', '<=', $this->dateTo))
            ->get()
            ->map(fn($p) => (object)[
                'id'           => $p->id,
                'type'         => 'Cash',
                'type_color'   => 'success',
                'invoice'      => $p->sale->invoice_number ?? '-',
                'customer'     => $p->sale->customer->name ?? 'Walking Customer',
                'date'         => $p->payment_date,
                'amount'       => $p->amount,
                'reference'    => '-',
            ]);

        $chequePayments = Payment::with(['sale.customer', 'cheques' => fn($q) => $q->where('status', 'complete')])
            ->whereNotNull('sale_id')
            ->where('payment_method', 'cheque')
            ->whereHas('cheques', fn($q) => $q->where('status', 'complete'))
            ->when($this->isStaff(), fn($q) => $q->whereHas('sale', fn($s) => $s->where('user_id', auth()->id())))
            ->when($this->dateFrom, fn($q) => $q->whereDate('payment_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('payment_date', '<=', $this->dateTo))
            ->get()
            ->map(fn($p) => (object)[
                'id'           => $p->id,
                'type'         => 'Cheque',
                'type_color'   => 'info',
                'invoice'      => $p->sale->invoice_number ?? '-',
                'customer'     => $p->sale->customer->name ?? 'Walking Customer',
                'date'         => $p->payment_date,
                'amount'       => $p->amount,
                'reference'    => $p->cheques->first()->cheque_number ?? '-',
            ]);

        $bankPayments = Payment::with(['sale.customer'])
            ->whereNotNull('sale_id')
            ->where('payment_method', 'bank_transfer')
            ->whereIn('status', ['approved', 'paid'])
            ->when($this->isStaff(), fn($q) => $q->whereHas('sale', fn($s) => $s->where('user_id', auth()->id())))
            ->when($this->dateFrom, fn($q) => $q->whereDate('payment_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('payment_date', '<=', $this->dateTo))
            ->get()
            ->map(fn($p) => (object)[
                'id'           => $p->id,
                'type'         => 'Bank Transfer',
                'type_color'   => 'primary',
                'invoice'      => $p->sale->invoice_number ?? '-',
                'customer'     => $p->sale->customer->name ?? 'Walking Customer',
                'date'         => $p->payment_date,
                'amount'       => $p->amount,
                'reference'    => '-',
            ]);

        $standalonePayments = Payment::with(['customer', 'allocations.sale'])
            ->whereNull('sale_id')
            ->whereIn('status', ['approved', 'paid'])
            ->when($this->isStaff(), fn($q) => $q->where('created_by', auth()->id()))
            ->when($this->dateFrom, fn($q) => $q->whereDate('payment_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('payment_date', '<=', $this->dateTo))
            ->get()
            ->map(fn($p) => (object)[
                'id'           => $p->id,
                'type'         => 'Due Settle',
                'type_color'   => 'dark',
                'invoice'      => $p->allocations->map(fn($a) => $a->sale?->invoice_number)->filter()->implode(', ') ?: 'Receipt',
                'customer'     => $p->customer->name ?? 'Walking Customer',
                'date'         => $p->payment_date,
                'amount'       => $p->amount,
                'reference'    => $p->payment_method . ($p->payment_reference ? ' (' . $p->payment_reference . ')' : ''),
            ]);

        $otherIncomes = Deposit::query()
            ->when($this->dateFrom, fn($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('date', '<=', $this->dateTo))
            ->get()
            ->map(fn($d) => (object)[
                'id'           => $d->id,
                'type'         => 'Other Income',
                'type_color'   => 'warning',
                'invoice'      => '-',
                'customer'     => 'General',
                'date'         => $d->date,
                'amount'       => $d->amount,
                'reference'    => $d->description ?? '-',
            ]);

        return $cashPayments->merge($chequePayments)->merge($bankPayments)
            ->merge($standalonePayments)->merge($otherIncomes)
            ->sortByDesc('date')
            ->values();
    }

    public function render()
    {
        return view('livewire.admin.total-sales', [
            'summary'        => $this->summary,
            'cashSales'      => $this->activeTab === 'cash'         ? $this->cashSales       : null,
            'creditSales'    => $this->activeTab === 'credit'       ? $this->creditSales     : null,
            'chequeSales'    => $this->activeTab === 'cheque'       ? $this->chequeSales     : null,
            'bankSales'      => $this->activeTab === 'bank'         ? $this->bankSales       : null,
            'totalIncome'    => $this->activeTab === 'total-income' ? $this->totalIncome     : null,
        ])->layout($this->layout);
    }
}
