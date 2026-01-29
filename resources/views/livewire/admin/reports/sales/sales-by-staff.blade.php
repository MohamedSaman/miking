{{-- Sales by Staff - Top 5 --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-people text-primary me-2"></i>Sales by Staff - Top 5
    </h6>

    @php $data = collect($reportData); @endphp
    @if($data->count() > 0)
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="stat-card success">
                <div class="stat-value">Rs. {{ number_format($data->sum('total_sales'), 2) }}</div>
                <div class="stat-label">Total Sales by Top 5</div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">{{ $data->sum('total_transactions') }}</div>
                <div class="stat-label">Total Transactions</div>
            </div>
        </div>
    </div>

    <!-- Top 5 Staff Cards -->
    <div class="row mb-4">
        @foreach($data as $index => $item)
        <div class="col-md-4 mb-3">
            <div class="card h-100 {{ $index === 0 ? 'border-warning' : '' }}">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-{{ $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'light') }} text-{{ $index < 2 ? 'white' : 'dark' }} d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <strong>#{{ $index + 1 }}</strong>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $item->user->name ?? 'Unknown' }}</h6>
                            <small class="text-muted">{{ $item->user->email ?? '' }}</small>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="fw-bold text-success">Rs. {{ number_format($item->total_sales, 2) }}</div>
                            <small class="text-muted">Total Sales</small>
                        </div>
                        <div class="col-6">
                            <div class="fw-bold">{{ $item->total_transactions }}</div>
                            <small class="text-muted">Transactions</small>
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
                    <th>Staff Name</th>
                    <th>Email</th>
                    <th>Transactions</th>
                    <th>Total Sales</th>
                    <th>Avg Sale</th>
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
                    <td class="fw-bold">{{ $item->user->name ?? 'Unknown' }}</td>
                    <td>{{ $item->user->email ?? '-' }}</td>
                    <td>{{ $item->total_transactions }}</td>
                    <td class="fw-bold text-success">Rs. {{ number_format($item->total_sales, 2) }}</td>
                    <td>Rs. {{ number_format($item->avg_sale, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="alert alert-info text-center py-4">
        <i class="bi bi-info-circle display-6 d-block mb-2"></i>
        No staff sales data found for the selected period.
    </div>
    @endif
</div>
