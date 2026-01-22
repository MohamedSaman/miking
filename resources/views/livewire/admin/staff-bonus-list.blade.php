<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-gift text-success me-2"></i> Staff Bonus Management
            </h3>
            <p class="text-muted mb-0">Track and manage staff sales bonuses by sale type and payment method</p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5">
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="bi bi-cash-stack fs-3"></i>
                    </div>
                    <h4 class="fw-bold text-primary mb-1">Rs.{{ number_format($totalStats->total ?? 0, 2) }}</h4>
                    <small class="text-muted">Total Bonus</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100 bg-success bg-opacity-10">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="bi bi-box-seam fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-success mb-1">Rs.{{ number_format($totalStats->wholesale_cash ?? 0, 2) }}</h5>
                    <small class="text-muted">Wholesale Cash</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100 bg-info bg-opacity-10">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="bi bi-credit-card fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-info mb-1">Rs.{{ number_format($totalStats->wholesale_credit ?? 0, 2) }}</h5>
                    <small class="text-muted">Wholesale Credit</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100 bg-warning bg-opacity-10">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="bi bi-shop fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-warning mb-1">Rs.{{ number_format($totalStats->retail_cash ?? 0, 2) }}</h5>
                    <small class="text-muted">Retail Cash</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100 bg-danger bg-opacity-10">
                <div class="card-body text-center">
                    <div class="text-danger mb-2">
                        <i class="bi bi-wallet2 fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-danger mb-1">Rs.{{ number_format($totalStats->retail_credit ?? 0, 2) }}</h5>
                    <small class="text-muted">Retail Credit</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Staff Bonus Summary --}}
    @if($staffBonusSummary->count() > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-people text-primary me-2"></i>Staff Bonus Summary
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Staff Name</th>
                            <th class="text-center">Wholesale Cash</th>
                            <th class="text-center">Wholesale Credit</th>
                            <th class="text-center">Retail Cash</th>
                            <th class="text-center">Retail Credit</th>
                            <th class="text-center">Total Sales</th>
                            <th class="text-end">Total Bonus</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staffBonusSummary as $summary)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                        {{ strtoupper(substr($summary->staff->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <span class="fw-medium">{{ $summary->staff->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success bg-opacity-25 text-success">Rs.{{ number_format($summary->wholesale_cash, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info bg-opacity-25 text-info">Rs.{{ number_format($summary->wholesale_credit, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning bg-opacity-25 text-warning">Rs.{{ number_format($summary->retail_cash, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger bg-opacity-25 text-danger">Rs.{{ number_format($summary->retail_credit, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $summary->total_sales }}</span>
                            </td>
                            <td class="text-end fw-bold text-primary">Rs.{{ number_format($summary->total_bonus, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" class="form-control" placeholder="Search..." wire:model.live="search">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Staff</label>
                    <select class="form-select" wire:model.live="staffFilter">
                        <option value="">All Staff</option>
                        @foreach($staffList as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Sale Type</label>
                    <select class="form-select" wire:model.live="saleTypeFilter">
                        <option value="">All Types</option>
                        <option value="wholesale">Wholesale</option>
                        <option value="retail">Retail</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Payment Method</label>
                    <select class="form-select" wire:model.live="paymentMethodFilter">
                        <option value="">All Methods</option>
                        <option value="cash">Cash</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">From Date</label>
                    <input type="date" class="form-control" wire:model.live="dateFrom">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">To Date</label>
                    <input type="date" class="form-control" wire:model.live="dateTo">
                </div>
            </div>
            <div class="mt-3">
                <button class="btn btn-outline-secondary btn-sm" wire:click="resetFilters">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
                </button>
            </div>
        </div>
    </div>

    {{-- Bonus Details Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-list-check text-primary me-2"></i>Bonus Details
            </h5>
            <div class="d-flex align-items-center gap-2">
                <label class="text-muted small">Show</label>
                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 80px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-muted small">entries</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Invoice</th>
                            <th>Staff</th>
                            <th>Product</th>
                            <th class="text-center">Qty</th>
                            <th>Sale Type</th>
                            <th>Payment</th>
                            <th class="text-end">Bonus/Unit</th>
                            <th class="text-end">Total Bonus</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bonuses as $index => $bonus)
                        <tr>
                            <td>{{ ($bonuses->currentPage() - 1) * $bonuses->perPage() + $index + 1 }}</td>
                            <td>
                                <small class="text-muted">{{ $bonus->created_at->format('d/m/Y') }}</small>
                                <br>
                                <small class="text-muted">{{ $bonus->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $bonus->sale->invoice_number ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 12px;">
                                        {{ strtoupper(substr($bonus->staff->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <span>{{ $bonus->staff->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="fw-medium">{{ $bonus->product->name ?? 'N/A' }}</span>
                                    <br>
                                    <small class="text-muted">{{ $bonus->product->code ?? '' }}</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $bonus->quantity }}</span>
                            </td>
                            <td>
                                @if($bonus->sale_type === 'wholesale')
                                <span class="badge bg-success">Wholesale</span>
                                @else
                                <span class="badge bg-info">Retail</span>
                                @endif
                            </td>
                            <td>
                                @if($bonus->payment_method === 'cash')
                                <span class="badge bg-warning text-dark">Cash</span>
                                @else
                                <span class="badge bg-danger">Credit</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="text-muted">Rs.{{ number_format($bonus->bonus_per_unit, 2) }}</span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-success">Rs.{{ number_format($bonus->total_bonus, 2) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                <p class="text-muted mb-0">No bonus records found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($bonuses->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-center">
                {{ $bonuses->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
