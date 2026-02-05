{{-- Sales/Payment Report --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-credit-card text-primary me-2"></i>Sales/Payment Report
    </h6>

    @if(isset($reportData['sales']) && count($reportData['sales']) > 0)
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card success">
                <div class="stat-value">Rs. {{ number_format($reportData['summary']['total_sales'], 2) }}</div>
                <div class="stat-label">Total Sales</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">Rs. {{ number_format($reportData['summary']['total_paid'], 2) }}</div>
                <div class="stat-label">Total Received</div>
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
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Sale Amount</th>
                    <th>Paid Amount</th>
                    <th>Due Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['sales'] as $sale)
                <tr>
                    <td><span class="badge bg-light text-dark">{{ $sale->invoice_number }}</span></td>
                    <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('d M Y') }}</td>
                    <td>{{ $sale->customer->name ?? 'Walk-in Customer' }}</td>
                    <td class="fw-bold">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                    <td class="text-success">Rs. {{ number_format($sale->payments->sum('amount'), 2) }}</td>
                    <td class="{{ $sale->due_amount > 0 ? 'text-danger' : 'text-muted' }}">
                        Rs. {{ number_format($sale->due_amount, 2) }}
                    </td>
                    <td>
                        @php
                            $methods = $sale->payments->pluck('payment_method')->unique()->filter()->toArray();
                        @endphp
                        {{ count($methods) > 0 ? implode(', ', $methods) : '-' }}
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
        No sales/payment data found for the selected period.
    </div>
    @endif
</div>
