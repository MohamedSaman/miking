{{-- Purchases/Payment Report --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-bag-check text-primary me-2"></i>Purchases/Payment Report
    </h6>

    @if(isset($reportData['purchases']) && count($reportData['purchases']) > 0)
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">Rs. {{ number_format($reportData['summary']['total_purchases'], 2) }}</div>
                <div class="stat-label">Total Purchases</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card success">
                <div class="stat-value">Rs. {{ number_format($reportData['summary']['total_paid'], 2) }}</div>
                <div class="stat-label">Total Paid</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card danger">
                <div class="stat-value">Rs. {{ number_format($reportData['summary']['total_due'], 2) }}</div>
                <div class="stat-label">Total Outstanding</div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Total Amount</th>
                    <th>Paid Amount</th>
                    <th>Due Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['purchases'] as $purchase)
                @php
                    $paidAmount = ($purchase->total_amount ?? 0) - ($purchase->due_amount ?? 0);
                @endphp
                <tr>
                    <td><span class="badge bg-light text-dark">{{ $purchase->order_code ?? 'PO-' . $purchase->id }}</span></td>
                    <td>{{ \Carbon\Carbon::parse($purchase->order_date)->format('d M Y') }}</td>
                    <td>{{ $purchase->supplier->businessname ?? $purchase->supplier->name ?? 'Unknown Supplier' }}</td>
                    <td class="fw-bold">Rs. {{ number_format($purchase->total_amount, 2) }}</td>
                    <td class="text-success">Rs. {{ number_format($paidAmount, 2) }}</td>
                    <td class="{{ $purchase->due_amount > 0 ? 'text-danger' : 'text-muted' }}">
                        Rs. {{ number_format($purchase->due_amount ?? 0, 2) }}
                    </td>
                    <td>
                        @if($purchase->status === 'received')
                            <span class="badge bg-success">Received</span>
                        @elseif($purchase->status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                        @elseif($purchase->status === 'partial')
                            <span class="badge bg-info">Partial</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($purchase->status) }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td colspan="3" class="fw-bold">Total</td>
                    <td class="fw-bold">Rs. {{ number_format($reportData['summary']['total_purchases'], 2) }}</td>
                    <td class="fw-bold text-success">Rs. {{ number_format($reportData['summary']['total_paid'], 2) }}</td>
                    <td class="fw-bold text-danger">Rs. {{ number_format($reportData['summary']['total_due'], 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="alert alert-info text-center py-4">
        <i class="bi bi-info-circle display-6 d-block mb-2"></i>
        No purchase data found for the selected period.
    </div>
    @endif
</div>
