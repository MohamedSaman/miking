{{-- Invoice Aging Report --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-calendar-x text-primary me-2"></i>Invoice Aging Report
    </h6>

    @if(isset($reportData['invoices']) && count($reportData['invoices']) > 0)
    <!-- Aging Buckets Summary -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card success">
                <div class="stat-value">Rs. {{ number_format($reportData['buckets']['0-30 days'] ?? 0, 2) }}</div>
                <div class="stat-label">0-30 Days</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card warning">
                <div class="stat-value">Rs. {{ number_format($reportData['buckets']['31-60 days'] ?? 0, 2) }}</div>
                <div class="stat-label">31-60 Days</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">Rs. {{ number_format($reportData['buckets']['61-90 days'] ?? 0, 2) }}</div>
                <div class="stat-label">61-90 Days</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card danger">
                <div class="stat-value">Rs. {{ number_format($reportData['buckets']['90+ days'] ?? 0, 2) }}</div>
                <div class="stat-label">90+ Days (Critical)</div>
            </div>
        </div>
    </div>

    <!-- Visual Aging Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Aging Distribution</h6>
            @php
                $total = array_sum($reportData['buckets']);
            @endphp
            <div class="progress" style="height: 30px;">
                @if($total > 0)
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($reportData['buckets']['0-30 days'] / $total) * 100 }}%">
                    0-30d
                </div>
                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ($reportData['buckets']['31-60 days'] / $total) * 100 }}%">
                    31-60d
                </div>
                <div class="progress-bar bg-info" role="progressbar" style="width: {{ ($reportData['buckets']['61-90 days'] / $total) * 100 }}%">
                    61-90d
                </div>
                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ ($reportData['buckets']['90+ days'] / $total) * 100 }}%">
                    90+d
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Total Amount</th>
                    <th>Due Amount</th>
                    <th>Days Overdue</th>
                    <th>Aging Bucket</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['invoices'] as $invoice)
                <tr>
                    <td><span class="badge bg-light text-dark">{{ $invoice->invoice_number }}</span></td>
                    <td>{{ \Carbon\Carbon::parse($invoice->created_at)->format('d M Y') }}</td>
                    <td>{{ $invoice->customer->name ?? 'Walk-in Customer' }}</td>
                    <td>Rs. {{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="fw-bold text-danger">Rs. {{ number_format($invoice->due_amount, 2) }}</td>
                    <td>{{ $invoice->days_overdue }} days</td>
                    <td>
                        @switch($invoice->aging_bucket)
                            @case('0-30 days')
                                <span class="badge bg-success">{{ $invoice->aging_bucket }}</span>
                                @break
                            @case('31-60 days')
                                <span class="badge bg-warning">{{ $invoice->aging_bucket }}</span>
                                @break
                            @case('61-90 days')
                                <span class="badge bg-info">{{ $invoice->aging_bucket }}</span>
                                @break
                            @default
                                <span class="badge bg-danger">{{ $invoice->aging_bucket }}</span>
                        @endswitch
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td colspan="4" class="fw-bold">Total Outstanding</td>
                    <td class="fw-bold text-danger">Rs. {{ number_format(collect($reportData['invoices'])->sum('due_amount'), 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="alert alert-success text-center py-4">
        <i class="bi bi-check-circle display-6 d-block mb-2"></i>
        Great! No outstanding invoices found.
    </div>
    @endif
</div>
