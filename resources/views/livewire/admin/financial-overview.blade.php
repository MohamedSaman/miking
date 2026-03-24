<div class="container-fluid p-0">
    <!-- Header/Filter Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap bg-white p-3 rounded shadow-sm">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-wallet2 text-primary me-2"></i> Financial Overview
            </h4>
            <p class="text-muted small mb-0">Monitor all financial activities, income, outcome and pending payments.</p>
        </div>
        <div class="d-flex align-items-center gap-2 mt-3 mt-md-0">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar-event"></i></span>
                <input type="date" wire:model.live="dateFrom" class="form-control border-start-0 ps-0" placeholder="From Date">
            </div>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar-event"></i></span>
                <input type="date" wire:model.live="dateTo" class="form-control border-start-0 ps-0" placeholder="To Date">
            </div>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-secondary" wire:click="filterToday">Today</button>
                <button type="button" class="btn btn-outline-secondary" wire:click="filterThisMonth">Month</button>
                <button type="button" class="btn btn-outline-secondary" wire:click="filterAllTime">All</button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #2a83df !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-600">Available Liquid Money</span>
                        <div class="icon-container bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                            <i class="bi bi-cash-stack fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1">Rs.{{ number_format($summaries['total_liquid'], 2) }}</h3>
                    <div class="d-flex flex-column gap-1">
                        <small class="text-muted">Cash: Rs.{{ number_format($summaries['available_cash'], 2) }}</small>
                        <small class="text-muted">Cleared Cheques: Rs.{{ number_format($summaries['cleared_cheques'], 2) }}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-600">Total Income</span>
                        <div class="icon-container bg-success bg-opacity-10 text-success rounded-circle p-2">
                            <i class="bi bi-graph-up-arrow fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1">Rs.{{ number_format($summaries['total_income'], 2) }}</h3>
                    <small class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Approved Collections</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-600">Total Outgoing</span>
                        <div class="icon-container bg-danger bg-opacity-10 text-danger rounded-circle p-2">
                            <i class="bi bi-graph-down-arrow fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1">Rs.{{ number_format($summaries['total_outcome'], 2) }}</h3>
                    <small class="text-danger fw-bold"><i class="bi bi-dash-circle me-1"></i>Expenses & Payments</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-600">Pending Receivables</span>
                        <div class="icon-container bg-warning bg-opacity-10 text-warning rounded-circle p-2">
                            <i class="bi bi-hourglass-split fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1">Rs.{{ number_format($summaries['pending_income'], 2) }}</h3>
                    <small class="text-muted">Unpaid/Partial Invoices & Cheques</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white p-0">
            <ul class="nav nav-tabs nav-fill border-0">
                <li class="nav-item">
                    <button class="nav-link py-3 border-0 rounded-0 @if($activeTab === 'summary') active fw-bold text-primary border-bottom border-primary @else text-muted @endif" wire:click="setTab('summary')">
                        <i class="bi bi-grid-alt me-2"></i>Summary
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link py-3 border-0 rounded-0 @if($activeTab === 'income') active fw-bold text-primary border-bottom border-primary @else text-muted @endif" wire:click="setTab('income')">
                        <i class="bi bi-arrow-down-left-circle me-2"></i>Detailed Income
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link py-3 border-0 rounded-0 @if($activeTab === 'outcome') active fw-bold text-primary border-bottom border-primary @else text-muted @endif" wire:click="setTab('outcome')">
                        <i class="bi bi-arrow-up-right-circle me-2"></i>Detailed Outgoing
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link py-3 border-0 rounded-0 @if($activeTab === 'pending') active fw-bold text-primary border-bottom border-primary @else text-muted @endif" wire:click="setTab('pending')">
                        <i class="bi bi-clock-history me-2"></i>Pending Stats
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link py-3 border-0 rounded-0 @if($activeTab === 'ledger') active fw-bold text-primary border-bottom border-primary @else text-muted @endif" wire:click="setTab('ledger')">
                        <i class="bi bi-list-columns-reverse me-2"></i>General Ledger
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-4 bg-light bg-opacity-50">
            @if($activeTab === 'summary')
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart me-2"></i>Money Distribution</h6>
                        <div class="list-group shadow-sm">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-cash me-2 text-success"></i> Cash in Hand</span>
                                <span class="fw-bold fs-5">Rs.{{ number_format($summaries['available_cash'], 2) }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-card-text me-2 text-primary"></i> Cleared Cheques</span>
                                <span class="fw-bold fs-5">Rs.{{ number_format($summaries['cleared_cheques'], 2) }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center text-primary bg-primary bg-opacity-10 border-top-0">
                                <span class="fw-bold">Liquid Money</span>
                                <span class="fw-bold fs-4">Rs.{{ number_format($summaries['available_cash'] + $summaries['cleared_cheques'], 2) }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center mt-2 border-top">
                                <span><i class="bi bi-bank me-2 text-info"></i> Bank Transfer Collections</span>
                                <span class="fw-bold fs-5">Rs.{{ number_format($summaries['bank_balance'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3"><i class="bi bi-activity me-2"></i>Cash Flow Analysis</h6>
                        <div class="p-3 bg-white rounded shadow-sm">
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                <span class="text-muted">Net Cash Flow:</span>
                                <span class="fw-bold @if($summaries['total_income'] - $summaries['total_outcome'] >= 0) text-success @else text-danger @endif fs-4">
                                    Rs.{{ number_format($summaries['total_income'] - $summaries['total_outcome'], 2) }}
                                </span>
                            </div>
                            <p class="small text-muted mb-0">This calculation is based on the selected date range and reflects real-time data from all financial modules.</p>
                        </div>
                    </div>
                </div>
            @elseif($activeTab === 'income')
                <div class="table-responsive shadow-sm rounded">
                    <table class="table table-hover bg-white mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Source</th>
                                <th>Customer</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($incomeRecords as $record)
                                <tr>
                                    <td>{{ $record->payment_date->format('Y-m-d') }}</td>
                                    <td>
                                        @php
                                            $method = $record->payment_method;
                                            $color = 'primary';
                                            if($method == 'cash') $color = 'success';
                                            if($method == 'cheque') $color = 'info';
                                            if($method == 'bank_transfer') $color = 'dark';
                                        @endphp
                                        <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} border border-{{ $color }}">
                                            {{ str_replace('_', ' ', ucfirst($method)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($record->sale) 
                                            Invoice: {{ $record->sale->invoice_number }}
                                        @elseif($record->allocations->count() > 0)
                                            Allocated: {{ $record->allocations->pluck('sale.invoice_number')->implode(', ') }}
                                        @else
                                            Direct Receipt
                                        @endif
                                    </td>
                                    <td>{{ $record->customer->name ?? 'Walking Customer' }}</td>
                                    <td class="text-end fw-bold">Rs.{{ number_format($record->amount, 2) }}</td>
                                    <td>{!! $record->status_badge !!}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No income records found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $incomeRecords->links() }}</div>
            @elseif($activeTab === 'outcome')
                <div class="table-responsive shadow-sm rounded">
                    <table class="table table-hover bg-white mb-0">
                        <thead class="bg-danger text-white">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Entity / Category</th>
                                <th>Description / Reference</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($outcomeRecords as $record)
                                <tr>
                                    <td>{{ $record['date'] }}</td>
                                    <td>
                                        <span class="badge bg-{{ $record['color'] }} bg-opacity-10 text-{{ $record['color'] }} border border-{{ $record['color'] }}">
                                            {{ $record['type'] }}
                                        </span>
                                    </td>
                                    <td>{{ $record['entity'] }}</td>
                                    <td class="small">{{ $record['desc'] }}</td>
                                    <td class="text-end fw-bold">Rs.{{ number_format($record['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No outgoing payments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @elseif($activeTab === 'pending')
                <div class="table-responsive shadow-sm rounded">
                    <table class="table table-hover bg-white mb-0">
                        <thead class="bg-warning text-dark">
                            <tr>
                                <th>Status Date</th>
                                <th>Type</th>
                                <th>Reference</th>
                                <th>Payer / Customer</th>
                                <th class="text-end">Due Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingRecords as $record)
                                <tr>
                                    <td>{{ $record['date'] }}</td>
                                    <td>{{ $record['type'] }}</td>
                                    <td>{{ $record['ref'] }}</td>
                                    <td>{{ $record['entity'] }}</td>
                                    <td class="text-end fw-bold text-danger">Rs.{{ number_format($record['amount'], 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $record['color'] }}">{{ $record['status'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Great! No pending payments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @elseif($activeTab === 'ledger')
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Comprehensive Ledger Statement</h6>
                    <button class="btn btn-sm btn-primary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print Statement
                    </button>
                </div>
                <div class="table-responsive shadow-sm rounded">
                    <table class="table table-sm table-hover bg-white mb-0">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>Date</th>
                                <th>Transaction Type</th>
                                <th>Details</th>
                                <th class="text-end">In (Income)</th>
                                <th class="text-end">Out (Payment)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $runningBal = 0; @endphp
                            @forelse($ledgerRecords as $record)
                                <tr>
                                    <td>{{ $record['date'] }}</td>
                                    <td>
                                        <span class="badge bg-{{ $record['color'] }} bg-opacity-10 text-{{ $record['color'] }}">
                                            {{ $record['type'] }}
                                        </span>
                                    </td>
                                    <td class="small">{{ $record['desc'] }}</td>
                                    <td class="text-end text-success fw-bold">
                                        @if($record['in'] > 0) Rs.{{ number_format($record['in'], 2) }} @else - @endif
                                    </td>
                                    <td class="text-end text-danger fw-bold">
                                        @if($record['out'] > 0) Rs.{{ number_format($record['out'], 2) }} @else - @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No transactions recorded for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-light fw-bold border-top">
                            <tr>
                                <td colspan="3" class="text-end">Totals:</td>
                                <td class="text-end text-success">Rs.{{ number_format($ledgerRecords->sum('in'), 2) }}</td>
                                <td class="text-end text-danger">Rs.{{ number_format($ledgerRecords->sum('out'), 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end">Net Balance:</td>
                                <td colspan="2" class="text-center @if($ledgerRecords->sum('in') - $ledgerRecords->sum('out') >= 0) text-success @else text-danger @endif">
                                    Rs.{{ number_format($ledgerRecords->sum('in') - $ledgerRecords->sum('out'), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
    <style>
        .nav-tabs .nav-link {
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }
        .nav-tabs .nav-link:hover {
            background-color: rgba(42, 131, 223, 0.05);
        }
        .fw-600 { font-weight: 600; }
        @media print {
            .nav-tabs, .btn-group, .input-group, #sidebar-wrapper, .top-bar { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
            .container-fluid { width: 100% !important; margin: 0 !important; padding: 0 !important; }
            .main-content { margin-left: 0 !important; margin-top: 0 !important; }
        }
    </style>
</div>
