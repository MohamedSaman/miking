<div>
    <div class="container-fluid py-4">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">
                <i class="bi bi-person-badge text-primary me-2"></i>
                Customer Returns List
            </h2>
        </div>

        {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" 
                                class="form-control border-start-0 ps-0" 
                                wire:model.live.debounce.300ms="staffReturnSearch"
                                placeholder="Search by Invoice, Product, or Staff...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" wire:model.live="statusFilter">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" wire:model.live="staffFilter">
                            <option value="">All Staff</option>
                            @foreach($allStaff as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <select class="form-select" wire:model.live="perPage">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>DATE</th>
                                <th>INVOICE #</th>
                                <th>STAFF</th>
                                <th>PRODUCT</th>
                                <th class="text-center">QTY</th>
                                <th class="text-end">TOTAL</th>
                                <th class="text-center">STATUS</th>
                                <th class="text-end">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffReturns as $return)
                            <tr>
                                <td>{{ $return->created_at->format('M d, Y') }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $return->sale->invoice_number ?? 'N/A' }}</span>
                                </td>
                                <td>{{ $return->staff->name }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $return->product->name }}</div>
                                    <small class="text-muted">{{ $return->product->code }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $return->quantity }}</span>
                                </td>
                                <td class="text-end fw-bold">
                                    Rs. {{ number_format($return->total_amount, 2) }}
                                </td>
                                <td class="text-center">
                                    @if($return->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($return->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button wire:click="showStaffReturnDetails({{ $return->id }})" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @if($return->status === 'pending')
                                            <button wire:click="approveStaffReturn({{ $return->id }})" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button wire:click="rejectStaffReturn({{ $return->id }})" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        @endif
                                        <button wire:click="deleteStaffReturn({{ $return->id }})" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">No staff returns found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($staffReturns->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $staffReturns->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Details Modal --}}
    <div class="modal fade" id="staffReturnDetailsModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Return Details</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                @if($selectedStaffReturn)
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="text-muted small">Customer</label>
                        <div class="fw-bold">{{ $selectedStaffReturn->customer->name ?? 'Walk-in' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Reason</label>
                        <div class="p-2 bg-light rounded">{{ $selectedStaffReturn->reason ?? 'Not specified' }}</div>
                    </div>
                    @if($selectedStaffReturn->notes)
                    <div class="mb-3">
                        <label class="text-muted small">Notes</label>
                        <div class="p-2 bg-light rounded small">{{ $selectedStaffReturn->notes }}</div>
                    </div>
                    @endif
                </div>
                @endif
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="closeModal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Approve Confirmation Modal --}}
    <div class="modal fade" id="approveStaffReturnModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">Approve Return?</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body py-4 text-center">
                    <i class="bi bi-check-circle text-success display-4 mb-3"></i>
                    <p>Are you sure you want to approve this return?</p>
                    <p class="small text-muted">This will restore the product stock and adjust the customer balance.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="closeModal">Cancel</button>
                    <button type="button" class="btn btn-success px-4" wire:click="confirmApproveStaffReturn">Yes, Approve</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Confirmation Modal --}}
    <div class="modal fade" id="rejectStaffReturnModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content border-warning">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold">Reject Return?</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body py-4 text-center">
                    <i class="bi bi-exclamation-triangle text-warning display-4 mb-3"></i>
                    <p>Are you sure you want to reject this return?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="closeModal">Cancel</button>
                    <button type="button" class="btn btn-warning px-4" wire:click="confirmRejectStaffReturn">Yes, Reject</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteStaffReturnModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">Delete Record?</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body py-4 text-center">
                    <i class="bi bi-trash text-danger display-4 mb-3"></i>
                    <p>Are you sure you want to delete this return record?</p>
                    <p class="small text-danger">Warning: This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="closeModal">Cancel</button>
                    <button type="button" class="btn btn-danger px-4" wire:click="confirmDeleteStaffReturn">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('showModal', event => {
            var modalId = event.detail;
            var myModal = new bootstrap.Modal(document.getElementById(modalId));
            myModal.show();
        });
        window.addEventListener('hideModal', event => {
            var modalId = event.detail;
            var modalEl = document.getElementById(modalId);
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        });
    </script>
</div>
