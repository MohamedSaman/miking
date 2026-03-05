<div
    x-data="{
        salesModalOpen: false,
        init() {
            $wire.on('open-sales-modal', () => {
                this.salesModalOpen = true;
                setTimeout(() => {
                    const el = document.getElementById('salesTrackingModal');
                    if (el) { new bootstrap.Modal(el).show(); }
                }, 50);
            });
        }
    }"
>
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-clock-history text-success me-2"></i> Purchase History
            </h3>
            <p class="text-muted mb-0">Track all purchased items — cost price, quantity, date, and linked sales invoices</p>
        </div>
    </div>

    <div class="container-fluid p-0">

        {{-- Summary Cards --}}
        <div class="row mb-3 g-3">
            <div class="col-xl-4 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 bg-primary bg-opacity-10 p-3 me-3">
                            <i class="bi bi-truck text-primary fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small">Total Purchase Orders</p>
                            <h4 class="fw-bold mb-0">{{ number_format($totalOrders) }}</h4>
                            <small class="text-muted">In selected period</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-box-seam text-success fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small">Total Line Items</p>
                            <h4 class="fw-bold mb-0">{{ number_format($totalItems) }}</h4>
                            <small class="text-muted">Across all orders</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 bg-warning bg-opacity-10 p-3 me-3">
                            <i class="bi bi-currency-dollar text-warning fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small">Total Cost Value</p>
                            <h4 class="fw-bold mb-0">Rs. {{ number_format($totalCostValue, 2) }}</h4>
                            <small class="text-muted">Received × Unit Price</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    {{-- Search --}}
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-1">Search</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input
                                type="text"
                                wire:model.live.debounce.400ms="search"
                                class="form-control border-start-0 ps-0"
                                placeholder="Product, code, PO number…"
                            >
                        </div>
                    </div>

                    {{-- Supplier --}}
                    <div class="col-xl-2 col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-1">Supplier</label>
                        <select wire:model.live="supplierFilter" class="form-select">
                            <option value="">All Suppliers</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier['id'] }}">{{ $supplier['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="col-xl-2 col-md-4">
                        <label class="form-label small fw-semibold text-muted mb-1">Order Status</label>
                        <select wire:model.live="statusFilter" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="complete">Complete</option>
                            <option value="received">Received</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    {{-- Date From --}}
                    <div class="col-xl-2 col-md-4">
                        <label class="form-label small fw-semibold text-muted mb-1">From</label>
                        <input type="date" wire:model.live="dateFrom" class="form-control">
                    </div>

                    {{-- Date To --}}
                    <div class="col-xl-2 col-md-4">
                        <label class="form-label small fw-semibold text-muted mb-1">To</label>
                        <input type="date" wire:model.live="dateTo" class="form-control">
                    </div>

                    {{-- Per Page + Reset --}}
                    <div class="col-xl-1 col-md-4 d-flex gap-2">
                        <select wire:model.live="perPage" class="form-select">
                            <option value="10">10</option>
                            <option value="15" selected>15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <button wire:click="clearFilters" class="btn btn-outline-secondary" title="Reset filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Results info --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <small class="text-muted">
                Showing <strong>{{ $items->firstItem() ?? 0 }}</strong>–<strong>{{ $items->lastItem() ?? 0 }}</strong>
                of <strong>{{ $items->total() }}</strong> records
            </small>
            <div wire:loading class="spinner-border spinner-border-sm text-success" role="status">
                <span class="visually-hidden">Loading…</span>
            </div>
        </div>

        {{-- Main Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive" style="font-size: 0.82rem;">
                    <table class="table table-sm table-hover align-middle mb-0" style="min-width: 1050px;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-2" style="width:3%">#</th>
                                <th style="width:10%">PO Number</th>
                                <th style="width:20%">Product</th>
                                <th style="width:11%">Supplier</th>
                                <th class="text-center" style="width:7%">Ord. Qty</th>
                                <th class="text-center" style="width:7%">Recv. Qty</th>
                                <th class="text-end" style="width:9%">Unit Cost</th>
                                <th class="text-end" style="width:9%">Total Cost</th>
                                <th class="text-center" style="width:8%">P. Date</th>
                                <th class="text-center" style="width:8%">R. Date</th>
                                <th class="text-center" style="width:7%">Status</th>
                                <th class="text-center" style="width:7%">Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $index => $item)
                                <tr>
                                    <td class="ps-2 text-muted">{{ $items->firstItem() + $index }}</td>

                                    {{-- PO Number --}}
                                    <td>
                                        <span class="badge bg-light text-dark border fw-semibold font-monospace" style="font-size:0.75rem;">
                                            {{ $item->order_code ?? '—' }}
                                        </span>
                                    </td>

                                    {{-- Product --}}
                                    <td style="max-width:180px;">
                                        <div class="fw-semibold text-dark text-truncate" style="max-width:170px;" title="{{ $item->product_name ?? '' }}">{{ $item->product_name ?? '—' }}</div>
                                        @if ($item->product_code)
                                            <span class="text-muted font-monospace" style="font-size:0.75rem;">{{ $item->product_code }}</span>
                                        @endif
                                        @if ($item->product_model)
                                            <span class="text-muted d-block" style="font-size:0.75rem;">{{ $item->product_model }}</span>
                                        @endif
                                    </td>

                                    {{-- Supplier --}}
                                    <td>
                                        <span class="text-dark">{{ $item->supplier_name ?? '—' }}</span>
                                    </td>

                                    {{-- Ordered Qty --}}
                                    <td class="text-center">
                                        <span class="badge bg-info text-white fw-semibold">
                                            {{ number_format($item->quantity) }}
                                        </span>
                                    </td>

                                    {{-- Received Qty --}}
                                    <td class="text-center">
                                        @php
                                            $received = $item->received_quantity ?? 0;
                                            $ordered  = $item->quantity ?? 0;
                                            $badgeClass = $received >= $ordered ? 'bg-success' : ($received > 0 ? 'bg-warning' : 'bg-danger');
                                        @endphp
                                        <span class="badge {{ $badgeClass }} text-white fw-bold">
                                            {{ number_format($received) }}
                                        </span>
                                    </td>

                                    {{-- Unit Cost --}}
                                    <td class="text-end fw-semibold">
                                        Rs. {{ number_format($item->unit_price, 2) }}
                                    </td>

                                    {{-- Total Cost --}}
                                    <td class="text-end fw-bold text-success">
                                        Rs. {{ number_format($received * $item->unit_price, 2) }}
                                    </td>

                                    {{-- Purchase Date --}}
                                    <td class="text-center">
                                        <span class="text-dark">
                                            {{ $item->order_date ? \Carbon\Carbon::parse($item->order_date)->format('d M Y') : '—' }}
                                        </span>
                                    </td>

                                    {{-- Received Date --}}
                                    <td class="text-center">
                                        <span class="text-dark">
                                            {{ $item->received_date ? \Carbon\Carbon::parse($item->received_date)->format('d M Y') : '—' }}
                                        </span>
                                    </td>

                                    {{-- Order Status --}}
                                    <td class="text-center">
                                        @php
                                            $statusMap = [
                                                'complete'  => ['bg-success', 'Complete'],
                                                'received'  => ['bg-primary', 'Received'],
                                                'pending'   => ['bg-warning', 'Pending'],
                                                'cancelled' => ['bg-danger',  'Cancelled'],
                                            ];
                                            [$statusBg, $statusLabel] = $statusMap[$item->order_status] ?? ['bg-secondary', ucfirst($item->order_status ?? '—')];
                                        @endphp
                                        <span class="badge {{ $statusBg }}">{{ $statusLabel }}</span>
                                    </td>

                                    {{-- View Sales Button --}}
                                    <td class="text-center">
                                        @if ($item->product_id)
                                            <button
                                                wire:click="viewProductSales({{ $item->product_id }}, '{{ addslashes($item->product_name ?? '') }}')"
                                                class="btn btn-sm btn-outline-primary px-2 py-1"
                                                title="View sales invoices for this product"
                                                style="font-size:0.78rem;"
                                            >
                                                <i class="bi bi-receipt me-1"></i>Sales
                                            </button>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                        No purchase records found for the selected filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            @if ($items->hasPages())
                <div class="card-footer bg-transparent d-flex justify-content-between align-items-center py-2 px-3">
                    <small class="text-muted">
                        Page {{ $items->currentPage() }} of {{ $items->lastPage() }}
                    </small>
                    {{ $items->links('livewire.custom-pagination') }}
                </div>
            @endif
        </div>

    </div>

    {{-- ===================== Sales Tracking Modal ===================== --}}
    <div class="modal fade" id="salesTrackingModal" tabindex="-1" aria-labelledby="salesTrackingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="salesTrackingModalLabel">
                        <i class="bi bi-receipt me-2"></i>
                        Sales for: <strong>{{ $modalProductName }}</strong>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    @if (!empty($productSales))
                        {{-- Sales Summary --}}
                        @php
                            $totalSoldQty   = collect($productSales)->sum('quantity');
                            $totalSaleValue = collect($productSales)->sum('total');
                        @endphp
                        <div class="px-3 pt-3 pb-2 border-bottom bg-light">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-3 bg-success bg-opacity-10 p-2 me-2">
                                            <i class="bi bi-bag-check text-success"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted small mb-0">Total Sold Qty</p>
                                            <strong>{{ number_format($totalSoldQty) }} units</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-3 bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="bi bi-receipt text-primary"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted small mb-0">Total Invoices</p>
                                            <strong>{{ count($productSales) }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-3 bg-warning bg-opacity-10 p-2 me-2">
                                            <i class="bi bi-currency-dollar text-warning"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted small mb-0">Total Sale Value</p>
                                            <strong>Rs. {{ number_format($totalSaleValue, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">#</th>
                                        <th>Invoice Number</th>
                                        <th class="text-center">Qty Sold</th>
                                        <th class="text-end">Unit Sale Price</th>
                                        <th class="text-end">Discount / Unit</th>
                                        <th class="text-end">Line Total</th>
                                        <th class="text-center">Sale Price Type</th>
                                        <th class="text-center">Sold By</th>
                                        <th class="text-center">Payment Status</th>
                                        <th class="text-center">Sale Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($productSales as $i => $sale)
                                        <tr>
                                            <td class="ps-3 text-muted small">{{ $i + 1 }}</td>

                                            {{-- Invoice Number --}}
                                            <td>
                                                <span class="badge bg-light text-dark border fw-semibold font-monospace">
                                                    {{ $sale['invoice_number'] ?? '—' }}
                                                </span>
                                            </td>

                                            {{-- Qty Sold --}}
                                            <td class="text-center">
                                                <span class="badge bg-success text-white fw-semibold px-2 py-1">
                                                    {{ number_format($sale['quantity']) }}
                                                </span>
                                            </td>

                                            {{-- Unit Sale Price --}}
                                            <td class="text-end fw-semibold">
                                                Rs. {{ number_format($sale['unit_price'], 2) }}
                                            </td>

                                            {{-- Discount --}}
                                            <td class="text-end text-danger">
                                                @if (($sale['discount_per_unit'] ?? 0) > 0)
                                                    - Rs. {{ number_format($sale['discount_per_unit'], 2) }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>

                                            {{-- Line Total --}}
                                            <td class="text-end fw-bold text-primary">
                                                Rs. {{ number_format($sale['total'], 2) }}
                                            </td>

                                            {{-- Sale Price Type --}}
                                            <td class="text-center">
                                                @php
                                                    $sptMap = [
                                                        'cash'         => ['bg-success',  'Cash Price'],
                                                        'credit'       => ['bg-info',      'Credit Price'],
                                                        'cash_credit'  => ['bg-primary',   'Cash & Credit'],
                                                    ];
                                                    [$sptBg, $sptLabel] = $sptMap[$sale['sale_price_type'] ?? ''] ?? ['bg-secondary', ucfirst(str_replace('_', ' ', $sale['sale_price_type'] ?? '—'))];
                                                @endphp
                                                <span class="badge {{ $sptBg }}">{{ $sptLabel }}</span>
                                            </td>

                                            {{-- Sold By --}}
                                            <td class="text-center">
                                                <span class="text-dark fw-semibold">{{ $sale['seller_name'] ?? '—' }}</span>
                                            </td>

                                            {{-- Payment Status --}}
                                            <td class="text-center">
                                                @php
                                                    $psMap = [
                                                        'paid'    => ['bg-success', 'Paid'],
                                                        'partial' => ['bg-warning text-dark', 'Partial'],
                                                        'pending' => ['bg-danger',  'Pending'],
                                                        'unpaid'  => ['bg-danger',  'Unpaid'],
                                                    ];
                                                    [$psBg, $psLabel] = $psMap[$sale['payment_status'] ?? ''] ?? ['bg-secondary', ucfirst($sale['payment_status'] ?? '—')];
                                                @endphp
                                                <span class="badge {{ $psBg }}">{{ $psLabel }}</span>
                                            </td>

                                            {{-- Sale Date --}}
                                            <td class="text-center small">
                                                {{ $sale['sale_date'] ? \Carbon\Carbon::parse($sale['sale_date'])->format('d M Y, h:i A') : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="3" class="ps-3 text-end">Totals</td>
                                        <td></td>
                                        <td></td>
                                        <td class="text-center">{{ number_format($totalSoldQty) }}</td>
                                        <td></td>
                                        <td></td>
                                        <td class="text-end text-primary">Rs. {{ number_format($totalSaleValue, 2) }}</td>
                                        <td></td>
                                        <td></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-bag-x fs-1 d-block mb-2 opacity-25"></i>
                            <p class="mb-0">No sales found for <strong>{{ $modalProductName }}</strong>.</p>
                            <small>This product has not been sold on any completed invoice yet.</small>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>
