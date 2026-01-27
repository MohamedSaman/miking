<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-list-check text-primary me-2"></i>Returns History
            </h3>
            <p class="text-muted mb-0">Track and manage your submitted return requests</p>
        </div>
        <div>
            <a href="{{ route('staff.return-add') }}" class="btn btn-primary shadow-sm rounded-pill px-4">
                <i class="bi bi-plus-lg me-2"></i> New Return Request
            </a>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" 
                            class="form-control border-start-0" 
                            wire:model.live="returnSearch" 
                            placeholder="Search by product, invoice # or customer...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="all">All Statuses</option>
                        <option value="pending">Pending Approval</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center justify-content-md-end gap-2 text-muted small">
                        <span>Show</span>
                        <select wire:model.live="perPage" class="form-select form-select-sm w-auto">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span>entries</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Returns Table -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th class="ps-4 py-3">Date</th>
                            <th>Invoice #</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Amount</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($returns as $return)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $return->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $return->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                @if($return->sale)
                                    <span class="badge bg-light text-primary border border-primary-subtle">#{{ $return->sale->invoice_number }}</span>
                                @else
                                    <span class="text-muted italic">Manual</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $return->product->name }}</div>
                                <small class="text-muted text-uppercase small">{{ $return->product->code }}</small>
                            </td>
                            <td>
                                <span class="fw-bold fs-6">{{ $return->quantity }}</span>
                                @if($return->is_damaged)
                                    <span class="badge bg-danger ms-1" title="Product is damaged" style="font-size: 10px;">DAMAGED</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-primary">Rs.{{ number_format($return->total_amount, 2) }}</div>
                                <small class="text-muted small">Rs.{{ number_format($return->unit_price, 2) }} / unit</small>
                            </td>
                            <td class="text-center">
                                @if($return->status === 'approved')
                                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill border border-success-subtle">
                                        <i class="bi bi-check-circle-fill me-1"></i> Approved
                                    </span>
                                @elseif($return->status === 'rejected')
                                    <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill border border-danger-subtle">
                                        <i class="bi bi-x-circle-fill me-1"></i> Rejected
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill border border-warning-subtle">
                                        <i class="bi bi-clock-fill me-1"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm box-shadow-none">
                                    <button class="btn btn-light border" wire:click="showReturnDetails({{ $return->id }})">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @if($return->status === 'pending')
                                    <button class="btn btn-light border text-danger" 
                                        onclick="confirm('Are you sure you want to delete this pending request?') || event.stopImmediatePropagation()"
                                        wire:click="deleteReturn({{ $return->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="py-5">
                                    <i class="bi bi-inbox display-1 text-muted opacity-25"></i>
                                    <h5 class="mt-3 text-muted">No return requests found</h5>
                                    @if($returnSearch || $statusFilter !== 'all')
                                        <button class="btn btn-link" wire:click="$set('returnSearch', ''); $set('statusFilter', 'all')">Clear active searches</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($returns->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $returns->links() }}
        </div>
        @endif
    </div>

    <!-- Details Modal -->
    <div wire:ignore.self class="modal fade" id="returnDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header {{ $selectedReturn?->status === 'approved' ? 'bg-success' : ($selectedReturn?->status === 'rejected' ? 'bg-danger' : 'bg-primary') }} text-white border-bottom-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-info-circle me-2"></i> Return Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="closeModal"></button>
                </div>
                <div class="modal-body p-4">
                    @if($selectedReturn)
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h4 class="fw-bold mb-0 text-dark">{{ $selectedReturn->product->name }}</h4>
                                    <small class="text-muted text-uppercase tracking-wider">{{ $selectedReturn->product->barcode }}</small>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted small uppercase fw-bold mb-1">Status</div>
                                    @if($selectedReturn->status === 'approved')
                                        <span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Approved</span>
                                    @elseif($selectedReturn->status === 'rejected')
                                        <span class="text-danger fw-bold"><i class="bi bi-x-circle-fill"></i> Rejected</span>
                                    @else
                                        <span class="text-warning fw-bold"><i class="bi bi-clock-fill"></i> Pending</span>
                                    @endif
                                </div>
                            </div>

                            <div class="row g-3 py-3 border-top border-bottom">
                                <div class="col-6">
                                    <label class="text-muted small uppercase fw-bold mb-1 d-block">Quantity</label>
                                    <span class="fs-5 fw-bold">{{ $selectedReturn->return_quantity }} Units</span>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small uppercase fw-bold mb-1 d-block">Return Value</label>
                                    <span class="fs-5 fw-bold text-primary">Rs.{{ number_format($selectedReturn->total_amount, 2) }}</span>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small uppercase fw-bold mb-1 d-block">Customer</label>
                                    <span class="fw-semibold">{{ $selectedReturn->sale->customer->name ?? 'Walk-in' }}</span>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small uppercase fw-bold mb-1 d-block">Invoice #</label>
                                    <span class="fw-semibold">{{ $selectedReturn->sale ? '#' . $selectedReturn->sale->invoice_number : 'Manual' }}</span>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="text-muted small uppercase fw-bold mb-1 d-block">Reason for Return</label>
                                <div class="p-3 rounded-3 bg-light border fst-italic">
                                    "{{ $selectedReturn->reason }}"
                                </div>
                            </div>

                            @if($selectedReturn->notes)
                            <div class="mt-3">
                                <label class="text-muted small uppercase fw-bold mb-1 d-block">Additional Notes</label>
                                <p class="text-dark small mb-0">{{ $selectedReturn->notes }}</p>
                            </div>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4 rounded-pill border" data-bs-dismiss="modal" wire:click="closeModal">Close</button>
                    @if($selectedReturn?->status === 'pending')
                    <button type="button" class="btn btn-outline-danger px-4 rounded-pill border" 
                        onclick="confirm('Are you sure?') || event.stopImmediatePropagation()"
                        wire:click="deleteReturn({{ $selectedReturn->id }})"
                        data-bs-dismiss="modal">Delete Request</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

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
    });
</script>
@endpush
