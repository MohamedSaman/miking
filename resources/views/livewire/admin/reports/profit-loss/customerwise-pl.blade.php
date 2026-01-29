{{-- Customerwise Profit/Loss --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-people text-primary me-2"></i>Customerwise Profit/Loss
    </h6>

    @php $data = collect($reportData); @endphp
    @if($data->count() > 0)
    @php
        $totalRevenue = $data->sum('total_revenue');
        $totalCost = $data->sum('total_cost');
        $totalProfit = $data->sum('profit');
        $totalTransactions = $data->sum('total_transactions');
    @endphp
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">{{ $data->count() }}</div>
                <div class="stat-label">Total Customers</div>
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

    <!-- Top Profitable Customers -->
    <div class="row mb-4">
        @foreach($data->take(3) as $index => $item)
        <div class="col-md-4 mb-3">
            <div class="card h-100 {{ $index === 0 ? 'border-success border-2' : '' }}">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-{{ $index === 0 ? 'success' : ($index === 1 ? 'primary' : 'secondary') }} text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-person fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $item['customer']->name ?? 'Walk-in Customer' }}</h6>
                            <small class="text-muted">{{ $item['total_transactions'] }} transactions</small>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="fw-bold text-success">Rs. {{ number_format($item['profit'], 2) }}</div>
                            <small class="text-muted">Profit</small>
                        </div>
                        <div class="col-6">
                            <div class="fw-bold">{{ number_format($item['margin'], 1) }}%</div>
                            <small class="text-muted">Margin</small>
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
                    <th>Customer</th>
                    <th>Transactions</th>
                    <th>Revenue</th>
                    <th>Cost</th>
                    <th>Profit/Loss</th>
                    <th>Margin %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr>
                    <td class="fw-bold">{{ $item['customer']->name ?? 'Walk-in Customer' }}</td>
                    <td>{{ $item['total_transactions'] }}</td>
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
                    <td class="fw-bold">Total</td>
                    <td class="fw-bold">{{ $totalTransactions }}</td>
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
        No customer data found for the selected period.
    </div>
    @endif
</div>
