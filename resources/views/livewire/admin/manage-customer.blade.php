<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-people-fill text-success me-2"></i> Manage Customers
            </h3>
            <p class="text-muted mb-0">Manage all customer information efficiently</p>
        </div>
        <div>
            <button class="btn btn-primary" wire:click="createCustomer">
                <i class="bi bi-plus-lg me-2"></i> Create Customer
            </button>
        </div>
    </div>

    @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if (session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Customer List --}}
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-journal-text text-primary me-2"></i> Customer List
                </h5>
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
                            <th>Customer Name</th>
                            <th>Business Name</th>
                            <th>Contact Number</th>
                            <th>Address</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th class="text-center">Opening Balance</th>
                            <th class="text-center">Due Amount</th>
                            <th class="text-center">Overpaid</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($customers->count() > 0)
                            @foreach ($customers as $customer)
                            <tr>
                                <td class="ps-4">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="fw-medium text-dark">{{ $customer->name ?? '-' }}</span>
                                </td>
                                <td>{{ $customer->business_name ?? '-' }}</td>
                                <td>{{ $customer->phone ?? '-' }}</td>
                                <td>{{ $customer->address ?? '-' }}</td>
                                <td>{{ $customer->email ?? '-' }}</td>
                                <td>
                                    @if($customer->type == 'retail')
                                    <span class="badge bg-success">Retail</span>
                                    @elseif($customer->type == 'wholesale')
                                    <span class="badge bg-info">Wholesale</span>
                                    @else
                                    <span class="badge bg-secondary">N/A</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold text-dark">
                                    Rs. {{ number_format($customer->opening_balance ?? 0, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark fw-bold px-3 py-2">
                                        Rs. {{ number_format($customer->sales_sum_due_amount ?? 0, 2) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success fw-bold px-3 py-2">
                                        Rs. {{ number_format($customer->overpaid_amount ?? 0, 2) }}
                                    </span>
                                </td>
                                <td class="text-end pe-2">
    <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false">
            <i class="bi bi-gear-fill"></i> Actions
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <!-- View Customer -->
            <li>
                <button class="dropdown-item"
                        wire:click="viewDetails({{ $customer->id }})"
                        wire:loading.attr="disabled"
                        title="View Details">
                    <span wire:loading wire:target="viewDetails({{ $customer->id }})">
                        <i class="spinner-border spinner-border-sm me-2"></i> Loading...
                    </span>
                    <span wire:loading.remove wire:target="viewDetails({{ $customer->id }})">
                        <i class="bi bi-eye text-info me-2"></i> View
                    </span>
                </button>
            </li>

            <!-- Edit Customer -->
            <li>
                <button class="dropdown-item"
                        wire:click="editCustomer({{ $customer->id }})"
                        wire:loading.attr="disabled"
                        title="Edit Customer">
                    <span wire:loading wire:target="editCustomer({{ $customer->id }})">
                        <i class="spinner-border spinner-border-sm me-2"></i> Loading...
                    </span>
                    <span wire:loading.remove wire:target="editCustomer({{ $customer->id }})">
                        <i class="bi bi-pencil text-primary me-2"></i> Edit
                    </span>
                </button>
            </li>

            <!-- Delete Customer -->
            <li>
                <button class="dropdown-item"
                        wire:click="confirmDelete({{ $customer->id }})"
                        wire:loading.attr="disabled"
                        title="Delete Customer">
                    <span wire:loading wire:target="confirmDelete({{ $customer->id }})">
                        <i class="spinner-border spinner-border-sm me-2"></i> Loading...
                    </span>
                    <span wire:loading.remove wire:target="confirmDelete({{ $customer->id }})">
                        <i class="bi bi-trash text-danger me-2"></i> Delete
                    </span>
                </button>
            </li>
        </ul>
    </div>
</td>

                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <i class="bi bi-people display-4 d-block mb-2"></i>
                                    No customers found
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-center">
                    {{ $customers->links('livewire.custom-pagination') }}
                </div>
            </div>
        </div>
    </div>

 {{-- Create Customer Modal --}}
@if($showCreateModal)
<div class="modal fade show d-block" tabindex="-1" aria-labelledby="createCustomerModalLabel" aria-hidden="false" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-plus-circle text-white me-2"></i> Create Customer
                </h5>
                <button type="button" class="btn-close" wire:click="closeModal"></button>
            </div>
            <div class="modal-body">
                <form wire:submit.prevent="saveCustomer">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="mb-1">
                                <label class="form-label fw-semibold">Customer Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       wire:model="name" placeholder="Enter customer name" required>
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-1">
                                <label class="form-label fw-semibold">Contact Number</label>
                                <input type="text" class="form-control @error('contactNumber') is-invalid @enderror" 
                                       wire:model="contactNumber" placeholder="Enter contact number" >
                                @error('contactNumber') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="mb-1">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       wire:model="email" placeholder="Enter email">
                                @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-1">
                                <label class="form-label fw-semibold">Business Name</label>
                                <input type="text" class="form-control @error('businessName') is-invalid @enderror" 
                                       wire:model="businessName" placeholder="Enter Business Name">
                                @error('businessName') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="mb-1">
                                <label class="form-label fw-semibold">Customer Type</label>
                                <select class="form-select @error('customerType') is-invalid @enderror" wire:model="customerType" >
                                    <option value="">Select customer type</option>
                                    <option value="retail">Retail</option>
                                    <option value="wholesale">Wholesale</option>
                                </select>
                                @error('customerType') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-1">
                                <label class="form-label fw-semibold">Address</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                       wire:model="address" placeholder="Enter address">
                                @error('address') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12">
                            <div class="alert alert-warning rounded-0 py-2 mb-3">
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>Previous Records (Old Balance Before System)</strong>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Opening Balance (Old Outstanding)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" step="0.01" class="form-control @error('openingBalance') is-invalid @enderror" 
                                           wire:model="openingBalance" placeholder="0">
                                </div>
                                @error('openingBalance') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Overpaid (Customer Credit)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" step="0.01" class="form-control @error('overpaidAmount') is-invalid @enderror" 
                                           wire:model="overpaidAmount" placeholder="0">
                                </div>
                                @error('overpaidAmount') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Remarks (Note old invoice numbers)</label>
                                <textarea class="form-control @error('openingRemarks') is-invalid @enderror" 
                                          wire:model="openingRemarks" rows="2" placeholder="e.g. Old INV-001, INV-002 – balance carried forward"></textarea>
                                @error('openingRemarks') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <i class="bi bi-check2-circle me-1"></i>
                            <span wire:loading.remove>Save Customer</span>
                            <span wire:loading>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

    {{-- Edit Customer Modal --}}
    @if($showEditModal)
    <div class="modal fade show d-block" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="false" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square text-white me-2"></i> Edit Customer
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="updateCustomer">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Customer Name</label>
                                    <input type="text" class="form-control @error('editName') is-invalid @enderror" 
                                           wire:model="editName" required>
                                    @error('editName') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Contact Number</label>
                                    <input type="text" class="form-control @error('editContactNumber') is-invalid @enderror" 
                                           wire:model="editContactNumber" required>
                                    @error('editContactNumber') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Email</label>
                                    <input type="email" class="form-control @error('editEmail') is-invalid @enderror" 
                                           wire:model="editEmail">
                                    @error('editEmail') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Business Name</label>
                                    <input type="text" class="form-control @error('editBusinessName') is-invalid @enderror" 
                                           wire:model="editBusinessName">
                                    @error('editBusinessName') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Customer Type</label>
                                    <select class="form-select @error('editCustomerType') is-invalid @enderror" wire:model="editCustomerType" required>
                                        <option value="retail">Retail</option>
                                        <option value="wholesale">Wholesale</option>
                                    </select>
                                    @error('editCustomerType') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Address</label>
                                    <input type="text" class="form-control @error('editAddress') is-invalid @enderror" 
                                           wire:model="editAddress">
                                    @error('editAddress') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-12">
                                <div class="alert alert-warning rounded-0 py-2 mb-3">
                                    <i class="bi bi-info-circle me-1"></i>
                                    <strong>Previous Records (Old Balance Before System)</strong>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Opening Balance (Old Outstanding)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rs.</span>
                                        <input type="number" step="0.01" class="form-control @error('editOpeningBalance') is-invalid @enderror" 
                                               wire:model="editOpeningBalance">
                                    </div>
                                    @error('editOpeningBalance') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Overpaid (Customer Credit)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rs.</span>
                                        <input type="number" step="0.01" class="form-control @error('editOverpaidAmount') is-invalid @enderror" 
                                               wire:model="editOverpaidAmount">
                                    </div>
                                    @error('editOverpaidAmount') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Remarks (Note old invoice numbers)</label>
                                    <textarea class="form-control @error('editOpeningRemarks') is-invalid @enderror" 
                                              wire:model="editOpeningRemarks" rows="2"></textarea>
                                    @error('editOpeningRemarks') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <i class="bi bi-check2-circle me-1"></i>
                                <span wire:loading.remove>Update Customer</span>
                                <span wire:loading>Updating...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- View Details Modal --}}
    @if($showViewModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6);">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                {{-- Premium Header --}}
                <div class="modal-header bg-danger text-white py-3 border-0">
                    <div class="d-flex align-items-center">
                        <div class="bg-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="bi bi-person-fill text-danger fs-4"></i>
                        </div>
                        <div>
                            <h4 class="modal-title fw-bold text-uppercase mb-0 tracking-tight">{{ $viewCustomerDetail['name'] ?? 'Walking Customer' }}</h4>
                            <small class="opacity-75 text-capitalize">{{ $viewCustomerDetail['type'] ?? 'retail' }} Customer | {{ $viewCustomerDetail['business_name'] ?? 'N/A' }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>

                <div class="modal-body p-0 bg-light">
                    {{-- Top Statistics Row --}}
                    <div class="row g-3 p-4 bg-white border-bottom mx-0">
                        <div class="col-md-3">
                            <div class="stat-card blue p-3 rounded-3 border">
                                <small class="text-uppercase fw-bold text-muted d-block mb-1">Opening Balance</small>
                                <h3 class="fw-bold mb-0 text-primary">{{ number_format($viewCustomerDetail['opening_balance'] ?? 0, 2) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card yellow p-3 rounded-3 border">
                                <small class="text-uppercase fw-bold text-muted d-block mb-1">Due Amount</small>
                                <h3 class="fw-bold mb-0 text-warning">{{ number_format($viewCustomerDetail['due_amount'] ?? 0, 2) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card green p-3 rounded-3 border">
                                <small class="text-uppercase fw-bold text-muted d-block mb-1">Overpaid</small>
                                <h3 class="fw-bold mb-0 text-success">{{ number_format($viewCustomerDetail['overpaid_amount'] ?? 0, 2) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card purple p-3 rounded-3 border text-end">
                                <small class="text-uppercase fw-bold text-muted d-block mb-1">Total Due</small>
                                <h3 class="fw-bold mb-0" style="color: #6f42c1;">{{ number_format(($viewCustomerDetail['due_amount'] ?? 0) + ($viewCustomerDetail['opening_balance'] ?? 0) - ($viewCustomerDetail['overpaid_amount'] ?? 0), 2) }}</h3>
                            </div>
                        </div>
                    </div>

                    {{-- Navigation Tabs --}}
                    <ul class="nav nav-tabs custom-tabs px-4 pt-3 bg-white sticky-top shadow-sm" style="top: -1px; z-index: 10;">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'overview' ? 'active' : '' }}" href="javascript:void(0)" wire:click="setTab('overview')">
                                <i class="bi bi-person-lines-fill me-2"></i> Overview
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'sales' ? 'active' : '' }}" href="javascript:void(0)" wire:click="setTab('sales')">
                                <i class="bi bi-cart3 me-2"></i> Sales <span class="badge rounded-pill bg-danger ms-1">{{ $stats['total_sales_count'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'payments' ? 'active' : '' }}" href="javascript:void(0)" wire:click="setTab('payments')">
                                <i class="bi bi-credit-card me-2"></i> Payments <span class="badge rounded-pill bg-success ms-1">{{ $stats['total_payments_count'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'dues' ? 'active' : '' }}" href="javascript:void(0)" wire:click="setTab('dues')">
                                <i class="bi bi-exclamation-triangle me-2"></i> Dues <span class="badge rounded-pill bg-warning text-dark ms-1">{{ $stats['pending_dues_count'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'ledger' ? 'active' : '' }}" href="javascript:void(0)" wire:click="setTab('ledger')">
                                <i class="bi bi-list-check me-2"></i> Ledger
                            </a>
                        </li>
                    </ul>

                    {{-- Tab Content --}}
                    <div class="p-4 overflow-auto" style="height: 60vh;">
                        @if($activeTab == 'overview')
                            <div class="row g-4">
                                {{-- Left Column: Info --}}
                                <div class="col-md-7">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <h6 class="fw-bold text-danger mb-4 d-flex align-items-center">
                                                <i class="bi bi-person-circle me-2 fs-5"></i> Personal Information
                                            </h6>
                                            <div class="row mb-3">
                                                <div class="col-4 text-muted fw-semibold">Name</div>
                                                <div class="col-8 fw-bold">{{ $viewCustomerDetail['name'] }}</div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-4 text-muted fw-semibold">Contact</div>
                                                <div class="col-8 fw-bold">{{ $viewCustomerDetail['phone'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-4 text-muted fw-semibold">Email</div>
                                                <div class="col-8 fw-bold">{{ $viewCustomerDetail['email'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-4 text-muted fw-semibold">Business</div>
                                                <div class="col-8 fw-bold">{{ $viewCustomerDetail['business_name'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="row">
                                                <div class="col-4 text-muted fw-semibold">Type</div>
                                                <div class="col-8">
                                                    <span class="badge bg-warning text-dark text-uppercase fw-bold">{{ $viewCustomerDetail['type'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Right Column: Address/Dates --}}
                                <div class="col-md-5">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <h6 class="fw-bold text-danger mb-4 d-flex align-items-center">
                                                <i class="bi bi-geo-alt-fill me-2 fs-5"></i> Address & Dates
                                            </h6>
                                            <div class="row mb-3">
                                                <div class="col-4 text-muted fw-semibold">Address</div>
                                                <div class="col-8 fw-bold">{{ $viewCustomerDetail['address'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-4 text-muted fw-semibold">Created</div>
                                                <div class="col-8 fw-bold">{{ \Carbon\Carbon::parse($viewCustomerDetail['created_at'])->format('M d, Y h:i A') }}</div>
                                            </div>
                                            <div class="row">
                                                <div class="col-4 text-muted fw-semibold">Updated</div>
                                                <div class="col-8 fw-bold">{{ \Carbon\Carbon::parse($viewCustomerDetail['updated_at'])->format('M d, Y h:i A') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Quick Summary --}}
                                <div class="col-12 mt-4">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-bar-chart-fill me-2"></i> Quick Summary
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="bg-blue-soft p-3 rounded-3 text-center border">
                                                <h2 class="fw-bold text-primary mb-0">{{ $stats['total_sales_count'] }}</h2>
                                                <small class="text-uppercase fw-bold text-muted">Total Sales</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="bg-green-soft p-3 rounded-3 text-center border">
                                                <h2 class="fw-bold text-success mb-0">{{ $stats['total_payments_count'] }}</h2>
                                                <small class="text-uppercase fw-bold text-muted">Total Payments</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="bg-yellow-soft p-3 rounded-3 text-center border">
                                                <h2 class="fw-bold text-warning mb-0">{{ $stats['pending_dues_count'] }}</h2>
                                                <small class="text-uppercase fw-bold text-muted">Pending Dues</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="bg-red-soft p-3 rounded-3 text-center border">
                                                <h2 class="fw-bold text-danger mb-0">{{ number_format($stats['total_sales_amount'], 2) }}</h2>
                                                <small class="text-uppercase fw-bold text-muted">Total Sales Amount</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($activeTab == 'sales')
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white py-3 d-flex justify-content-end border-0">
                                    <a href="{{ route('admin.customer-report', ['type' => 'Sales', 'id' => $viewCustomerDetail['id']]) }}" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill fw-bold">
                                        <i class="bi bi-printer me-1"></i> Print Sales Report
                                    </a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle custom-table">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4">#</th>
                                                <th>Invoice</th>
                                                <th>Date</th>
                                                <th>Items</th>
                                                <th class="text-end">Total</th>
                                                <th class="text-end text-success">Paid</th>
                                                <th class="text-end text-danger">Due</th>
                                                <th>Payment</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($customerSales as $sale)
                                            <tr>
                                                <td class="ps-4">{{ $loop->iteration }}</td>
                                                <td class="fw-bold text-primary">{{ $sale->invoice_number }}</td>
                                                <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('M d, Y h:i A') }}</td>
                                                <td><span class="badge bg-light text-dark border">{{ $sale->items_count ?? 1 }} items</span></td>
                                                <td class="text-end fw-bold">{{ number_format($sale->total_amount, 2) }}</td>
                                                <td class="text-end text-success fw-bold">{{ number_format($sale->paid_amount, 2) }}</td>
                                                <td class="text-end text-danger fw-bold">{{ number_format($sale->due_amount, 2) }}</td>
                                                <td><span class="badge bg-secondary text-capitalize">{{ $sale->payment_status }}</span></td>
                                                <td><span class="badge bg-success">Confirm</span></td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="9" class="text-center py-4">No sales records found.</td></tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot class="bg-light fw-bold">
                                            <tr>
                                                <td colspan="4" class="text-end">TOTALS:</td>
                                                <td class="text-end">{{ number_format($customerSales->sum('total_amount'), 2) }}</td>
                                                <td class="text-end text-success">{{ number_format($customerSales->sum('paid_amount'), 2) }}</td>
                                                <td class="text-end text-danger">{{ number_format($customerSales->sum('due_amount'), 2) }}</td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        @elseif($activeTab == 'payments')
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white py-3 d-flex justify-content-end border-0">
                                    <a href="{{ route('admin.customer-report', ['type' => 'Payments', 'id' => $viewCustomerDetail['id']]) }}" target="_blank" class="btn btn-outline-success btn-sm rounded-pill fw-bold">
                                        <i class="bi bi-printer me-1"></i> Print Payments Report
                                    </a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle custom-table">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4">#</th>
                                                <th>Date</th>
                                                <th>Invoice</th>
                                                <th>Method</th>
                                                <th>Reference</th>
                                                <th class="text-end">Amount</th>
                                                <th>Status</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($customerPayments as $payment)
                                            <tr>
                                                <td class="ps-4">{{ $loop->iteration }}</td>
                                                <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                                <td class="text-primary fw-bold">{{ $payment->sale->invoice_number ?? 'Internal' }}</td>
                                                <td class="text-capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                                <td class="text-muted">{{ $payment->payment_reference ?? 'N/A' }}</td>
                                                <td class="text-end fw-bold text-success">{{ number_format($payment->amount, 2) }}</td>
                                                <td><span class="badge bg-success">{{ $payment->status }}</span></td>
                                                <td class="small">{{ $payment->notes ?? '-' }}</td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="8" class="text-center py-4">No payments found.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @elseif($activeTab == 'dues')
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white py-3 d-flex justify-content-end border-0">
                                    <a href="{{ route('admin.customer-report', ['type' => 'Dues', 'id' => $viewCustomerDetail['id']]) }}" target="_blank" class="btn btn-outline-warning btn-sm rounded-pill fw-bold text-dark">
                                        <i class="bi bi-printer me-1"></i> Print Dues Report
                                    </a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle custom-table">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4">#</th>
                                                <th>Invoice</th>
                                                <th>Date</th>
                                                <th class="text-end">Total Amount</th>
                                                <th class="text-end text-success">Paid</th>
                                                <th class="text-end text-danger">Due Amount</th>
                                                <th>Payment Status</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(($viewCustomerDetail['opening_balance'] ?? 0) > 0)
                                            <tr class="table-info">
                                                <td class="ps-4">0</td>
                                                <td class="fw-bold text-dark">OPENING_BALANCE</td>
                                                <td>-</td>
                                                <td class="text-end fw-bold">{{ number_format($viewCustomerDetail['opening_balance'], 2) }}</td>
                                                <td class="text-end text-success fw-bold">0.00</td>
                                                <td class="text-end text-danger fw-bold">{{ number_format($viewCustomerDetail['opening_balance'], 2) }}</td>
                                                <td><span class="badge bg-danger">Carry Forward</span></td>
                                                <td><span class="badge bg-secondary">N/A</span></td>
                                            </tr>
                                            @endif
                                            @forelse($customerDues as $due)
                                            <tr>
                                                <td class="ps-4">{{ $loop->iteration }}</td>
                                                <td class="fw-bold text-primary">{{ $due->invoice_number }}</td>
                                                <td>{{ \Carbon\Carbon::parse($due->sale_date)->format('M d, Y h:i A') }}</td>
                                                <td class="text-end fw-bold">{{ number_format($due->total_amount, 2) }}</td>
                                                <td class="text-end text-success fw-bold">{{ number_format($due->paid_amount, 2) }}</td>
                                                <td class="text-end text-danger fw-bold">{{ number_format($due->due_amount, 2) }}</td>
                                                <td><span class="badge bg-secondary text-capitalize">{{ $due->payment_status }}</span></td>
                                                <td><span class="badge bg-success">Confirm</span></td>
                                            </tr>
                                            @empty
                                            @if(($viewCustomerDetail['opening_balance'] ?? 0) <= 0)
                                            <tr><td colspan="8" class="text-center py-4">No pending dues found.</td></tr>
                                            @endif
                                            @endforelse
                                        </tbody>
                                        <tfoot class="bg-light fw-bold text-danger">
                                            <tr>
                                                <td colspan="5" class="text-end">TOTAL DUES:</td>
                                                <td class="text-end">{{ number_format($customerDues->sum('due_amount') + ($viewCustomerDetail['opening_balance'] ?? 0), 2) }}</td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        @elseif($activeTab == 'ledger')
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white py-3 d-flex justify-content-end border-0">
                                    <a href="{{ route('admin.customer-report', ['type' => 'Ledger', 'id' => $viewCustomerDetail['id']]) }}" target="_blank" class="btn btn-outline-info btn-sm rounded-pill fw-bold text-dark">
                                        <i class="bi bi-printer me-1"></i> Print Ledger Report
                                    </a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle custom-table">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4">#</th>
                                                <th>Date</th>
                                                <th>Invoice</th>
                                                <th>Description</th>
                                                <th>Reference</th>
                                                <th class="text-end">Debit</th>
                                                <th class="text-end">Credit</th>
                                                <th class="text-end">Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                // Starting Balance = Opening Balance (Debit) - Overpaid Amount (Credit)
                                                // Note: If overpaid amounts are already recorded as payments, they will be counted later.
                                                $runningBalance = ($viewCustomerDetail['opening_balance'] ?? 0) - ($viewCustomerDetail['overpaid_amount'] ?? 0);
                                                
                                                // Combine and sort Sales/Payments/Returns
                                                $ledger = collect([]);
                                                foreach($customerSales as $s) {
                                                    $ledger->push(['date' => $s->sale_date, 'invoice' => $s->invoice_number, 'desc' => 'Sale Record', 'ref' => 'Sale', 'debit' => $s->total_amount, 'credit' => 0]);
                                                }
                                                foreach($customerPayments as $p) {
                                                    $ledger->push(['date' => $p->payment_date, 'invoice' => $p->sale->invoice_number ?? '-', 'desc' => 'Payment Received', 'ref' => str_replace('_',' ',$p->payment_method), 'debit' => 0, 'credit' => $p->amount]);
                                                }
                                                foreach($customerReturns as $r) {
                                                    $ledger->push(['date' => $r->created_at, 'invoice' => $r->sale->invoice_number ?? '-', 'desc' => 'Product Return', 'ref' => $r->reason ?? 'Return', 'debit' => 0, 'credit' => $r->total_amount]);
                                                }
                                                $ledger = $ledger->sortBy('date');
                                            @endphp
                                            <tr>
                                                <td class="ps-4">0</td>
                                                <td colspan="4"><strong>Opening Balance / Initial Credit</strong></td>
                                                <td class="text-end text-danger">{{ ($viewCustomerDetail['opening_balance'] ?? 0) > 0 ? number_format($viewCustomerDetail['opening_balance'], 2) : '-' }}</td>
                                                <td class="text-end text-success">{{ ($viewCustomerDetail['overpaid_amount'] ?? 0) > 0 ? number_format($viewCustomerDetail['overpaid_amount'], 2) : '-' }}</td>
                                                <td class="text-end fw-bold {{ $runningBalance >= 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format(abs($runningBalance), 2) }} {{ $runningBalance >= 0 ? 'Dr' : 'Cr' }}
                                                </td>
                                            </tr>
                                            @foreach($ledger as $item)
                                            @php
                                                $runningBalance += $item['debit'];
                                                $runningBalance -= $item['credit'];
                                            @endphp
                                            <tr>
                                                <td class="ps-4">{{ $loop->iteration }}</td>
                                                <td>{{ \Carbon\Carbon::parse($item['date'])->format('M d, Y h:i A') }}</td>
                                                <td class="text-primary fw-bold">{{ $item['invoice'] }}</td>
                                                <td class="small">
                                                    @if($item['debit'] > 0)
                                                        <i class="bi bi-cart me-1 text-danger"></i>
                                                    @else
                                                        <i class="bi bi-arrow-return-left me-1 text-success"></i>
                                                    @endif
                                                    {{ $item['desc'] }}
                                                </td>
                                                <td>{{ $item['ref'] }}</td>
                                                <td class="text-end text-danger">{{ $item['debit'] > 0 ? number_format($item['debit'], 2) : '-' }}</td>
                                                <td class="text-end text-success">{{ $item['credit'] > 0 ? number_format($item['credit'], 2) : '-' }}</td>
                                                <td class="text-end fw-bold {{ $runningBalance >= 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format(abs($runningBalance), 2) }} {{ $runningBalance >= 0 ? 'Dr' : 'Cr' }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-light fw-bold">
                                            <tr>
                                                <td colspan="7" class="text-end">Final Balance:</td>
                                                <td class="text-end fw-bold {{ $runningBalance >= 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format(abs($runningBalance), 2) }} {{ $runningBalance >= 0 ? 'Dr' : 'Cr' }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="closeModal">
                        <i class="bi bi-x-lg me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- A5 Print Area (Hidden in Browser) --}}
    <div id="print-area" class="d-none">
        @if($showViewModal)
        <div class="print-header text-center mb-4">
            <h2 class="fw-bold mb-0">MIKING</h2>
            <p class="mb-0 small">No.12, Bankshall Street, Colombo 11. | Tel: (076) 7535786, (077) 3724163, (011) 2339786</p>
            <h4 class="text-uppercase fw-bold border-bottom border-top py-2 mt-3">CUSTOMER HISTORY - {{ strtoupper($activeTab) }}</h4>
        </div>

        <div class="print-info-box border p-3 mb-4 rounded">
            <div class="row">
                <div class="col-7">
                    <table class="table table-borderless table-sm mb-0">
                        <tr><td width="30%"><strong>Customer:</strong></td><td>{{ $viewCustomerDetail['name'] }}</td></tr>
                        <tr><td><strong>Business:</strong></td><td>{{ $viewCustomerDetail['business_name'] ?? 'N/A' }}</td></tr>
                        <tr><td><strong>Contact:</strong></td><td>{{ $viewCustomerDetail['phone'] ?? 'xxxxx' }}</td></tr>
                        <tr><td><strong>Address:</strong></td><td>{{ $viewCustomerDetail['address'] ?? 'xxxxx' }}</td></tr>
                    </table>
                </div>
                <div class="col-5 text-end">
                    <p class="mb-1"><strong>Type:</strong> {{ strtoupper($viewCustomerDetail['type']) }}</p>
                    <p class="mb-0"><strong>Report Date:</strong> {{ now()->format('d/m/Y h:i A') }}</p>
                </div>
            </div>
        </div>

        <table class="table table-bordered print-table">
            @if($activeTab == 'sales')
                <thead>
                    <tr><th>#</th><th>DATE</th><th>INVOICE NO</th><th>ITEMS</th><th>TOTAL</th><th>PAID</th><th>DUE</th></tr>
                </thead>
                <tbody>
                    @foreach($customerSales as $sale)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y h:i A') }}</td>
                        <td>{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->items_count ?? 1 }}</td>
                        <td class="text-end">{{ number_format($sale->total_amount, 2) }}</td>
                        <td class="text-end">{{ number_format($sale->paid_amount, 2) }}</td>
                        <td class="text-end">{{ number_format($sale->due_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            @elseif($activeTab == 'payments')
                <thead>
                    <tr><th>#</th><th>DATE</th><th>INVOICE NO</th><th>METHOD</th><th>REF</th><th>AMOUNT</th></tr>
                </thead>
                <tbody>
                    @foreach($customerPayments as $p)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $p->payment_date->format('d/m/Y') }}</td>
                        <td>{{ $p->sale->invoice_number ?? '-' }}</td>
                        <td>{{ $p->payment_method }}</td>
                        <td>{{ $p->payment_reference }}</td>
                        <td class="text-end">{{ number_format($p->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            @elseif($activeTab == 'dues')
                <thead>
                    <tr><th>#</th><th>DATE</th><th>INVOICE NO</th><th>TOTAL</th><th>PAID</th><th>DUE</th></tr>
                </thead>
                <tbody>
                    @if(($viewCustomerDetail['opening_balance'] ?? 0) > 0)
                    <tr><td>0</td><td>-</td><td>OPENING</td><td>{{ number_format($viewCustomerDetail['opening_balance'], 2) }}</td><td>0.00</td><td>{{ number_format($viewCustomerDetail['opening_balance'], 2) }}</td></tr>
                    @endif
                    @foreach($customerDues as $due)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($due->sale_date)->format('d/m/Y') }}</td>
                        <td>{{ $due->invoice_number }}</td>
                        <td class="text-end">{{ number_format($due->total_amount, 2) }}</td>
                        <td class="text-end">{{ number_format($due->paid_amount, 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($due->due_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            @elseif($activeTab == 'ledger')
                <thead>
                    <tr><th>#</th><th>DATE</th><th>INVOICE NO</th><th>DESCRIPTION</th><th>DEBIT</th><th>CREDIT</th><th>BALANCE</th></tr>
                </thead>
                <tbody>
                    @php $running = ($viewCustomerDetail['opening_balance'] ?? 0) - ($viewCustomerDetail['overpaid_amount'] ?? 0); @endphp
                    <tr><td>0</td><td>-</td><td>-</td><td>Opening Balance</td><td>{{ number_format($viewCustomerDetail['opening_balance'] ?? 0, 2) }}</td><td>{{ number_format($viewCustomerDetail['overpaid_amount'] ?? 0, 2) }}</td><td>{{ number_format(abs($running), 2) }} {{ $running >= 0 ? 'Dr' : 'Cr' }}</td></tr>
                    @foreach($ledger as $item)
                        @php $running += $item['debit']; $running -= $item['credit']; @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($item['date'])->format('d/m/Y h:i A') }}</td>
                            <td>{{ $item['invoice'] }}</td>
                            <td>{{ $item['desc'] }}</td>
                            <td class="text-end">{{ $item['debit'] > 0 ? number_format($item['debit'], 2) : '-' }}</td>
                            <td class="text-end">{{ $item['credit'] > 0 ? number_format($item['credit'], 2) : '-' }}</td>
                            <td class="text-end fw-bold">{{ number_format(abs($running), 2) }} {{ $running >= 0 ? 'Dr' : 'Cr' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            @endif
        </table>

        <div class="row mt-4">
            <div class="col-6 offset-6">
                <table class="table table-sm table-borderless border-top border-dark border-3">
                    <tr><td class="fw-bold">Report Type:</td><td class="text-end text-capitalize">{{ $activeTab }}</td></tr>
                    <tr><td class="fw-bold fs-5">Closing Balance:</td><td class="text-end fw-bold fs-5">Rs. {{ number_format(abs($runningBalance ?? ($customerDues->sum('due_amount') + ($viewCustomerDetail['opening_balance'] ?? 0))), 2) }}</td></tr>
                </table>
            </div>
        </div>

        <div class="print-footer text-center mt-5 pt-5 border-top">
            <p class="mb-0 italic text-muted">Thank you for your business!</p>
            <small class="generated-at">Generated on {{ now()->format('M d, Y h:i A') }}</small>
        </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
    <div class="modal fade show d-block" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="false" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" wire:click="cancelDelete"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="bi bi-person-x text-danger fs-1 mb-3 d-block"></i>
                    <h5 class="fw-bold mb-3">Are you sure?</h5>
                    <p class="text-muted">You are about to delete this customer. This action cannot be undone.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" wire:click="cancelDelete">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteCustomer" wire:loading.attr="disabled">
                        <i class="bi bi-trash me-1"></i>
                        <span wire:loading.remove>Delete Customer</span>
                        <span wire:loading>Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
    }

    .card-header {
        background-color: white;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 12px 12px 0 0 !important;
        padding: 1.25rem 1.5rem;
    }
    .table td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
    }

    .btn-link {
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-link:hover {
        transform: scale(1.1);
    }

    .modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        padding: 0.75rem 1rem;
        border: 1px solid #e2e8f0;
    }

    .form-control:focus,
    .form-select:focus {
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        border-color: #4361ee;
    }

    .btn {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background-color: #4361ee;
        border-color: #4361ee;
    }

    .btn-primary:hover {
        background-color: #3f37c9;
        border-color: #3f37c9;
        transform: translateY(-2px);
    }

    .btn-danger {
        background-color: #e63946;
        border-color: #e63946;
    }

    .btn-danger:hover {
        background-color: #d00000;
        border-color: #d00000;
        transform: translateY(-2px);
    }

    .alert {
        border-radius: 8px;
        border: none;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
    }

    /* View Modal Revamp Styles */
    .stat-card {
        transition: all 0.2s ease;
        background: white;
    }
    .stat-card.blue { border-left: 5px solid #0d6efd; background-color: #f0f7ff; }
    .stat-card.yellow { border-left: 5px solid #ffc107; background-color: #fff9e6; }
    .stat-card.green { border-left: 5px solid #198754; background-color: #f0fff4; }
    .stat-card.purple { border-left: 5px solid #6f42c1; background-color: #f5f0ff; }
    
    .custom-tabs .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 600;
        padding: 1rem 1.5rem;
        transition: all 0.3s ease;
    }
    .custom-tabs .nav-link:hover {
        color: #dc3545;
    }
    .custom-tabs .nav-link.active {
        color: #dc3545;
        border-bottom: 3px solid #dc3545;
        background: transparent;
    }

    .bg-blue-soft { background-color: #f0f7ff; }
    .bg-green-soft { background-color: #f0fff4; }
    .bg-yellow-soft { background-color: #fff9e6; }
    .bg-red-soft { background-color: #fff5f5; }

    .custom-table thead {
        background-color: #0d6efd !important;
    }
    .custom-table thead th {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #ffffff !important;
        border-top: none;
    }
    .custom-table tbody tr {
        transition: background 0.2s;
    }
    .tracking-tight { letter-spacing: -0.5px; }

    /* A5 Print Styles - Optimized for Single Page */
    @media print {
        @page { size: A5 landscape; margin: 5mm; }
        body * { visibility: hidden; }
        #print-area, #print-area * { visibility: visible; display: block !important; }
        #print-area { position: absolute; left: 0; top: 0; width: 100%; font-family: 'Inter', sans-serif; color: #000; font-size: 9pt; }
        .print-header h2 { font-size: 16pt !important; margin-bottom: 2px !important; }
        .print-header p { font-size: 8pt !important; }
        .print-header h4 { font-size: 12pt !important; padding: 4px 0 !important; margin-top: 5px !important; }
        .print-info-box { padding: 8px !important; margin-bottom: 10px !important; }
        .print-info-box table td { padding: 2px !important; font-size: 9pt; }
        .print-table { border: 1.5px solid #000 !important; margin-bottom: 10px !important; }
        .print-table th, .print-table td { border: 1px solid #000 !important; color: #000 !important; font-size: 8.5pt; padding: 3px 5px !important; }
        .print-table thead th { background-color: #f0f0f0 !important; font-weight: bold !important; }
        .print-footer { margin-top: 15px !important; padding-top: 5px !important; }
        .no-print { display: none !important; }
        .d-none { display: block !important; }
        /* Prevent multi-page spill for typical history lengths */
        tr { page-break-inside: avoid; }
    }
</style>
@endpush