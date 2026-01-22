<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-arrow-return-left text-primary me-2"></i> Customer Returns Request
            </h3>
            <p class="text-muted mb-0">Request product returns from customers for admin approval</p>
        </div>
        <div>
            <a href="{{ route('staff.return-list') }}" class="btn btn-outline-primary shadow-sm rounded-pill px-4">
                <i class="bi bi-list-ul me-2"></i> View Previous Returns
            </a>
        </div>
    </div>

    <!-- Customer Search and Invoice Selection -->
    <div class="row mb-4">
        <!-- Customer Search -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-person-search text-primary me-2"></i> Customer / Invoice Search
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3 position-relative">
                        <label class="form-label fw-semibold">Search by Name, Phone or Invoice #</label>
                        <div class="input-group search-box">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" 
                                class="form-control border-start-0 ps-0" 
                                wire:model.live="searchCustomer" 
                                placeholder="Start typing to search...">
                        </div>

                        {{-- Search Results Dropdown --}}
                        @if($searchCustomer && (count($customers) > 0 || count($customerInvoices) > 0))
                        <div class="position-absolute w-100 mt-1 shadow-lg bg-white rounded border overflow-hidden z-3" style="max-height: 300px; overflow-y: auto;">
                            {{-- Customers --}}
                            @if(count($customers) > 0)
                                <div class="bg-light px-3 py-2 small fw-bold text-uppercase text-muted border-bottom">Customers</div>
                                @foreach($customers as $customer)
                                <button type="button" class="list-group-item list-group-item-action border-0 px-3 py-2 border-bottom"
                                    wire:click="selectCustomer({{ $customer->id }})">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary-100 text-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div class="flex-grow-1 text-start">
                                            <div class="fw-semibold">{{ $customer->name }}</div>
                                            <small class="text-muted">{{ $customer->phone }}</small>
                                        </div>
                                    </div>
                                </button>
                                @endforeach
                            @endif

                            {{-- Invoices --}}
                            @if(count($customerInvoices) > 0)
                                <div class="bg-light px-3 py-2 small fw-bold text-uppercase text-muted border-bottom">Invoices</div>
                                @foreach($customerInvoices as $invoice)
                                <button type="button" class="list-group-item list-group-item-action border-0 px-3 py-2 border-bottom"
                                    wire:click="selectInvoiceForReturn({{ $invoice->id }})">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-info-100 text-info rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="bi bi-receipt"></i>
                                        </div>
                                        <div class="flex-grow-1 text-start">
                                            <div class="fw-semibold">#{{ $invoice->invoice_number }}</div>
                                            <small class="text-muted">{{ $invoice->created_at->format('M d, Y') }} | Rs.{{ number_format($invoice->total_amount, 2) }}</small>
                                        </div>
                                    </div>
                                </button>
                                @endforeach
                            @endif
                        </div>
                        @endif
                    </div>

                    @if($selectedCustomer)
                    <div class="mt-4 p-3 rounded-3 border border-primary bg-primary bg-opacity-10">
                        <div class="d-flex align-items-center">
                            <div class="avatar-md bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-person-check fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">{{ $selectedCustomer->name }}</h6>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-phone me-1"></i> {{ $selectedCustomer->phone }} | 
                                    <i class="bi bi-geo-alt me-1"></i> {{ $selectedCustomer->address ?? 'No address' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-person-circle display-4 mb-3 opacity-25"></i>
                        <p>Search for a customer or invoice to begin</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Invoices for Selected Customer -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-clock-history text-muted me-2"></i> Recent Invoices
                    </h5>
                    @if($selectedCustomer)
                    <button class="btn btn-sm btn-light border" wire:click="loadCustomerInvoices">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($selectedCustomer && count($customerInvoices) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small">
                                <tr>
                                    <th class="ps-4 py-3">Invoice #</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customerInvoices as $invoice)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold text-dark">#{{ $invoice->invoice_number }}</span>
                                    </td>
                                    <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <span class="fw-bold text-primary">Rs.{{ number_format($invoice->total_amount, 2) }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-primary px-3" wire:click="selectInvoiceForReturn({{ $invoice->id }})">
                                                Select
                                            </button>
                                            <button class="btn btn-light border px-2" wire:click="viewInvoice({{ $invoice->id }})">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5 text-muted opacity-50">
                        <i class="bi bi-receipt-cutoff display-4 mb-3"></i>
                        <p>No invoices available to display</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($showReturnSection && $selectedInvoice)
    <!-- Return Items Selection -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card shadow-lg border-0 overflow-hidden">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-basket me-2"></i> Select Items to Return (Invoice #{{ $selectedInvoice->invoice_number }})
                        </h5>
                        <div class="small opacity-75">
                            Created: {{ $selectedInvoice->created_at->format('M d, Y H:i') }}
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="bg-light text-muted small uppercase">
                                <tr>
                                    <th class="ps-4 py-3">Product</th>
                                    <th class="text-center">Orig. Qty</th>
                                    <th class="text-center" style="width: 120px;">Return Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Is Damaged?</th>
                                    <th class="pe-4">Reason for Return</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($returnItems as $index => $item)
                                <tr class="{{ ($item['return_qty'] ?? 0) > 0 ? 'bg-primary bg-opacity-10' : '' }}">
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $item['name'] }}</div>
                                        <div class="small text-muted">{{ $item['code'] }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-secondary bg-opacity-25 text-dark">{{ $item['original_qty'] }}</span>
                                    </td>
                                    <td class="text-center px-1">
                                        <div class="input-group input-group-sm">
                                            <input type="number" 
                                                class="form-control text-center fw-bold border-primary shadow-sm" 
                                                wire:model.live="returnItems.{{ $index }}.return_qty"
                                                min="0" 
                                                max="{{ $item['original_qty'] }}"
                                                placeholder="0">
                                        </div>
                                    </td>
                                    <td>Rs.{{ number_format($item['unit_price'], 2) }}</td>
                                    <td>
                                        <span class="fw-bold text-primary">Rs.{{ number_format(floatval($item['return_qty'] ?? 0) * floatval($item['unit_price'] ?? 0), 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" wire:model.live="returnItems.{{ $index }}.is_damaged">
                                        </div>
                                    </td>
                                    <td class="pe-4">
                                        <input type="text" 
                                            class="form-control form-control-sm" 
                                            placeholder="Reason..." 
                                            wire:model.live="returnItems.{{ $index }}.reason">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 bg-light border-top">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="alert alert-info border-0 shadow-sm mb-md-0 d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                                    <div>
                                        <p class="mb-0 small fw-bold text-primary-dark">Note for Stock Adjustment</p>
                                        <p class="mb-0 small text-muted">Stock will be adjusted automatically ONLY after Admin approval. Damaged items will be excluded from available stock.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="mb-3">
                                    <span class="text-muted small me-2 uppercase fw-bold">Grand Total Value:</span>
                                    <span class="fs-3 fw-bold text-primary">Rs.{{ number_format($totalReturnValue, 2) }}</span>
                                </div>
                                <button type="button" 
                                    class="btn btn-primary btn-lg rounded-pill px-5 shadow"
                                    wire:click="processReturn"
                                    wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="processReturn">
                                        <i class="bi bi-send-check me-2"></i> Submit Return Request
                                    </span>
                                    <span wire:loading wire:target="processReturn">
                                        <span class="spinner-border spinner-border-sm me-2"></span> Submitting...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Confirmation Modal -->
    <div wire:ignore.self class="modal fade" id="confirmReturnModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark border-bottom-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-exclamation-triangle me-2"></i> Confirm Return Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3">Are you sure you want to submit this return request?</p>
                    <div class="alert alert-info border-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Total Return Value:</strong> Rs.{{ number_format($totalReturnValue, 2) }}
                    </div>
                    <p class="small text-muted mb-0">
                        This request will be sent to admin for approval. Stock will be adjusted only after approval.
                    </p>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light border px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success px-4 rounded-pill" wire:click="confirmReturn" data-bs-dismiss="modal">
                        <i class="bi bi-check-circle me-1"></i> Confirm & Submit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Modal -->
    <div wire:ignore.self class="modal fade" id="invoiceModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-bottom-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-receipt me-2"></i> Invoice Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="closeModal"></button>
                </div>
                <div class="modal-body p-4">
                    @if($invoiceModalData)
                        <div class="row mb-4 bg-light p-3 rounded-4 mx-0">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <div class="text-muted small mb-1">Invoice Number</div>
                                <div class="fw-bold fs-5">#{{ $invoiceModalData['invoice_number'] }}</div>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <div class="text-muted small mb-1">Date & Time</div>
                                <div class="fw-bold">{{ $invoiceModalData['date'] }}</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="fw-bold"><i class="bi bi-list-stars me-2"></i> Items List</h6>
                        </div>
                        <div class="table-responsive rounded-3 border overflow-hidden">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-gray-50 text-muted small">
                                    <tr>
                                        <th class="ps-3 py-3">Product</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end pe-3">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoiceModalData['items'] as $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold">{{ $item['product_name'] }}</div>
                                            <small class="text-muted">{{ $item['product_code'] }}</small>
                                        </td>
                                        <td class="text-center">{{ $item['quantity'] }}</td>
                                        <td class="text-end text-primary">Rs.{{ number_format($item['unit_price'], 2) }}</td>
                                        <td class="text-end fw-bold pe-3">Rs.{{ number_format($item['total'], 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light fw-bold">
                                    <tr>
                                        <td colspan="3" class="text-end py-3">Gross Total:</td>
                                        <td class="text-end pe-3 py-3 text-primary fs-5">Rs.{{ number_format($invoiceModalData['total_amount'], 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light border px-4 rounded-pill" data-bs-dismiss="modal" wire:click="closeModal">Close</button>
                    <button type="button" class="btn btn-primary px-4 rounded-pill">Print Invoice</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .bg-primary-100 { background-color: #cfe2ff; }
    .bg-info-100 { background-color: #cff4fc; }
    .search-box:focus-within {
        box-shadow: 0 0 0 0.25rem rgba(42, 131, 223, 0.25);
        border-radius: 0.375rem;
    }
    .card { transition: transform 0.2s; }
    /*.card:hover { transform: translateY(-5px); }*/
    .z-3 { z-index: 1060; }
    .avatar-md { width: 48px; height: 48px; }
    .list-group-item:hover { background-color: #f8f9fa; }
    .table th { background-color: #f8f9fa; border-top: none; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('showModal', (modalId) => {
            const el = document.getElementById(modalId);
            if (el) {
                const modal = new bootstrap.Modal(el);
                modal.show();
            }
        });

        Livewire.on('show-return-modal', () => {
            const el = document.getElementById('confirmReturnModal');
            if (el) {
                const modal = new bootstrap.Modal(el);
                modal.show();
            }
        });

        Livewire.on('close-return-modal', () => {
            const el = document.getElementById('confirmReturnModal');
            if (el) {
                const modal = bootstrap.Modal.getInstance(el);
                if (modal) {
                    modal.hide();
                }
            }
        });

        Livewire.on('reload-page', () => {
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        });

        Livewire.on('showToast', (e) => {
            Swal.fire({
                title: e.type.charAt(0).toUpperCase() + e.type.slice(1),
                text: e.message,
                icon: e.type,
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        });

        Livewire.on('alert', (data) => {
            Swal.fire({
                title: 'Notice',
                text: data.message,
                icon: 'info',
                confirmButtonText: 'OK'
            });
        });
    });
</script>
@endpush
