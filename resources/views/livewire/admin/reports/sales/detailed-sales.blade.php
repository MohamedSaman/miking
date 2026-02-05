{{-- Detailed Sales Report --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-file-earmark-text text-primary me-2"></i>Detailed Sales Report
    </h6>

    @php $data = collect($reportData); @endphp
    @if($data->count() > 0)
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">{{ $data->count() }}</div>
                <div class="stat-label">Total Invoices</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card success">
                <div class="stat-value">Rs. {{ number_format($data->sum('total_amount'), 2) }}</div>
                <div class="stat-label">Gross Sales</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card warning">
                <div class="stat-value">Rs. {{ number_format($data->sum('discount_amount'), 2) }}</div>
                <div class="stat-label">Total Discounts</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card danger">
                <div class="stat-value">Rs. {{ number_format($data->sum('due_amount'), 2) }}</div>
                <div class="stat-label">Total Outstanding</div>
            </div>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Sold By</th>
                    <th class="text-center">Items</th>
                    <th>Total</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $sale)
                <tr>
                    <td><span class="badge bg-light text-dark">{{ $sale->invoice_number }}</span></td>
                    <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('d M Y') }}</td>
                    <td>{{ $sale->customer->name ?? 'Walk-in Customer' }}</td>
                    <td>{{ $sale->user->name ?? 'N/A' }}</td>
                    <td class="text-center"><span class="badge bg-secondary">{{ count($sale->items) }}</span></td>
                    <td class="fw-bold">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                    <td class="{{ $sale->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                        Rs. {{ number_format($sale->due_amount, 2) }}
                    </td>
                    <td>
                        @if($sale->payment_status === 'paid')
                            <span class="badge bg-success">Paid</span>
                        @elseif($sale->payment_status === 'partial')
                            <span class="badge bg-warning">Partial</span>
                        @else
                            <span class="badge bg-danger">Pending</span>
                        @endif
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                data-bs-toggle="modal" data-bs-target="#saleModal{{ $index }}">
                            <i class="bi bi-eye me-1"></i>View
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td colspan="5" class="fw-bold">Total</td>
                    <td class="fw-bold">Rs. {{ number_format($data->sum('total_amount'), 2) }}</td>
                    <td class="fw-bold text-danger">Rs. {{ number_format($data->sum('due_amount'), 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Bootstrap Modals -->
    @foreach($data as $index => $sale)
    <div class="modal fade" id="saleModal{{ $index }}" tabindex="-1" aria-labelledby="saleModalLabel{{ $index }}" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="saleModalLabel{{ $index }}">
                        <i class="bi bi-receipt text-primary me-2"></i>Invoice Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Invoice Header -->
                    <div class="row mb-3 pb-3 border-bottom">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Invoice:</strong> {{ $sale->invoice_number }}</p>
                            <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->created_at)->format('d M Y, h:i A') }}</p>
                            <p class="mb-1"><strong>Customer:</strong> {{ $sale->customer->name ?? 'Walk-in Customer' }}</p>
                            <p class="mb-0"><strong>Sold By:</strong> {{ $sale->user->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-1"><strong>Payment Method:</strong> {{ ucfirst($sale->payment_method ?? 'N/A') }}</p>
                            <p class="mb-1"><strong>Subtotal:</strong> Rs. {{ number_format($sale->subtotal, 2) }}</p>
                            <p class="mb-1"><strong>Discount:</strong> Rs. {{ number_format($sale->discount_amount, 2) }}</p>
                            <p class="mb-0 fs-5 fw-bold text-success">Total: Rs. {{ number_format($sale->total_amount, 2) }}</p>
                        </div>
                    </div>
                    
                    <!-- Items Table -->
                    <h6 class="fw-bold mb-2">Items ({{ count($sale->items) }})</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Code</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Discount</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td><small class="text-muted">{{ $item->product_code }}</small></td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>Rs. {{ number_format($item->unit_price, 2) }}</td>
                                    <td>Rs. {{ number_format($item->total_discount ?? 0, 2) }}</td>
                                    <td class="fw-bold">Rs. {{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Status -->
                    <div class="mt-3 text-end">
                        @if($sale->payment_status === 'paid')
                            <span class="badge bg-success fs-6">Fully Paid</span>
                        @elseif($sale->payment_status === 'partial')
                            <span class="badge bg-warning fs-6">Partial - Due: Rs. {{ number_format($sale->due_amount, 2) }}</span>
                        @else
                            <span class="badge bg-danger fs-6">Pending - Due: Rs. {{ number_format($sale->due_amount, 2) }}</span>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    @else
    <div class="alert alert-info text-center py-4">
        <i class="bi bi-info-circle display-6 d-block mb-2"></i>
        No sales data found for the selected period.
    </div>
    @endif
</div>
