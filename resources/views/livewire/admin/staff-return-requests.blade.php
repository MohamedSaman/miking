<div>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">
                <i class="bi bi-arrow-return-left text-primary me-2"></i>
                Staff Product Return Requests
            </h2>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" 
                                class="form-control border-start-0 ps-0" 
                                wire:model.live.debounce.300ms="search"
                                placeholder="Search by staff name or product...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" wire:model.live="perPage">
                            <option value="12">12 per page</option>
                            <option value="24">24 per page</option>
                            <option value="48">48 per page</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>DATE</th>
                                <th>STAFF</th>
                                <th>PRODUCT</th>
                                <th class="text-center">REQUESTED QTY</th>
                                <th class="text-center">STATUS</th>
                                <th>PROCESSED BY</th>
                                <th class="text-end">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($returnRequests as $request)
                            <tr>
                                <td>{{ $request->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $request->staff->name }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $request->product->name }}</div>
                                    <small class="text-muted">{{ $request->product->code }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-pill fs-6">{{ $request->return_quantity }}</span>
                                </td>
                                <td class="text-center">
                                    @if($request->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($request->status === 'processed')
                                        <span class="badge bg-success">Processed</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    @if($request->processed_by)
                                        <div>{{ $request->processor->name }}</div>
                                        <small class="text-muted">{{ $request->processed_at->format('M d, Y') }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($request->status === 'pending')
                                    <button wire:click="openReentry({{ $request->id }})" class="btn btn-sm btn-primary">
                                        <i class="bi bi-box-arrow-in-down"></i> Re-entry
                                    </button>
                                    <button wire:click="rejectRequest({{ $request->id }})" 
                                        wire:confirm="Are you sure you want to reject this request? The quantity will be returned to the staff's stock."
                                        class="btn btn-sm btn-outline-danger ms-1">
                                        Reject
                                    </button>
                                    @else
                                    <span class="text-muted small">No actions</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                    <p class="text-muted mb-0">No return requests found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($returnRequests->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $returnRequests->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Re-entry Modal --}}
    @if($showSidePanel && $selectedReturn)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Process Stock Re-entry</h5>
                    <button type="button" class="btn-close" wire:click="closeSidePanel"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2">
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            Staff returned <strong>{{ $selectedReturn->return_quantity }}</strong> units of <strong>{{ $selectedReturn->product->name }}</strong>.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Restock Quantity (Back to Available)</label>
                        <input type="number" class="form-control" wire:model.live="restockQty" max="{{ $selectedReturn->return_quantity }}" min="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-danger">Damaged Quantity (To Damage Stock)</label>
                        <input type="number" class="form-control" wire:model.live="damagedQty" max="{{ (int)$selectedReturn->return_quantity - (int)$restockQty }}" min="0">
                    </div>

                    <div class="bg-light p-3 rounded">
                        <div class="d-flex justify-content-between">
                            <span>Total Accounted:</span>
                            <span class="fw-bold">{{ (int)$restockQty + (int)$damagedQty }} / {{ $selectedReturn->return_quantity }}</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="closeSidePanel">Cancel</button>
                    <button type="button" class="btn btn-primary px-4" wire:click="submitReentry">
                        Confirm Re-entry
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
