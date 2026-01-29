{{-- Payment Mode Report --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-credit-card-2-back text-primary me-2"></i>Payment Mode Report
    </h6>

    @if(isset($reportData['payments']) && count($reportData['payments']) > 0)
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">{{ count($reportData['payments']) }}</div>
                <div class="stat-label">Total Transactions</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card success">
                <div class="stat-value">Rs. {{ number_format($reportData['total'], 2) }}</div>
                <div class="stat-label">Total Amount</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card warning">
                <div class="stat-value">{{ count($reportData['by_mode']) }}</div>
                <div class="stat-label">Payment Methods Used</div>
            </div>
        </div>
    </div>

    <!-- Payment Methods Breakdown -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart me-2"></i>Payment Methods Breakdown</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($reportData['by_mode'] as $mode)
                @php
                    $percentage = $reportData['total'] > 0 ? ($mode['total'] / $reportData['total']) * 100 : 0;
                    $iconClass = match(strtolower($mode['method'])) {
                        'cash' => 'bi-cash-stack text-success',
                        'card', 'credit card', 'debit card' => 'bi-credit-card text-primary',
                        'cheque', 'check' => 'bi-bank text-info',
                        'transfer', 'bank transfer' => 'bi-arrow-left-right text-warning',
                        'online', 'upi', 'digital' => 'bi-phone text-purple',
                        default => 'bi-wallet2 text-secondary'
                    };
                @endphp
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                    <i class="bi {{ $iconClass }} fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ ucfirst($mode['method']) }}</h6>
                                    <small class="text-muted">{{ $mode['count'] }} transactions</small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-bold text-success fs-5">Rs. {{ number_format($mode['total'], 2) }}</div>
                                <span class="badge bg-primary">{{ number_format($percentage, 1) }}%</span>
                            </div>
                            <div class="progress mt-2" style="height: 6px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%"></div>
                            </div>
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
                    <th>Payment Method</th>
                    <th>Reference</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['payments'] as $payment)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                    <td>
                        @php
                            $method = $payment->payment_method ?? 'Unknown';
                            $badgeClass = match(strtolower($method)) {
                                'cash' => 'bg-success',
                                'card', 'credit card', 'debit card' => 'bg-primary',
                                'cheque', 'check' => 'bg-info',
                                'transfer', 'bank transfer' => 'bg-warning',
                                default => 'bg-secondary'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($method) }}</span>
                    </td>
                    <td>{{ $payment->payment_reference ?? '-' }}</td>
                    <td class="fw-bold text-success">Rs. {{ number_format($payment->amount, 2) }}</td>
                    <td>
                        @if($payment->status === 'approved' || $payment->status === 'paid' || $payment->status === 'completed')
                            <span class="badge bg-success">{{ ucfirst($payment->status) }}</span>
                        @elseif($payment->status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($payment->status ?? 'N/A') }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td colspan="3" class="fw-bold">Total</td>
                    <td class="fw-bold text-success">Rs. {{ number_format($reportData['total'], 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="alert alert-info text-center py-4">
        <i class="bi bi-info-circle display-6 d-block mb-2"></i>
        No payment data found for the selected period.
    </div>
    @endif
</div>
