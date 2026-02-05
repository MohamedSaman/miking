{{-- Monthly/Weekly/Daily P & L using COGS --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-calendar-week text-primary me-2"></i>{{ ucfirst($periodType) }} Profit & Loss (COGS Method)
    </h6>

    @php $data = collect($reportData); @endphp
    @if($data->count() > 0)
    @php
        $totalSales = $data->sum('sales');
        $totalCOGS = $data->sum('cogs');
        $totalGrossProfit = $data->sum('gross_profit');
        $totalExpenses = $data->sum('expenses');
        $totalNetProfit = $data->sum('net_profit');
    @endphp
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card success">
                <div class="stat-value">Rs. {{ number_format($totalSales, 2) }}</div>
                <div class="stat-label">Total Sales</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card danger">
                <div class="stat-value">Rs. {{ number_format($totalCOGS, 2) }}</div>
                <div class="stat-label">Total COGS</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">Rs. {{ number_format($totalGrossProfit, 2) }}</div>
                <div class="stat-label">Total Gross Profit</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card {{ $totalNetProfit >= 0 ? 'success' : 'danger' }}">
                <div class="stat-value">Rs. {{ number_format($totalNetProfit, 2) }}</div>
                <div class="stat-label">Total Net Profit</div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Sales</th>
                    <th>COGS</th>
                    <th>Gross Profit</th>
                    <th>Expenses</th>
                    <th>Net Profit</th>
                    <th>Margin</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $period)
                <tr>
                    <td class="fw-bold">{{ $period['period'] }}</td>
                    <td>Rs. {{ number_format($period['sales'], 2) }}</td>
                    <td class="text-danger">Rs. {{ number_format($period['cogs'], 2) }}</td>
                    <td class="{{ $period['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                        Rs. {{ number_format($period['gross_profit'], 2) }}
                    </td>
                    <td class="text-warning">Rs. {{ number_format($period['expenses'], 2) }}</td>
                    <td class="fw-bold {{ $period['net_profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">
                        Rs. {{ number_format($period['net_profit'], 2) }}
                    </td>
                    <td>
                        @php
                            $margin = $period['sales'] > 0 ? ($period['net_profit'] / $period['sales']) * 100 : 0;
                        @endphp
                        <span class="badge {{ $margin >= 10 ? 'bg-success' : ($margin >= 0 ? 'bg-warning' : 'bg-danger') }}">
                            {{ number_format($margin, 1) }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td class="fw-bold">Total</td>
                    <td class="fw-bold">Rs. {{ number_format($totalSales, 2) }}</td>
                    <td class="fw-bold text-danger">Rs. {{ number_format($totalCOGS, 2) }}</td>
                    <td class="fw-bold {{ $totalGrossProfit >= 0 ? 'text-success' : 'text-danger' }}">
                        Rs. {{ number_format($totalGrossProfit, 2) }}
                    </td>
                    <td class="fw-bold text-warning">Rs. {{ number_format($totalExpenses, 2) }}</td>
                    <td class="fw-bold {{ $totalNetProfit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                        Rs. {{ number_format($totalNetProfit, 2) }}
                    </td>
                    <td>
                        @php
                            $avgMargin = $totalSales > 0 ? ($totalNetProfit / $totalSales) * 100 : 0;
                        @endphp
                        <span class="badge bg-info">
                            {{ number_format($avgMargin, 1) }}%
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="alert alert-info text-center py-4">
        <i class="bi bi-info-circle display-6 d-block mb-2"></i>
        No data available for the selected period.
    </div>
    @endif
</div>
