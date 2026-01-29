{{-- Productwise Profit/Loss --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-box text-primary me-2"></i>Productwise Profit/Loss
    </h6>

    @php $data = collect($reportData); @endphp
    @if($data->count() > 0)
    @php
        $totalRevenue = $data->sum('total_revenue');
        $totalCost = $data->sum('total_cost');
        $totalProfit = $data->sum('profit');
    @endphp
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">{{ $data->count() }}</div>
                <div class="stat-label">Products Analyzed</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card success">
                <div class="stat-value">Rs. {{ number_format($totalRevenue, 2) }}</div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card warning">
                <div class="stat-value">Rs. {{ number_format($totalCost, 2) }}</div>
                <div class="stat-label">Total Cost</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card {{ $totalProfit >= 0 ? 'success' : 'danger' }}">
                <div class="stat-value">Rs. {{ number_format($totalProfit, 2) }}</div>
                <div class="stat-label">Total Profit</div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Brand</th>
                    <th>Qty Sold</th>
                    <th>Revenue</th>
                    <th>Cost</th>
                    <th>Profit/Loss</th>
                    <th>Margin %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr>
                    <td class="fw-bold">{{ $item['product']->name ?? 'Unknown' }}</td>
                    <td>{{ $item['product']->brand->brand_name ?? '-' }}</td>
                    <td>{{ number_format($item['quantity_sold']) }}</td>
                    <td>Rs. {{ number_format($item['total_revenue'], 2) }}</td>
                    <td class="text-danger">Rs. {{ number_format($item['total_cost'], 2) }}</td>
                    <td class="fw-bold {{ $item['profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">
                        Rs. {{ number_format($item['profit'], 2) }}
                    </td>
                    <td>
                        <span class="badge {{ $item['margin'] >= 20 ? 'bg-success' : ($item['margin'] >= 0 ? 'bg-warning' : 'bg-danger') }}">
                            {{ number_format($item['margin'], 1) }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td colspan="3" class="fw-bold">Total</td>
                    <td class="fw-bold">Rs. {{ number_format($totalRevenue, 2) }}</td>
                    <td class="fw-bold text-danger">Rs. {{ number_format($totalCost, 2) }}</td>
                    <td class="fw-bold {{ $totalProfit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                        Rs. {{ number_format($totalProfit, 2) }}
                    </td>
                    <td>
                        @php
                            $avgMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;
                        @endphp
                        <span class="badge bg-info">{{ number_format($avgMargin, 1) }}%</span>
                    </td>
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
