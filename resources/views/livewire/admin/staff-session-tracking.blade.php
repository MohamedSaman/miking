<div>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-white">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-0">
                            <div>
                                <h4 class="mb-1 fw-bold text-dark">Staff Attendance & Sales Tracking</h4>
                                <p class="text-muted mb-0">Monitor staff login sessions and real-time sales performance</p>
                                @if (session()->has('message'))
                                    <div class="alert alert-success py-1 px-3 mt-2 mb-0 small border-0 shadow-sm rounded-pill d-inline-block">
                                        <i class="bi bi-check-circle-fill me-1"></i> {{ session('message') }}
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex gap-3">
                                <div class="input-group" style="width: 300px;">
                                    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control bg-light border-0" placeholder="Search staff..." wire:model.live="search">
                                </div>
                                <select class="form-select border-0 bg-light" style="width: 200px;" wire:model.live="dateFilter">
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="this_week">This Week</option>
                                    <option value="this_month">This Month</option>
                                    <option value="all">All Time</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!$showSessionDetails)
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4">Staff Member</th>
                                        <th>Session Info</th>
                                        <th>Attendance ({{ $this->targetDate == now()->toDateString() ? 'Today' : \Carbon\Carbon::parse($this->targetDate)->format('M d') }})</th>
                                        <th>Sales ({{ ucfirst(str_replace('_', ' ', $dateFilter)) }})</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($staffMembers as $staff)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                                                    {{ strtoupper(substr($staff->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold">{{ $staff->name }}</h6>
                                                    <small class="text-muted">{{ $staff->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $activeSession = DB::table('sessions')->where('user_id', $staff->id)->orderBy('last_activity', 'desc')->first();
                                                $isOnline = $activeSession && ($activeSession->last_activity > now()->subMinutes(5)->timestamp);
                                            @endphp
                                            @if($isOnline)
                                            <span class="badge bg-success bg-opacity-10 text-success border-0 px-3 py-2 rounded-pill mb-1 d-inline-block">
                                                <span class="p-1 bg-success rounded-circle d-inline-block me-1"></span> Online
                                            </span>
                                            @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border-0 px-3 py-2 rounded-pill mb-1 d-inline-block">
                                                <span class="p-1 bg-secondary rounded-circle d-inline-block me-1"></span> Offline
                                            </span>
                                            @endif
                                            @if($activeSession && $this->targetDate == now()->toDateString())
                                                <br><small class="text-muted" style="font-size: 10px;">{{ \Carbon\Carbon::createFromTimestamp($activeSession->last_activity)->diffForHumans() }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $att = $staff->getAttendanceStatus($this->targetDate);
                                                
                                                if ($att['status'] === 'pending' && $this->targetDate == now()->toDateString() && !$isOnline) {
                                                    $att['status'] = 'absent';
                                                    $att['present_status'] = 'absent';
                                                }
                                            @endphp
                                            
                                            <div class="d-flex align-items-center gap-2">
                                                @if($att['status'] === 'present' || $att['present_status'] === 'present')
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-bold shadow-sm d-flex align-items-center">
                                                            @if(isset($att['is_auto_marked']) && $att['is_auto_marked'])
                                                                <i class="bi bi-shield-check-fill me-1 fs-6"></i> Auto Success
                                                            @else
                                                                <i class="bi bi-check-circle-fill me-1 fs-6"></i> Present
                                                            @endif
                                                        </span>
                                                        <button wire:click="markAsAbsent({{ $staff->id }})" class="ms-2 p-0 border-0 bg-transparent text-danger shadow-none" style="cursor: pointer;" title="Reject Attendance">
                                                            <i class="bi bi-x-circle fs-5"></i>
                                                        </button>
                                                    </div>
                                                @elseif($att['status'] === 'pending' || $att['present_status'] === 'pending')
                                                    <div class="d-flex flex-column gap-1">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold shadow-sm border border-warning d-flex align-items-center animate-pulse">
                                                                <i class="bi bi-clock-fill me-1"></i> Pending Review
                                                            </span>
                                                            <div class="d-flex gap-3 ms-1">
                                                                <button wire:click="markAsPresent({{ $staff->id }})" class="p-0 border-0 bg-transparent text-success shadow-none" style="cursor: pointer;" title="Approve">
                                                                    <i class="bi bi-check-circle fs-5"></i>
                                                                </button>
                                                                <button wire:click="markAsAbsent({{ $staff->id }})" class="p-0 border-0 bg-transparent text-danger shadow-none" style="cursor: pointer;" title="Reject">
                                                                    <i class="bi bi-x-circle fs-5"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        @if($att['type'] === 'detected')
                                                            <div class="ms-2">
                                                                <span class="badge bg-info bg-opacity-10 text-info border-0 p-0" style="font-size: 11px; font-weight: 500;">
                                                                    <i class="bi bi-info-circle me-1"></i> {{ $att['reason'] }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @elseif($att['status'] === 'absent')
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill fw-bold d-flex align-items-center">
                                                            <i class="bi bi-x-circle-fill me-1 fs-6"></i> Absent
                                                        </span>
                                                        @if($att['has_activity'] ?? false)
                                                            <button wire:click="markAsPresent({{ $staff->id }})" class="ms-2 p-0 border-0 bg-transparent text-success shadow-none" style="cursor: pointer;" title="Mark as Present">
                                                                <i class="bi bi-plus-circle fs-5"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-light text-muted px-3 py-2 rounded-pill border fw-bold">
                                                            {{ strtoupper($att['status']) }}
                                                        </span>
                                                        @if($att['has_activity'] ?? false)
                                                            <button wire:click="markAsPresent({{ $staff->id }})" class="ms-2 p-0 border-0 bg-transparent text-primary shadow-none" style="cursor: pointer;">
                                                                <i class="bi bi-pencil-circle fs-5"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $staff->sales_count }}</span> Sales
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-outline-primary btn-sm px-3 rounded-pill" wire:click="selectStaff({{ $staff->id }})">
                                                <i class="bi bi-eye me-1"></i> Track Activity
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <i class="bi bi-people display-1 text-light"></i>
                                            <p class="text-muted mt-3">No staff members found matching your search.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 py-3">
                        {{ $staffMembers->links() }}
                    </div>
                </div>
            </div>
        </div>
        @else
        {{-- Detailed Tracking View --}}
        <div class="row">
            <div class="col-12 mb-4">
                <button class="btn btn-dark btn-sm rounded-pill px-4 shadow-sm" wire:click="closeDetails">
                    <i class="bi bi-arrow-left me-2"></i> Back to List
                </button>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <h5 class="fw-bold mb-3"><i class="bi bi-cpu me-2 text-primary"></i>Active Sessions</h5>
                    </div>
                    <div class="card-body px-4">
                        <div class="session-list">
                            @forelse($sessions as $session)
                            <div class="p-3 bg-light rounded-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-white text-dark shadow-xs">{{ $session['ip_address'] }}</span>
                                    <small class="text-muted">{{ $session['last_activity']->diffForHumans() }}</small>
                                </div>
                                <p class="small text-muted mb-0 text-truncate" title="{{ $session['user_agent'] }}">
                                    <i class="bi bi-laptop me-1"></i> {{ substr($session['user_agent'], 0, 40) }}...
                                </p>
                            </div>
                            @empty
                            <div class="text-center py-4">
                                <p class="text-muted">No active sessions found.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-4 px-4 pb-0">
                        <h5 class="fw-bold mb-3"><i class="bi bi-cart-check me-2 text-success"></i>Sales History for {{ $selectedStaff->name }}</h5>
                        <span class="badge bg-light text-dark px-3 py-2 rounded-pill">{{ ucfirst($dateFilter) }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4">Invoice #</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Time</th>
                                        <th class="pe-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sales as $sale)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $sale->invoice_number }}</td>
                                        <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                                        <td><span class="fw-bold text-dark">Rs. {{ number_format($sale->total_amount, 2) }}</span></td>
                                        <td>{{ $sale->created_at->format('h:i A') }}</td>
                                        <td class="pe-4">
                                            @if($sale->status == 'completed')
                                            <span class="badge bg-success-subtle text-success px-2 py-1 rounded">Completed</span>
                                            @else
                                            <span class="badge bg-warning-subtle text-warning px-2 py-1 rounded">{{ ucfirst($sale->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <p class="text-muted mb-0">No sales recorded during this period.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <style>
        .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .bg-success-subtle { background-color: rgba(25, 135, 84, 0.1) !important; }
        .bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1) !important; }
        .avatar-sm { font-size: 14px; }
        .animate-pulse {
            animation: pulse-animation 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse-animation {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.02); }
        }
        .btn-group .btn {
            font-size: 11px;
            font-weight: 600;
        }
    </style>
</div>
