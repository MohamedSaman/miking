<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\StaffProductReturn as ReturnModel;
use App\Models\ProductStock;
use App\Models\StaffProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Livewire\Concerns\WithDynamicLayout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.admin')]
#[Title('Staff Return Requests (Re-entry)')]
class StaffReturnRequests extends Component
{
    use WithPagination, WithDynamicLayout;

    protected $paginationTheme = 'bootstrap';

    public $staffId = null;
    public $search = '';
    public $perPage = 12;

    // Sidebar/Modal properties
    public $showSidePanel = false;
    public $selectedReturnId = null;
    public $selectedReturn = null;
    public $restockQty = 0;
    public $damagedQty = 0;

    public function mount($staffId = null)
    {
        $this->staffId = $staffId;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openReentry($returnId)
    {
        $this->selectedReturnId = $returnId;
        $this->selectedReturn = ReturnModel::with(['product', 'staff'])->find($returnId);
        $this->restockQty = $this->selectedReturn->return_quantity;
        $this->damagedQty = 0;
        $this->showSidePanel = true;
    }

    public function closeSidePanel()
    {
        $this->showSidePanel = false;
        $this->selectedReturnId = null;
        $this->selectedReturn = null;
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
            $this->js("Swal.fire('Error', 'Total restock and damaged quantity cannot exceed requested quantity (".$this->selectedReturn->return_quantity.")', 'error')");
            return;
        }

        try {
            DB::beginTransaction();

            $productStock = ProductStock::where('product_id', $this->selectedReturn->product_id)->first();
            
            if (!$productStock) {
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

            $productStock->total_stock = $productStock->available_stock + $productStock->damage_stock;
            $productStock->save();

            // Update staff's allocated quantity record (if it hasn't been reduced yet)
            // Note: MyAllocatedProducts already reduces it when requesting.
            // But we should double check if we need to adjust further.

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
            $this->js("Swal.fire('Success', 'Stock re-entered and request processed!', 'success')");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing staff return: " . $e->getMessage());
            $this->js("Swal.fire('Error', 'Failed to process request', 'error')");
        }
    }

    public function rejectRequest($returnId)
    {
        try {
            DB::beginTransaction();
            $request = ReturnModel::find($returnId);
            if ($request) {
                // Return the quantity to staff's allocated stock
                $staffProduct = StaffProduct::where('staff_id', $request->staff_id)
                    ->where('product_id', $request->product_id)
                    ->first();
                
                if ($staffProduct) {
                    $staffProduct->quantity += $request->return_quantity;
                    $staffProduct->save();
                }

                $request->update([
                    'status' => 'rejected',
                    'processed_by' => auth()->id(),
                    'processed_at' => now()
                ]);
            }
            DB::commit();
            $this->js("Swal.fire('Rejected', 'Request has been rejected and stock returned to staff', 'info')");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error rejecting staff return: " . $e->getMessage());
            $this->js("Swal.fire('Error', 'Failed to reject request', 'error')");
        }
    }

    public function render()
    {
        $query = ReturnModel::with(['product', 'staff', 'processor'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at');

        if ($this->staffId) {
            $query->where('staff_id', $this->staffId);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('product', function($pq) {
                    $pq->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
                })->orWhereHas('staff', function($sq) {
                    $sq->where('name', 'like', '%' . $this->search . '%');
                });
            });
        }

        $returnRequests = $query->paginate($this->perPage);

        return view('livewire.admin.staff-return-requests', [
            'returnRequests' => $returnRequests
        ]);
    }
}
