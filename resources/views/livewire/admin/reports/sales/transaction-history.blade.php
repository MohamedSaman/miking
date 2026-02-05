{{-- Transaction History Report --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-clock-history text-primary me-2"></i>Transaction History
    </h6>

    @php $data = collect($reportData); @endphp
    @if($data->count() > 0)
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">{{ $data->count() }}</div>
                <div class="stat-label">Total Transactions</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card success">
                <div class="stat-value">Rs. {{ number_format($data->sum('total_amount'), 2) }}</div>
                <div class="stat-label">Total Sales</div>
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
                <div class="stat-label">Total Due</div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Due</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $sale)
                <tr>
                    <td><span class="badge bg-light text-dark">{{ $sale->invoice_number }}</span></td>
                    <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('d M Y') }}</td>
                    <td>{{ $sale->customer->name ?? 'Walk-in Customer' }}</td>
                    <td>{{ $sale->items->count() }} items</td>
                    <td class="fw-bold">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                    <td class="text-success">Rs. {{ number_format($sale->payments->sum('amount'), 2) }}</td>
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
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="alert alert-info text-center py-4">
        <i class="bi bi-info-circle display-6 d-block mb-2"></i>
        No transactions found for the selected period.
    </div>
    @endif
</div>
