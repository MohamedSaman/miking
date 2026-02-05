{{-- Expense Report --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-wallet2 text-primary me-2"></i>Expense Report
    </h6>

    @if(isset($reportData['expenses']) && count($reportData['expenses']) > 0)
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">{{ count($reportData['expenses']) }}</div>
                <div class="stat-label">Total Expense Entries</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card danger">
                <div class="stat-value">Rs. {{ number_format($reportData['total'], 2) }}</div>
                <div class="stat-label">Total Expenses</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card warning">
                <div class="stat-value">{{ count($reportData['by_category']) }}</div>
                <div class="stat-label">Expense Categories</div>
            </div>
        </div>
    </div>

    <!-- Expense by Category -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart me-2"></i>Expenses by Category</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($reportData['by_category'] as $category)
                <div class="col-md-4 mb-3">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                        <div>
                            <h6 class="mb-0">{{ $category['category'] ?? 'Uncategorized' }}</h6>
                            <small class="text-muted">{{ $category['count'] }} entries</small>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-danger">Rs. {{ number_format($category['total'], 2) }}</span>
                        </div>
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
                    <th>Date</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['expenses'] as $expense)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}</td>
                    <td><span class="badge bg-secondary">{{ $expense->category ?? 'General' }}</span></td>
                    <td>{{ $expense->expense_type ?? '-' }}</td>
                    <td>{{ $expense->description ?? '-' }}</td>
                    <td class="fw-bold text-danger">Rs. {{ number_format($expense->amount, 2) }}</td>
                    <td>
                        @if($expense->status === 'approved' || $expense->status === 'paid')
                            <span class="badge bg-success">{{ ucfirst($expense->status) }}</span>
                        @elseif($expense->status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($expense->status ?? 'N/A') }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td colspan="4" class="fw-bold">Total Expenses</td>
                    <td class="fw-bold text-danger">Rs. {{ number_format($reportData['total'], 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="alert alert-info text-center py-4">
        <i class="bi bi-info-circle display-6 d-block mb-2"></i>
        No expense records found for the selected period.
    </div>
    @endif
</div>
