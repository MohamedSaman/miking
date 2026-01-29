{{-- P & L using COGS --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-graph-up text-primary me-2"></i>Profit & Loss using COGS
    </h6>

    @if($reportData)
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card success">
                <div class="stat-value">Rs. {{ number_format($reportData['total_sales'], 2) }}</div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card danger">
                <div class="stat-value">Rs. {{ number_format($reportData['total_cogs'], 2) }}</div>
                <div class="stat-label">Cost of Goods Sold</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">Rs. {{ number_format($reportData['gross_profit'], 2) }}</div>
                <div class="stat-label">Gross Profit</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card {{ $reportData['net_profit'] >= 0 ? 'success' : 'danger' }}">
                <div class="stat-value">Rs. {{ number_format($reportData['net_profit'], 2) }}</div>
                <div class="stat-label">Net Profit</div>
            </div>
        </div>
    </div>

    <!-- P&L Statement -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Profit & Loss Statement</h6>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <tbody>
                    <!-- Revenue Section -->
                    <tr class="table-primary">
                        <td colspan="2" class="fw-bold">REVENUE</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Gross Sales</td>
                        <td class="text-end fw-bold">Rs. {{ number_format($reportData['total_sales'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Less: Sales Returns</td>
                        <td class="text-end text-danger">(Rs. {{ number_format($reportData['total_returns'], 2) }})</td>
                    </tr>
                    <tr class="table-light">
                        <td class="fw-bold">Net Revenue</td>
                        <td class="text-end fw-bold">Rs. {{ number_format($reportData['total_sales'] - $reportData['total_returns'], 2) }}</td>
                    </tr>

                    <!-- COGS Section -->
                    <tr class="table-warning">
                        <td colspan="2" class="fw-bold">COST OF GOODS SOLD</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Cost of Goods Sold (COGS)</td>
                        <td class="text-end text-danger">(Rs. {{ number_format($reportData['total_cogs'], 2) }})</td>
                    </tr>

                    <!-- Gross Profit -->
                    <tr class="table-success">
                        <td class="fw-bold">GROSS PROFIT</td>
                        <td class="text-end fw-bold {{ $reportData['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                            Rs. {{ number_format($reportData['gross_profit'], 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="ps-4">Gross Margin</td>
                        <td class="text-end">
                            <span class="badge {{ $reportData['gross_margin'] >= 20 ? 'bg-success' : 'bg-warning' }}">
                                {{ number_format($reportData['gross_margin'], 1) }}%
                            </span>
                        </td>
                    </tr>

                    <!-- Expenses Section -->
                    <tr class="table-danger">
                        <td colspan="2" class="fw-bold">OPERATING EXPENSES</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Total Operating Expenses</td>
                        <td class="text-end text-danger">(Rs. {{ number_format($reportData['total_expenses'], 2) }})</td>
                    </tr>

                    <!-- Net Profit -->
                    <tr class="table-dark">
                        <td class="fw-bold text-white">NET PROFIT / (LOSS)</td>
                        <td class="text-end fw-bold {{ $reportData['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}" style="background: {{ $reportData['net_profit'] >= 0 ? '#d4edda' : '#f8d7da' }};">
                            Rs. {{ number_format($reportData['net_profit'], 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="ps-4">Net Profit Margin</td>
                        <td class="text-end">
                            <span class="badge {{ $reportData['net_margin'] >= 10 ? 'bg-success' : ($reportData['net_margin'] >= 0 ? 'bg-warning' : 'bg-danger') }}">
                                {{ number_format($reportData['net_margin'], 1) }}%
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="alert alert-info text-center py-4">
        <i class="bi bi-info-circle display-6 d-block mb-2"></i>
        No data available for the selected period.
    </div>
    @endif
</div>
