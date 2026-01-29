{{-- Year Wise COGS Report --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-calendar-year text-primary me-2"></i>Year Wise - COGS Method
    </h6>

    @php $data = collect($reportData); @endphp
    @if($data->count() > 0)
    @php
        $totalSales = $data->sum('total_sales');
        $totalCOGS = $data->sum('total_cogs');
        $totalGrossProfit = $data->sum('gross_profit');
    @endphp
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">Rs. {{ number_format($totalSales, 2) }}</div>
                <div class="stat-label">Total Sales (All Years)</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card danger">
                <div class="stat-value">Rs. {{ number_format($totalCOGS, 2) }}</div>
                <div class="stat-label">Total COGS (All Years)</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card success">
                <div class="stat-value">Rs. {{ number_format($totalGrossProfit, 2) }}</div>
                <div class="stat-label">Total Gross Profit</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card warning">
                <div class="stat-value">{{ $totalSales > 0 ? number_format(($totalGrossProfit / $totalSales) * 100, 1) : 0 }}%</div>
                <div class="stat-label">Avg. Gross Margin</div>
            </div>
        </div>
    </div>

    <!-- Visual Chart -->
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Year-over-Year Performance</h6>
            <div class="row">
                @foreach($data as $year)
                <div class="col mb-3">
                    <div class="text-center">
                        <h5 class="fw-bold mb-2">{{ $year['year'] }}</h5>
                        <div class="progress mb-2" style="height: 100px; flex-direction: column-reverse;">
                            @php
                                $maxSales = $data->max('total_sales') ?: 1;
                                $heightPercent = ($year['total_sales'] / $maxSales) * 100;
                            @endphp
                            <div class="progress-bar bg-primary" style="height: {{ $heightPercent }}%"></div>
                        </div>
                        <small class="text-muted">Rs. {{ number_format($year['total_sales'] / 1000, 0) }}K</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>Year</th>
                    <th>Total Sales</th>
                    <th>Total COGS</th>
                    <th>Gross Profit</th>
                    <th>Gross Margin %</th>
                    <th>Trend</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $year)
                <tr>
                    <td class="fw-bold">{{ $year['year'] }}</td>
                    <td>Rs. {{ number_format($year['total_sales'], 2) }}</td>
                    <td class="text-danger">Rs. {{ number_format($year['total_cogs'], 2) }}</td>
                    <td class="{{ $year['gross_profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">
                        Rs. {{ number_format($year['gross_profit'], 2) }}
                    </td>
                    <td>
                        <span class="badge {{ $year['margin_percentage'] >= 20 ? 'bg-success' : ($year['margin_percentage'] >= 10 ? 'bg-warning' : 'bg-danger') }}">
                            {{ number_format($year['margin_percentage'], 1) }}%
                        </span>
                    </td>
                    <td>
                        @if($index > 0)
                            @php
                                $prevProfit = $data[$index - 1]['gross_profit'] ?? 0;
                                $change = $prevProfit > 0 ? (($year['gross_profit'] - $prevProfit) / $prevProfit) * 100 : 0;
                            @endphp
                            @if($change > 0)
                                <span class="text-success"><i class="bi bi-arrow-up"></i> {{ number_format($change, 1) }}%</span>
                            @elseif($change < 0)
                                <span class="text-danger"><i class="bi bi-arrow-down"></i> {{ number_format(abs($change), 1) }}%</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td class="fw-bold">Total</td>
                    <td class="fw-bold">Rs. {{ number_format($totalSales, 2) }}</td>
                    <td class="fw-bold text-danger">Rs. {{ number_format($totalCOGS, 2) }}</td>
                    <td class="fw-bold {{ $totalGrossProfit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                        Rs. {{ number_format($totalGrossProfit, 2) }}
                    </td>
                    <td>
                        <span class="badge bg-info">
                            {{ $totalSales > 0 ? number_format(($totalGrossProfit / $totalSales) * 100, 1) : 0 }}%
                        </span>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="alert alert-info text-center py-4">
        <i class="bi bi-info-circle display-6 d-block mb-2"></i>
        No yearly data available.
    </div>
    @endif
</div>
