<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-journal-check text-success me-2"></i> Cheque Management
            </h3>
            <p class="text-muted mb-0">View and manage all customer cheques</p>
        </div>

    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-5">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-start border-warning border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Pending Cheques</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $pendingCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-clock-history fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-start border-success border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Completed Cheques</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $completeCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-check2-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-start border-danger border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">Overdue Cheques</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $overdueCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cheque Table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-list-ul text-primary me-2"></i> Cheque List
                </h5>
                <span class="badge bg-primary">{{ $cheques->total() ?? 0 }} records</span>

            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="width: 60%; margin: auto">
                <!-- 🔍 Search Bar -->
                <div class="search-bar flex-grow-1">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" wire:model.live="search"
                            placeholder="Search by cheque number or customer name...">
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="text-sm text-muted fw-medium">Filter</label>
                <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width: 130px;">
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="complete">Complete</option>
                    <option value="overdue">Overdue</option>
                    <option value="return">Return</option>
                </select>

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
                            <th class="ps-4">Cheque No</th>
                            <th>Customer</th>
                            <th>Invoices</th>
                            <th class="text-center">Bank</th>
                            <th class="text-center">Amount</th>
                            <th class="text-center">Date</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cheques as $cheque)
                        @php
                            $firstSaleId = null;
                            $invoices = collect();
                            if ($cheque->payment) {
                                if ($cheque->payment->sale) {
                                    $invoices->push(['id' => $cheque->payment->sale->id, 'number' => $cheque->payment->sale->invoice_number]);
                                    $firstSaleId = $cheque->payment->sale->id;
                                }
                                if ($cheque->payment->allocations) {
                                    foreach($cheque->payment->allocations as $allocation) {
                                        if ($allocation->sale) {
                                            $invoices->push(['id' => $allocation->sale->id, 'number' => $allocation->sale->invoice_number]);
                                            if (!$firstSaleId) $firstSaleId = $allocation->sale->id;
                                        }
                                    }
                                }
                            }
                            $uniqueInvoices = $invoices->unique('id')->filter();
                        @endphp
                        <tr wire:key="cheque-{{ $cheque->id }}" @if($firstSaleId) wire:click="viewSale({{ $firstSaleId }})" style="cursor: pointer;" class="row-hover" @endif>
                            <td class="ps-4">{{ $cheque->cheque_number }}</td>
                            <td>{{ $cheque->customer->name ?? '-' }}</td>
                            <td>
                                @if($cheque->payment)
                                    @forelse($uniqueInvoices as $invoice)
                                        <span class="badge bg-light text-primary border mb-1">{{ $invoice['number'] }}</span>
                                    @empty
                                        <span class="text-muted small">N/A</span>
                                    @endforelse
                                @else
                                    <span class="text-muted small">No Payment</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $cheque->bank_name }}</td>
                            <td class="text-center">Rs.{{ number_format($cheque->cheque_amount, 2) }}</td>
                            <td class="text-center">{{ $cheque->cheque_date ? date('M d, Y', strtotime($cheque->cheque_date)) : '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $cheque->status == 'pending' ? 'warning' : ($cheque->status == 'complete' ? 'success' : ($cheque->status == 'return' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst($cheque->status) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                @if(auth()->user()->role === 'admin' && ($cheque->status == 'pending' || $cheque->status == 'overdue'))
                                @php
                                    $paymentApproved = $cheque->payment && in_array($cheque->payment->status, ['approved', 'paid']);
                                    $chequeDatePassed = $cheque->cheque_date && \Carbon\Carbon::parse($cheque->cheque_date)->startOfDay()->lte(now()->startOfDay());
                                @endphp
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                        wire:click.stop>
                                        <i class="bi bi-gear-fill"></i> Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" wire:click.stop>
                                        @if($paymentApproved && $chequeDatePassed)
                                        <!-- Mark as Complete - only when payment approved AND cheque date has passed -->
                                        <li>
                                            <button class="dropdown-item"
                                                wire:click="confirmComplete({{ $cheque->id }})">
                                                <i class="bi bi-check2-circle text-success me-2"></i>
                                                Complete
                                            </button>
                                        </li>
                                        @elseif(!$paymentApproved)
                                        <li>
                                            <span class="dropdown-item text-muted" style="cursor: default;">
                                                <i class="bi bi-shield-exclamation text-warning me-2"></i>
                                                Awaiting payment approval
                                            </span>
                                        </li>
                                        @elseif(!$chequeDatePassed)
                                        <li>
                                            <span class="dropdown-item text-muted" style="cursor: default;">
                                                <i class="bi bi-clock text-warning me-2"></i>
                                                Cheque date: {{ \Carbon\Carbon::parse($cheque->cheque_date)->format('M d, Y') }}
                                            </span>
                                        </li>
                                        @endif
                                        <!-- Return Cheque -->
                                        <li>
                                            <button class="dropdown-item"
                                                wire:click="confirmReturn({{ $cheque->id }})">
                                                <i class="bi bi-arrow-counterclockwise text-danger me-2"></i>
                                                Return
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-x-circle display-4 d-block mb-2"></i>
                                No cheques found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($cheques->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $cheques->links('livewire.custom-pagination') }}
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ==================== VIEW SALE MODAL ==================== --}}
    <div wire:ignore.self class="modal fade sale-preview" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="printableInvoice">
                @if($selectedSale)
                {{-- Screen Only Header (visible on screen, hidden on print) --}}
                <div class="screen-only-header p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        {{-- Left: Logo --}}
                        <div style="flex: 0 0 150px;">
                            <img src="{{ asset('images/MI-King.png') }}" alt="Logo" class="img-fluid" style="max-height:80px;">
                        </div>

                        {{-- Center: Company Name --}}
                        <div class="text-center" style="flex: 1;">
                            <h2 class="mb-0 fw-bold" style="font-size: 2.5rem; letter-spacing: 2px;">MI-KING</h2>
                            <p class="mb-0 text-muted small">BEST IN BOYS</p>
                        </div>

                        {{-- Right:  & Invoice --}}
                        <div class="text-end" style="flex: 0 0 150px;">
                            <h5 class="mb-0 fw-bold"></h5>
                            <h6 class="mb-0 text-muted">{{ ($selectedSale->sale_price_type ?? 'cash') === 'cash' ? 'CASH INVOICE' : 'CREDIT INVOICE' }}</h6>
                        </div>
                    </div>
                    <hr class="my-2" style="border-top: 2px solid #000;">
                </div>

                <div class="modal-body">
                    {{-- ==================== CUSTOMER + INVOICE INFO ==================== --}}
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Customer :</strong><br>
                            {{ $selectedSale->customer->name ?? 'Walk-in Customer' }}<br>
                            {{ $selectedSale->customer->address ?? '' }}<br>
                            Tel: {{ $selectedSale->customer->phone ?? '' }}
                        </div>
                        <div class="col-6 text-end">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Invoice #</strong></td>
                                    <td>{{ $selectedSale->invoice_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Sale ID</strong></td>
                                    <td>{{ $selectedSale->sale_id }}</td>
                                </tr>
                                 <tr>
                                     <td><strong>Date</strong></td>
                                     <td>{{ $selectedSale->created_at->format('M d, Y h:i A') }}</td>
                                 </tr>
                                 <tr>
                                     <td><strong>Sale Type</strong></td>
                                     <td><span class="badge bg-primary">{{ in_array($selectedSale->sale_price_type ?? '', ['cash', 'cash_credit']) ? 'Cash Invoice' : 'Credit Invoice' }}</span></td>
                                 </tr>
                            </table>
                        </div>
                    </div>

                    {{-- ==================== ITEMS TABLE ==================== --}}
                    <div class="table-responsive mb-3" style="min-height: 10px;">
                        <table class="table table-bordered table-sm invoice-table">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th class="text-center">Code</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedSale->items as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-center">{{ $item->product_code }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">Rs.{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">Rs.{{ number_format($item->discount_per_unit * $item->quantity, 2) }}</td>
                                    <td class="text-end">Rs.{{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                                @if($selectedSale->items->count() == 0)
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No items found.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- ==================== TOTALS (right-aligned) ==================== --}}
                    <div class="row">
                        <div class="col-7"></div>
                        <div class="col-5">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Subtotal</strong></td>
                                    <td class="text-end">Rs.{{ number_format($selectedSale->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Discount</strong></td>
                                    <td class="text-end">- Rs.{{ number_format($selectedSale->discount_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Grand Total</strong></td>
                                    <td class="text-end fw-bold">Rs.{{ number_format($selectedSale->total_amount, 2) }}</td>
                                </tr>
                                @if((!isset($selectedSale->returns) || count($selectedSale->returns) == 0) && (!isset($selectedSale->staffReturns) || $selectedSale->staffReturns->count() == 0))
                                @if($selectedSale->total_amount - $selectedSale->due_amount > 0)
                                <tr>
                                    <td><strong class="text-success">Paid Amount</strong></td>
                                    <td class="text-end fw-bold text-success">Rs.{{ number_format($selectedSale->total_amount - $selectedSale->due_amount, 2) }}</td>
                                </tr>
                                @endif
                                @if($selectedSale->due_amount > 0)
                                <tr>
                                    <td><strong class="text-danger">Due Amount</strong></td>
                                    <td class="text-end fw-bold text-danger">Rs.{{ number_format($selectedSale->due_amount, 2) }}</td>
                                </tr>
                                @endif
                                @endif
                            </table>
                        </div>
                    </div>

                    {{-- ==================== PAYMENT INFORMATION ==================== --}}
                    @if($selectedSale->payments->count() > 0)
                    <div class="mb-4 mt-3">
                        <div class="section-title mb-3" style="border-bottom: 2px solid #2a83df;">
                            <h5 class="fw-bold mb-1" style="color: #2a83df;">PAYMENT INFORMATION</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Payment Method</th>
                                        <th class="text-end">Amount</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedSale->payments as $payment)
                                    <tr>
                                        <td class="text-uppercase fw-semibold">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                        <td class="text-end fw-bold">Rs.{{ number_format($payment->amount, 2) }}</td>
                                        <td>
                                            @if(in_array(strtolower($payment->payment_method), ['cheque', 'cheques']) && $payment->cheques && $payment->cheques->count() > 0)
                                            <div class="small">
                                                @foreach($payment->cheques as $chq)
                                                <div class="border-bottom pb-1 mb-1 last-child-border-0">
                                                    <strong>No:</strong> {{ $chq->cheque_number }} | 
                                                    <strong>Bank:</strong> {{ $chq->bank_name }} | 
                                                    <strong>Date:</strong> {{ date('d/m/Y', strtotime($chq->cheque_date)) }} | 
                                                    <strong>Amt:</strong> Rs.{{ number_format($chq->cheque_amount, 2) }}
                                                </div>
                                                @endforeach
                                            </div>
                                            @elseif(strtolower($payment->payment_method) == 'bank_transfer')
                                                <span class="small"><strong>Ref:</strong> {{ $payment->payment_reference }}</span>
                                            @else
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    @if($selectedSale->notes)
                    <h6 class="text-muted mb-2">NOTES</h6>
                    <div class="card bg-light">
                        <div class="card-body">
                            <p class="mb-0">{{ $selectedSale->notes }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Footer Note --}}
                    <div class="invoice-footer mt-4">
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <p class=""><strong>.............................</strong></p>
                                <p class="mb-2"><strong>Checked By</strong></p>
                                
                            </div>
                            <div class="col-4">
                                <p class=""><strong>.............................</strong></p>
                                <p class="mb-2"><strong>Authorized Officer</strong></p>
                                
                            </div>
                            <div class="col-4">
                                <p class=""><strong>.............................</strong></p>
                                <p class="mb-2"><strong>Customer Stamp</strong></p>
                                
                            </div>
                        </div>
                        <div class="border-top pt-3">
                            <p class="text-center mb-0"><strong>ADDRESS :</strong> 122/10A, Super Paradise Market, Keyzer Street, Colombo 11 .</p>
                            <p class="text-center mb-0"><strong>TEL :</strong> (076) 1234567, <strong>EMAIL :</strong> sample@gmail.com</p>
                            <p class="text-center" style="font-size: 11px;"><strong>Goods return will be accepted within 10 days only. Electrical and body parts non-returnable.</strong></p>
                        </div>
                    </div>
                </div>
                @endif
                {{-- ==================== FOOTER BUTTONS ==================== --}}
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary me-2" wire:click="closeModals">
                            <i class="bi bi-x-circle me-1"></i> Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('showModal', (event) => {
            const modalId = Array.isArray(event) ? event[0] : event;
            setTimeout(() => {
                const modalElement = document.getElementById(modalId);
                if (modalElement) {
                    const existingModal = bootstrap.Modal.getInstance(modalElement);
                    if (existingModal) {
                        existingModal.dispose();
                    }
                    const modal = new bootstrap.Modal(modalElement, {
                        backdrop: 'static',
                        keyboard: false
                    });
                    modal.show();
                }
            }, 100);
        });

        Livewire.on('hideModal', (event) => {
            const modalId = Array.isArray(event) ? event[0] : event;
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
                setTimeout(() => {
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }, 200);
            }
        });
    });
</script>
@endpush
@push('styles')
<style>
    /* Fix for excessive spacing in preview modal */
    .sale-preview .table-responsive {
        min-height: auto !important;
        margin-bottom: 1rem !important;
    }

    .sale-preview .table {
        margin-bottom: 0 !important;
    }

    .sale-preview .invoice-table th {
        background: #2a83df !important;
        color: white !important;
        padding: 8px !important;
        font-size: 0.8rem !important;
    }

    .sale-preview .invoice-table td {
        padding: 6px 8px !important;
    }

    .row-hover:hover {
        background-color: rgba(42, 131, 223, 0.05);
        transition: background-color 0.2s;
    }
</style>
@endpush