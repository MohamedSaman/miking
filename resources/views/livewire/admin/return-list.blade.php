<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-arrow-return-left text-success me-2"></i> Product Returns List
            </h3>
            <p class="text-muted mb-0">View and manage all product returns</p>
        </div>
    </div>

    @if(!$this->isStaff())
    <!-- Tabs for Admin -->
    <ul class="nav nav-tabs mb-4" id="returnTabs" role="tablist" style="border-bottom: 2px solid #dee2e6;">
        <li class="nav-item" role="presentation">
            <button class="nav-link px-4 py-2 {{ $activeTab === 'returns' ? 'active fw-bold' : '' }}" 
                    wire:click="setActiveTab('returns')"
                    type="button" role="tab"
                    style="white-space: nowrap; {{ $activeTab === 'returns' ? 'border-bottom: 3px solid #0d6efd; color: #0d6efd;' : 'color: #6c757d;' }}">
                <i class="bi bi-list-ul me-2"></i>Direct Returns
                <span class="badge bg-primary ms-2">{{ count($returns) }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link px-4 py-2 {{ $activeTab === 'staff-returns' ? 'active fw-bold' : '' }}" 
                    wire:click="setActiveTab('staff-returns')"
                    type="button" role="tab"
                    style="white-space: nowrap; {{ $activeTab === 'staff-returns' ? 'border-bottom: 3px solid #0d6efd; color: #0d6efd;' : 'color: #6c757d;' }}">
                <i class="bi bi-person-badge me-2"></i>Staff Return Requests
                @if($pendingStaffReturnsCount > 0)
                <span class="badge bg-warning text-dark ms-2">{{ $pendingStaffReturnsCount }} Pending</span>
                @else
                <span class="badge bg-secondary ms-2">{{ count($staffReturns) }}</span>
                @endif
            </button>
        </li>
    </ul>
    @endif

    <!-- Direct Returns Table (existing) -->
    <div class="{{ (!$this->isStaff() && $activeTab !== 'returns') ? 'd-none' : '' }}">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-list-ul text-primary me-2"></i> Returns List
                </h5>
                <span class="badge bg-primary">{{ count($returns) }} records</span>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="width: 60%; margin: auto">
                <!-- 🔍 Search Bar -->
                    <div class="search-bar flex-grow-1">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" wire:model.live="returnSearch"
                                placeholder="Search by invoice number or product name...">
                        </div>
                    </div>
                </div>
            <div class="d-flex align-items-center gap-2">
                <label class="text-sm text-muted fw-medium">Show</label>
                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 80px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="500">500</option>
                </select>
                <span class="text-sm text-muted">entries</span>
            </div>
        </div>
        <div class="card-body p-0 overflow-auto">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Invoice Number</th>
                            <th>Product</th>
                            <th>Return Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $index => $return)
                        <tr style="cursor:pointer" wire:key="return-{{ $return->id }}">
                            <td class="ps-4">{{ $index + 1 }}</td>
                            <td wire:click="showReceipt({{ $return->id }})">{{ $return->sale?->invoice_number ?? '-' }}</td>
                            <td wire:click="showReceipt({{ $return->id }})">{{ $return->product?->name ?? '-' }}</td>
                            <td wire:click="showReceipt({{ $return->id }})">{{ $return->return_quantity }}</td>
                            <td wire:click="showReceipt({{ $return->id }})">Rs.{{ number_format($return->selling_price, 2) }}</td>
                            <td wire:click="showReceipt({{ $return->id }})">Rs.{{ number_format($return->total_amount, 2) }}</td>
                            <td wire:click="showReceipt({{ $return->id }})">{{ $return->created_at?->format('M d, Y') }}</td>
                            <td class="text-end pe-4">
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false">
            <i class="bi bi-gear-fill"></i> Actions
        </button>

        <ul class="dropdown-menu dropdown-menu-end">

            <!-- Delete Return -->
            <li>
                <button class="dropdown-item"
                        wire:click="deleteReturn({{ $return->id }})"
                        wire:loading.attr="disabled"
                        wire:target="deleteReturn({{ $return->id }})">

                    <span wire:loading wire:target="deleteReturn({{ $return->id }})">
                        <i class="spinner-border spinner-border-sm me-2"></i>
                        Loading...
                    </span>
                    <span wire:loading.remove wire:target="deleteReturn({{ $return->id }})">
                        <i class="bi bi-trash text-danger me-2"></i>
                        Delete
                    </span>
                </button>
            </li>

        </ul>
    </div>
