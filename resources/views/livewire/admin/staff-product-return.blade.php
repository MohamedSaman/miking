<div class="container-fluid py-4 min-vh-100" style="background-color: #f4f7f6;">
    {{-- Breadcrumb and Back --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.staff-allocation-list') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Staff Allocations</a></li>
                <li class="breadcrumb-item active">Stock Re-entry for {{ $staffName }}</li>
            </ol>
        </nav>
        <a href="{{ route('admin.staff-allocation-list') }}" class="btn btn-sm btn-outline-secondary px-3">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <h2 class="fw-bold mb-4">Stock Re-entry for {{ $staffName }}</h2>

    {{-- Search Bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-transparent ps-3">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-0 shadow-none ps-0" 
                               wire:model.live.debounce.300ms="search" 
                               placeholder="Search watches...">
                    </div>
                </div>
                <div class="col-auto">
                    <select class="form-select border-0 shadow-none" wire:model.live="perPage">
                        <option value="12">12 per page</option>
                        <option value="24">24 per page</option>
                        <option value="48">48 per page</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Products Grid --}}
        <div class="{{ $showSidePanel ? 'col-lg-8' : 'col-12' }} transition-all">
            <div class="row g-4">
                @forelse($pendingReturns as $item)
                    <div class="col-md-6 col-lg-{{ $showSidePanel ? '4' : '3' }}">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-body">
                                <h6 class="fw-bold mb-1 text-truncate">{{ $item->product->name }}</h6>
                                <p class="text-muted small mb-3 text-uppercase">Code: {{ $item->product->code }}</p>
                                
                                <div class="mb-4">
                                    <span class="text-muted small">Return Quantity:</span>
                                    <span class="fw-bold ms-1" style="font-size: 1.1rem;">{{ $item->return_quantity }}</span>
                                </div>

                                <button wire:click="openReentry({{ $item->id }})" 
                                        class="btn btn-outline-primary w-100 rounded-3 py-2 fw-semibold">
                                    Re-entry
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="py-5 bg-white rounded-4 shadow-sm">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <h4 class="mt-3 text-muted">No pending returns for this staff</h4>
                        </div>
                    </div>
                @endforelse
            </div>
            
            <div class="mt-4">
                {{ $pendingReturns->links() }}
            </div>
        </div>

        {{-- Side Panel --}}
        @if($showSidePanel)
        <div class="col-lg-4">
            <div class="card border-0 shadow rounded-4 sticky-top" style="top: 1rem; z-index: 100;">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Edit Stock</h5>
                    <button type="button" class="btn-close shadow-none" wire:click="closeSidePanel"></button>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <h6 class="fw-bold mb-1">{{ $selectedReturn->product->name }}</h6>
                        <small class="text-muted text-uppercase">Code: {{ $selectedReturn->product->code }}</small>
                    </div>

                    <div class="alert alert-info border-0 rounded-4 p-4 d-flex justify-content-between align-items-center mb-4" style="background-color: #e0f7fa;">
                        <span class="fw-semibold text-info-emphasis fs-5">Available Quantity:</span>
                        <span class="display-6 fw-bold text-info-emphasis">{{ $selectedReturn->return_quantity }}</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small text-uppercase">Damaged Quantity</label>
                        <input type="number" class="form-control form-control-lg bg-light border-0 rounded-3 shadow-none" 
                               wire:model.live="damagedQty" placeholder="0">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted small text-uppercase">Restock Quantity</label>
                        <input type="number" class="form-control form-control-lg bg-light border-0 rounded-3 shadow-none" 
                               wire:model.live="restockQty" placeholder="0">
                    </div>

                    <div class="bg-light rounded-4 p-4 mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Damaged:</span>
                            <span class="fw-bold text-danger">{{ (int)$damagedQty }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <span class="text-muted">Restock:</span>
                            <span class="fw-bold text-success">{{ (int)$restockQty }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total Return:</span>
                            <span class="fw-bold fs-5">{{ (int)$damagedQty + (int)$restockQty }}</span>
                        </div>
                    </div>

                    <button wire:click="submitReentry" class="btn btn-success w-100 rounded-3 py-3 fw-bold shadow-sm">
                        <i class="bi bi-check-circle me-1"></i> Submit
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
    <style>
        .transition-all {
            transition: all 0.3s ease-in-out;
        }
        .form-control:focus {
            background-color: #fff !important;
            border: 1px solid var(--primary) !important;
        }
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
    </style>
</div>
