<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-truck text-primary me-2"></i> Sales Distribution
            </h3>
            <p class="text-muted mb-0">Track and manage sales distribution logs and travel expenses</p>
        </div>
        <button class="btn btn-primary" wire:click="openAddModal">
            <i class="bi bi-plus-lg me-1"></i> Add Distribution
        </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Search</label>
                    <input type="text" class="form-control" placeholder="Staff, Location, Handover..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Status</label>
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">From Date</label>
                    <input type="date" class="form-control" wire:model.live="dateFrom">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">To Date</label>
                    <input type="date" class="form-control" wire:model.live="dateTo">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-outline-secondary w-100" wire:click="$set('search', ''); $set('statusFilter', ''); $set('dateFrom', ''); $set('dateTo', '')">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Staff Name</th>
                            <th>Dispatch Location</th>
                            <th>Distance (KM)</th>
                            <th>Expense (Rs.)</th>
                            <th>Handover To</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($distributions as $item)
                        <tr>
                            <td class="ps-4">{{ $item->distribution_date->format('M d, Y') }}</td>
                            <td>
                                <div class="fw-bold">{{ $item->staff_name }}</div>
                            </td>
                            <td>{{ $item->dispatch_location }}</td>
                            <td>{{ number_format($item->distance_km, 1) }} KM</td>
                            <td><span class="fw-bold">Rs.{{ number_format($item->travel_expense, 2) }}</span></td>
                            <td>{{ $item->handover_to }}</td>
                            <td>
                                @php
                                    $statusClass = match($item->status) {
                                        'pending' => 'bg-warning text-dark',
                                        'completed' => 'bg-info text-white',
                                        'approved' => 'bg-success text-white',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ ucfirst($item->status) }}</span>
                            </td>
                            <td class="text-end pe-4">
                                @if($this->isAdmin() && $item->status !== 'approved')
                                <button class="btn btn-sm btn-outline-success me-1" wire:click="approve({{ $item->id }})" title="Approve">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                                @endif
                                <button class="btn btn-sm btn-outline-primary me-1" wire:click="openEditModal({{ $item->id }})" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @if($this->isAdmin())
                                <button class="btn btn-sm btn-outline-danger" wire:click="confirmDelete({{ $item->id }})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No distribution records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3">
                {{ $distributions->links() }}
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    @if($showFormModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi {{ $isEdit ? 'bi-pencil-square' : 'bi-plus-circle' }} me-2"></i>
                        {{ $isEdit ? 'Edit Distribution' : 'Add Distribution' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showFormModal', false)"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="save">
                        <div class="row g-3 mb-4">
                            <!-- Staff & Date Selection -->
                            @if($this->isAdmin())
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Select Staff</label>
                                <select class="form-select" wire:model="staff_id" wire:change="$set('staff_name', $event.target.options[$event.target.selectedIndex].text)">
                                    <option value="">Select Staff</option>
                                    @foreach($staffMembers as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                                @error('staff_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            @endif

                            <div class="col-md-{{ $this->isAdmin() ? '6' : '12' }}">
                                <label class="form-label fw-bold">Distribution Date</label>
                                <input type="date" class="form-control" wire:model="distribution_date">
                                @error('distribution_date') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <!-- Route Details Section -->
                            <div class="col-12 mt-4">
                                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="bi bi-geo-alt me-2"></i>Distribution Details</h6>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold small text-muted">Dispatch Location</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-geo-alt"></i></span>
                                    <input type="text" class="form-control" wire:model="dispatch_location" placeholder="e.g. Customer Store, Area Name">
                                </div>
                                @error('dispatch_location') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <!-- Trip Metrics Section -->
                            <div class="col-12 mt-4">
                                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="bi bi-speedometer2 me-2"></i>Trip Metrics</h6>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Distance</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" class="form-control" wire:model="distance_km" placeholder="0.0">
                                    <span class="input-group-text bg-light">KM</span>
                                </div>
                                @error('distance_km') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Travel Expense</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rs.</span>
                                    <input type="number" step="0.01" class="form-control" wire:model="travel_expense" placeholder="0.00">
                                </div>
                                @error('travel_expense') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Handover To</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" wire:model="handover_to" placeholder="Person/Company">
                                </div>
                                @error('handover_to') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            @if($this->isAdmin())
                            <div class="col-md-12 mt-3">
                                <label class="form-label fw-bold">Status</label>
                                <select class="form-select" wire:model="status">
                                    <option value="pending">Pending</option>
                                    <option value="completed">Completed</option>
                                    <option value="approved">Approved</option>
                                </select>
                                @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            @endif

                            <div class="col-md-12 mt-3">
                                <label class="form-label fw-bold">Description (Optional)</label>
                                <textarea class="form-control" wire:model="description" rows="2" placeholder="Any additional notes..."></textarea>
                            </div>
                        </div>

                        <!-- Products Section -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0">Distributed Products</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addProductRow">
                                    <i class="bi bi-plus-circle me-1"></i> Add Product
                                </button>
                            </div>
                            <div class="p-2 rounded border" style="overflow: visible;">
                                <table class="table table-sm table-borderless mb-0">
                                    <thead>
                                        <tr>
                                            <th>Product Name/Model</th>
                                            <th style="width: 150px;">Quantity</th>
                                            <th style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($products as $index => $product)
                                        <tr>
                                            <td class="position-relative">
                                                <input type="text" 
                                                       class="form-control form-control-sm" 
                                                       wire:model="products.{{ $index }}.name" 
                                                       wire:input="searchProduct({{ $index }})"
                                                       placeholder="Search product name or model...">
                                                
                                                @if($active_product_index === $index && count($search_results) > 0)
                                                <div class="list-group position-absolute w-100 shadow-lg z-3" style="top: 100%;">
                                                    @foreach($search_results as $res)
                                                    <button type="button" 
                                                            class="list-group-item list-group-item-action py-2 border-bottom"
                                                            wire:click="selectProduct({{ $index }}, '{{ $res['name'] }} @if($res['model'])- {{ $res['model'] }}@endif', {{ $res['id'] }})">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div class="flex-grow-1">
                                                                <div class="fw-bold small text-primary">{{ $res['name'] }}</div>
                                                                <div class="text-muted extra-small">{{ $res['code'] }} @if($res['model'])| {{ $res['model'] }}@endif</div>
                                                            </div>
                                                            <div class="text-end ms-2">
                                                                <div class="badge {{ $res['available_qty'] > 0 ? 'bg-success' : 'bg-danger' }} small mb-1">
                                                                    {{ number_format($res['available_qty'], 0) }} {{ $res['unit'] }}
                                                                </div>
                                                                <div class="extra-small text-muted">{{ $res['source'] ?? 'Available' }}</div>
                                                            </div>
                                                        </div>
                                                    </button>
                                                    @endforeach
                                                </div>
                                                @endif

                                                @error("products.$index.name") <span class="text-danger x-small">{{ $message }}</span> @enderror
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       step="0.01" 
                                                       class="form-control form-control-sm {{ $errors->has("products.$index.quantity") ? 'is-invalid' : '' }}" 
                                                       wire:model.blur="products.{{ $index }}.quantity" 
                                                       placeholder="Qty">
                                                @error("products.$index.quantity") <div class="invalid-feedback x-small">{{ $message }}</div> @enderror
                                            </td>
                                            <td class="text-center">
                                                @if(count($products) > 1)
                                                <button type="button" class="btn btn-sm text-danger" wire:click="removeProductRow({{ $index }})">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="modal-footer px-0 pb-0 pt-3 border-top">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showFormModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Save Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
        .x-small { font-size: 0.75rem; }
        .extra-small { font-size: 0.65rem; }
        .z-3 { z-index: 1070 !important; }
        .list-group-item-action { cursor: pointer; }
    </style>
</div>
