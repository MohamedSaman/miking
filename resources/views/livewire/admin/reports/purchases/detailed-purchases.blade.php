{{-- Detailed Purchases Report --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-file-earmark-ruled text-primary me-2"></i>Detailed Purchases Report
    </h6>

    @php $data = collect($reportData); @endphp
    @if($data->count() > 0)
    @php
        $totalValue = $data->sum('total_amount');
        $totalDue = $data->sum('due_amount');
        $totalPaid = $totalValue - $totalDue;
    @endphp
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">{{ $data->count() }}</div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card success">
                <div class="stat-value">Rs. {{ number_format($totalValue, 2) }}</div>
                <div class="stat-label">Total Value</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card warning">
                <div class="stat-value">Rs. {{ number_format($totalPaid, 2) }}</div>
                <div class="stat-label">Total Paid</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card danger">
                <div class="stat-value">Rs. {{ number_format($totalDue, 2) }}</div>
                <div class="stat-label">Total Outstanding</div>
            </div>
        </div>
    </div>

    <!-- Purchases Table -->
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>PO #</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th class="text-center">Items</th>
                    <th>Total</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $purchase)
                <tr>
                    <td><span class="badge bg-light text-dark">{{ $purchase->order_code ?? 'PO-' . $purchase->id }}</span></td>
                    <td>{{ \Carbon\Carbon::parse($purchase->order_date)->format('d M Y') }}</td>
                    <td>{{ $purchase->supplier->company_name ?? 'Unknown' }}</td>
                    <td class="text-center"><span class="badge bg-secondary">{{ count($purchase->items ?? []) }}</span></td>
                    <td class="fw-bold">Rs. {{ number_format($purchase->total_amount, 2) }}</td>
                    <td class="{{ ($purchase->due_amount ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                        Rs. {{ number_format($purchase->due_amount ?? 0, 2) }}
                    </td>
                    <td>
                        @if($purchase->status === 'received')
                            <span class="badge bg-success">Received</span>
                        @elseif($purchase->status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($purchase->status) }}</span>
                        @endif
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                data-bs-toggle="modal" data-bs-target="#purchaseModal{{ $index }}">
                            <i class="bi bi-eye me-1"></i>View
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td colspan="4" class="fw-bold">Total</td>
                    <td class="fw-bold">Rs. {{ number_format($totalValue, 2) }}</td>
                    <td class="fw-bold text-danger">Rs. {{ number_format($totalDue, 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Bootstrap Modals -->
    @foreach($data as $index => $purchase)
    @php
        $purchasePaid = ($purchase->total_amount ?? 0) - ($purchase->due_amount ?? 0);
    @endphp
    <div class="modal fade" id="purchaseModal{{ $index }}" tabindex="-1" aria-labelledby="purchaseModalLabel{{ $index }}" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="purchaseModalLabel{{ $index }}">
                        <i class="bi bi-receipt text-primary me-2"></i>Purchase Order Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- PO Header -->
                    <div class="row mb-3 pb-3 border-bottom">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>PO Number:</strong> {{ $purchase->order_code ?? 'PO-' . $purchase->id }}</p>
                            <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($purchase->order_date)->format('d M Y') }}</p>
                            <p class="mb-1"><strong>Supplier:</strong> {{ $purchase->supplier->company_name ?? 'Unknown' }}</p>
                            <p class="mb-1"><strong>Contact:</strong> {{ $purchase->supplier->contact_person ?? 'N/A' }}</p>
                            <p class="mb-0"><strong>Phone:</strong> {{ $purchase->supplier->phone ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-1"><strong>Total:</strong> Rs. {{ number_format($purchase->total_amount, 2) }}</p>
                            <p class="mb-1"><strong>Paid:</strong> <span class="text-success">Rs. {{ number_format($purchasePaid, 2) }}</span></p>
                            <p class="mb-1"><strong>Due:</strong> <span class="text-danger">Rs. {{ number_format($purchase->due_amount ?? 0, 2) }}</span></p>
                            <p class="mb-0 mt-2">
                                @if($purchase->status === 'received')
                                    <span class="badge bg-success fs-6">Received</span>
                                @elseif($purchase->status === 'pending')
                                    <span class="badge bg-warning fs-6">Pending</span>
                                @else
                                    <span class="badge bg-secondary fs-6">{{ ucfirst($purchase->status) }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <!-- Items Table -->
                    @if($purchase->items && count($purchase->items) > 0)
                    <h6 class="fw-bold mb-2">Items ({{ count($purchase->items) }})</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Qty Ordered</th>
                                    <th>Qty Received</th>
                                    <th>Unit Cost</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->items as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                                    <td>{{ $item->quantity ?? 0 }}</td>
                                    <td>{{ $item->received_quantity ?? 0 }}</td>
                                    <td>Rs. {{ number_format($item->unit_price ?? 0, 2) }}</td>
                                    <td class="fw-bold">Rs. {{ number_format(($item->quantity ?? 0) * ($item->unit_price ?? 0), 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-light text-center">No items found for this purchase order.</div>
                    @endif
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
        No purchase orders found for the selected period.
    </div>
    @endif
</div>
