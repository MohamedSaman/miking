<?php

namespace App\Livewire\Staff;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StaffProduct;
use App\Models\ProductStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Livewire\Concerns\WithDynamicLayout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.staff')]
#[Title('Allocated Products')]
class MyAllocatedProducts extends Component
{
    use WithPagination, WithDynamicLayout;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statusFilter = 'all';
    public $perPage = 10;
    public $selectedProducts = [];
    public $returnQtys = []; // To store customized return quantities
    public $canReturnProducts = false;
    public $showReviewModal = false;
    public $returnReviewItems = [];

    public function mount()
    {
        $user = Auth::user();

        if (!$user->hasPermission('staff_my_allocated_products')) {
            $this->redirect(route('staff.dashboard'), navigate: true);
            return;
        }

        // Check if staff has permission to return products
        $this->canReturnProducts = $user->hasPermission('staff_product_return') ?? true;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function toggleProduct($productId)
    {
        if (!$this->canReturnProducts) {
            $this->js("Swal.fire('Permission Denied', 'You do not have permission to return products!', 'error');");
            return;
        }

        if (in_array($productId, $this->selectedProducts)) {
            $this->selectedProducts = array_diff($this->selectedProducts, [$productId]);
            unset($this->returnQtys[$productId]);
        } else {
            $this->selectedProducts[] = $productId;
            
            // Default return quantity to full available quantity
            $staffProduct = StaffProduct::find($productId);
            if ($staffProduct) {
                $this->returnQtys[$productId] = $staffProduct->quantity - $staffProduct->sold_quantity;
            }
        }
    }

    public function updatedReturnQtys($value, $key)
    {
        $staffProductId = $key;
        $staffProduct = StaffProduct::find($staffProductId);
        
        if ($staffProduct) {
            $availableQty = $staffProduct->quantity - $staffProduct->sold_quantity;
            
            if ($value > $availableQty) {
                $this->js("Swal.fire({
                    icon: 'error',
                    title: 'Invalid Quantity',
                    text: 'Return quantity cannot exceed available stock (' + $availableQty + ')!',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });");
            }
        }
    }

    public function openReviewModal()
    {
        if (!$this->canReturnProducts) {
            $this->js("Swal.fire('Permission Denied', 'You do not have permission to return products!', 'error');");
            return;
        }

        if (empty($this->selectedProducts)) {
            $this->js("Swal.fire('Error', 'Please select at least one product to return!', 'error');");
            return;
        }

        $this->returnReviewItems = [];
        foreach ($this->selectedProducts as $id) {
            $staffProd = StaffProduct::with('product')->find($id);
            if ($staffProd) {
                $qty = (int)($this->returnQtys[$id] ?? 0);
                $available = (int)($staffProd->quantity - $staffProd->sold_quantity);

                if ($qty > $available) {
                    $this->js("Swal.fire('Validation Error', 'Quantity for {$staffProd->product->name} exceeds available stock.', 'error');");
                    return;
                }
                if ($qty < 1) {
                    $this->js("Swal.fire('Validation Error', 'Quantity for {$staffProd->product->name} must be at least 1.', 'error');");
                    return;
                }

                $this->returnReviewItems[] = [
                    'id' => $id,
                    'name' => $staffProd->product->name,
                    'code' => $staffProd->product->code,
                    'requested_qty' => $qty,
                    'available_qty' => $available
                ];
            }
        }

        $this->showReviewModal = true;
    }

    public function closeReviewModal()
    {
        $this->showReviewModal = false;
    }

    public function returnProducts()
    {
        if (!$this->canReturnProducts) {
            $this->js("Swal.fire('Permission Denied', 'You do not have permission to return products!', 'error');");
            return;
        }

        if (empty($this->selectedProducts)) {
            $this->js("Swal.fire('Error', 'Please select at least one product to return!', 'error');");
            return;
        }

        // Validate all selected quantities before processing
        foreach ($this->selectedProducts as $id) {
            $staffProd = StaffProduct::find($id);
            $qty = (int)($this->returnQtys[$id] ?? 0);
            $available = $staffProd->quantity - $staffProd->sold_quantity;

            if ($qty > $available) {
                $this->js("Swal.fire('Validation Error', 'One or more items exceed available stock. Please correct highlighted quantities.', 'error');");
                return;
            }
            if ($qty < 1) {
                $this->js("Swal.fire('Validation Error', 'Return quantity must be at least 1.', 'error');");
                return;
            }
        }

        try {
            DB::beginTransaction();

            $staffId = Auth::id();

            foreach ($this->selectedProducts as $staffProductId) {
                $staffProduct = StaffProduct::where('id', $staffProductId)
                    ->where('staff_id', $staffId)
                    ->first();
                
                if (!$staffProduct) continue;

                // Get custom return quantity and cast to int
                $requestedQty = isset($this->returnQtys[$staffProductId]) ? (int)$this->returnQtys[$staffProductId] : 0;
                
                // Ensure requested quantity doesn't exceed available
                $availableQty = $staffProduct->quantity - $staffProduct->sold_quantity;
                if ($requestedQty > $availableQty) {
                    $requestedQty = $availableQty;
                }

                if ($requestedQty > 0) {
                    // Create a pending return request
                    \App\Models\StaffProductReturn::create([
                        'staff_id' => $staffId,
                        'product_id' => $staffProduct->product_id,
                        'return_quantity' => $requestedQty,
                        'status' => 'pending',
                        'notes' => 'Returned by staff from allocated products (Custom Qty)'
                    ]);

                    // Deduct from staff's allocated quantity
                    $staffProduct->quantity -= $requestedQty;
                    $staffProduct->save();

                    Log::info("Product return requested by staff: Staff {$staffId}, Product {$staffProduct->product_id}, Qty: {$requestedQty}");
                }
            }

            DB::commit();

            $this->selectedProducts = [];
            $this->returnQtys = [];
            $this->showReviewModal = false;
            $this->returnReviewItems = [];
            $this->js("Swal.fire({
                icon: 'success',
                title: 'Return Requested',
                text: 'Selected products have been sent to admin for re-entry!',
                timer: 2000,
                showConfirmButton: false
            });");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error returning products: " . $e->getMessage());
            $this->js("Swal.fire('Error', 'Failed to request return: " . addslashes($e->getMessage()) . "', 'error');");
        }
    }

    public function render()
    {
        $staffId = Auth::id();

        $query = StaffProduct::where('staff_id', $staffId)
            ->with(['product'])
            // Only show products with available quantity > 0
            ->whereRaw('(quantity - sold_quantity) > 0');

        // Search filter
        if ($this->search) {
            $query->whereHas('product', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        // Status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $allocatedProducts = $query->paginate($this->perPage);

        return view('livewire.staff.my-allocated-products', [
            'allocatedProducts' => $allocatedProducts
        ]);
    }
}
