{{-- Sales Return Report --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-arrow-return-left text-primary me-2"></i>Sales Return Report
    </h6>

    @php $data = collect($reportData); @endphp
    @if($data->count() > 0)
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card danger">
                <div class="stat-value">{{ $data->count() }}</div>
                <div class="stat-label">Total Returns</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card warning">
                <div class="stat-value">{{ number_format($data->sum('return_quantity')) }}</div>
                <div class="stat-label">Units Returned</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">Rs. {{ number_format($data->sum('total_amount'), 2) }}</div>
                <div class="stat-label">Total Return Value</div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>Return Date</th>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Qty Returned</th>
                    <th>Unit Price</th>
                    <th>Return Amount</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $return)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($return->created_at)->format('d M Y') }}</td>
                    <td><span class="badge bg-light text-dark">{{ $return->sale->invoice_number ?? 'N/A' }}</span></td>
                    <td>{{ $return->sale->customer->name ?? 'Walk-in Customer' }}</td>
                    <td>{{ $return->product->name ?? 'N/A' }}</td>
                    <td class="fw-bold text-danger">{{ $return->return_quantity }}</td>
                    <td>Rs. {{ number_format($return->selling_price, 2) }}</td>
                    <td class="fw-bold text-danger">Rs. {{ number_format($return->total_amount, 2) }}</td>
                    <td><small class="text-muted">{{ $return->notes ?? '-' }}</small></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td colspan="4" class="fw-bold">Total</td>
                    <td class="fw-bold text-danger">{{ number_format($data->sum('return_quantity')) }}</td>
                    <td>-</td>
                    <td class="fw-bold text-danger">Rs. {{ number_format($data->sum('total_amount'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="alert alert-success text-center py-4">
        <i class="bi bi-check-circle display-6 d-block mb-2"></i>
        No sales returns found for the selected period.
    </div>
    @endif
</div>
