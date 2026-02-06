<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-wallet2 text-success me-2"></i> Staff Salary Management
            </h3>
            <p class="text-muted mb-0">Manage staff salaries including basic salary, allowances, bonuses, and advance payments</p>
        </div>
    </div>

    {{-- Month Selection --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-end g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Select Month</label>
                    <input type="month" class="form-control" wire:model.live="selectedMonth">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Search Staff</label>
                    <input type="text" class="form-control" wire:model.live="searchStaff" placeholder="Search by name or email...">
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-info p-2">
                        <i class="bi bi-info-circle me-1"></i> Total Staff: {{ $staffList->count() }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Salary Summary Cards --}}
    <div class="row g-3 mb-4 row-cols-1 row-cols-sm-2 row-cols-md-4">
        <div class="col">
            <div class="card border-0 shadow-sm h-100 bg-primary bg-opacity-10">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="bi bi-cash-coin fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-primary mb-1">Rs.{{ number_format($staffList->sum('basic_salary'), 2) }}</h5>
                    <small class="text-muted">Total Basic Salary</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100 bg-info bg-opacity-10">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="bi bi-gift fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-info mb-1">Rs.{{ number_format($staffList->sum('allowance'), 2) }}</h5>
                    <small class="text-muted">Total Allowance</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100 bg-success bg-opacity-10">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="bi bi-star fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-success mb-1">Rs.{{ number_format($staffList->sum('bonus'), 2) }}</h5>
                    <small class="text-muted">Total Sales Bonus</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100 bg-warning bg-opacity-10">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="bi bi-currency-dollar fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-warning mb-1">Rs.{{ number_format($staffList->sum('total_salary'), 2) }}</h5>
                    <small class="text-muted">Total Net Salary</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Staff Salary Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-table text-primary me-2"></i>Staff Salary Details
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Staff Name</th>
                            <th class="text-center">Basic Salary</th>
                            <th class="text-center">Allowance</th>
                            <th class="text-center">Sales Comm</th>
                            <th class="text-center">Advance</th>
                            <th class="text-center">Net Salary</th>
                            <th class="text-center">Salary Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staffList as $staff)
                        <tr wire:key="staff-{{ $staff['id'] }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                        {{ strtoupper(substr($staff['name'], 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $staff['name'] }}</div>
                                        <small class="text-muted">{{ $staff['email'] }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fw-semibold text-primary">Rs.{{ number_format($staff['basic_salary'], 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-semibold text-info">Rs.{{ number_format($staff['allowance'], 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-semibold text-success">Rs.{{ number_format($staff['bonus'], 2) }}</span>
                            </td>
                            <td class="text-center">
                                @if($staff['advance_salary'] > 0)
                                    <div>
                                        <span class="fw-semibold text-danger">-Rs.{{ number_format($staff['advance_salary'], 2) }}</span>
                                    </div>
                                    <small class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> {{ $staff['advance_count'] }} Paid
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="fw-bold" style="color: #2a83df;">
                                    Rs.{{ number_format($staff['total_salary'], 2) }}
                                </div>
                            </td>
                            <td class="text-center">
                                @if($staff['payment_status'] === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($staff['payment_status'] === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($staff['payment_status']) }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-gear"></i> Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <button class="dropdown-item py-2" wire:click="openAdvanceModal({{ $staff['id'] }}, '{{ $staff['name'] }}')">
                                                <i class="bi bi-cash-coin me-2" style="color: #ff9800; font-size: 1.1em;"></i> <strong>Add Advance</strong>
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button class="dropdown-item py-2" wire:click="processSalaryPayment({{ $staff['id'] }})">
                                                <i class="bi bi-check-circle me-2" style="color: #28a745;"></i> Mark as Paid
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Advance Salary Modal --}}
    @if($showAdvanceModal)
    <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); display: block;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="bi bi-cash-coin me-2"></i> Add Advance Payment
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeAdvanceModal()"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-person-circle me-2"></i>
                        <strong>Staff:</strong> {{ $advanceStaffName }}<br>
                        <small class="text-muted">Month: {{ date('F Y', strtotime($selectedMonth . '-01')) }}</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Advance Amount *</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-warning text-dark">Rs.</span>
                            <input type="number" class="form-control" wire:model="advanceAmount" placeholder="Enter amount..." step="0.01" min="1">
                        </div>
                        @error('advanceAmount') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold">Reason (Optional)</label>
                        <textarea class="form-control" wire:model="advanceReason" rows="2" placeholder="e.g., Emergency, Medical, Personal..."></textarea>
                        @error('advanceReason') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="closeAdvanceModal()">
                        <i class="bi bi-x me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-warning text-dark fw-bold" wire:click="processAdvanceSalary()">
                        <i class="bi bi-check-circle me-1"></i> Pay Advance
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Advance Salary Records Section --}}
    @if(count($advanceSalaryRecords) > 0)
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-history text-warning me-2"></i>Recent Advance Payments - {{ date('F Y', strtotime($selectedMonth . '-01')) }}
            </h5>
            <span class="badge bg-warning text-dark fs-6">
                {{ count($advanceSalaryRecords) }} Record(s)
            </span>
        </div>
        <div class="card-body p-0">
            @if(count($advanceSalaryRecords) > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Staff Name</th>
                            <th class="text-center">Date</th>
                            <th class="text-end">Amount</th>
                            <th>Reason</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($advanceSalaryRecords as $index => $record)
                        <tr class="{{ $record->status === 'cancelled' ? 'table-secondary text-muted' : '' }}">
                            <td>
                                <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                        <i class="bi bi-person text-warning"></i>
                                    </div>
                                    <span class="fw-semibold">{{ $record->staff->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    {{ \Carbon\Carbon::parse($record->advance_date)->format('d M Y') }}
                                </small>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold {{ $record->status === 'cancelled' ? 'text-muted text-decoration-line-through' : 'text-warning' }}">
                                    Rs.{{ number_format($record->amount, 2) }}
                                </span>
                            </td>
                            <td>
                                <small class="{{ $record->status === 'cancelled' ? 'text-muted' : '' }}">
                                    {{ $record->reason ?: '-' }}
                                </small>
                            </td>
                            <td class="text-center">
                                @if($record->status === 'paid')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i> Paid
                                    </span>
                                @elseif($record->status === 'cancelled')
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-x-circle me-1"></i> Cancelled
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($record->status === 'paid')
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            wire:click="confirmCancelAdvance({{ $record->id }})"
                                            title="Cancel Advance">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No advance payments recorded for this month</p>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
