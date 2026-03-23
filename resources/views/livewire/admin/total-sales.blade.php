<div class="container-fluid py-3">

    {{-- ========== HEADER ========== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-bar-chart-line text-primary me-2"></i> Total Sales Summary
            </h3>
            <p class="text-muted mb-0">Overview of all sales categorized by payment method</p>
        </div>
    </div>

    {{-- ========== DATE FILTER ========== --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">From Date</label>
                    <input type="date" class="form-control" wire:model.live="dateFrom">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">To Date</label>
                    <input type="date" class="form-control" wire:model.live="dateTo">
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary btn-sm" wire:click="filterThisMonth">
                            This Month
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" wire:click="filterToday">
                            Today
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" wire:click="filterAllTime">
                            All Time
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== TABS ========== --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs border-0">
                <li class="nav-item">
                    <button class="nav-link fw-semibold px-4 py-3 {{ $activeTab === 'overview' ? 'active text-primary border-bottom border-2 border-primary' : 'text-muted' }}"
                        wire:click="setTab('overview')">
                        <i class="bi bi-grid-3x3-gap me-1"></i> Overview
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold px-4 py-3 {{ $activeTab === 'cash' ? 'active text-success border-bottom border-2 border-success' : 'text-muted' }}"
                        wire:click="setTab('cash')">
                        <i class="bi bi-cash me-1"></i> Cash Sales
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold px-4 py-3 {{ $activeTab === 'cheque' ? 'active text-info border-bottom border-2 border-info' : 'text-muted' }}"
                        wire:click="setTab('cheque')">
                        <i class="bi bi-journal-check me-1"></i> Cheque Sales
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold px-4 py-3 {{ $activeTab === 'credit' ? 'active text-warning border-bottom border-2 border-warning' : 'text-muted' }}"
                        wire:click="setTab('credit')">
                        <i class="bi bi-person-lines-fill me-1"></i> Credit Sales
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold px-4 py-3 {{ $activeTab === 'bank' ? 'active border-bottom border-2' : 'text-muted' }}"
                        style="{{ $activeTab === 'bank' ? 'color:#6f42c1;border-color:#6f42c1 !important;' : '' }}"
                        wire:click="setTab('bank')">
                        <i class="bi bi-bank me-1"></i> Bank Transfers
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold px-4 py-3 {{ $activeTab === 'total-income' ? 'active border-bottom border-2' : 'text-muted' }}"
                        style="{{ $activeTab === 'total-income' ? 'color:#fd7e14;border-color:#fd7e14 !important;' : '' }}"
                        wire:click="setTab('total-income')">
                        <i class="bi bi-graph-up-arrow me-1"></i> Total Income
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-0">

            {{-- ---- OVERVIEW TAB ---- --}}
            @if($activeTab === 'overview')
            <div class="p-4">
                <h6 class="fw-bold mb-3 text-muted text-uppercase" style="font-size:.8rem;letter-spacing:.05em;">Payment Method Breakdown</h6>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Payment Method</th>
                                <th class="text-center">Transactions</th>
                                <th class="text-end">Amount Collected</th>
                                <th class="text-center">Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $total = $summary['totalCollected'] ?: 1;
                                $rows = [
                                    ['Cash',          'success', 'bi-cash-coin',         $summary['cashCollected'],   $summary['cashSaleCount']],
                                    ['Cheque',        'info',    'bi-journal-check',      $summary['chequeCollected'], $summary['chequeSaleCount']],
                                    ['Credit',        'warning', 'bi-person-lines-fill',  $summary['creditCollected'], $summary['creditSaleCount']],
                                    ['Bank Transfer', 'primary', 'bi-bank',               $summary['bankCollected'],   '-'],
                                ];
                            @endphp
                            @foreach($rows as [$label, $color, $icon, $amount, $count])
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $color }} me-2"><i class="bi {{ $icon }}"></i></span>
                                    {{ $label }}
                                </td>
                                <td class="text-center">{{ is_numeric($count) ? number_format($count) : $count }}</td>
                                <td class="text-end fw-semibold">Rs.{{ number_format($amount, 2) }}</td>
                                <td class="text-center">
                                    @php $pct = $total > 0 ? round($amount / $total * 100, 1) : 0; @endphp
                                    <div class="progress" style="height:8px;min-width:80px;">
                                        <div class="progress-bar bg-{{ $color }}" style="width:{{ $pct }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $pct }}%</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td>Total</td>
                                <td class="text-center">{{ number_format($summary['totalSalesCount']) }}</td>
                                <td class="text-end">Rs.{{ number_format($summary['totalCollected'], 2) }}</td>
                                <td class="text-center">100%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Outstanding Due --}}
                <div class="alert alert-danger mt-4 d-flex align-items-center gap-3 border-0 shadow-sm" style="border-radius:10px;">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size:1.5rem;"></i>
                    <div>
                        <div class="fw-bold">Outstanding Due Amount</div>
                        <div class="h5 mb-0">Rs.{{ number_format($summary['totalDue'], 2) }}</div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ---- CASH SALES TAB ---- --}}
            @if($activeTab === 'cash')
            <div class="p-0">
                <div class="px-4 pt-3 pb-2 d-flex justify-content-between align-items-center border-bottom">
                    <span class="fw-semibold text-success"><i class="bi bi-cash me-1"></i> Cash Sales Detail</span>
                    <span class="badge bg-success">Total: Rs.{{ number_format($summary['cashCollected'], 2) }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Payment ID</th>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Amount</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cashSales as $payment)
                            <tr>
                                <td class="ps-4 text-muted">#{{ $payment->id }}</td>
                                <td>
                                    <span class="badge bg-primary" style="font-size:.75rem;">
                                        {{ $payment->sale->invoice_number ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $payment->sale->customer->name ?? 'Walking Customer' }}</td>
                                <td class="text-center">{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : '-' }}</td>
                                <td class="text-center fw-semibold text-success">Rs.{{ number_format($payment->amount, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $payment->status === 'approved' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($payment->status ?? 'paid') }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i> No cash sales found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($cashSales->hasPages())
                <div class="px-4 py-3">{{ $cashSales->links('livewire.custom-pagination') }}</div>
                @endif
            </div>
            @endif

            {{-- ---- CHEQUE SALES TAB ---- --}}
            @if($activeTab === 'cheque')
            <div class="p-0">
                <div class="px-4 pt-3 pb-2 d-flex justify-content-between align-items-center border-bottom">
                    <span class="fw-semibold text-info"><i class="bi bi-journal-check me-1"></i> Cheque Sales Detail</span>
                    <span class="badge bg-info text-dark">Total: Rs.{{ number_format($summary['chequeCollected'], 2) }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Payment ID</th>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th class="text-center">Cheque No</th>
                                <th class="text-center">Cheque Date</th>
                                <th class="text-center">Amount</th>
                                <th class="text-center">Cheque Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($chequeSales as $payment)
                            @php $cheque = $payment->cheques->first(); @endphp
                            <tr>
                                <td class="ps-4 text-muted">#{{ $payment->id }}</td>
                                <td>
                                    <span class="badge bg-primary" style="font-size:.75rem;">
                                        {{ $payment->sale->invoice_number ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $payment->sale->customer->name ?? 'Walking Customer' }}</td>
                                <td class="text-center">{{ $cheque->cheque_number ?? '-' }}</td>
                                <td class="text-center">{{ $cheque && $cheque->cheque_date ? \Carbon\Carbon::parse($cheque->cheque_date)->format('M d, Y') : '-' }}</td>
                                <td class="text-center fw-semibold text-info">Rs.{{ number_format($payment->amount, 2) }}</td>
                                <td class="text-center">
                                    @if($cheque)
                                    <span class="badge bg-{{ $cheque->status === 'complete' ? 'success' : ($cheque->status === 'pending' ? 'warning' : ($cheque->status === 'return' ? 'danger' : 'secondary')) }}">
                                        {{ ucfirst($cheque->status) }}
                                    </span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i> No cheque sales found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($chequeSales->hasPages())
                <div class="px-4 py-3">{{ $chequeSales->links('livewire.custom-pagination') }}</div>
                @endif
            </div>
            @endif

            {{-- ---- CREDIT SALES TAB ---- --}}
            @if($activeTab === 'credit')
            <div class="p-0">
                <div class="px-4 pt-3 pb-2 d-flex justify-content-between align-items-center border-bottom">
                    <span class="fw-semibold text-warning"><i class="bi bi-person-lines-fill me-1"></i> Credit Sales Detail</span>
                    <span class="badge bg-warning text-dark">Total outstanding: Rs.{{ number_format($summary['totalDue'], 2) }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Invoice</th>
                                <th>Customer</th>
                                <th class="text-center">Sale Date</th>
                                <th class="text-center">Total Amount</th>
                                <th class="text-center">Paid</th>
                                <th class="text-center">Due</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($creditSales as $sale)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-primary" style="font-size:.75rem;">{{ $sale->invoice_number }}</span>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $sale->customer->name ?? 'Walking Customer' }}</div>
                                    @if($sale->customer?->phone)
                                    <small class="text-muted">{{ $sale->customer->phone }}</small>
                                    @endif
                                </td>
                                <td class="text-center">{{ $sale->created_at->format('M d, Y') }}</td>
                                <td class="text-center fw-semibold">Rs.{{ number_format($sale->total_amount, 2) }}</td>
                                <td class="text-center text-success">Rs.{{ number_format($sale->total_amount - $sale->due_amount, 2) }}</td>
                                <td class="text-center text-danger fw-bold">Rs.{{ number_format($sale->due_amount, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $sale->payment_status === 'paid' ? 'success' : ($sale->payment_status === 'partial' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($sale->payment_status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i> No credit sales found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($creditSales->hasPages())
                <div class="px-4 py-3">{{ $creditSales->links('livewire.custom-pagination') }}</div>
                @endif
            </div>
            @endif

            {{-- ---- BANK TRANSFERS TAB ---- --}}
            @if($activeTab === 'bank')
            <div class="p-0">
                <div class="px-4 pt-3 pb-2 d-flex justify-content-between align-items-center border-bottom">
                    <span class="fw-semibold" style="color:#6f42c1;"><i class="bi bi-bank me-1"></i> Bank Transfer Detail</span>
                    <span class="badge text-white" style="background:#6f42c1;">Total: Rs.{{ number_format($summary['bankCollected'], 2) }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Payment ID</th>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Amount</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bankSales ?? [] as $payment)
                            <tr>
                                <td class="ps-4 text-muted">#{{ $payment->id }}</td>
                                <td>
                                    <span class="badge bg-primary" style="font-size:.75rem;">
                                        {{ $payment->sale->invoice_number ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $payment->sale->customer->name ?? 'Walking Customer' }}</td>
                                <td class="text-center">{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : '-' }}</td>
                                <td class="text-center fw-semibold" style="color:#6f42c1;">Rs.{{ number_format($payment->amount, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $payment->status === 'approved' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($payment->status ?? '-') }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i> No bank transfer records found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(isset($bankSales) && $bankSales->hasPages())
                <div class="px-4 py-3">{{ $bankSales->links('livewire.custom-pagination') }}</div>
                @endif
            </div>
            @endif

            {{-- ---- TOTAL INCOME TAB ---- --}}
            @if($activeTab === 'total-income')
            <div class="p-0">
                <div class="px-4 pt-3 pb-2 d-flex justify-content-between align-items-center border-bottom">
                    <div>
                        <span class="fw-semibold" style="color:#fd7e14;">
                            <i class="bi bi-graph-up-arrow me-1"></i> Total Company Income
                        </span>
                        <small class="text-muted ms-2">(Approved cash + Cleared cheques + Bank transfers — excludes pending cheques &amp; credit)</small>
                    </div>
                </div>

                {{-- Income breakdown summary --}}
                <div class="row g-3 p-4 pb-3">
                    <div class="col-md-2">
                        <div class="p-3 rounded text-center border" style="background:#fff8f0;">
                            <div class="fw-bold text-success" style="font-size:1rem;">Rs.{{ number_format($summary['incomeCash'], 2) }}</div>
                            <small class="text-muted" style="font-size:0.75rem;"><i class="bi bi-cash-coin me-1"></i>Cash Sales</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="p-3 rounded text-center border" style="background:#fff8f0;">
                            <div class="fw-bold text-info" style="font-size:1rem;">Rs.{{ number_format($summary['incomeCheque'], 2) }}</div>
                            <small class="text-muted" style="font-size:0.75rem;"><i class="bi bi-journal-check me-1"></i>Cheques</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="p-3 rounded text-center border" style="background:#fff8f0;">
                            <div class="fw-bold text-primary" style="font-size:1rem;">Rs.{{ number_format($summary['incomeBank'], 2) }}</div>
                            <small class="text-muted" style="font-size:0.75rem;"><i class="bi bi-bank me-1"></i>Bank Transfers</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="p-3 rounded text-center border" style="background:#fff8f0;">
                            <div class="fw-bold text-dark" style="font-size:1rem;">Rs.{{ number_format($summary['incomeReceipts'], 2) }}</div>
                            <small class="text-muted" style="font-size:0.75rem;"><i class="bi bi-receipt me-1"></i>Due Settle</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="p-3 rounded text-center border" style="background:#fff8f0;">
                            <div class="fw-bold text-warning" style="font-size:1rem;">Rs.{{ number_format($summary['incomeOther'], 2) }}</div>
                            <small class="text-muted" style="font-size:0.75rem;"><i class="bi bi-plus-circle me-1"></i>Other Income</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="p-3 rounded text-center" style="background:#fff3e0;border:2px solid #fd7e14;">
                            <div class="fw-bold" style="font-size:1.1rem;color:#fd7e14;">Rs.{{ number_format($summary['totalIncome'], 2) }}</div>
                            <small class="text-muted fw-semibold" style="font-size:0.75rem;"><i class="bi bi-graph-up-arrow me-1"></i>Total Income</small>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Payment ID</th>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Reference</th>
                                <th class="text-center">Date</th>
                                <th class="text-end pe-4">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($totalIncome ?? [] as $row)
                            <tr>
                                <td class="ps-4 text-muted">#{{ $row->id }}</td>
                                <td>
                                    <span class="badge bg-primary" style="font-size:.75rem;">{{ $row->invoice }}</span>
                                </td>
                                <td>{{ $row->customer }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $row->type_color }}">{{ $row->type }}</span>
                                </td>
                                <td class="text-center text-muted">{{ $row->reference }}</td>
                                <td class="text-center">{{ $row->date ? \Carbon\Carbon::parse($row->date)->format('M d, Y') : '-' }}</td>
                                <td class="text-end pe-4 fw-bold" style="color:#fd7e14;">Rs.{{ number_format($row->amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i> No income records found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if(($totalIncome ?? collect())->isNotEmpty())
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="6" class="text-end fw-bold pe-3">Total Income</td>
                                <td class="text-end pe-4 fw-bold" style="color:#fd7e14;">
                                    Rs.{{ number_format(($totalIncome ?? collect())->sum('amount'), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
            @endif

        </div>{{-- end card-body --}}
    </div>{{-- end card --}}

</div>
