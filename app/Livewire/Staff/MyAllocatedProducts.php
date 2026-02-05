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
    public $canReturnProducts = false;

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
        } else {
            $this->selectedProducts[] = $productId;
        }
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

        try {
            DB::beginTransaction();

            $staffId = Auth::id();

            foreach ($this->selectedProducts as $staffProductId) {
                $staffProduct = StaffProduct::where('id', $staffProductId)
                    ->where('staff_id', $staffId)
                    ->first();
                
                if (!$staffProduct) continue;

                // Calculate available quantity
                $availableQty = $staffProduct->quantity - $staffProduct->sold_quantity;

                if ($availableQty > 0) {
                    // Create a pending return request
                    \App\Models\StaffProductReturn::create([
                        'staff_id' => $staffId,
                        'product_id' => $staffProduct->product_id,
                        'return_quantity' => $availableQty,
                        'status' => 'pending',
                        'notes' => 'Returned by staff from allocated products'
                    ]);

                    // Deduct from staff's allocated quantity
                    // Note: We used to deduct here, but maybe it's better to wait for admin approval?
                    // Actually, the previous implementation deducted immediately to prevent double selling.
                    $staffProduct->quantity -= $availableQty;
                    $staffProduct->save();

                    Log::info("Product return requested by staff: Staff {$staffId}, Product {$staffProduct->product_id}, Qty: {$availableQty}");
                }
            }

            DB::commit();

            $this->selectedProducts = [];
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
