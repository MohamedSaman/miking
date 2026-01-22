<?php

namespace App\Livewire\Staff;

use Livewire\Component;
use App\Models\Customer;
use App\Models\ProductDetail;
use App\Models\Sale;
use App\Models\ProductStock;
use App\Models\StaffProduct;
use App\Models\StaffReturn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("Product Return")]
class StaffReturnManagement extends Component
{
    use WithDynamicLayout;

    public $searchCustomer = '';
    public $customers = [];
    public $selectedCustomer = null;

    public $customerInvoices = [];
    public $selectedInvoice = null;
    public $selectedInvoices = [];

    public $invoiceProducts = [];
    public $returnItems = [];
    public $totalReturnValue = 0;
    public $overallDiscountPerItem = 0;

    public $showInvoiceModal = false;
    public $invoiceModalData = null;

    public $showReturnSection = false;
    public $searchReturnProduct = '';
    public $availableProducts = [];
    public $invoiceProductsForSearch = [];
    public $selectedProducts = [];

    public $previousReturns = [];

    public function mount()
    {
        // Staff can only access their own customers and sales
    }

    /** 🔍 Search Customer or Invoice - filtered by staff's user_id */
    public function updatedSearchCustomer()
    {
        if (strlen($this->searchCustomer) > 2) {
            // Search customers created by this staff
            $this->customers = Customer::query()
                ->where('user_id', Auth::id())
                ->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->searchCustomer . '%')
                        ->orWhere('phone', 'like', '%' . $this->searchCustomer . '%')
                        ->orWhere('email', 'like', '%' . $this->searchCustomer . '%');
                })
                ->limit(10)
                ->get();

            // Search invoices created by this staff
            $this->customerInvoices = Sale::where('user_id', Auth::id())
                ->where('invoice_number', 'like', '%' . $this->searchCustomer . '%')
                ->latest()
                ->limit(5)
                ->get();
        } else {
            $this->customers = [];
            $this->customerInvoices = [];
        }
    }

    /** 👤 Select Customer */
    public function selectCustomer($customerId)
    {
        // Verify customer belongs to this staff
        $customer = Customer::where('id', $customerId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$customer) {
            $this->dispatch('show-error', 'Customer not found or access denied.');
            return;
        }

        $this->selectedCustomer = $customer;
        $this->searchCustomer = '';
        $this->customers = [];

        $this->resetReturnData();
        $this->loadCustomerInvoices();
    }

    /** 🧾 Load Selected Customer's Invoices - filtered by staff */
    public function loadCustomerInvoices()
    {
        if (!$this->selectedCustomer) {
            $this->customerInvoices = [];
            return;
        }

        $this->customerInvoices = Sale::where('customer_id', $this->selectedCustomer->id)
            ->where('user_id', Auth::id())
            ->latest()
            ->limit(5)
            ->get();
    }

    /** 🎯 Simple Invoice Selection for Return */
    public function selectInvoiceForReturn($invoiceId)
    {
        $this->resetReturnData();

        // Verify invoice belongs to this staff
        $invoice = Sale::with(['items.product', 'customer'])
            ->where('id', $invoiceId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$invoice) {
            $this->dispatch('show-error', 'Invoice not found or access denied.');
            return;
        }

        $this->selectedInvoice = $invoice;
        $this->selectedInvoices = [$invoiceId];
        $this->showReturnSection = true;

        if ($this->selectedInvoice && $this->selectedInvoice->customer) {
            $this->selectedCustomer = $this->selectedInvoice->customer;
        }

        if ($this->selectedInvoice) {
            // Calculate overall discount per item
            $this->calculateOverallDiscountPerItem();

            // Load previous returns for this invoice
            $this->loadPreviousReturns();

            // Build return items with remaining quantities
            foreach ($this->selectedInvoice->items as $item) {
                $alreadyReturned = $this->getAlreadyReturnedQuantity($item->product->id);
                $remainingQty = $item->quantity - $alreadyReturned;

                if ($remainingQty > 0) {
                    $unitDiscount = $item->discount_per_unit ?? 0;
                    $proportionalOverallDiscount = $this->overallDiscountPerItem;
                    $totalDiscountPerUnit = $unitDiscount + $proportionalOverallDiscount;
                    $netUnitPrice = $item->unit_price - $totalDiscountPerUnit;

                    $this->returnItems[] = [
                        'product_id' => $item->product->id,
                        'name' => $item->product->name,
                        'code' => $item->product->code,
                        'unit_price' => $item->unit_price,
                        'discount_per_unit' => $unitDiscount,
                        'overall_discount_per_unit' => $proportionalOverallDiscount,
                        'total_discount_per_unit' => $totalDiscountPerUnit,
                        'net_unit_price' => $netUnitPrice,
                        'original_qty' => $item->quantity,
                        'already_returned' => $alreadyReturned,
                        'max_qty' => $remainingQty,
                        'return_qty' => 0,
                        'is_damaged' => false,
                        'reason' => '',
                    ];
                }
            }
        }

        $this->loadInvoiceProductsForSearch();
        $this->searchCustomer = '';
    }

    /** 📊 Calculate Overall Discount Per Item */
    private function calculateOverallDiscountPerItem()
    {
        if (!$this->selectedInvoice) {
            $this->overallDiscountPerItem = 0;
            return;
        }

        $totalQuantity = $this->selectedInvoice->items->sum('quantity');
        $totalDiscountAmount = $this->selectedInvoice->discount_amount ?? 0;

        $totalUnitDiscounts = $this->selectedInvoice->items->sum(function ($item) {
            return ($item->discount_per_unit ?? 0) * $item->quantity;
        });

        $remainingOverallDiscount = $totalDiscountAmount - $totalUnitDiscounts;
        $this->overallDiscountPerItem = $totalQuantity > 0 ? ($remainingOverallDiscount / $totalQuantity) : 0;
    }

    /** 📜 Load Previous Returns */
    private function loadPreviousReturns()
    {
        if (!$this->selectedInvoice) {
            $this->previousReturns = [];
            return;
        }

        $this->previousReturns = StaffReturn::where('sale_id', $this->selectedInvoice->id)
            ->where('staff_id', Auth::id())
            ->with('product')
            ->get()
            ->groupBy('product_id')
            ->map(function ($returns) {
                return [
                    'product_name' => $returns->first()->product->name ?? 'Unknown',
                    'total_returned' => $returns->sum('quantity'),
                    'total_amount' => $returns->sum('total_amount'),
                    'returns' => $returns->map(function ($return) {
                        return [
                            'quantity' => $return->quantity,
                            'amount' => $return->total_amount,
                            'date' => $return->created_at->format('Y-m-d H:i'),
                            'status' => $return->status,
                        ];
                    })->toArray()
                ];
            })
            ->toArray();
    }

    /** 🔢 Get Already Returned Quantity */
    private function getAlreadyReturnedQuantity($productId)
    {
        if (!$this->selectedInvoice) return 0;

        // For staff returns, only count approved returns
        return StaffReturn::where('sale_id', $this->selectedInvoice->id)
            ->where('product_id', $productId)
            ->where('status', 'approved')
            ->sum('quantity');
    }

    /** 👁️ View Invoice Details in Modal */
    public function viewInvoice($invoiceId)
    {
        $invoice = Sale::with(['items.product', 'customer'])
            ->where('id', $invoiceId)
            ->where('user_id', Auth::id())
            ->first();

        if ($invoice) {
            $totalDiscountAmount = $invoice->discount_amount ?? 0;
            $totalQty = $invoice->items->sum('quantity');

            $totalUnitDiscounts = $invoice->items->sum(function ($item) {
                return ($item->discount_per_unit ?? 0) * $item->quantity;
            });

            $remainingOverallDiscount = $totalDiscountAmount - $totalUnitDiscounts;
            $overallDiscountPerItem = $totalQty > 0 ? ($remainingOverallDiscount / $totalQty) : 0;

            $this->invoiceModalData = [
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->customer->name,
                'date' => $invoice->created_at->format('Y-m-d H:i:s'),
                'total_amount' => $invoice->total_amount,
                'overall_discount' => $totalDiscountAmount,
                'items' => $invoice->items->map(function ($item) use ($overallDiscountPerItem) {
                    $itemDiscount = $item->discount_per_unit ?? 0;
                    $totalDiscountPerUnit = $itemDiscount + $overallDiscountPerItem;
                    $netPrice = $item->unit_price - $totalDiscountPerUnit;

                    return [
                        'product_name' => $item->product->name,
                        'product_code' => $item->product->code,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'item_discount' => $itemDiscount,
                        'overall_discount' => $overallDiscountPerItem,
                        'net_price' => $netPrice,
                        'total' => $item->quantity * $netPrice,
                    ];
                })->toArray()
            ];
            $this->showInvoiceModal = true;
            $this->dispatch('show-invoice-modal');
        }
    }

    /** ❌ Close Invoice Modal */
    public function closeInvoiceModal()
    {
        $this->showInvoiceModal = false;
        $this->invoiceModalData = null;
    }

    /** 📦 Load Products from Selected Invoice for Search */
    private function loadInvoiceProductsForSearch()
    {
        if (empty($this->selectedInvoices)) {
            $this->invoiceProductsForSearch = [];
            return;
        }

        $allProducts = collect();

        foreach ($this->selectedInvoices as $invoiceId) {
            $invoice = Sale::with(['items.product.price'])
                ->where('id', $invoiceId)
                ->where('user_id', Auth::id())
                ->first();

            if ($invoice) {
                $products = $invoice->items->map(function ($item) use ($invoice) {
                    $alreadyReturned = $this->getAlreadyReturnedQuantity($item->product->id);
                    $remainingQty = $item->quantity - $alreadyReturned;

                    return [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'code' => $item->product->code,
                        'image' => $item->product->image,
                        'selling_price' => $item->unit_price,
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'max_qty' => $remainingQty,
                    ];
                });
                $allProducts = $allProducts->merge($products);
            }
        }

        $this->invoiceProductsForSearch = $allProducts->unique('id')->values()->toArray();
    }

    /** ❌ Remove Product from Return Cart */
    public function removeFromReturn($index)
    {
        unset($this->returnItems[$index]);
        $this->returnItems = array_values($this->returnItems);
        $this->calculateTotalReturnValue();
    }

    /** 🧹 Clear Cart */
    public function clearReturnCart()
    {
        $this->returnItems = [];
        $this->totalReturnValue = 0;
    }

    /** ♻️ Auto-update total when quantities change */
    public function updatedReturnItems()
    {
        $this->calculateTotalReturnValue();
    }

    /** 💰 Calculate Total Return Value */
    private function calculateTotalReturnValue()
    {
        $this->totalReturnValue = collect($this->returnItems)->sum(function($item) {
            $returnQty = floatval($item['return_qty'] ?? 0);
            $netUnitPrice = floatval($item['net_unit_price'] ?? 0);
            return $returnQty * $netUnitPrice;
        });
    }

    /** ✅ Validate before showing confirmation */
    public function processReturn()
    {
        $this->calculateTotalReturnValue();

        if (empty($this->returnItems) || !$this->selectedInvoice) {
            $this->js("Swal.fire('Error!', 'Please select items for return.', 'error')");
            return;
        }

        $hasReturnItems = false;
        foreach ($this->returnItems as $item) {
            if ($item['return_qty'] < 0) {
                $this->js("Swal.fire('Error!', 'Return quantity cannot be negative for " . $item['name'] . "', 'error')");
                return;
            }

            if (isset($item['return_qty']) && $item['return_qty'] > 0) {
                if ($item['return_qty'] > $item['max_qty']) {
                    $this->js("Swal.fire('Error!', 'Invalid return quantity for " . $item['name'] . ". Maximum available: " . $item['max_qty'] . "', 'error')");
                    return;
                }
                $hasReturnItems = true;
            }
        }

        if (!$hasReturnItems) {
            $this->dispatch('alert', ['message' => 'Please enter at least one return quantity.']);
            return;
        }

        $this->dispatch('show-return-modal');
    }

    /** 💾 Confirm Return & Save to Database */
    public function confirmReturn()
    {
        $this->calculateTotalReturnValue();

        if (empty($this->returnItems) || !$this->selectedCustomer || !$this->selectedInvoice) return;

        $itemsToReturn = array_filter($this->returnItems, function ($item) {
            return isset($item['return_qty']) && $item['return_qty'] > 0;
        });

        if (empty($itemsToReturn)) {
            $this->dispatch('alert', ['message' => 'No valid return quantities entered.']);
            return;
        }

        DB::transaction(function () use ($itemsToReturn) {
            foreach ($itemsToReturn as $item) {
                // Create staff return request (pending approval)
                StaffReturn::create([
                    'staff_id' => Auth::id(),
                    'sale_id' => $this->selectedInvoice->id,
                    'product_id' => $item['product_id'],
                    'customer_id' => $this->selectedCustomer->id,
                    'quantity' => floatval($item['return_qty']),
                    'unit_price' => floatval($item['net_unit_price']),
                    'total_amount' => floatval($item['return_qty']) * floatval($item['net_unit_price']),
                    'is_damaged' => $item['is_damaged'] ?? false,
                    'reason' => $item['reason'] ?? null,
                    'notes' => 'Return request submitted by staff',
                    'status' => 'pending', // Staff returns need admin approval
                ]);

                // Note: Stock update will happen after admin approval
            }
        });

        $this->clearReturnCart();
        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Return request submitted successfully! Awaiting admin approval.']);
        $this->dispatch('close-return-modal');
        
        // Reset and redirect after a short delay
        $this->js('setTimeout(() => { window.location.reload(); }, 2000);');
    }

    /** 📈 Update Product Stock */
    private function updateProductStock($productId, $quantity)
    {
        // Update main product stock
        $stock = ProductStock::where('product_id', $productId)->first();

        if ($stock) {
            $stock->available_stock += $quantity;
            $stock->total_stock += $quantity;
            $stock->save();
        } else {
            ProductStock::create([
                'product_id' => $productId,
                'available_stock' => $quantity,
                'damage_stock' => 0,
                'total_stock' => $quantity,
                'sold_count' => 0,
                'restocked_quantity' => 0,
            ]);
        }

        // Also update staff product stock if exists
        $staffProduct = StaffProduct::where('staff_id', Auth::id())
            ->where('product_id', $productId)
            ->first();

        if ($staffProduct) {
            // Decrease sold_quantity since product is returned
            $staffProduct->sold_quantity = max(0, $staffProduct->sold_quantity - $quantity);
            $staffProduct->save();
        }
    }

    /** 🔄 Reset Return Data */
    private function resetReturnData()
    {
        $this->selectedInvoice = null;
        $this->selectedInvoices = [];
        $this->invoiceProducts = [];
        $this->returnItems = [];
        $this->selectedProducts = [];
        $this->showReturnSection = false;
        $this->searchReturnProduct = '';
        $this->availableProducts = [];
        $this->invoiceProductsForSearch = [];
        $this->totalReturnValue = 0;
        $this->overallDiscountPerItem = 0;
        $this->previousReturns = [];
    }

    public function render()
    {
        return view('livewire.staff.staff-return-management')->layout($this->layout);
    }
}
