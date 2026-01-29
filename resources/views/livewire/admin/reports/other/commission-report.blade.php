{{-- Commission Report --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-currency-dollar text-primary me-2"></i>Commission Report
    </h6>

    @php $data = collect($reportData); @endphp
    @if($data->count() > 0)
    @php
        $totalCommission = $data->sum('total_commission');
        $totalTransactions = $data->sum('transactions');
    @endphp
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">{{ $data->count() }}</div>
                <div class="stat-label">Staff Members</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card success">
                <div class="stat-value">Rs. {{ number_format($totalCommission, 2) }}</div>
                <div class="stat-label">Total Commission</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card warning">
                <div class="stat-value">{{ $totalTransactions }}</div>
                <div class="stat-label">Total Transactions</div>
            </div>
        </div>
    </div>

    <!-- Top Earners -->
    <div class="row mb-4">
        @foreach($data->take(3) as $index => $item)
        <div class="col-md-4 mb-3">
            <div class="card h-100 {{ $index === 0 ? 'border-warning border-2' : '' }}">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="rounded-circle bg-{{ $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'light') }} text-{{ $index < 2 ? 'white' : 'dark' }} d-flex align-items-center justify-content-center mx-auto" style="width: 60px; height: 60px;">
                            @if($index === 0)
                                <i class="bi bi-trophy fs-4"></i>
                            @else
                                <span class="fw-bold fs-5">#{{ $index + 1 }}</span>
                            @endif
                        </div>
                    </div>
                    <h6 class="mb-1">{{ $item['staff']->name ?? 'Unknown' }}</h6>
                    <p class="text-muted small mb-2">{{ $item['transactions'] }} sales</p>
                    <h4 class="fw-bold text-success mb-0">Rs. {{ number_format($item['total_commission'], 2) }}</h4>
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
                    <th>Staff Name</th>
                    <th>Email</th>
                    <th>Transactions</th>
                    <th>Total Commission</th>
                    <th>Avg per Sale</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $item)
                <tr>
                    <td>
                        @if($index === 0)
                            <span class="badge bg-warning text-dark"><i class="bi bi-trophy"></i> #1</span>
                        @elseif($index === 1)
                            <span class="badge bg-secondary">#2</span>
                        @elseif($index === 2)
                            <span class="badge bg-danger">#3</span>
                        @else
                            <span class="badge bg-light text-dark">#{{ $index + 1 }}</span>
                        @endif
                    </td>
                    <td class="fw-bold">{{ $item['staff']->name ?? 'Unknown' }}</td>
                    <td>{{ $item['staff']->email ?? '-' }}</td>
                    <td>{{ $item['transactions'] }}</td>
                    <td class="fw-bold text-success">Rs. {{ number_format($item['total_commission'], 2) }}</td>
                    <td>
                        @php
                            $avgPerSale = $item['transactions'] > 0 ? $item['total_commission'] / $item['transactions'] : 0;
                        @endphp
                        Rs. {{ number_format($avgPerSale, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td colspan="3" class="fw-bold">Total</td>
                    <td class="fw-bold">{{ $totalTransactions }}</td>
                    <td class="fw-bold text-success">Rs. {{ number_format($totalCommission, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="alert alert-info text-center py-4">
        <i class="bi bi-info-circle display-6 d-block mb-2"></i>
        No commission data found for the selected period.
    </div>
    @endif
</div>
