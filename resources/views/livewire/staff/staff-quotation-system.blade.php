<div class="container-fluid py-3"
    x-data="{ 
        init() {
            window.addEventListener('keydown', (e) => {
                // Focus search: F1
                if (e.key === 'F1') {
                    e.preventDefault();
                    document.getElementById('productSearchInput').focus();
                }
                
                // Create quotation: F2
                if (e.key === 'F2') {
                    e.preventDefault();
                    document.getElementById('createQuotationButton').click();
                }

                // Alt + C: Add new customer
                if (e.altKey && e.key === 'c' || e.altKey && e.key === 'C') {
                    e.preventDefault();
                    @this.call('openCustomerModal');
                }

                // Alt + X: Clear cart
                if (e.altKey && e.key === 'x' || e.altKey && e.key === 'X') {
                    e.preventDefault();
                    if(confirm('Are you sure you want to clear the cart?')) {
                        @this.call('clearCart');
                    }
                }
            });

            window.addEventListener('focus-search', () => {
                setTimeout(() => {
                    let searchInput = document.getElementById('productSearchInput');
                    if(searchInput) {
                        searchInput.focus();
                        searchInput.select();
                    }
                }, 100);
            });

            window.addEventListener('focus-qty', (e) => {
                setTimeout(() => {
                    let index = e.detail.index !== undefined ? e.detail.index : 0;
                    let qtyInput = document.getElementById('qty-input-' + index);
                    if(qtyInput) {
                        qtyInput.focus();
                        qtyInput.select();
                    }
                }, 150);
            });

            window.addEventListener('focus-pos-field', (e) => {
                setTimeout(() => {
                    let { index, field } = e.detail;
                    let input = document.getElementById(`${field}-input-${index}`);
                    if (input) {
                        input.focus();
                        if(input.select) input.select();
                    }
                }, 50);
            });

            window.addEventListener('scroll-to-result', (e) => {
                const index = e.detail.index;
                const container = document.getElementById('search-results-container');
                const element = document.getElementById('search-result-' + index);
                
                if (container && element) {
                    const containerRect = container.getBoundingClientRect();
                    const elementRect = element.getBoundingClientRect();

                    if (elementRect.bottom > containerRect.bottom) {
                        // Scroll down
                        container.scrollTop += (elementRect.bottom - containerRect.bottom);
                    } else if (elementRect.top < containerRect.top) {
                        // Scroll up
                        container.scrollTop -= (containerRect.top - elementRect.top);
                    }
                }
            });
        }
    }">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold text-dark mb-2">
                        <i class="bi bi-file-earmark-text text-success me-2"></i> Create Quotation
                    </h3>
                    <p class="text-muted">Quickly create professional quotations for customers</p>
                </div>
            </div>
        </div>
    </div>

    

    <div class="row">
        {{-- Customer Information --}}
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-1">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="bi bi-person me-2 text-primary"></i> Customer Information
                    </h5>
                    <button class="btn btn-sm btn-primary" wire:click="openCustomerModal">
                        <i class="bi bi-plus-circle me-1"></i> Add New Customer
                    </button>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        {{-- Select Customer (No default) --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Select Customer *</label>
                            <select class="form-select shadow-sm" wire:model.live="customerId">
                                <option value="">-- Select a Customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->name }}
                                        @if($customer->phone)
                                            - {{ $customer->phone }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text mt-2">
                                Select an existing customer or add a new one.
                            </div>
                        </div>

                        {{-- Valid Until --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Valid Until *</label>
                            <input type="date" class="form-control shadow-sm" wire:model="validUntil">
                        </div>

                        {{-- Sale Type --}}
                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-semibold">Sale Type *</label>
                            <select class="form-select shadow-sm" wire:model.live="saleType">
                                <option value="retail">Retail Sale</option>
                                <option value="wholesale">Wholesale Sale</option>
                            </select>
                            <div class="form-text mt-2">
                                Prices will automatically adjust based on selection.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Add Products --}}
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-1">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="bi bi-search me-2 text-success"></i> Add Products
                    </h5>
                </div>

                <div class="card-body">
                    {{-- Search Field --}}
                    <div class="mb-3">
                        <input type="text" class="form-control shadow-sm"
                            id="productSearchInput"
                            wire:model.live="search"
                            wire:keydown.arrow-down.prevent="selectNextResult"
                            wire:keydown.arrow-up.prevent="selectPreviousResult"
                            wire:keydown.enter.prevent="addSelectedResult"
                            wire:keydown.escape.prevent="$set('search', '')"
                            placeholder="Search by product name, code, or model... [F1]">
                    </div>

                    {{-- Search Results --}}
                    @if($search && count($searchResults) > 0)
                        <div class="search-results border rounded bg-white shadow-sm" 
                            id="search-results-container"
                            style="max-height: 300px; overflow-y: auto;">
                            @foreach($searchResults as $index => $product)
                                <div class="p-3 border-bottom d-flex justify-content-between align-items-center {{ $selectedResultIndex === $index ? 'bg-primary bg-opacity-10 border-primary' : '' }}"
                                    id="search-result-{{ $index }}"
                                    wire:key="product-{{ $product['id'] }}"
                                    style="{{ $selectedResultIndex === $index ? 'border-left: 4px solid #0d6efd;' : '' }}">
                                    <div>
                                        <h6 class="mb-1 fw-semibold">{{ $product['name'] }}</h6>
                                        <p class="text-muted small mb-0">
                                            Code: {{ $product['code'] }} | Model: {{ $product['model'] }}
                                        </p>
                                        <p class="text-success small mb-0">
                                            Rs.{{ number_format($product['price'], 2) }} | Stock: {{ $product['stock'] }}
                                        </p>
                                    </div>
                                    <button class="btn btn-sm btn-success" wire:click="addToCart({{ json_encode($product) }})">
                                        <i class="bi bi-plus-circle"></i> Add
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @elseif($search && count($searchResults) == 0)
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i> No products found
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Quotation Items Table --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="bi bi-cart3 me-2 text-primary"></i>Quotation Items
                        <span class="badge bg-primary rounded-pill ms-2" style="font-size: 0.7rem;">{{ count($cart) }} Items</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 align-middle">
                            <thead class="bg-primary text-white text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                                <tr>
                                    <th class="py-3 text-center" style="width: 40px; background-color: #0b5ed7 !important; border-color: #0b5ed7;">#</th>
                                    <th class="py-3" style="background-color: #0b5ed7 !important; border-color: #0b5ed7;">Product</th>
                                    <th class="py-3 text-center" style="width: 150px; background-color: #0b5ed7 !important; border-color: #0b5ed7;">Unit Price</th>
                                    <th class="py-3 text-center" style="width: 150px; background-color: #0b5ed7 !important; border-color: #0b5ed7;">Quantity</th>
                                    <th class="py-3 text-center" style="width: 150px; background-color: #0b5ed7 !important; border-color: #0b5ed7;">Discount/Unit</th>
                                    <th class="py-3 text-center" style="width: 150px; background-color: #0b5ed7 !important; border-color: #0b5ed7;">Total</th>
                                    <th class="py-3 text-center" style="width: 100px; background-color: #0b5ed7 !important; border-color: #0b5ed7;">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @forelse($cart as $index => $item)
                                    <tr wire:key="cart-{{ $index }}">
                                        <td class="text-center font-monospace">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $item['name'] }}</div>
                                            <div class="text-muted small font-monospace">{{ $item['code'] }}</div>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm text-center border-1" 
                                                id="price-input-{{ $index }}"
                                                style="border-color: #dee2e6;"
                                                value="{{ number_format($item['price'], 2, '.', '') }}"
                                                wire:change="updateUnitPrice({{ $index }}, $event.target.value)"
                                                wire:keydown.enter.prevent="$dispatch('focus-search')"
                                                x-on:keydown.arrow-right.prevent="$dispatch('focus-pos-field', { index: {{ $index }}, field: 'qty' })"
                                                x-on:keydown.arrow-left.prevent="$dispatch('focus-pos-field', { index: {{ $index }}, field: 'qty' })"
                                                x-on:keydown.arrow-down.prevent="$dispatch('focus-pos-field', { index: {{ $index + 1 }}, field: 'price' })"
                                                x-on:keydown.arrow-up.prevent="$dispatch('focus-pos-field', { index: {{ $index - 1 }}, field: 'price' })">
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm justify-content-center">
                                                <input type="number" class="form-control text-center border-1 fw-bold" 
                                                    id="qty-input-{{ $index }}"
                                                    style="max-width: 60px; border-color: #dee2e6;"
                                                    value="{{ $item['quantity'] }}"
                                                    wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                                    wire:keydown.enter.prevent="$dispatch('focus-search')"
                                                    x-on:keydown.arrow-right.prevent="$dispatch('focus-pos-field', { index: {{ $index }}, field: 'discount' })"
                                                    x-on:keydown.arrow-left.prevent="$dispatch('focus-pos-field', { index: {{ $index }}, field: 'price' })"
                                                    x-on:keydown.arrow-down.prevent="$dispatch('focus-pos-field', { index: {{ $index + 1 }}, field: 'qty' })"
                                                    x-on:keydown.arrow-up.prevent="$dispatch('focus-pos-field', { index: {{ $index - 1 }}, field: 'qty' })">
                                            </div>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm text-center border-1 text-danger" 
                                                id="discount-input-{{ $index }}"
                                                style="border-color: #dee2e6;"
                                                value="{{ number_format($item['discount'], 2, '.', '') }}"
                                                wire:change="updateDiscount({{ $index }}, $event.target.value)"
                                                wire:keydown.enter.prevent="$dispatch('focus-search')"
                                                x-on:keydown.arrow-left.prevent="$dispatch('focus-pos-field', { index: {{ $index }}, field: 'qty' })"
                                                x-on:keydown.arrow-down.prevent="$dispatch('focus-pos-field', { index: {{ $index + 1 }}, field: 'discount' })"
                                                x-on:keydown.arrow-up.prevent="$dispatch('focus-pos-field', { index: {{ $index - 1 }}, field: 'discount' })">
                                        </td>
                                        <td class="text-end pe-3 fw-bold">
                                            Rs.{{ number_format($item['total'], 2) }}
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-danger border-1" wire:click="removeFromCart({{ $index }})">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-cart-x display-5 d-block mb-3"></i>
                                                Your cart is empty. Search and add products to start.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(count($cart) > 0)
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="5" class="text-end py-2 fw-bold border-0">Subtotal:</td>
                                    <td colspan="2" class="text-end pe-3 py-2 fw-bold border-0">Rs.{{ number_format($subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end py-2 fw-bold align-middle border-0">
                                        Additional Discount:
                                        @if($additionalDiscount > 0)
                                            <i class="bi bi-x-circle text-danger ms-1 cursor-pointer" wire:click="removeAdditionalDiscount"></i>
                                        @endif
                                    </td>
                                    <td class="py-2 border-0">
                                        <div class="input-group input-group-sm ms-auto" style="width: 160px;">
                                            <input type="number" class="form-control text-center text-danger fw-bold border-1"
                                                style="border-color: #dee2e6;"
                                                wire:model.live="additionalDiscount"
                                                placeholder="0.00">
                                            <span class="input-group-text bg-white border-1" style="border-color: #dee2e6; color: #6c757d;">
                                                {{ $additionalDiscountType === 'percentage' ? '%' : 'Rs.' }}
                                            </span>
                                            <button class="btn btn-outline-secondary border-1" wire:click="toggleDiscountType">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td colspan="2" class="text-end pe-3 py-2 fw-bold text-danger border-0">
                                        - Rs.{{ number_format($additionalDiscountAmount, 2) }}
                                    </td>
                                </tr>
                                <tr style="border-top: 2px solid #dee2e6;">
                                    <td colspan="5" class="text-end py-3 fw-bold fs-5 text-dark border-0">Grand Total:</td>
                                    <td colspan="2" class="text-end pe-3 py-3 fw-bold fs-5 text-primary border-0">Rs.{{ number_format($grandTotal, 2) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
                @if(count($cart) > 0)
                    <div class="card-footer bg-white border-0 py-3">
                        <button class="btn btn-danger btn-sm rounded-pill px-3" wire:click="clearCart">
                            <i class="bi bi-trash-fill me-2"></i>Clear All Items
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Terms & Conditions --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="bi bi-file-earmark-text me-2 text-primary"></i>Terms & Conditions
                    </h5>
                </div>
                <div class="card-body">
                    <textarea class="form-control border-1" rows="5" wire:model="termsConditions" 
                        style="border-color: #dee2e6; font-size: 0.9rem;"
                        placeholder="1. This quotation is valid for 30 days."></textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="row mb-5">
        <div class="col-12 text-center">
            <button class="btn btn-primary btn-lg px-5 py-2 shadow-sm fw-bold" 
                style="border-radius: 8px; font-size: 1.1rem;"
                wire:click="createQuotation"
                id="createQuotationButton"
                {{ count($cart) === 0 ? 'disabled' : '' }}>
                <i class="bi bi-file-earmark-plus-fill me-2"></i>Create Quotation [F2]
            </button>
        </div>
    </div>

    {{-- Quotation Preview Modal --}}
    @if($showQuotationModal && $createdQuotation)
        <div class="modal show d-block" style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1050;">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 0;">
                    {{-- Modal Header --}}
                    <div class="modal-header bg-primary text-white py-2" style="border-radius: 0;">
                        <h6 class="modal-title fw-semibold">
                            <i class="bi bi-file-earmark-text me-2"></i>Quotation Preview - {{ $createdQuotation->quotation_number }}
                        </h6>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body p-0 bg-white" style="max-height: 80vh; overflow-y: auto;">
                        <div class="quotation-preview-container p-4">
                            {{-- Company Header --}}
                            <div class="text-center mb-4">
                                <h1 class="fw-bold mb-1" style="color: #0d8a4e; letter-spacing: 2px;">MI-KING</h1>
                                <p class="text-muted small mb-1">122/10A, Super Paradise Market, Keyzer Street, Colombo 11 .</p>
                                <p class="text-muted small mb-0">Phone: (076) 1234567 | Email: sample@gmail.com</p>
                                <div class="mt-3 mx-auto" style="height: 1.5px; width: 100%; background: #0d8a4e;"></div>
                            </div>

                            {{-- Info Cards --}}
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <div class="card h-100 border shadow-none" style="border-radius: 8px;">
                                        <div class="card-header bg-light py-2 px-3 border-bottom">
                                            <h6 class="mb-0 fw-semibold text-secondary small">Customer Information</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <p class="mb-1 fw-bold text-dark">{{ $createdQuotation->customer_name }}</p>
                                            <p class="mb-1 text-muted small" style="white-space: pre-line;">{{ $createdQuotation->customer_address }}</p>
                                            <p class="mb-0 text-muted small">Tel: {{ $createdQuotation->customer_phone }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 border shadow-none" style="border-radius: 8px;">
                                        <div class="card-header bg-light py-2 px-3 border-bottom">
                                            <h6 class="mb-0 fw-semibold text-secondary small">Quotation Details</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="text-muted small">Quotation No:</span>
                                                <span class="fw-bold small">{{ $createdQuotation->quotation_number }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="text-muted small">Date:</span>
                                                <span class="fw-bold small">{{ $createdQuotation->quotation_date->format('d/m/Y') }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted small">Valid Until:</span>
                                                <span class="fw-bold small">{{ $createdQuotation->valid_until->format('d/m/Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Preview Table --}}
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead class="bg-primary text-white" style="font-size: 0.75rem;">
                                        <tr>
                                            <th class="py-2 text-center" style="width: 40px; background-color: #0b5ed7 !important; border: none;">#</th>
                                            <th class="py-2" style="background-color: #0b5ed7 !important; border: none;">ITEM CODE</th>
                                            <th class="py-2" style="background-color: #0b5ed7 !important; border: none;">DESCRIPTION</th>
                                            <th class="py-2 text-center" style="width: 60px; background-color: #0b5ed7 !important; border: none;">QTY</th>
                                            <th class="py-2 text-end" style="width: 120px; background-color: #0b5ed7 !important; border: none;">UNIT PRICE (LKR)</th>
                                            <th class="py-2 text-end" style="width: 120px; background-color: #0b5ed7 !important; border: none;">DISCOUNT (LKR)</th>
                                            <th class="py-2 text-end" style="width: 130px; background-color: #0b5ed7 !important; border: none;">SUBTOTAL (LKR)</th>
                                        </tr>
                                    </thead>
                                    <tbody style="font-size: 0.85rem;">
                                        @foreach($createdQuotation->items as $index => $item)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>{{ $item['product_code'] }}</td>
                                                <td>{{ $item['product_name'] }}</td>
                                                <td class="text-center">{{ $item['quantity'] }}</td>
                                                <td class="text-end">{{ number_format($item['unit_price'], 2) }}</td>
                                                <td class="text-end">{{ number_format($item['total_discount'] ?? 0, 2) }}</td>
                                                <td class="text-end">{{ number_format($item['total'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot style="font-size: 0.85rem;">
                                        <tr>
                                            <td colspan="6" class="text-end fw-bold py-2 border-0">Subtotal:</td>
                                            <td class="text-end fw-bold py-2 border-0">{{ number_format($createdQuotation->subtotal, 2) }}</td>
                                        </tr>
                                        @php
                                            $itemDiscounts = collect($createdQuotation->items)->sum('total_discount');
                                            $totalDisc = $itemDiscounts + $createdQuotation->additional_discount;
                                        @endphp
                                        @if($totalDisc > 0)
                                        <tr>
                                            <td colspan="6" class="text-end fw-bold text-danger py-2 border-0">Total Discount:</td>
                                            <td class="text-end fw-bold text-danger py-2 border-0">- {{ number_format($totalDisc, 2) }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td colspan="6" class="text-end fw-bold py-2 border-0">Grand Total:</td>
                                            <td class="text-end fw-bold py-2 border-0 text-primary">{{ number_format($createdQuotation->total_amount, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            {{-- Terms Section --}}
                            <div class="card border shadow-none mt-5" style="border-radius: 8px;">
                                <div class="card-header bg-light py-2 px-3 border-bottom">
                                    <h6 class="mb-0 fw-semibold text-secondary small">Terms & Conditions</h6>
                                </div>
                                <div class="card-body p-3">
                                    <p class="mb-0 text-muted small" style="white-space: pre-line;">{!! nl2br(e($createdQuotation->terms_conditions)) !!}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="modal-footer bg-light border-top justify-content-center py-2">
                        <button class="btn btn-outline-secondary btn-sm px-4 me-3" wire:click="createNewQuotation" style="border-radius: 6px;">
                            <i class="bi bi-plus-circle me-1"></i> Create New Quotation
                        </button>
                        <button class="btn btn-success btn-sm px-4" wire:click="downloadQuotation" style="background-color: #198754; border-radius: 6px;">
                            <i class="bi bi-download me-1"></i> Download Quotation
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Customer Modal --}}
    @if($showCustomerModal)
        <div class="modal show d-block" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-person-plus me-2"></i> Add New Customer
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeCustomerModal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Customer Name *</label>
                                <input type="text" class="form-control" wire:model="customerName" placeholder="Enter customer name">
                                @error('customerName') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" class="form-control" wire:model="customerPhone" placeholder="Phone number">
                                @error('customerPhone') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control" wire:model="customerEmail" placeholder="Email address">
                                @error('customerEmail') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Address *</label>
                                <input type="text" class="form-control" wire:model="customerAddress" placeholder="Customer address">
                                @error('customerAddress') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Customer Type *</label>
                                <select class="form-select" wire:model="customerType">
                                    <option value="retail">Retail</option>
                                    <option value="wholesale">Wholesale</option>
                                    <option value="business">Business</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Business Name</label>
                                <input type="text" class="form-control" wire:model="businessName" placeholder="Business name (optional)">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" wire:click="closeCustomerModal">Cancel</button>
                        <button class="btn btn-primary" wire:click="createCustomer">
                            <i class="bi bi-check-circle me-1"></i> Create Customer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Shortcut Legend --}}
    <div class="position-fixed bottom-0 end-0 m-3 d-none d-lg-block" style="z-index: 1050;">
        <div class="bg-dark text-white p-2 rounded shadow-lg opacity-85 small">
            <div class="mb-1"><span class="badge bg-primary">F1</span> Search</div>
            <div class="mb-1"><span class="badge bg-primary">F2</span> Create Quotation</div>
            <div class="mb-1"><span class="badge bg-primary">Alt + C</span> Add Customer</div>
            <div><span class="badge bg-danger">Alt + X</span> Clear Cart</div>
        </div>
    </div>
</div>

