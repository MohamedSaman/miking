<div>
    <div class="container-fluid py-4">
        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">
                <i class="bi bi-person-check text-primary me-2"></i>
                Allocated Products
            </h2>
            <button type="button" 
                wire:click="openReviewModal"
                class="btn btn-primary" 
                @if(empty($selectedProducts)) disabled @endif>
                <i class="bi bi-arrow-return-left me-1"></i> Return Selected to Admin
            </button>
        </div>

        {{-- Filters & Search --}}
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
                                placeholder="Search by product name or code...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" wire:model.live="perPage">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Products Grid/Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">
                                    <i class="bi bi-check2-square"></i>
                                </th>
                                <th>PRODUCT</th>
                                <th class="text-center">ALLOCATED QTY</th>
                                <th class="text-center">SOLD QTY</th>
                                <th class="text-center">AVAILABLE</th>
                                <th class="text-center" style="width: 120px;">RETURN QTY</th>
                                <th class="text-end">UNIT PRICE</th>
                                <th class="text-end">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allocatedProducts as $item)
                            @php
                                $availableQty = $item->quantity - $item->sold_quantity;
                                $isSelected = in_array($item->id, $selectedProducts);
                            @endphp
                            <tr wire:key="staff-prod-{{ $item->id }}" class="{{ $isSelected ? 'table-primary bg-opacity-10' : '' }}">
                                <td class="text-center">
                                    <input type="checkbox" 
                                        class="form-check-input" 
                                        wire:click="toggleProduct({{ $item->id }})"
                                        @if($isSelected) checked @endif>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $item->product->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $item->product->code ?? 'N/A' }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $item->quantity }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-soft text-success">{{ $item->sold_quantity }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info-soft text-info fs-6">{{ $availableQty }}</span>
                                </td>
                                 <td class="text-center">
                                     @if($isSelected)
                                         @php $exceeds = ($returnQtys[$item->id] ?? 0) > $availableQty; @endphp
                                         <div class="d-flex flex-column align-items-center">
                                             <input type="number" 
                                                 wire:model.live.debounce.300ms="returnQtys.{{ $item->id }}" 
                                                 class="form-control form-control-sm text-center {{ $exceeds ? 'is-invalid border-danger' : '' }}" 
                                                 style="max-width: 80px;"
                                                 min="1" 
                                                 max="{{ $availableQty }}">
                                             @if($exceeds)
                                                 <small class="text-danger mt-1" style="font-size: 10px; font-weight: bold;">Exceeds Max!</small>
                                             @else
                                                 <small class="text-muted mt-1" style="font-size: 10px;">Max: {{ $availableQty }}</small>
                                             @endif
                                         </div>
                                     @else
                                         <span class="text-muted small">-</span>
                                     @endif
                                 </td>
                                <td class="text-end fw-bold text-primary">
                                    Rs. {{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="text-end">
                                    <button wire:click="toggleProduct({{ $item->id }})" 
                                        class="btn btn-sm {{ $isSelected ? 'btn-danger' : 'btn-outline-primary' }}">
                                        {{ $isSelected ? 'Deselect' : 'Select for Return' }}
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-box-seam display-1 d-block mb-3"></i>
                                    <p class="mb-0">No allocated products with available stock found.</p>
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

    {{-- Review Return Modal --}}
    @if($showReviewModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-card-checklist me-2"></i> Review Return Request
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeReviewModal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted mb-4">Please review the products and quantities you are about to return to the admin.</p>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Available Stock</th>
                                    <th class="text-center" style="width: 150px;">Return Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($returnReviewItems as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $item['name'] }}</div>
                                            <small class="text-muted">{{ $item['code'] }}</small>
                                        </td>
                                        <td class="text-center font-monospace">{{ $item['available_qty'] }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary fs-6 px-3 py-2">{{ $item['requested_qty'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info border-0 shadow-sm mt-3 mb-0">
                        <div class="d-flex">
                            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                            <div>
                                <strong>Important Note:</strong> Once confirmed, these quantities will be deducted from your allocated stock and sent to the admin for re-entry into the main inventory.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-secondary px-4" wire:click="closeReviewModal">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary px-4" wire:click="returnProducts">
                        <i class="bi bi-check2-circle me-1"></i> Confirm & Submit Return
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
