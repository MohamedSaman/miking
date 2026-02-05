{{-- Sales by Product - Top 5 --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-star text-primary me-2"></i>Sales by Product - Top 5
    </h6>

    @php $data = collect($reportData); @endphp
    @if($data->count() > 0)
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="stat-card success">
                <div class="stat-value">Rs. {{ number_format($data->sum('total_revenue'), 2) }}</div>
                <div class="stat-label">Total Revenue (Top 5)</div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">{{ number_format($data->sum('total_quantity')) }}</div>
                <div class="stat-label">Total Units Sold</div>
            </div>
        </div>
    </div>

    <!-- Top 5 Products Cards -->
    <div class="row mb-4">
        @foreach($data as $index => $item)
        <div class="col-md-4 mb-3">
            <div class="card h-100 {{ $index === 0 ? 'border-success border-2' : '' }}">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <div class="rounded-circle bg-{{ $index === 0 ? 'success' : ($index === 1 ? 'primary' : 'secondary') }} text-white d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 40px; height: 40px;">
                            <strong>#{{ $index + 1 }}</strong>
                        </div>
                        <div>
                            <h6 class="mb-1">{{ $item->product_name }}</h6>
                            @if($item->product && $item->product->brand)
                            <small class="text-muted">{{ $item->product->brand->brand_name }}</small>
                            @endif
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="fw-bold text-success">Rs. {{ number_format($item->total_revenue, 2) }}</div>
                            <small class="text-muted">Revenue</small>
                        </div>
                        <div class="col-6">
                            <div class="fw-bold">{{ number_format($item->total_quantity) }}</div>
                            <small class="text-muted">Units Sold</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Data Table -->
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Product Name</th>
                    <th>Units Sold</th>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $item)
                <tr>
                    <td>
                        @if($index === 0)
                            <span class="badge bg-success"><i class="bi bi-trophy"></i> #1</span>
                        @elseif($index === 1)
                            <span class="badge bg-primary">#2</span>
                        @elseif($index === 2)
                            <span class="badge bg-info">#3</span>
                        @else
                            <span class="badge bg-light text-dark">#{{ $index + 1 }}</span>
                        @endif
                    </td>
                    <td class="fw-bold">{{ $item->product_name }}</td>
                    <td>{{ number_format($item->total_quantity) }}</td>
                    <td class="fw-bold text-success">Rs. {{ number_format($item->total_revenue, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="alert alert-info text-center py-4">
        <i class="bi bi-info-circle display-6 d-block mb-2"></i>
        No product sales data found for the selected period.
    </div>
    @endif
</div>
