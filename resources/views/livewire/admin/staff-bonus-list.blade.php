<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-gift text-success me-2"></i> Staff Commission Management
            </h3>
            <p class="text-muted mb-0">Track and manage staff sales commissions by payment method</p>
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
                    <small class="text-muted">Total Commission</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100 bg-success bg-opacity-10">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="bi bi-cash-stack fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-success mb-1">Rs.{{ number_format($totalStats->cash_commission ?? 0, 2) }}</h5>
                    <small class="text-muted">Cash Sales</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100 bg-info bg-opacity-10">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="bi bi-credit-card fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-info mb-1">Rs.{{ number_format($totalStats->credit_commission ?? 0, 2) }}</h5>
                    <small class="text-muted">Credit Sales</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Staff Bonus Summary --}}
    @if($staffBonusSummary->count() > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-people text-primary me-2"></i>Staff Commission Summary
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Staff Name</th>
                            <th class="text-center">Cash Commission</th>
                            <th class="text-center">Credit Commission</th>
                            <th class="text-center">Total Sales</th>
                            <th class="text-end">Total Commission</th>
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
                                <span class="badge bg-success bg-opacity-25 text-success">Rs.{{ number_format($summary->cash_commission, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info bg-opacity-25 text-info">Rs.{{ number_format($summary->credit_commission, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $summary->total_sales }}</span>
                            </td>
                            <td class="text-end fw-bold text-primary">Rs.{{ number_format($summary->total_commission, 2) }}</td>
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
                <i class="bi bi-list-check text-primary me-2"></i>Sale-wise Commission Details
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
                            <th>Items</th>
                            <th>Payment</th>
                            <th class="text-end">Total Commission</th>
                            <th class="text-center">Actions</th>
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
                                <span class="badge bg-light text-dark fw-bold">{{ $bonus->sale->invoice_number ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 12px;">
                                        {{ strtoupper(substr($bonus->staff->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <span class="fw-medium">{{ $bonus->staff->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary rounded-pill">{{ $bonus->items_count }} products</span>
                            </td>
                            <td>
                                @if($bonus->payment_method === 'cash')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25"><i class="bi bi-cash-stack me-1"></i>Cash</span>
                                @else
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25"><i class="bi bi-credit-card me-1"></i>Credit</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-success fs-6">Rs.{{ number_format($bonus->total_sale_commission, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" wire:click="viewSaleCommission({{ $bonus->sale_id }})">
                                    <i class="bi bi-eye me-1"></i> View
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                <p class="text-muted mb-0">No commission records found</p>
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
                {{ $bonuses->links('livewire.custom-pagination') }}
            </div>
        </div>
        @endif
    </div>

    {{-- Bonus Detail Modal --}}
    @if($showBonusDetailModal && $selectedSaleInfo)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-info-circle me-2"></i>Commission Breakdown for Invoice #{{ $selectedSaleInfo->invoice_number }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeCommissionDetailModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-6 border-end">
                            <label class="text-muted small d-block mb-1">STAF MEMBER</label>
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                    {{ strtoupper(substr($selectedSaleInfo->user->name ?? 'S', 0, 1)) }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $selectedSaleInfo->user->name ?? 'Unknown' }}</h6>
                                    <small class="text-muted">{{ $selectedSaleInfo->user->email ?? '' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 ps-md-4">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="text-muted small d-block mb-1">SALE DATE</label>
                                    <span class="fw-medium">{{ $selectedSaleInfo->created_at->format('d M, Y H:i') }}</span>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="text-muted small d-block mb-1">CUSTOMER</label>
                                    <span class="fw-medium">{{ $selectedSaleInfo->customer->name ?? 'Walk-in Customer' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Details</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Commission/Unit</th>
                                    <th class="text-end">Total Commission</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalModalCommission = 0; @endphp
                                @foreach($selectedSaleCommissions as $commission)
                                @php $totalModalCommission += $commission->total_commission; @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $bonus->product->name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $bonus->product->code ?? '' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary rounded-pill">{{ $commission->quantity }}</span>
                                    </td>
                                    <td class="text-end">Rs.{{ number_format($commission->commission_per_unit, 2) }}</td>
                                    <td class="text-end fw-bold text-success">Rs.{{ number_format($commission->total_commission, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Grand Total Commission:</td>
                                    <td class="text-end fw-bold text-primary fs-5">Rs.{{ number_format($totalModalCommission, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-secondary px-4" wire:click="closeBonusDetailModal">Close</button>
                    <button type="button" class="btn btn-primary px-4" wire:click="viewSaleInvoice({{ $selectedSaleInfo->id }})">
                        <i class="bi bi-receipt me-1"></i> View Original Sale
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Sale Invoice Modal --}}
    @if($showSaleInvoiceModal && $selectedSaleForInvoice)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-receipt-cutoff me-2"></i>Invoice #{{ $selectedSaleForInvoice->invoice_number }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeSaleInvoiceModal"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Invoice Header -->
                    <div class="invoice-header p-4 bg-light border-bottom">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="mb-3">MI-KING</h4>
                                <p class="mb-1"><strong>Invoice:</strong> {{ $selectedSaleForInvoice->invoice_number }}</p>
                                <p class="mb-1"><strong>Sale ID:</strong> {{ $selectedSaleForInvoice->sale_id }}</p>
                                <p class="mb-1"><strong>Date:</strong> {{ $selectedSaleForInvoice->created_at->format('d/m/Y H:i') }}</p>
                                <p class="mb-1"><strong>Staff:</strong> {{ $selectedSaleForInvoice->user->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 text-end">
                                <h6 class="text-muted mb-2">Customer Details</h6>
                                <p class="mb-1"><strong>{{ $selectedSaleForInvoice->customer->name ?? 'Walk-in Customer' }}</strong></p>
                                <p class="mb-1">{{ $selectedSaleForInvoice->customer->phone ?? 'N/A' }}</p>
                                <p class="mb-1">{{ $selectedSaleForInvoice->customer->address ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sale Items -->
                    @if($selectedSaleForInvoice->items && $selectedSaleForInvoice->items->count() > 0)
                    <div class="p-4">
                        <h6 class="mb-3">Sale Items</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>Code</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Discount</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedSaleForInvoice->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->product_name }}</td>
                                        <td>{{ $item->product_code }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">Rs. {{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end">Rs. {{ number_format($item->discount_per_unit, 2) }}</td>
                                        <td class="text-end">Rs. {{ number_format($item->total, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="6" class="text-end"><strong>Subtotal:</strong></td>
                                        <td class="text-end"><strong>Rs. {{ number_format($selectedSaleForInvoice->subtotal, 2) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-end"><strong>Discount:</strong></td>
                                        <td class="text-end"><strong>Rs. {{ number_format($selectedSaleForInvoice->discount_amount ?? 0, 2) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-end"><strong>Grand Total:</strong></td>
                                        <td class="text-end"><strong>Rs. {{ number_format($selectedSaleForInvoice->total_amount, 2) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-end"><strong>Due Amount:</strong></td>
                                        <td class="text-end"><strong class="text-danger">Rs. {{ number_format($selectedSaleForInvoice->due_amount ?? 0, 2) }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Sale Summary -->
                    <div class="p-4 bg-light border-top">
                        <h6 class="mb-3">Sale Summary</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Sale Type:</strong> {{ ucfirst($selectedSaleForInvoice->sale_type) }}</p>
                                <p class="mb-1"><strong>Payment Method:</strong> {{ ucfirst($selectedSaleForInvoice->payment_method) }}</p>
                                <p class="mb-1"><strong>Payment Status:</strong> 
                                    @php
                                        $statusBadge = match($selectedSaleForInvoice->payment_status) {
                                            'pending' => ['class' => 'bg-warning', 'text' => 'Pending'],
                                            'partial' => ['class' => 'bg-info', 'text' => 'Partial'],
                                            'paid' => ['class' => 'bg-success', 'text' => 'Paid'],
                                            default => ['class' => 'bg-secondary', 'text' => 'Unknown']
                                        };
                                    @endphp
                                    <span class="badge {{ $statusBadge['class'] }}">{{ $statusBadge['text'] }}</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Total Items:</strong> {{ $selectedSaleForInvoice->items->sum('quantity') }}</p>
                                <p class="mb-1"><strong>Created:</strong> {{ $selectedSaleForInvoice->created_at->format('d/m/Y H:i:s') }}</p>
                                <p class="mb-1"><strong>Updated:</strong> {{ $selectedSaleForInvoice->updated_at->format('d/m/Y H:i:s') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeSaleInvoiceModal">
                        <i class="bi bi-x"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
