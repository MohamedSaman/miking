<div>
    <div class="container-fluid py-4">
        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">
                <i class="bi bi-person-check text-primary me-2"></i>
                Allocated Products
            </h2>
            <button type="button" 
                onclick="confirmReturn()"
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
                                <th class="text-end">UNIT PRICE</th>
                                <th class="text-end">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allocatedProducts as $item)
                            @php
                                $availableQty = $item->quantity - $item->sold_quantity;
                            @endphp
                            <tr wire:key="staff-prod-{{ $item->id }}">
                                <td class="text-center">
                                    <input type="checkbox" 
                                        class="form-check-input" 
                                        wire:click="toggleProduct({{ $item->id }})"
                                        @if(in_array($item->id, $selectedProducts)) checked @endif>
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
                                <td class="text-end fw-bold text-primary">
                                    Rs. {{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="text-end">
                                    <button wire:click="toggleProduct({{ $item->id }})" 
                                        class="btn btn-sm {{ in_array($item->id, $selectedProducts) ? 'btn-danger' : 'btn-outline-primary' }}">
                                        {{ in_array($item->id, $selectedProducts) ? 'Deselect' : 'Select for Return' }}
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
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

    <script>
        function confirmReturn() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to return the selected products to the admin and warehouse!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, return stock!'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.returnProducts();
                }
            })
        }
    </script>
</div>
