<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Customer;
use App\Models\ProductDetail;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\POSSession;
use App\Services\FIFOStockService;
use App\Services\StaffBonusService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Livewire\Concerns\WithDynamicLayout;
use App\Livewire\Concerns\WithPagination;



#[Title('Create Sale')]
class SalesSystem extends Component
{
    use WithDynamicLayout;
    // Basic Properties
    public $search = '';
    public $searchResults = [];
    public $selectedResultIndex = 0;
    public $customerId = '';
    public $customerSearch = '';
    public $customerSearchResults = [];
    public $selectedCustomerIndex = 0;

    // Cart Items
    public $cart = [];

    // Customer Properties
    public $customers = [];
    public $selectedCustomer = null;

    // Customer Form (for new customer - only used in modal)
    public $customerName = '';
    public $customerPhone = '';
    public $customerEmail = '';
    public $customerAddress = '';
    public $customerType = 'retail';
    public $businessName = '';

    // Sale Properties
    public $notes = '';
    public $paymentMethod = 'credit'; // Always credit for Sales System
    public $salePriceType = 'cash'; // Price type: cash, credit, cash_credit

    // Discount Properties
    public $additionalDiscount = 0;
    public $additionalDiscountType = 'fixed'; // 'fixed' or 'percentage'

    // Modals
    public $showSaleModal = false;
    public $showCustomerModal = false;
    public $lastSaleId = null;
    public $createdSale = null;
    // POS Session (to track/update daily totals)
    public $currentSession = null;

    // Editing Sale
    public $editSaleId = null;
    public $isEditing = false;
    public $originalSale = null;

    public function mount($saleId = null)
    {
        $this->loadCustomers();

        if ($saleId) {
            $this->loadSaleForEditing($saleId);
        } else {
            $this->setDefaultCustomer();
        }
    }

    public function loadSaleForEditing($saleId)
    {
        $sale = Sale::with(['items.product.stock', 'items.product.price', 'customer'])->find($saleId);

        if (!$sale) {
            $this->js("Swal.fire('error', 'Sale not found!', 'error')");
            $this->setDefaultCustomer();
            return;
        }

        $this->editSaleId = $sale->id;
        $this->isEditing = true;
        $this->originalSale = $sale;

        $this->customerId = $sale->customer_id;
        $this->selectedCustomer = $sale->customer;
        $this->customerSearch = $sale->customer->name ?? '';
        $this->notes = $sale->notes;
        $this->salePriceType = $sale->sale_price_type;
        $this->additionalDiscount = $sale->discount_amount;
        $this->additionalDiscountType = 'fixed'; // We'll use fixed for simplicity upon loading

        // Load items into cart
        $this->cart = [];
        foreach ($sale->items as $item) {
            $product = $item->product;
            if (!$product) continue;

            $this->cart[] = [
                'key' => uniqid('cart_'),
                'id' => $product->id,
                'name' => $item->product_name,
                'code' => $item->product_code,
                'model' => $item->product_model,
                'price' => $item->unit_price,
                'cash_price' => $product->price->cash_price ?? $item->unit_price,
                'credit_price' => $product->price->credit_price ?? $item->unit_price,
                'cash_credit_price' => $product->price->cash_credit_price ?? $product->price->cash_price ?? $item->unit_price,
                'quantity' => $item->quantity,
                'discount' => $item->discount_per_unit,
                'total' => $item->total,
                'stock' => ($product->stock->available_stock ?? 0) + $item->quantity // Add back original qty for stock check
            ];
        }
    }

    // Set default walking customer
    public function setDefaultCustomer()
    {
        // Find or create walking customer (only one)
        $walkingCustomer = Customer::where('name', 'Walking Customer')->first();

        if (!$walkingCustomer) {
            $walkingCustomer = Customer::create([
                'name' => 'Walking Customer',
                'phone' => 'xxxxx', // Empty phone number
                'email' => null,
                'address' => 'xxxxx',
                'type' => 'retail',
                'business_name' => null,
            ]);

            $this->loadCustomers(); // Reload customers after creating new one
        }

        $this->customerId = $walkingCustomer->id;
        $this->selectedCustomer = $walkingCustomer;
        $this->customerSearch = $walkingCustomer->name;
    }

