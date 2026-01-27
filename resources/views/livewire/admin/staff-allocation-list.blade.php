<div>
    <div class="container-fluid py-4">
        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">
                <i class="bi bi-people-fill text-primary me-2"></i>
                Staff Allocated Products
            </h2>
        </div>

        {{-- Search Bar --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" 
                                class="form-control border-start-0 ps-0" 
                                wire:model.live.debounce.300ms="search"
                                placeholder="Search by staff name, email, or contact...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" wire:model.live="perPage">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Staff Allocation List --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-list-ul text-primary me-2"></i>
                    Staff Allocation List
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th>STAFF NAME</th>
                                <th class="text-center">CONTACT</th>
                                <th class="text-center">TOTAL ALLOCATED</th>
                                <th class="text-center">SOLD</th>
                                <th class="text-center">AVAILABLE</th>
                                <th class="text-end">TOTAL VALUE</th>
                                <th class="text-center">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffAllocations as $index => $staff)
                            <tr wire:key="staff-{{ $staff->id }}">
                                <td class="text-center">{{ $staffAllocations->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary text-white me-2" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                            <strong>{{ strtoupper(substr($staff->name, 0, 1)) }}</strong>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $staff->name }}</div>
                                            <small class="text-muted">{{ $staff->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="text-muted">{{ $staff->contact ?? 'N/A' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-pill">{{ $staff->total_allocated ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success rounded-pill">{{ $staff->total_sold ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info rounded-pill">{{ $staff->total_available ?? 0 }}</span>
                                </td>
                                <td class="text-end">
                                    <strong class="text-primary">Rs. {{ number_format($staff->total_value ?? 0, 2) }}</strong>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.staff-allocated-products', ['staffId' => $staff->id]) }}" 
                                            class="btn btn-sm btn-outline-primary"
                                            title="View Allocated Products">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.staff-return-requests', ['staffId' => $staff->id]) }}" 
                                            class="btn btn-sm btn-outline-success"
                                            title="Return Products">
                                            <i class="bi bi-arrow-return-left"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                    <p class="text-muted mb-0">No staff members found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($staffAllocations->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $staffAllocations->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
