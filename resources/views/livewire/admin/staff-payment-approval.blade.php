<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #2a83df;">Payment Approvals</h2>
            <p class="text-muted">Review and approve staff member payments</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.staff-payment-approval') }}" class="btn btn-outline-primary rounded-2">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-warning" style="border-width: 4px !important;">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Pending Approvals</h6>
                    <h3 class="fw-bold text-warning">{{ $pendingCount }}</h3>
                    <small class="text-muted">Awaiting review</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-success" style="border-width: 4px !important;">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Approved</h6>
                    <h3 class="fw-bold text-success">{{ $approvedCount }}</h3>
                    <small class="text-muted">Completed payments</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-danger" style="border-width: 4px !important;">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Rejected</h6>
                    <h3 class="fw-bold text-danger">{{ $rejectedCount }}</h3>
                    <small class="text-muted">Declined payments</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Filter by Status</label>
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="pending">Pending Approval</option>
                        <option value="all">All Status</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Payment Method</label>
                    <select class="form-select" wire:model.live="filterPaymentMethod">
                        <option value="all">All Methods</option>
                        <option value="cash">Cash</option>
                        <option value="cheque">Cheque</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" class="form-control" wire:model.live="searchTerm" placeholder="Payment ID, Reference or Customer...">
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold" style="color: #2a83df;">
                <i class="bi bi-credit-card me-2"></i>Payments List
            </h5>
            <div class="d-flex align-items-center gap-2">
                <label class="text-sm text-muted fw-medium mb-0">Show</label>
                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 80px;">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-sm text-muted mb-0">entries</span>
            </div>
        </div>
        <div class="card-body p-0">
            @if ($payments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="80">Payment ID</th>
                                <th>Customer Name</th>
                                <th>Invoice No.</th>
                                <th width="100">Amount</th>
                                <th width="100">Method</th>
                                <th width="100">Reference</th>
                                <th width="120">Payment Status</th>
                                <th width="100">Date</th>
                                <th width="150" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">{{ $payment->id }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $payment->sale->customer->name ?? $payment->customer->name ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        @if ($payment->sale)
                                            <span class="badge bg-info">{{ $payment->sale->invoice_number }}</span>
                                        @elseif ($payment->allocations && $payment->allocations->count() > 0)
                                            @foreach ($payment->allocations as $allocation)
                                                <span class="badge bg-info mb-1">{{ \App\Models\Sale::find($allocation->sale_id)?->invoice_number ?? 'N/A' }}</span>
                                            @endforeach
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-success">Rs. {{ number_format($payment->amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        @php
                                            $methodBadge = match($payment->payment_method) {
                                                'cash' => ['class' => 'bg-success', 'text' => 'Cash', 'icon' => 'bi-cash-coin'],
                                                'cheque' => ['class' => 'bg-info', 'text' => 'Cheque', 'icon' => 'bi-card-text'],
                                                'bank_transfer' => ['class' => 'bg-warning', 'text' => 'Bank Transfer', 'icon' => 'bi-bank'],
                                                'credit' => ['class' => 'bg-danger', 'text' => 'Credit', 'icon' => 'bi-percent'],
                                                default => ['class' => 'bg-secondary', 'text' => 'Unknown', 'icon' => 'bi-question']
                                            };
                                        @endphp
                                        <span class="badge {{ $methodBadge['class'] }}">
                                            <i class="bi {{ $methodBadge['icon'] }} me-1"></i>{{ $methodBadge['text'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $payment->payment_reference ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $statusBadge = match($payment->status) {
                                                'pending' => ['class' => 'bg-warning', 'text' => 'Pending Approval'],
                                                'approved' => ['class' => 'bg-success', 'text' => 'Approved'],
                                                'rejected' => ['class' => 'bg-danger', 'text' => 'Rejected'],
                                                'paid' => ['class' => 'bg-success', 'text' => 'Paid'],
                                                default => ['class' => 'bg-secondary', 'text' => 'Unknown']
                                            };
                                        @endphp
                                        <span class="badge {{ $statusBadge['class'] }}">{{ $statusBadge['text'] }}</span>
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($payment->created_at)->format('d M, Y') }}
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex flex-column gap-1 mx-auto" style="max-width: 120px;">
                                            <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" 
                                                    wire:click="viewInvoice({{ $payment->id }})" title="View Details">
                                                <i class="bi bi-eye-fill me-1"></i> View
                                            </button>
                                            
                                            @if ($payment->status === 'pending')
                                                <button class="btn btn-sm btn-success rounded-pill px-3 shadow-sm" 
                                                        wire:click="approvePayment({{ $payment->id }})" title="Approve Payment">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Approve
                                                </button>
                                                <button class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm" 
                                                        wire:click="rejectPayment({{ $payment->id }})" title="Reject Payment">
                                                    <i class="bi bi-x-circle-fill me-1"></i> Reject
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-center">
                        {{ $payments->links('livewire.custom-pagination') }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-3 text-muted mb-3"></i>
                    <p class="text-muted">No payments found</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Invoice View Modal -->
    @if($showInvoiceModal && $selectedPayment)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-receipt me-2"></i>Invoice Details - 
                        @if($selectedPayment->sale)
                            {{ $selectedPayment->sale->invoice_number }}
                        @else
                            Payment #{{ $selectedPayment->id }}
                        @endif
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeInvoiceModal"></button>
                </div>
                <div class="modal-body p-0">
                    @if($selectedPayment->sale)
                        <!-- Invoice Header -->
                        <div class="invoice-header p-4 bg-light border-bottom">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4 class="mb-3">MI-KING</h4>
                                    <p class="mb-1"><strong>Invoice:</strong> {{ $selectedPayment->sale->invoice_number }}</p>
                                    <p class="mb-1"><strong>Sale ID:</strong> {{ $selectedPayment->sale->sale_id }}</p>
                                    <p class="mb-1"><strong>Date:</strong> {{ $selectedPayment->sale->created_at->format('d/m/Y H:i') }}</p>
                                    <p class="mb-1"><strong>Staff:</strong> {{ $selectedPayment->sale->user->name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <h6 class="text-muted mb-2">Customer Details</h6>
                                    <p class="mb-1"><strong>{{ $selectedPayment->sale->customer->name ?? 'N/A' }}</strong></p>
                                    <p class="mb-1">{{ $selectedPayment->sale->customer->phone ?? 'N/A' }}</p>
                                    <p class="mb-1">{{ $selectedPayment->sale->customer->address ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Sale Items -->
                        @if($selectedPayment->sale->items && $selectedPayment->sale->items->count() > 0)
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
                                        @foreach($selectedPayment->sale->items as $index => $item)
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
                                            <td class="text-end"><strong>Rs. {{ number_format($selectedPayment->sale->subtotal, 2) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="text-end"><strong>Discount:</strong></td>
                                            <td class="text-end"><strong>Rs. {{ number_format($selectedPayment->sale->discount_amount ?? 0, 2) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="text-end"><strong>Grand Total:</strong></td>
                                            <td class="text-end"><strong>Rs. {{ number_format($selectedPayment->sale->total_amount, 2) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="text-end"><strong>Due Amount:</strong></td>
                                            <td class="text-end"><strong class="text-danger">Rs. {{ number_format($selectedPayment->sale->due_amount ?? 0, 2) }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- Payment Information -->
                        <div class="p-4 bg-light border-top">
                            <h6 class="mb-3">Payment Details</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Payment ID:</strong> {{ $selectedPayment->id }}</p>
                                    <p class="mb-1"><strong>Amount:</strong> Rs. {{ number_format($selectedPayment->amount, 2) }}</p>
                                    <p class="mb-1"><strong>Method:</strong> {{ ucfirst($selectedPayment->payment_method) }}</p>
                                    <p class="mb-1"><strong>Reference:</strong> {{ $selectedPayment->payment_reference ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Payment Date:</strong> {{ $selectedPayment->payment_date ? \Carbon\Carbon::parse($selectedPayment->payment_date)->format('d/m/Y H:i') : 'N/A' }}</p>
                                    <p class="mb-1"><strong>Status:</strong> 
                                        @php
                                            $statusBadge = match($selectedPayment->status) {
                                                'pending' => ['class' => 'bg-warning', 'text' => 'Pending Approval'],
                                                'approved' => ['class' => 'bg-success', 'text' => 'Approved'],
                                                'rejected' => ['class' => 'bg-danger', 'text' => 'Rejected'],
                                                'paid' => ['class' => 'bg-success', 'text' => 'Paid'],
                                                default => ['class' => 'bg-secondary', 'text' => 'Unknown']
                                            };
                                        @endphp
                                        <span class="badge {{ $statusBadge['class'] }}">{{ $statusBadge['text'] }}</span>
                                    </p>
                                    <p class="mb-1"><strong>Created By:</strong> {{ $selectedPayment->creator->name ?? 'System' }}</p>
                                </div>
                            </div>
                        </div>
                    @elseif($selectedPayment->allocations && $selectedPayment->allocations->count() > 0)
                        <!-- Multi-Invoice Payment -->
                        <div class="p-4">
                            <h6 class="mb-3">Payment Allocation Details</h6>
                            <div class="mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Payment ID:</strong> {{ $selectedPayment->id }}</p>
                                        <p class="mb-1"><strong>Total Amount:</strong> Rs. {{ number_format($selectedPayment->amount, 2) }}</p>
                                        <p class="mb-1"><strong>Method:</strong> {{ ucfirst($selectedPayment->payment_method) }}</p>
                                        <p class="mb-1"><strong>Customer:</strong> {{ $selectedPayment->customer->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Payment Date:</strong> {{ $selectedPayment->payment_date ? \Carbon\Carbon::parse($selectedPayment->payment_date)->format('d/m/Y H:i') : 'N/A' }}</p>
                                        <p class="mb-1"><strong>Status:</strong> 
                                            @php
                                                $statusBadge = match($selectedPayment->status) {
                                                    'pending' => ['class' => 'bg-warning', 'text' => 'Pending Approval'],
                                                    'approved' => ['class' => 'bg-success', 'text' => 'Approved'],
                                                    'rejected' => ['class' => 'bg-danger', 'text' => 'Rejected'],
                                                    'paid' => ['class' => 'bg-success', 'text' => 'Paid'],
                                                    default => ['class' => 'bg-secondary', 'text' => 'Unknown']
                                                };
                                            @endphp
                                            <span class="badge {{ $statusBadge['class'] }}">{{ $statusBadge['text'] }}</span>
                                        </p>
                                        <p class="mb-1"><strong>Created By:</strong> {{ $selectedPayment->creator->name ?? 'System' }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <h6 class="mb-3">Allocated Invoices</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Invoice Number</th>
                                            <th>Sale Date</th>
                                            <th class="text-end">Allocated Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($selectedPayment->allocations as $allocation)
                                            @php $sale = \App\Models\Sale::find($allocation->sale_id); @endphp
                                            <tr>
                                                <td>{{ $sale->invoice_number ?? 'N/A' }}</td>
                                                <td>{{ $sale ? $sale->created_at->format('d/m/Y') : 'N/A' }}</td>
                                                <td class="text-end">Rs. {{ number_format($allocation->allocated_amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="p-4 text-center">
                            <i class="bi bi-exclamation-triangle display-4 text-muted mb-3"></i>
                            <h5>No Invoice Details Available</h5>
                            <p class="text-muted">This payment record doesn't have associated sale information.</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-light border-top-0 py-3">
                    @if($selectedPayment->status === 'pending')
                        <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm" wire:click="approvePayment({{ $selectedPayment->id }})">
                            <i class="bi bi-check-circle-fill me-1"></i> Approve Payment
                        </button>
                        <button type="button" class="btn btn-danger rounded-pill px-4 shadow-sm" wire:click="rejectPayment({{ $selectedPayment->id }})">
                            <i class="bi bi-x-circle-fill me-1"></i> Reject Payment
                        </button>
                    @endif
                    <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeInvoiceModal">
                        <i class="bi bi-x"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
