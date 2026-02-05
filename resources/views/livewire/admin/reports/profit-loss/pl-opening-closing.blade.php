{{-- P & L using Opening/Closing Stock --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-box-seam text-primary me-2"></i>Profit & Loss using Opening/Closing Stock
    </h6>

    @if($reportData)
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">Rs. {{ number_format($reportData['opening_stock_value'], 2) }}</div>
                <div class="stat-label">Opening Stock</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card warning">
                <div class="stat-value">Rs. {{ number_format($reportData['purchases'], 2) }}</div>
                <div class="stat-label">Purchases</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card info">
                <div class="stat-value">Rs. {{ number_format($reportData['closing_stock_value'], 2) }}</div>
                <div class="stat-label">Closing Stock</div>
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
            <h6 class="mb-0"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Profit & Loss Statement (Stock Method)</h6>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <tbody>
                    <!-- Revenue Section -->
                    <tr class="table-primary">
                        <td colspan="2" class="fw-bold">REVENUE</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Total Sales</td>
                        <td class="text-end fw-bold">Rs. {{ number_format($reportData['total_sales'], 2) }}</td>
                    </tr>

                    <!-- COGS Calculation -->
                    <tr class="table-warning">
                        <td colspan="2" class="fw-bold">COST OF GOODS SOLD (COGS)</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Opening Stock</td>
                        <td class="text-end">Rs. {{ number_format($reportData['opening_stock_value'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Add: Purchases</td>
                        <td class="text-end">Rs. {{ number_format($reportData['purchases'], 2) }}</td>
                    </tr>
                    <tr class="table-light">
                        <td class="ps-4 fw-bold">Cost of Goods Available for Sale</td>
                        <td class="text-end fw-bold">Rs. {{ number_format($reportData['opening_stock_value'] + $reportData['purchases'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Less: Closing Stock</td>
                        <td class="text-end text-danger">(Rs. {{ number_format($reportData['closing_stock_value'], 2) }})</td>
                    </tr>
                    <tr class="table-secondary">
                        <td class="fw-bold">Cost of Goods Sold</td>
                        <td class="text-end fw-bold text-danger">Rs. {{ number_format($reportData['cogs'], 2) }}</td>
                    </tr>

                    <!-- Gross Profit -->
                    <tr class="table-success">
                        <td class="fw-bold">GROSS PROFIT</td>
                        <td class="text-end fw-bold {{ $reportData['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                            Rs. {{ number_format($reportData['gross_profit'], 2) }}
                        </td>
                    </tr>

                    <!-- Expenses Section -->
                    <tr class="table-danger">
                        <td colspan="2" class="fw-bold">OPERATING EXPENSES</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Total Operating Expenses</td>
                        <td class="text-end text-danger">(Rs. {{ number_format($reportData['expenses'], 2) }})</td>
                    </tr>

                    <!-- Net Profit -->
                    <tr class="table-dark">
                        <td class="fw-bold text-white">NET PROFIT / (LOSS)</td>
                        <td class="text-end fw-bold {{ $reportData['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}" style="background: {{ $reportData['net_profit'] >= 0 ? '#d4edda' : '#f8d7da' }};">
                            Rs. {{ number_format($reportData['net_profit'], 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Formula Explanation -->
    <div class="card mt-4">
        <div class="card-body bg-light">
            <h6 class="fw-bold mb-2"><i class="bi bi-lightbulb text-warning me-2"></i>Calculation Method</h6>
            <p class="mb-0 small text-muted">
                <strong>COGS = Opening Stock + Purchases - Closing Stock</strong><br>
                This method calculates the cost of goods sold based on inventory changes during the period.
            </p>
        </div>
    </div>
    @else
    <div class="alert alert-info text-center py-4">
        <i class="bi bi-info-circle display-6 d-block mb-2"></i>
        No data available for the selected period.
    </div>
    @endif
</div>
