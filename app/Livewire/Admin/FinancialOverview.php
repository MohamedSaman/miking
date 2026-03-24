<?php

namespace App\Livewire\Admin;

use App\Models\Cheque;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\PurchasePayment;
use App\Models\ReturnsProduct;
use App\Models\CashInHand;
use App\Models\Deposit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Financial Overview')]
class FinancialOverview extends Component
{
    use WithDynamicLayout, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $dateFrom = '';
    public $dateTo   = '';
    public $activeTab = 'summary'; // summary | income | outcome | pending | ledger

    // Summary data
    public $summaries = [
        'available_cash' => 0,
        'cleared_cheques' => 0,
        'bank_balance' => 0,
        'total_liquid' => 0,
        'total_income' => 0,
        'total_outcome' => 0,
        'pending_income' => 0,
        'pending_outcome' => 0,
    ];

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
        $this->calculateSummaries();
    }

    public function updatedDateFrom() { $this->calculateSummaries(); $this->resetPage(); }
    public function updatedDateTo()   { $this->calculateSummaries(); $this->resetPage(); }
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
        $this->calculateSummaries();
        $this->resetPage();
    }

    public function filterToday()
    {
        $this->dateFrom = now()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
        $this->calculateSummaries();
        $this->resetPage();
    }

    public function filterAllTime()
    {
        $this->dateFrom = '';
        $this->dateTo   = '';
        $this->calculateSummaries();
        $this->resetPage();
    }

    public function calculateSummaries()
    {
        // Available Money (Based on Transactions)
        $cashInQuery = Payment::where('payment_method', 'cash')->whereIn('status', ['approved', 'paid']);
        if ($this->dateFrom) $cashInQuery->whereDate('payment_date', '>=', $this->dateFrom);
        if ($this->dateTo) $cashInQuery->whereDate('payment_date', '<=', $this->dateTo);
        $cashIn = (float) $cashInQuery->sum('amount');

        $cashOutQuery = PurchasePayment::where('payment_method', 'cash');
        if ($this->dateFrom) $cashOutQuery->whereDate('payment_date', '>=', $this->dateFrom);
        if ($this->dateTo) $cashOutQuery->whereDate('payment_date', '<=', $this->dateTo);
        $cashOut = (float) $cashOutQuery->sum('amount');

        $expenseQuery = Expense::query();
        if ($this->dateFrom) $expenseQuery->whereDate('date', '>=', $this->dateFrom);
        if ($this->dateTo) $expenseQuery->whereDate('date', '<=', $this->dateTo);
        $cashOut += (float) $expenseQuery->sum('amount');

        $this->summaries['available_cash'] = $cashIn - $cashOut;
        
        $chequeQuery = Cheque::where('status', 'complete');
        if ($this->dateFrom) $chequeQuery->whereDate('created_at', '>=', $this->dateFrom);
        if ($this->dateTo) $chequeQuery->whereDate('created_at', '<=', $this->dateTo);
        $this->summaries['cleared_cheques'] = (float) $chequeQuery->sum('cheque_amount');

        $bankQuery = Payment::where('payment_method', 'bank_transfer')->whereIn('status', ['approved', 'paid']);
        if ($this->dateFrom) $bankQuery->whereDate('payment_date', '>=', $this->dateFrom);
        if ($this->dateTo) $bankQuery->whereDate('payment_date', '<=', $this->dateTo);
        $this->summaries['bank_balance'] = (float) $bankQuery->sum('amount');

        $this->summaries['total_liquid'] = $this->summaries['available_cash'] + $this->summaries['cleared_cheques'] + $this->summaries['bank_balance'];

        // Income (Only Cash, Bank, and Cleared Cheques)
        $incomePaymentQuery = Payment::with('cheques')->whereIn('status', ['approved', 'paid']);
        if ($this->dateFrom) $incomePaymentQuery->whereDate('payment_date', '>=', $this->dateFrom);
        if ($this->dateTo) $incomePaymentQuery->whereDate('payment_date', '<=', $this->dateTo);
        
        $totalIncome = 0;
        foreach($incomePaymentQuery->get() as $p) {
            if($p->payment_method === 'cheque') {
                if($p->cheques->where('status', 'complete')->count() > 0) {
                    $totalIncome += (float)$p->amount;
                }
            } else {
                $totalIncome += (float)$p->amount;
            }
        }

        $depositQuery = Deposit::query();
        if ($this->dateFrom) $depositQuery->whereDate('date', '>=', $this->dateFrom);
        if ($this->dateTo) $depositQuery->whereDate('date', '<=', $this->dateTo);
        $totalIncome += (float) $depositQuery->sum('amount');

        $this->summaries['total_income'] = $totalIncome;

        // Outcome (Expenses + Supplier Payments + Refunds)
        $expenseQuery = Expense::query();
        if ($this->dateFrom) $expenseQuery->whereDate('date', '>=', $this->dateFrom);
        if ($this->dateTo) $expenseQuery->whereDate('date', '<=', $this->dateTo);
        $totalOutcome = (float) $expenseQuery->sum('amount');

        $supplierPayQuery = PurchasePayment::query();
        if ($this->dateFrom) $supplierPayQuery->whereDate('payment_date', '>=', $this->dateFrom);
        if ($this->dateTo) $supplierPayQuery->whereDate('payment_date', '<=', $this->dateTo);
        $totalOutcome += (float) $supplierPayQuery->sum('amount');

        $refundQuery = ReturnsProduct::query();
        if ($this->dateFrom) $refundQuery->whereDate('created_at', '>=', $this->dateFrom);
        if ($this->dateTo) $refundQuery->whereDate('created_at', '<=', $this->dateTo);
        $totalOutcome += (float) $refundQuery->sum('total_amount');

        $this->summaries['total_outcome'] = $totalOutcome;

        // Pending Income
        $pendingChequeQuery = Cheque::where('status', 'pending');
        if ($this->dateFrom) $pendingChequeQuery->whereDate('created_at', '>=', $this->dateFrom);
        if ($this->dateTo) $pendingChequeQuery->whereDate('created_at', '<=', $this->dateTo);
        $pendingIncome = (float) $pendingChequeQuery->sum('cheque_amount');

        $unpaidSaleQuery = Sale::whereIn('payment_status', ['pending', 'partial']);
        if ($this->dateFrom) $unpaidSaleQuery->whereDate('created_at', '>=', $this->dateFrom);
        if ($this->dateTo) $unpaidSaleQuery->whereDate('created_at', '<=', $this->dateTo);
        $pendingIncome += (float) $unpaidSaleQuery->sum('due_amount');

        $this->summaries['pending_income'] = $pendingIncome;
    }

    public function getIncomeRecordsProperty()
    {
        $q = Payment::with(['customer', 'sale', 'allocations.sale', 'cheques'])
            ->whereIn('status', ['approved', 'paid'])
            ->when($this->dateFrom, fn($q) => $q->whereDate('payment_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('payment_date', '<=', $this->dateTo));
        
        // Filter out pending cheques if desired in income tab too
        // But for pagination we might need a more complex query.
        // Let's use whereHas or similar to maintain pagination if possible.
        $q->where(function($query) {
            $query->where('payment_method', '!=', 'cheque')
                  ->orWhereHas('cheques', function($cq) {
                      $cq->where('status', 'complete');
                  });
        });

        return $q->orderByDesc('payment_date')->paginate(15, pageName: 'incomePage');
    }

    public function getOutcomeRecordsProperty()
    {
        // Combined query for Expenses and Supplier Payments might be tricky with pagination.
        // We'll use a manually unified collection for the "Ledger" tab, but for separate tabs we can paginate them.
        $q = Expense::query()
            ->when($this->dateFrom, fn($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('date', '<=', $this->dateTo));
        
        return $q->orderByDesc('date')->paginate(15, pageName: 'outcomePage');
    }

    public function getPendingRecordsProperty()
    {
        // Return 3 separate result sets or a combined one?
        // Let's do combined for Pending as well for a "Ledger" feel.
        $cheques = Cheque::with('payment.sale.customer', 'payment.customer')
            ->where('status', 'pending')
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->get()->map(fn($c) => [
                'type' => 'Cheque',
                'date' => $c->cheque_date,
                'ref' => $c->cheque_number,
                'entity' => $c->payment->sale->customer->name ?? $c->payment->customer->name ?? 'Walking Customer',
                'amount' => $c->cheque_amount,
                'status' => 'Pending',
                'color' => 'primary'
            ]);

        $sales = Sale::with('customer')
            ->whereIn('payment_status', ['pending', 'partial'])
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->get()->map(fn($s) => [
                'type' => $s->payment_status === 'pending' ? 'Sale' : 'Partial Payment',
                'date' => $s->created_at->format('Y-m-d'),
                'ref' => $s->invoice_number,
                'entity' => $s->customer->name ?? 'Walking Customer',
                'amount' => $s->due_amount,
                'status' => $s->payment_status === 'pending' ? 'Unpaid' : 'Partial',
                'color' => $s->payment_status === 'pending' ? 'danger' : 'info'
            ]);

        return $cheques->merge($sales)->sortByDesc('date');
    }

    public function getLedgerRecordsProperty()
    {
        // Comprehensive list of ALL transactions
        // 1. Approved Payments (In) - Only Cash, Bank, and Cleared Cheques
        $incomes = Payment::with(['customer', 'sale', 'allocations.sale', 'cheques'])
            ->whereIn('status', ['approved', 'paid'])
            ->where(function($query) {
                $query->where('payment_method', '!=', 'cheque')
                      ->orWhereHas('cheques', function($cq) {
                          $cq->where('status', 'complete');
                      });
            })
            ->when($this->dateFrom, fn($q) => $q->whereDate('payment_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('payment_date', '<=', $this->dateTo))
            ->get()->map(fn($p) => [
                'date' => $p->payment_date->format('Y-m-d'),
                'type' => 'Income (' . str_replace('_', ' ', ucfirst($p->payment_method)) . ')',
                'desc' => ($p->sale ? 'Invoice ' . $p->sale->invoice_number : ($p->allocations->count() > 0 ? 'Allocated: ' . $p->allocations->pluck('sale.invoice_number')->implode(', ') : 'Customer Receipt')) . ' - ' . ($p->customer->name ?? 'Walking Customer'),
                'in' => $p->amount,
                'out' => 0,
                'color' => 'success'
            ]);

        // 2. Deposits (In)
        $deposits = Deposit::query()
            ->when($this->dateFrom, fn($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('date', '<=', $this->dateTo))
            ->get()->map(fn($d) => [
                'date' => $d->date,
                'type' => 'Other Income',
                'desc' => $d->description ?? 'General Deposit',
                'in' => $d->amount,
                'out' => 0,
                'color' => 'info'
            ]);

        // 3. Expenses (Out)
        $expenses = Expense::query()
            ->when($this->dateFrom, fn($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('date', '<=', $this->dateTo))
            ->get()->map(fn($e) => [
                'date' => $e->date,
                'type' => 'Expense',
                'desc' => $e->category . ' - ' . ($e->description ?? 'N/A'),
                'in' => 0,
                'out' => $e->amount,
                'color' => 'danger'
            ]);

        // 4. Supplier Payments (Out)
        $supplierPmts = PurchasePayment::with('supplier')
            ->when($this->dateFrom, fn($q) => $q->whereDate('payment_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('payment_date', '<=', $this->dateTo))
            ->get()->map(fn($sp) => [
                'date' => $sp->payment_date,
                'type' => 'Supplier Payment',
                'desc' => 'Paid to: ' . ($sp->supplier->name ?? 'Unknown Supplier'),
                'in' => 0,
                'out' => $sp->amount,
                'color' => 'warning'
            ]);

        // 5. Refunds (Out)
        $refunds = ReturnsProduct::with('sale.customer')
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->get()->map(fn($r) => [
                'date' => $r->created_at->format('Y-m-d'),
                'type' => 'Refund',
                'desc' => 'Invoice ' . ($r->sale->invoice_number ?? 'N/A') . ' - ' . ($r->sale->customer->name ?? 'Walking Customer'),
                'in' => 0,
                'out' => $r->total_amount,
                'color' => 'dark'
            ]);

        return $incomes->merge($deposits)->merge($expenses)->merge($supplierPmts)->merge($refunds)->sortByDesc('date')->values();
    }

    public function getOutcomeRecordsDetailedProperty()
    {
        $expenses = Expense::query()
            ->when($this->dateFrom, fn($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('date', '<=', $this->dateTo))
            ->get()->map(fn($e) => [
                'type' => 'General Expense',
                'date' => $e->date,
                'entity' => $e->category,
                'desc' => $e->description,
                'amount' => $e->amount,
                'color' => 'danger'
            ]);

        $supplierPmts = PurchasePayment::with('supplier')
            ->when($this->dateFrom, fn($q) => $q->whereDate('payment_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('payment_date', '<=', $this->dateTo))
            ->get()->map(fn($sp) => [
                'type' => 'Supplier Payment',
                'date' => $sp->payment_date,
                'entity' => $sp->supplier->name ?? 'Unknown',
                'desc' => 'Ref: ' . $sp->payment_reference,
                'amount' => $sp->amount,
                'color' => 'warning'
            ]);

        return $expenses->merge($supplierPmts)->sortByDesc('date');
    }

    public function render()
    {
        return view('livewire.admin.financial-overview', [
            'incomeRecords' => $this->activeTab === 'income' ? $this->incomeRecords : null,
            'outcomeRecords' => $this->activeTab === 'outcome' ? $this->outcomeRecordsDetailed : null,
            'pendingRecords' => $this->activeTab === 'pending' ? $this->pendingRecords : null,
            'ledgerRecords' => $this->activeTab === 'ledger' ? $this->ledgerRecords : null,
        ])->layout($this->layout);
    }
}
