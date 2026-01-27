<div>
    <div class="container-fluid py-4">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.staff-allocation-list') }}" class="text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Staff Allocations
                    </a>
                </li>
                <li class="breadcrumb-item active">{{ $staffName }}</li>
            </ol>
        </nav>

        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">
                <i class="bi bi-box-seam text-primary me-2"></i>
                Allocated Products - {{ $staffName }}
            </h2>
            <a href="{{ route('admin.staff-allocation-list') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
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
                                wire:model.live.debounce.300ms="search"
                                placeholder="Search by product name or code...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" wire:model.live="statusFilter">
                            <option value="all">All Status</option>
                            <option value="allocated">Allocated</option>
                            <option value="sold">Sold</option>
                            <option value="returned">Returned</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" wire:model.live="perPage">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Products Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>PRODUCT</th>
                                <th class="text-center">ALLOCATED QTY</th>
                                <th class="text-center">SOLD QTY</th>
                                <th class="text-center">AVAILABLE QTY</th>
                                <th class="text-end">UNIT PRICE</th>
                                <th class="text-end">TOTAL VALUE</th>
                                <th class="text-center">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allocatedProducts as $index => $item)
                            @php
                                $availableQty = $item->quantity - $item->sold_quantity;
                            @endphp
                            <tr wire:key="product-{{ $item->id }}">
                                <td>{{ $allocatedProducts->firstItem() + $index }}</td>
                                <td>
                                    <div>
                                        <div class="fw-semibold">{{ $item->product->name ?? 'N/A' }}</div>
                                        <small class="text-muted">Code: {{ $item->product->code ?? 'N/A' }}</small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $item->quantity }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ $item->sold_quantity }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $availableQty }}</span>
                                </td>
                                <td class="text-end">Rs. {{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">
                                    <strong class="text-primary">Rs. {{ number_format($item->total_value, 2) }}</strong>
                                </td>
                                <td class="text-center">
                                    @if($item->status === 'allocated')
                                        <span class="badge bg-primary">Allocated</span>
                                    @elseif($item->status === 'sold')
                                        <span class="badge bg-success">Sold</span>
                                    @elseif($item->status === 'returned')
                                        <span class="badge bg-secondary">Returned</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($item->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                    <p class="text-muted mb-1 fw-semibold">No products found</p>
                                    <small class="text-muted">No products have been allocated to this staff member yet</small>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($allocatedProducts->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $allocatedProducts->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
