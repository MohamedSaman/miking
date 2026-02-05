<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\StaffProductReturn as ReturnModel;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Livewire\Concerns\WithDynamicLayout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.admin')]
#[Title('Staff Product Re-entry')]

class StaffProductReturn extends Component
{
    use WithPagination, WithDynamicLayout;

    protected $paginationTheme = 'bootstrap';

    public $staffId;
    public $staffName;
    public $search = '';
    public $perPage = 12;

    // Sidebar/Modal properties
    public $showSidePanel = false;
    public $selectedReturnId = null;
    public $selectedReturn = null;
    public $restockQty = 0;
    public $damagedQty = 0;

    public function mount($staffId)
    {
        $this->staffId = $staffId;
        $staff = User::find($staffId);
        $this->staffName = $staff ? $staff->name : 'Unknown Staff';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openReentry($returnId)
    {
        $this->selectedReturnId = $returnId;
        $this->selectedReturn = ReturnModel::with('product')->find($returnId);
        $this->restockQty = 0;
        $this->damagedQty = 0;
        $this->showSidePanel = true;
    }

    public function closeSidePanel()
    {
        $this->showSidePanel = false;
        $this->selectedReturnId = null;
        $this->selectedReturn = null;
    }

    public function updatedDamagedQty($value)
    {
        $this->validateQuantities();
    }

    public function updatedRestockQty($value)
    {
        $this->validateQuantities();
    }

    protected function validateQuantities()
    {
        if (!$this->selectedReturn) return;

        $total = (int)$this->restockQty + (int)$this->damagedQty;
        if ($total > $this->selectedReturn->return_quantity) {
            // Adjust to fit
            if ($this->restockQty > 0 && $this->damagedQty > 0) {
                 // Hard to know which to reduce, let's just warn or cap
            }
        }
    }

    public function submitReentry()
    {
        if (!$this->selectedReturn) return;

        $total = (int)$this->restockQty + (int)$this->damagedQty;
        
        if ($total == 0) {
            $this->js("Swal.fire('Error', 'Please enter restock or damaged quantity', 'error')");
            return;
        }

        if ($total > $this->selectedReturn->return_quantity) {
            $this->js("Swal.fire('Error', 'Total restock and damaged quantity cannot exceed available quantity (".$this->selectedReturn->return_quantity.")', 'error')");
            return;
        }

        try {
            DB::beginTransaction();

            $productStock = ProductStock::where('product_id', $this->selectedReturn->product_id)->first();
            
            if (!$productStock) {
                // Should not happen, but create if missing
                $productStock = ProductStock::create([
                    'product_id' => $this->selectedReturn->product_id,
                    'available_stock' => 0,
                    'damage_stock' => 0,
                    'total_stock' => 0
                ]);
            }

            if ($this->restockQty > 0) {
                $productStock->available_stock += (int)$this->restockQty;
            }

            if ($this->damagedQty > 0) {
                $productStock->damage_stock += (int)$this->damagedQty;
            }

            // Sync total stock: available + damage
            $productStock->total_stock = $productStock->available_stock + $productStock->damage_stock;
            
            $productStock->save();

            // Reduce staff's allocated quantity
            $staffProduct = \App\Models\StaffProduct::where('staff_id', $this->selectedReturn->staff_id)
                ->where('product_id', $this->selectedReturn->product_id)
                ->first();

            if ($staffProduct) {
                // Reduce the allocated quantity by the total returned
                $returnedQty = (int)$this->restockQty + (int)$this->damagedQty;
                $staffProduct->quantity = max(0, $staffProduct->quantity - $returnedQty);
                $staffProduct->save();
            }

            // Update return record
            $this->selectedReturn->update([
                'restock_quantity' => $this->restockQty,
                'damaged_quantity' => $this->damagedQty,
                'status' => 'processed',
                'processed_by' => auth()->id(),
                'processed_at' => now()
            ]);

            DB::commit();

            $this->closeSidePanel();
            $this->js("Swal.fire('Success', 'Stock re-entered successfully', 'success')");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing re-entry: " . $e->getMessage());
            $this->js("Swal.fire('Error', 'Failed to process re-entry', 'error')");
        }
    }

    public function render()
    {
        $query = ReturnModel::where('staff_id', $this->staffId)
            ->where('status', 'pending')
            ->with(['product']);

        if ($this->search) {
            $query->whereHas('product', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        $pendingReturns = $query->paginate($this->perPage);

        return view('livewire.admin.staff-product-return', [
            'pendingReturns' => $pendingReturns
        ]);
    }
}
