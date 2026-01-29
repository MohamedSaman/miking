{{-- Product Report --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-box-seam text-primary me-2"></i>Product Report
    </h6>

    @php $data = collect($reportData); @endphp
    @if($data->count() > 0)
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">{{ $data->count() }}</div>
                <div class="stat-label">Products Sold</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card success">
                <div class="stat-value">{{ number_format($data->sum('total_quantity')) }}</div>
                <div class="stat-label">Total Units Sold</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card warning">
                <div class="stat-value">Rs. {{ number_format($data->sum('total_revenue'), 2) }}</div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>Product Code</th>
                    <th>Product Name</th>
                    <th>Qty Sold</th>
                    <th>Avg. Price</th>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr>
                    <td><span class="badge bg-light text-dark">{{ $item->product_code }}</span></td>
                    <td>{{ $item->product_name }}</td>
                    <td class="fw-bold">{{ number_format($item->total_quantity) }}</td>
                    <td>Rs. {{ number_format($item->avg_price, 2) }}</td>
                    <td class="fw-bold text-success">Rs. {{ number_format($item->total_revenue, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td colspan="2" class="fw-bold">Total</td>
                    <td class="fw-bold">{{ number_format($data->sum('total_quantity')) }}</td>
                    <td>-</td>
                    <td class="fw-bold text-success">Rs. {{ number_format($data->sum('total_revenue'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="alert alert-info text-center py-4">
        <i class="bi bi-info-circle display-6 d-block mb-2"></i>
        No product sales data found for the selected period.
    </div>
    @endif
</div>