</td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-arrow-return-left display-4 d-block mb-2"></i>
                                No returns found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($returns->hasPages())
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-center">
                    {{ $returns->links('livewire.custom-pagination') }}
                </div>
            </div>
            @endif
        </div>
    </div>
    </div><!-- End Direct Returns Tab -->

    @if(!$this->isStaff())
    <!-- Staff Return Requests Table (Admin Only) -->
    <div class="{{ $activeTab !== 'staff-returns' ? 'd-none' : '' }}">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-person-badge text-warning me-2"></i> Staff Return Requests
                    </h5>
                    <span class="badge bg-warning text-dark">{{ count($staffReturns) }} requests</span>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="width: 60%; margin: auto">
                    <div class="search-bar flex-grow-1">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" wire:model.live="staffReturnSearch"
                                placeholder="Search by invoice, product, or staff name...">
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="text-sm text-muted fw-medium">Show</label>
                    <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 80px;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-sm text-muted">entries</span>
                </div>
            </div>
            <div class="card-body p-0 overflow-auto">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Invoice Number</th>
                                <th>Product</th>
                                <th>Staff</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffReturns as $index => $staffReturn)
                            <tr wire:key="staff-return-{{ $staffReturn->id }}">
                                <td class="ps-4">{{ $index + 1 }}</td>
                                <td>{{ $staffReturn->sale?->invoice_number ?? '-' }}</td>
                                <td>{{ $staffReturn->product?->name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $staffReturn->staff?->name ?? '-' }}</span>
                                </td>
                                <td>{{ $staffReturn->quantity }}</td>
                                <td>Rs.{{ number_format($staffReturn->unit_price, 2) }}</td>
                                <td>Rs.{{ number_format($staffReturn->total_amount, 2) }}</td>
                                <td>
                                    @if($staffReturn->status === 'pending')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>
                                    @elseif($staffReturn->status === 'approved')
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Approved</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                    @endif
                                </td>
                                <td>{{ $staffReturn->created_at?->format('M d, Y') }}</td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                type="button"
                                                data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="bi bi-gear-fill"></i> Actions
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <!-- View Details -->
                                            <li>
                                                <button class="dropdown-item" wire:click="showStaffReturnDetails({{ $staffReturn->id }})">
                                                    <i class="bi bi-eye text-primary me-2"></i>View Details
                                                </button>
                                            </li>
                                            @if($staffReturn->status === 'pending')
                                            <li><hr class="dropdown-divider"></li>
                                            <!-- Approve -->
                                            <li>
                                                <button class="dropdown-item" wire:click="approveStaffReturn({{ $staffReturn->id }})">
                                                    <i class="bi bi-check-circle text-success me-2"></i>Approve
                                                </button>
                                            </li>
                                            <!-- Reject -->
                                            <li>
                                                <button class="dropdown-item" wire:click="rejectStaffReturn({{ $staffReturn->id }})">
                                                    <i class="bi bi-x-circle text-danger me-2"></i>Reject
                                                </button>
                                            </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <!-- Delete -->
                                            <li>
                                                <button class="dropdown-item" wire:click="deleteStaffReturn({{ $staffReturn->id }})">
                                                    <i class="bi bi-trash text-danger me-2"></i>Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    No staff return requests found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($staffReturns instanceof \Illuminate\Pagination\LengthAwarePaginator && $staffReturns->hasPages())
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-center">
                        {{ $staffReturns->links('livewire.custom-pagination') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
    

    <!-- Receipt Modal (Bill Style) -->
    <div wire:ignore.self class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="printableReturnReceipt">
                <!-- Header – logo + company name -->
                <div class="modal-header text-center border-0" style="background: linear-gradient(135deg, #2a83df 0%, #1a5fb8 100%); color: #fff;">
                    <div class="w-100">
                        <img src="{{ asset('images/MI-King.png') }}" alt="Logo"
                             class="img-fluid mb-2" style="max-height:60px;">
                        <h4 class="mb-0 fw-bold">MI-KING</h4>
                        
                    </div>
                    <button type="button" class="btn-close btn-close-white closebtn"
                            wire:click="closeModal"></button>
                </div>

                @if($selectedReturn)
                <div class="modal-body">
                    <!-- Customer + Return info (two columns) -->
                    <div class="row mb-4">
                        <div class="col-6">
                            <strong>Customer :</strong><br>
                            {{ $selectedReturn->sale?->customer?->name ?? 'Walk-in Customer' }}<br>
                            {{ $selectedReturn->sale?->customer?->address ?? '' }}<br>
                            Tel: {{ $selectedReturn->sale?->customer?->phone ?? '' }}
                        </div>
                        <div class="col-6">
                            <table class="table table-sm table-borderless">
                                <tr><td><strong>Return No :</strong></td><td>{{ $selectedReturn->id }}</td></tr>
                                <tr><td><strong>Invoice No :</strong></td><td>{{ $selectedReturn->sale?->invoice_number ?? '-' }}</td></tr>
                                <tr><td><strong>Return Status :</strong></td><td>Completed</td></tr>
                                <tr><td><strong>Return Date :</strong></td><td>{{ $selectedReturn->created_at->format('d/m/Y H:i') }}</td></tr>
                            </table>
                        </div>
                    </div>

                    <!-- Items table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:5%">#</th>
                                    <th style="width:15%">ITEM CODE</th>
                                    <th>DESCRIPTION</th>
                                    <th class="text-center" style="width:12%">RETURN QTY</th>
                                    <th class="text-end" style="width:12%">UNIT PRICE</th>
                                    <th class="text-end" style="width:12%">SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>{{ $selectedReturn->product?->code ?? 'N/A' }}</td>
                                    <td>{{ $selectedReturn->product?->name ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $selectedReturn->return_quantity }} Pc(s)</td>
                                    <td class="text-end">Rs.{{ number_format($selectedReturn->selling_price, 2) }}</td>
                                    <td class="text-end">Rs.{{ number_format($selectedReturn->total_amount, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals – right-aligned block -->
                    <div class="row">
                        <div class="col-7"></div>
                        <div class="col-5">
                            <table class="table table-sm table-borderless">
                                <tr><td class="text-end"><strong>Total Return Amount (LKR)</strong></td><td class="text-end">Rs.{{ number_format($selectedReturn->total_amount, 2) }}</td></tr>
                                <tr><td class="text-end"><strong>Refunded Amount (LKR)</strong></td><td class="text-end">Rs.{{ number_format($selectedReturn->total_amount, 2) }}</td></tr>
                                <tr><td class="text-end"><strong>Balance (LKR)</strong></td><td class="text-end">Rs.0.00</td></tr>
                            </table>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="row mt-4  note">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body p-3">
                                    <strong>Notes:</strong><br>
                                    {{ $selectedReturn->notes ?? 'No additional notes.' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer – logos + address + note -->
                    <div class="mt-4 text-center small">
                        
                        <p class="mb-0">
                            <strong>ADDRESS :</strong> 122/10A, Super Paradise Market, Keyzer Street, Colombo 11 .<br>
                            <strong>TEL :</strong> (076) 1234567, <strong>EMAIL :</strong> sample@gmail.com
                        </p>
                        <p class="mt-1 text-muted">
                            Goods return will be accepted within 10 days only. Electrical and body parts non-returnable.
                        </p>
                    </div>
                </div>
                @endif

                <!-- Modal footer buttons -->
                <div class="modal-footer bg-light justify-content-between">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                    <div>
                        @if($currentReturnId)
                       
                        <button type="button" class="btn btn-primary"
                                onclick="printReturnReceipt()">
                            <i class="bi bi-printer me-1"></i> Print
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div wire:ignore.self class="modal fade" id="deleteReturnModal" tabindex="-1"
         aria-labelledby="deleteReturnModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle me-2"></i> Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    @if($selectedReturn)
                    <div class="alert alert-danger">
                        <h6 class="alert-heading">Warning!</h6>
                        <p class="mb-0">You are about to delete this return record. This action cannot be undone and will adjust product stock accordingly.</p>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <p><strong>Return ID:</strong> #{{ $selectedReturn->id }}</p>
                            <p><strong>Product:</strong> {{ $selectedReturn->product?->name ?? '-' }}</p>
                            <p><strong>Quantity:</strong> {{ $selectedReturn->return_quantity }}</p>
                            <p><strong>Amount:</strong> Rs.{{ number_format($selectedReturn->total_amount, 2) }}</p>
                            <p><strong>Date:</strong> {{ $selectedReturn->created_at?->format('M d, Y') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="confirmDeleteReturn">
                        <i class="bi bi-trash me-1"></i> Delete Return
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Return Details Modal -->
    <div wire:ignore.self class="modal fade" id="staffReturnDetailsModal" tabindex="-1"
         aria-labelledby="staffReturnDetailsModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-info-circle me-2"></i> Staff Return Request Details</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    @if($selectedStaffReturn)
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light"><strong>Return Information</strong></div>
                                <div class="card-body">
                                    <p><strong>Return ID:</strong> #{{ $selectedStaffReturn->id }}</p>
                                    <p><strong>Invoice:</strong> {{ $selectedStaffReturn->sale?->invoice_number ?? '-' }}</p>
                                    <p><strong>Status:</strong> 
                                        @if($selectedStaffReturn->status === 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($selectedStaffReturn->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @else
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </p>
                                    <p><strong>Date:</strong> {{ $selectedStaffReturn->created_at?->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light"><strong>Staff & Customer</strong></div>
                                <div class="card-body">
                                    <p><strong>Staff Member:</strong> {{ $selectedStaffReturn->staff?->name ?? '-' }}</p>
                                    <p><strong>Customer:</strong> {{ $selectedStaffReturn->customer?->name ?? ($selectedStaffReturn->sale?->customer?->name ?? 'Walk-in') }}</p>
                                    <p><strong>Phone:</strong> {{ $selectedStaffReturn->customer?->phone ?? ($selectedStaffReturn->sale?->customer?->phone ?? '-') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-header bg-light"><strong>Product Details</strong></div>
                        <div class="card-body">
                            <table class="table table-bordered mb-0">
                                <tr>
                                    <th width="30%">Product</th>
                                    <td>{{ $selectedStaffReturn->product?->name ?? '-' }} ({{ $selectedStaffReturn->product?->code ?? '-' }})</td>
                                </tr>
                                <tr>
                                    <th>Quantity</th>
                                    <td>{{ $selectedStaffReturn->quantity }} Pc(s)</td>
                                </tr>
                                <tr>
                                    <th>Unit Price</th>
                                    <td>Rs.{{ number_format($selectedStaffReturn->unit_price, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Total Amount</th>
                                    <td class="fw-bold text-danger">Rs.{{ number_format($selectedStaffReturn->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Condition</th>
                                    <td>
                                        @if($selectedStaffReturn->is_damaged)
                                            <span class="badge bg-danger">Damaged</span>
                                        @else
                                            <span class="badge bg-success">Good Condition</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header bg-light"><strong>Reason & Notes</strong></div>
                        <div class="card-body">
                            <p><strong>Reason:</strong> {{ ucfirst(str_replace('_', ' ', $selectedStaffReturn->reason ?? '-')) }}</p>
                            <p><strong>Notes:</strong> {{ $selectedStaffReturn->notes ?? 'No additional notes.' }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                    @if($selectedStaffReturn && $selectedStaffReturn->status === 'pending')
                    <button type="button" class="btn btn-success" wire:click="approveStaffReturn({{ $selectedStaffReturn->id }})">
                        <i class="bi bi-check-circle me-1"></i> Approve
                    </button>
                    <button type="button" class="btn btn-danger" wire:click="rejectStaffReturn({{ $selectedStaffReturn->id }})">
                        <i class="bi bi-x-circle me-1"></i> Reject
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Staff Return Modal -->
    <div wire:ignore.self class="modal fade" id="approveStaffReturnModal" tabindex="-1"
         aria-labelledby="approveStaffReturnModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-check-circle me-2"></i> Approve Staff Return</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    @if($selectedStaffReturn)
                    <div class="alert alert-success">
                        <h6 class="alert-heading">Confirm Approval</h6>
                        <p class="mb-0">Approving this return will:</p>
                        <ul class="mb-0 mt-2">
                            <li>Update product stock (add {{ $selectedStaffReturn->quantity }} units back)</li>
                            <li>Reduce customer due amount by Rs.{{ number_format($selectedStaffReturn->total_amount, 2) }}</li>
                        </ul>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <p><strong>Return ID:</strong> #{{ $selectedStaffReturn->id }}</p>
                            <p><strong>Invoice:</strong> {{ $selectedStaffReturn->sale?->invoice_number ?? '-' }}</p>
                            <p><strong>Product:</strong> {{ $selectedStaffReturn->product?->name ?? '-' }}</p>
                            <p><strong>Quantity:</strong> {{ $selectedStaffReturn->quantity }}</p>
                            <p><strong>Return Amount:</strong> <span class="text-success fw-bold">Rs.{{ number_format($selectedStaffReturn->total_amount, 2) }}</span></p>
                            <p><strong>Staff:</strong> {{ $selectedStaffReturn->staff?->name ?? '-' }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                    <button type="button" class="btn btn-success" wire:click="confirmApproveStaffReturn">
                        <i class="bi bi-check-circle me-1"></i> Confirm Approval
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Staff Return Modal -->
    <div wire:ignore.self class="modal fade" id="rejectStaffReturnModal" tabindex="-1"
         aria-labelledby="rejectStaffReturnModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-x-circle me-2"></i> Reject Staff Return</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    @if($selectedStaffReturn)
                    <div class="alert alert-warning">
                        <h6 class="alert-heading">Confirm Rejection</h6>
                        <p class="mb-0">Are you sure you want to reject this return request? The staff member will be notified.</p>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <p><strong>Return ID:</strong> #{{ $selectedStaffReturn->id }}</p>
                            <p><strong>Product:</strong> {{ $selectedStaffReturn->product?->name ?? '-' }}</p>
                            <p><strong>Quantity:</strong> {{ $selectedStaffReturn->quantity }}</p>
                            <p><strong>Amount:</strong> Rs.{{ number_format($selectedStaffReturn->total_amount, 2) }}</p>
                            <p><strong>Staff:</strong> {{ $selectedStaffReturn->staff?->name ?? '-' }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="confirmRejectStaffReturn">
                        <i class="bi bi-x-circle me-1"></i> Confirm Rejection
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Staff Return Modal -->
    <div wire:ignore.self class="modal fade" id="deleteStaffReturnModal" tabindex="-1"
         aria-labelledby="deleteStaffReturnModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle me-2"></i> Delete Staff Return</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    @if($selectedStaffReturn)
                    <div class="alert alert-danger">
                        <h6 class="alert-heading">Warning!</h6>
                        <p class="mb-0">You are about to delete this staff return record. This action cannot be undone.
                        @if($selectedStaffReturn->status === 'approved')
                        <br><strong>Note:</strong> Since this return was approved, stock and due amounts will be reverted.
                        @endif
                        </p>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <p><strong>Return ID:</strong> #{{ $selectedStaffReturn->id }}</p>
                            <p><strong>Product:</strong> {{ $selectedStaffReturn->product?->name ?? '-' }}</p>
                            <p><strong>Quantity:</strong> {{ $selectedStaffReturn->quantity }}</p>
                            <p><strong>Amount:</strong> Rs.{{ number_format($selectedStaffReturn->total_amount, 2) }}</p>
                            <p><strong>Status:</strong> 
                                @if($selectedStaffReturn->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($selectedStaffReturn->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="confirmDeleteStaffReturn">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="livewire-toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Notification</strong>
                <small>Just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body"></div>
        </div>
    </div>
</div>

@push('styles')
<style>

    .note{
        display: block;
    }
    .table th {
        font-weight: 600;
        background-color: #f8f9fa;
    }

    .btn-group-sm>.btn {
        padding: 0.25rem 0.5rem;
    }

    .modal-header {
        border-bottom: 1px solid #dee2e6;
    }

    .badge {
        font-size: 0.75em;
    }

    .table th {
            border-top: none;
            font-weight: 600;
            color: #ffffff;
            background: #2a83df;
            background: linear-gradient(135deg, #2a83df 0%, #1a5fb8 100%);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.025);
    }

    .closebtn { 
        top:3%; 
        right:3%; 
        position:absolute; 
    }

    /* Print styles for receipt */
    @media print {

        .note{
            display: none;
        }
        body * { 
            visibility: hidden; 
        }
        #printableReturnReceipt, 
        #printableReturnReceipt * { 
            visibility: visible; 
        }
        #printableReturnReceipt {
            position: absolute;
            left: 0;
            top: 0;
            width: 210mm;
            min-height: 297mm;
            padding: 15mm;
            background: #fff;
            font-size: 11pt;
            color: #000;
        }
        .modal, 
        .modal-dialog, 
        .modal-content { 
            all: unset; 
        }
        .modal-footer, 
        .btn, 
        .btn-close { 
            display: none !important; 
        }

        .modal-header { 
            border: none; 
            padding: 0; 
            text-align: center; 
            margin-bottom: 1rem; 
            background: #000 !important;
            color: #000 !important;
        }
        .modal-header img { 
            max-height: 55px; 
            filter: brightness(0) !important;
        }
        .modal-header h4 { 
            margin: 4px 0; 
            font-size: 1.4rem; 
            color: #000;
        }
        .modal-header p { 
            margin: 0; 
            font-size: 0.85rem; 
            color: #000;
        }

        .table { 
            border-collapse: collapse; 
            width: 100%; 
            margin-bottom: .8rem; 
        }
        .table th, 
        .table td { 
            border: 1px solid #999; 
            padding: 4px 6px; 
        }
        .table th { 
            background: #e9ecef; 
            -webkit-print-color-adjust: exact; 
        }
        .table-sm { 
            font-size: 0.9rem; 
        }

        .table-sm td { 
            border: none; 
            padding: 2px 4px; 
        }
        .table-sm strong { 
            min-width: 110px; 
            display: inline-block; 
        }

        .d-flex img { 
            height: 30px; 
            margin: 0 8px; 
        }
        .text-muted { 
            font-size: 0.8rem; 
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('showModal', (modalId) => {
            const el = document.getElementById(modalId);
            if (el) new bootstrap.Modal(el).show();
        });
        
        Livewire.on('hideModal', (modalId) => {
            const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
            if (modal) modal.hide();
        });
        
        Livewire.on('showToast', (e) => {
            const toast = document.getElementById('livewire-toast');
            toast.querySelector('.toast-body').textContent = e.message;
            toast.querySelector('.toast-header').className = 'toast-header text-white bg-' + e.type;
            new bootstrap.Toast(toast).show();
        });
        
        Livewire.on('printReceipt', () => {
            printReturnReceipt();
        });

        document.addEventListener('keydown', e => { 
            if (e.key === 'Escape') Livewire.dispatch('closeModals'); 
        });
    });

    function printReturnReceipt() {
        window.print();
    }
</script>
@endpush