    // Load customers for dropdown
    public function loadCustomers()
    {
        $this->customers = Customer::orderBy('name')->get();
    }

    // Computed Properties for Totals
    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum('total');
    }

    public function getTotalDiscountProperty()
    {
        return collect($this->cart)->sum(function ($item) {
            return ($item['discount'] * $item['quantity']);
        });
    }

    public function getSubtotalAfterItemDiscountsProperty()
    {
        return $this->subtotal;
    }

    public function getAdditionalDiscountAmountProperty()
    {
        if (empty($this->additionalDiscount) || $this->additionalDiscount <= 0) {
            return 0;
        }

        if ($this->additionalDiscountType === 'percentage') {
            return ($this->subtotalAfterItemDiscounts * $this->additionalDiscount) / 100;
        }

        return min($this->additionalDiscount, $this->subtotalAfterItemDiscounts);
    }

    public function getGrandTotalProperty()
    {
        return $this->subtotalAfterItemDiscounts - $this->additionalDiscountAmount;
    }



    public function updatedCustomerSearch()
    {
        $this->selectedCustomerIndex = 0;
        if (strlen($this->customerSearch) >= 1) {
            $this->customerSearchResults = Customer::where('name', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('phone', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('business_name', 'like', '%' . $this->customerSearch . '%')
                ->orderBy('name')
                ->take(10)
                ->get();
        } else {
            $this->customerSearchResults = [];
        }
    }

    public function selectNextCustomer()
    {
        if (count($this->customerSearchResults) > 0) {
            $this->selectedCustomerIndex = ($this->selectedCustomerIndex + 1) % count($this->customerSearchResults);
        }
    }

    public function selectPreviousCustomer()
    {
        if (count($this->customerSearchResults) > 0) {
            $this->selectedCustomerIndex = ($this->selectedCustomerIndex - 1 + count($this->customerSearchResults)) % count($this->customerSearchResults);
        }
    }

    public function selectSelectedCustomer()
    {
        if (count($this->customerSearchResults) > 0 && isset($this->customerSearchResults[$this->selectedCustomerIndex])) {
            $this->selectCustomer($this->customerSearchResults[$this->selectedCustomerIndex]['id']);
        }
    }

    public function selectCustomer($id)
    {
        $this->customerId = $id;
        $customer = Customer::find($id);
        if ($customer) {
            $this->selectedCustomer = $customer;
            $this->customerSearch = $customer->name;
        }
        $this->customerSearchResults = [];
    }

    // When sale price type is changed, recalculate all cart prices
    public function updatedSalePriceType($value)
    {
        foreach ($this->cart as $index => $item) {
            $price = 0;

            switch ($value) {
                case 'cash':
                    $price = $item['cash_price'] ?? $item['price'];
                    break;
                case 'credit':
                    $price = $item['credit_price'] ?? $item['price'];
                    break;
                case 'cash_credit':
                    $price = $item['cash_credit_price'] ?? $item['cash_price'] ?? $item['price'];
                    break;
                default:
                    $price = $item['cash_price'] ?? $item['price'];
            }

            $this->cart[$index]['price'] = $price;
            $this->cart[$index]['total'] = ($price - $this->cart[$index]['discount']) * $this->cart[$index]['quantity'];
        }
    }

    // Reset customer fields
    public function resetCustomerFields()
    {
        $this->customerName = '';
        $this->customerPhone = '';
        $this->customerEmail = '';
        $this->customerAddress = '';
        $this->customerType = 'retail';
        $this->businessName = '';
    }

    // Open customer modal
    public function openCustomerModal()
    {
        $this->resetCustomerFields();
        $this->showCustomerModal = true;
    }

    // Close customer modal
    public function closeCustomerModal()
    {
        $this->showCustomerModal = false;
        $this->resetCustomerFields();
    }

    // Create new customer
    public function createCustomer()
    {
        $this->validate([
            'customerName' => 'required|string|max:255',
            'customerPhone' => 'nullable|string|max:10|unique:customers,phone',
            'customerEmail' => 'nullable|email|unique:customers,email',
            'customerAddress' => 'required|string',
            'customerType' => 'required|in:retail,wholesale',
        ]);

        try {
            $customer = Customer::create([
                'name' => $this->customerName,
                'phone' => $this->customerPhone ?: null,
                'email' => $this->customerEmail,
                'address' => $this->customerAddress,
                'type' => $this->customerType,
                'business_name' => $this->businessName,
                'user_id' => Auth::id(),
            ]);

            $this->loadCustomers();
            $this->customerId = $customer->id;
            $this->selectedCustomer = $customer;
            $this->closeCustomerModal();

            $this->js("Swal.fire('success', 'Customer created successfully!', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('error', 'Failed to create customer: ', 'error')");
        }
    }

    // Search Products
    public function updatedSearch()
    {
        $this->selectedResultIndex = 0; // Reset index on search update
        if (strlen($this->search) >= 2) {
            if ($this->isStaff()) {
                // For staff: only show their allocated products
                $this->searchResults = \App\Models\StaffProduct::where('staff_id', auth()->id())
                    ->join('product_details', 'staff_products.product_id', '=', 'product_details.id')
                    ->where(function ($query) {
                        $query->where('product_details.name', 'like', '%' . $this->search . '%')
                            ->orWhere('product_details.code', 'like', '%' . $this->search . '%')
                            ->orWhere('product_details.model', 'like', '%' . $this->search . '%');
                    })
                    ->select(
                        'product_details.id',
                        'product_details.name',
                        'product_details.code',
                        'product_details.model',
                        'product_details.image',
                        'staff_products.unit_price as price',
                        'staff_products.quantity',
                        'staff_products.sold_quantity'
                    )
                    ->take(10)
                    ->get()
                    ->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'code' => $product->code,
                            'model' => $product->model,
                            'price' => $product->price,
                            'stock' => ($product->quantity - $product->sold_quantity),
                            'sold' => $product->sold_quantity,
                            'image' => $product->image
                        ];
                    });
            } else {
                // For admin: show all products
                $this->searchResults = ProductDetail::with(['stock', 'price'])
                    ->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhere('model', 'like', '%' . $this->search . '%')
                    ->take(10)
                    ->get()
                    ->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'code' => $product->code,
                            'model' => $product->model,
                            'price' => $product->price->selling_price ?? 0,
                            'retail_price' => $product->price->retail_price ?? $product->price->selling_price ?? 0,
                            'wholesale_price' => $product->price->wholesale_price ?? 0,
                            'stock' => $product->stock->available_stock ?? 0,
                            'sold' => $product->stock->sold_count ?? 0,
                            'image' => $product->image
                        ];
                    });
            }
        } else {
            $this->searchResults = [];
            $this->selectedResultIndex = 0;
        }
    }

    public function selectNextResult()
    {
        if (count($this->searchResults) > 0) {
            $this->selectedResultIndex = ($this->selectedResultIndex + 1) % count($this->searchResults);
            $this->dispatch('scroll-to-result', index: $this->selectedResultIndex);
        }
    }

    public function selectPreviousResult()
    {
        if (count($this->searchResults) > 0) {
            $this->selectedResultIndex = ($this->selectedResultIndex - 1 + count($this->searchResults)) % count($this->searchResults);
            $this->dispatch('scroll-to-result', index: $this->selectedResultIndex);
        }
    }

    public function addSelectedResult()
    {
        if (count($this->searchResults) > 0 && isset($this->searchResults[$this->selectedResultIndex])) {
            $this->addToCart($this->searchResults[$this->selectedResultIndex]);
        }
    }

    // Add to Cart
    public function addToCart($product)
    {
        // Check stock availability
        if (($product['stock'] ?? 0) <= 0) {
            $this->js("Swal.fire('error', 'Not enough stock available!', 'error')");
            return;
        }

        $existing = collect($this->cart)->firstWhere('id', $product['id']);

        if ($existing) {
            // Check if adding more exceeds stock
            if (($existing['quantity'] + 1) > $product['stock']) {
                $this->js("Swal.fire('error', 'Not enough stock available!', 'error')");
                return;
            }

            $this->cart = collect($this->cart)->map(function ($item) use ($product) {
                if ($item['id'] == $product['id']) {
                    $item['quantity'] += 1;
                    $item['total'] = ($item['price'] - $item['discount']) * $item['quantity'];
                    // Ensure key exists
                    if (!isset($item['key'])) {
                        $item['key'] = uniqid('cart_');
                    }
                }
                return $item;
            })->toArray();
        } else {
            // Get product price details
            $productDetail = ProductDetail::with('price')->find($product['id']);

            // Determine which price to use based on salePriceType
            $finalPrice = $product['price']; // default
            $cashPrice = $productDetail->price->cash_price ?? $product['price'];
            $creditPrice = $productDetail->price->credit_price ?? $product['price'];
            $cashCreditPrice = $productDetail->price->cash_credit_price ?? $cashPrice;

            switch ($this->salePriceType) {
                case 'cash':
                    $finalPrice = $cashPrice;
                    break;
                case 'credit':
                    $finalPrice = $creditPrice;
                    break;
                case 'cash_credit':
                    $finalPrice = $cashCreditPrice;
                    break;
                default:
                    $finalPrice = $cashPrice;
            }

            $newItem = [
                'key' => uniqid('cart_'),  // Add unique key to maintain state
                'id' => $product['id'],
                'name' => $product['name'],
                'code' => $product['code'],
                'model' => $product['model'],
                'price' => $finalPrice,
                'cash_price' => $cashPrice,
                'credit_price' => $creditPrice,
                'cash_credit_price' => $cashCreditPrice,
                'quantity' => 1,
                'discount' => 0,
                'total' => $finalPrice,
                'stock' => $product['stock']
            ];

            // Prepend new item to the beginning of the cart so it appears at the top
            array_unshift($this->cart, $newItem);
        }

        $this->search = '';
        $this->searchResults = [];
        $this->selectedResultIndex = 0;
        $this->dispatch('focus-qty', index: 0);
    }

    public function updateQuantity($index, $quantity)
    {
        if ($quantity < 1) $quantity = 1;

        // Check stock availability
        $productStock = $this->cart[$index]['stock'];
        if ($quantity > $productStock) {
            $this->js("Swal.fire('error', 'Not enough stock available! Maximum: ' . $productStock, 'error')");
            return;
        }

        $this->cart[$index]['quantity'] = $quantity;
        $this->cart[$index]['total'] = ($this->cart[$index]['price'] - $this->cart[$index]['discount']) * $quantity;
    }

    // Increment Quantity
    public function incrementQuantity($index)
    {
        $currentQuantity = $this->cart[$index]['quantity'];
        $productStock = $this->cart[$index]['stock'];

        if (($currentQuantity + 1) > $productStock) {
            $this->js("Swal.fire('error', 'Not enough stock available! Maximum: ' . $productStock, 'error')");
            return;
        }

        $this->cart[$index]['quantity'] += 1;
        $this->cart[$index]['total'] = ($this->cart[$index]['price'] - $this->cart[$index]['discount']) * $this->cart[$index]['quantity'];
    }

    // Decrement Quantity
    public function decrementQuantity($index)
    {
        if ($this->cart[$index]['quantity'] > 1) {
            $this->cart[$index]['quantity'] -= 1;
            $this->cart[$index]['total'] = ($this->cart[$index]['price'] - $this->cart[$index]['discount']) * $this->cart[$index]['quantity'];
        }
    }

    // Update Price
    public function updatePrice($index, $price)
    {
        if ($price < 0) $price = 0;

        $this->cart[$index]['price'] = $price;
        $this->cart[$index]['total'] = ($price - $this->cart[$index]['discount']) * $this->cart[$index]['quantity'];
    }

    // Update Discount
    public function updateDiscount($index, $discount)
    {
        if ($discount < 0) $discount = 0;
        if ($discount > $this->cart[$index]['price']) {
            $discount = $this->cart[$index]['price'];
        }

        $this->cart[$index]['discount'] = $discount;
        $this->cart[$index]['total'] = ($this->cart[$index]['price'] - $discount) * $this->cart[$index]['quantity'];
    }

    // Remove from Cart
    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->js("Swal.fire('success', 'Product removed from sale!', 'success')");
    }

    // Clear Cart
    public function clearCart()
    {
        $this->cart = [];
        $this->additionalDiscount = 0;
        $this->additionalDiscountType = 'fixed';
        $this->js("Swal.fire('success', 'Cart cleared!', 'success')");
    }

    // Update additional discount
    public function updatedAdditionalDiscount($value)
    {
        if ($value === '') {
            $this->additionalDiscount = 0;
            return;
        }

        if ($value < 0) {
            $this->additionalDiscount = 0;
            return;
        }

        if ($this->additionalDiscountType === 'percentage' && $value > 100) {
            $this->additionalDiscount = 100;
            return;
        }

        if ($this->additionalDiscountType === 'fixed' && $value > $this->subtotalAfterItemDiscounts) {
            $this->additionalDiscount = $this->subtotalAfterItemDiscounts;
            return;
        }
    }

    public function toggleDiscountType()
    {
        $this->additionalDiscountType = $this->additionalDiscountType === 'percentage' ? 'fixed' : 'percentage';
        $this->additionalDiscount = 0;
    }

    public function removeAdditionalDiscount()
    {
        $this->additionalDiscount = 0;
        $this->js("Swal.fire('success', 'Additional discount removed!', 'success')");
    }

    // Create Sale
    public function createSale()
    {
        if (empty($this->cart)) {
            $this->js("Swal.fire('error', 'Please add at least one product to the sale.', 'error')");
            return;
        }

        // If no customer selected, use walking customer
        if (!$this->selectedCustomer && !$this->customerId) {

            $this->js("Swal.fire('error', 'Please select a customer.', 'error')");
            return;
            $this->setDefaultCustomer();
        }

        // If editing, delegate to updateSale (which manages its own DB transaction)
        if ($this->isEditing) {
            $this->updateSale();
            return;
        }

        try {
            DB::beginTransaction();

            // Get customer data - now guaranteed to have a customer
            if ($this->selectedCustomer) {
                $customer = $this->selectedCustomer;
            } else {
                $customer = Customer::find($this->customerId);
            }

            if (!$customer) {
                $this->js("Swal.fire('error', 'Customer not found.', 'error')");
                return;
            }

            // Create sale
            $sale = Sale::create([
                'sale_id' => Sale::generateSaleId(),
                'invoice_number' => Sale::generateInvoiceNumber(),
                'customer_id' => $customer->id,
                'customer_type' => $customer->type,
                'subtotal' => $this->subtotal,
                'discount_amount' => $this->additionalDiscountAmount,
                'total_amount' => $this->grandTotal,
                'payment_type' => 'full',
                'payment_method' => $this->paymentMethod,
                'payment_status' => 'pending',
                'due_amount' => $this->grandTotal,
                'notes' => $this->notes,
                'user_id' => Auth::id(),
                'status' => 'confirm',
                'sale_type' => $this->getSaleType(),
                'sale_price_type' => $this->salePriceType,
            ]);

            // Create sale items and update stock
            foreach ($this->cart as $item) {
                try {
                    if ($this->isStaff()) {
                        // Staff: deduct from their allocated StaffProduct instead of main inventory
                        $staffProduct = \App\Models\StaffProduct::where('staff_id', auth()->id())
                            ->where('product_id', $item['id'])
                            ->first();

                        if (!$staffProduct) {
                            throw new \Exception("Product not allocated to staff.");
                        }

                        $availableQty = $staffProduct->quantity - $staffProduct->sold_quantity;
                        if ($availableQty < $item['quantity']) {
                            throw new \Exception("Insufficient allocated stock. Required: {$item['quantity']}, Available: {$availableQty}");
                        }

                        // Update staff product sold quantity
                        $staffProduct->sold_quantity += $item['quantity'];
                        $staffProduct->sold_value += ($item['price'] - $item['discount']) * $item['quantity'];
                        $staffProduct->save();

                        // Create single sale item for staff (no FIFO batching needed)
                        SaleItem::create([
                            'sale_id'          => $sale->id,
                            'product_id'       => $item['id'],
                            'product_code'     => $item['code'],
                            'product_name'     => $item['name'],
                            'product_model'    => $item['model'],
                            'quantity'         => $item['quantity'],
                            'unit_price'       => $item['price'],
                            'discount_per_unit'=> $item['discount'],
                            'total_discount'   => $item['discount'] * $item['quantity'],
                            'total'            => ($item['price'] - $item['discount']) * $item['quantity']
                        ]);

                        Log::info("Staff Sale Stock Deduction for Product {$item['id']}", [
                            'staff_id' => auth()->id(),
                            'quantity' => $item['quantity'],
                            'remaining_allocated' => $staffProduct->quantity - $staffProduct->sold_quantity
                        ]);
                    } else {
                        // Admin: use FIFO method to deduct from main inventory
                        $fifoResult = FIFOStockService::deductStock($item['id'], $item['quantity']);

                        // Create sale items using the actual cart price (what was charged to the customer)
                        // $item['price'] = the price shown/entered in the sales UI
                        // $deduction['selling_price'] = the batch reference price (used for FIFO cost tracking only)
                        foreach ($fifoResult['deductions'] as $deduction) {
                            SaleItem::create([
                                'sale_id'          => $sale->id,
                                'product_id'       => $item['id'],
                                'product_code'     => $item['code'],
                                'product_name'     => $item['name'],
                                'product_model'    => $item['model'],
                                'quantity'         => $deduction['quantity'],
                                'unit_price'       => $item['price'], // Actual sale price from the cart UI
                                'discount_per_unit'=> $item['discount'],
                                'total_discount'   => $item['discount'] * $deduction['quantity'],
                                'total'            => ($item['price'] - $item['discount']) * $deduction['quantity']
                            ]);
                        }

                        // Log FIFO deduction details
                        Log::info("FIFO Stock Deduction for Product {$item['id']}", [
                            'quantity' => $item['quantity'],
                            'batches_used' => count($fifoResult['deductions']),
                            'average_cost' => $fifoResult['average_cost'],
                            'deductions' => $fifoResult['deductions']
                        ]);
                    }
                } catch (\Exception $e) {
                    // If stock deduction fails, throw exception to rollback transaction
                    throw new \Exception("Failed to deduct stock for {$item['name']}: " . $e->getMessage());
                }
            }

            // Calculate and record staff bonuses
            StaffBonusService::calculateBonusesForSale($sale);

            DB::commit();

            // Ensure there is an open POS session for this user and update its totals
            try {
                $this->currentSession = POSSession::getTodaySession(Auth::id());
                if (! $this->currentSession) {
                    // Create a session with zero opening cash so admin sales are still tracked
                    $this->currentSession = POSSession::openSession(Auth::id(), 0);
                }

                // Update session totals from today's sales/payments
                $this->currentSession->updateFromSales();
                // Recalculate expected cash (cash_difference remains until close)
                $this->currentSession->calculateDifference();
            } catch (\Exception $e) {
                Log::error('Failed to update POS session after admin sale: ' . $e->getMessage());
            }

            $this->lastSaleId = $sale->id;
            $this->createdSale = Sale::with(['customer', 'items'])->find($sale->id);
            $this->showSaleModal = true;

            $this->js("Swal.fire('success', 'Sale created successfully! Payment status: Pending', 'success')");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create sale: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'cart' => $this->cart,
                'exception' => $e,
            ]);
        }
    }

    public function updateSale()
    {
        try {
            DB::beginTransaction();

            $sale = Sale::find($this->editSaleId);
            if (!$sale) {
                throw new \Exception("Sale not found.");
            }

            // 1. Revert stock
            foreach ($sale->items as $item) {
                if ($this->isStaff()) {
                    // Staff: revert to StaffProduct allocation
                    $staffProduct = \App\Models\StaffProduct::where('staff_id', auth()->id())
                        ->where('product_id', $item->product_id)
                        ->first();
                    if ($staffProduct) {
                        $staffProduct->sold_quantity -= $item->quantity;
                        $staffProduct->sold_value -= $item->total;
                        $staffProduct->save();
                    }
                } else {
                    // Admin: revert to main inventory
                    $productStock = \App\Models\ProductStock::where('product_id', $item->product_id)->first();
                    if ($productStock) {
                        $productStock->available_stock += $item->quantity;
                        if ($productStock->sold_count >= $item->quantity) {
                            $productStock->sold_count -= $item->quantity;
                        }
                        $productStock->save();
                    }
                }
            }

            // 2. Delete old items and bonuses
            $sale->items()->delete();
            \App\Models\StaffBonus::where('sale_id', $sale->id)->delete();

            // 3. Update sale header
            $customer = $this->selectedCustomer ?: Customer::find($this->customerId);

            // Calculate new totals and due amount based on existing payments
            $totalPayments = $sale->payments()->where('status', '!=', 'rejected')->sum('amount');
            $newDueAmount = max(0, $this->grandTotal - $totalPayments);

            $sale->update([
                'customer_id' => $customer->id,
                'customer_type' => $customer->type,
                'subtotal' => $this->subtotal,
                'discount_amount' => $this->additionalDiscountAmount,
                'total_amount' => $this->grandTotal,
                'due_amount' => $newDueAmount,
                'payment_status' => $newDueAmount <= 0 ? 'paid' : ($totalPayments > 0 ? 'partial' : 'pending'),
                'notes' => $this->notes,
                'sale_price_type' => $this->salePriceType,
            ]);

            // 4. Create new items and deduct stock
            foreach ($this->cart as $item) {
                if ($this->isStaff()) {
                    // Staff: deduct from their allocated StaffProduct
                    $staffProduct = \App\Models\StaffProduct::where('staff_id', auth()->id())
                        ->where('product_id', $item['id'])
                        ->first();

                    if (!$staffProduct) {
                        throw new \Exception("Product not allocated to staff.");
                    }

                    $availableQty = $staffProduct->quantity - $staffProduct->sold_quantity;
                    if ($availableQty < $item['quantity']) {
                        throw new \Exception("Insufficient allocated stock. Required: {$item['quantity']}, Available: {$availableQty}");
                    }

                    $staffProduct->sold_quantity += $item['quantity'];
                    $staffProduct->sold_value += ($item['price'] - $item['discount']) * $item['quantity'];
                    $staffProduct->save();

                    SaleItem::create([
                        'sale_id'          => $sale->id,
                        'product_id'       => $item['id'],
                        'product_code'     => $item['code'],
                        'product_name'     => $item['name'],
                        'product_model'    => $item['model'],
                        'quantity'         => $item['quantity'],
                        'unit_price'       => $item['price'],
                        'discount_per_unit'=> $item['discount'],
                        'total_discount'   => $item['discount'] * $item['quantity'],
                        'total'            => ($item['price'] - $item['discount']) * $item['quantity']
                    ]);
                } else {
                    // Admin: use FIFO method
                    $fifoResult = FIFOStockService::deductStock($item['id'], $item['quantity']);
                    foreach ($fifoResult['deductions'] as $deduction) {
                        SaleItem::create([
                            'sale_id'          => $sale->id,
                            'product_id'       => $item['id'],
                            'product_code'     => $item['code'],
                            'product_name'     => $item['name'],
                            'product_model'    => $item['model'],
                            'quantity'         => $deduction['quantity'],
                            'unit_price'       => $item['price'],
                            'discount_per_unit'=> $item['discount'],
                            'total_discount'   => $item['discount'] * $deduction['quantity'],
                            'total'            => ($item['price'] - $item['discount']) * $deduction['quantity']
                        ]);
                    }
                }
            }

            // 5. Recalculate bonuses
            StaffBonusService::calculateBonusesForSale($sale);

            DB::commit();

            // Update POS session if needed
            $this->currentSession = POSSession::getTodaySession(Auth::id());
            if ($this->currentSession) {
                $this->currentSession->updateFromSales();
            }

            $this->lastSaleId = $sale->id;
            $this->createdSale = Sale::with(['customer', 'items'])->find($sale->id);
            $this->showSaleModal = true;

            $this->js("Swal.fire('success', 'Sale updated successfully!', 'success')");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update sale: ' . $e->getMessage());
            $this->js("Swal.fire('error', 'Failed to update sale: " . addslashes($e->getMessage()) . "', 'error')");
        }
    }

    // Download Invoice
    public function downloadInvoice()
    {
        if (!$this->lastSaleId) {
            $this->js("Swal.fire('error', 'No sale found to download.', 'error')");
            return;
        }

        $sale = Sale::with(['customer', 'items', 'user', 'payments', 'returns' => function ($q) {
            $q->with('product');
        }, 'staffReturns' => function ($q) {
            $q->where('status', 'approved')->with('product');
        }])->find($this->lastSaleId);

        if (!$sale) {
            $this->js("Swal.fire('error', 'Sale not found.', 'error')");
            return;
        }

        $pdf = PDF::loadView('admin.sales.invoice', compact('sale'));
        $pdf->setPaper('a5', 'portrait');
        $pdf->setOption('dpi', 150);
        $pdf->setOption('defaultFont', 'sans-serif');

        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf->output();
            },
            'invoice-' . $sale->invoice_number . '.pdf'
        );
    }

    // Close Modal
    public function closeModal()
    {
        $this->showSaleModal = false;
        $this->lastSaleId = null;
        $this->dispatch('refreshPage');
    }

    // Continue creating new sale
    public function createNewSale()
    {
        $this->resetExcept(['customers']);
        $this->loadCustomers();
        $this->setDefaultCustomer(); // Set walking customer again for new sale
        $this->showSaleModal = false;
    }

    // Print Invoice
    public function printInvoice($saleId)
    {
        $sale = Sale::with(['customer', 'items', 'payments', 'returns' => function ($q) {
            $q->with('product');
        }, 'staffReturns' => function ($q) {
            $q->where('status', 'approved')->with('product');
        }])->find($saleId);

        if (!$sale) {
            $this->js("Swal.fire('error', 'Sale not found.', 'error')");
            return;
        }

        // Use role-appropriate print route so staff users don't get redirected
        $routeName = $this->isAdmin() ? 'admin.print.sale' : 'staff.print.sale';
        $printUrl = route($routeName, $sale->id);
        $this->js("window.open('$printUrl', '_blank', 'width=800,height=600');");
    }

    public function sendWhatsApp($saleId)
    {
        $sale = Sale::with('customer')->findOrFail($saleId);
        
        if (!$sale->customer || !$sale->customer->phone) {
            $this->js("Swal.fire('error', 'Customer phone number not found.', 'error')");
            return;
        }

        $phone = $sale->customer->phone;
        // Basic cleanup of phone number if needed
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Ensure international format (assuming Sri Lanka +94 if it starts with 0)
        if (strlen($phone) === 10 && $phone[0] === '0') {
            $phone = '94' . substr($phone, 1);
        } elseif (strlen($phone) === 9 && $phone[0] !== '0') {
            $phone = '94' . $phone;
        }

        // Construct the message
        $invoiceLink = route('invoice.external_view', $sale->id);
        
        // Calculate customer total due accurately
        $salesDue = Sale::where('customer_id', $sale->customer_id)->sum('due_amount');
        $totalDue = $salesDue + ($sale->customer->opening_balance ?? 0) - ($sale->customer->overpaid_amount ?? 0);
        
        $message = "Hello " . ($sale->customer->name ?? 'Customer') . ",\n\n";
        $message .= "Your invoice (" . $sale->invoice_number . ") has been created.\n";
        $message .= "Total Amount: Rs." . number_format($sale->total_amount, 2) . "\n";
        $message .= "Paid Amount: Rs." . number_format($sale->total_amount - $sale->due_amount, 2) . "\n";
        $message .= "Current Sale Due: Rs." . number_format($sale->due_amount, 2) . "\n";
        $message .= "Total Outstanding Balance: Rs." . number_format($totalDue, 2) . "\n\n";
        $message .= "You can view and download your invoice online here: " . $invoiceLink . "\n\n";
        $message .= "Thank you for your business!";

        $whatsappUrl = "https://wa.me/" . $phone . "?text=" . urlencode($message);
        
        $this->js("window.open('$whatsappUrl', '_blank');");
    }

    public function render()
    {
        $layoutPath = $this->layout;

        return view('livewire.admin.sales-system', [
            'subtotal' => $this->subtotal,
            'totalDiscount' => $this->totalDiscount,
            'subtotalAfterItemDiscounts' => $this->subtotalAfterItemDiscounts,
            'additionalDiscountAmount' => $this->additionalDiscountAmount,
            'grandTotal' => $this->grandTotal
        ])->layout($layoutPath);
    }
}
