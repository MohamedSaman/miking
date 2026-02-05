<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\StaffReturn;
use App\Models\ProductStock;
use App\Models\Sale;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("Staff Customer Returns")]
class StaffCustomerReturnList extends Component
{
    use WithPagination, WithDynamicLayout;

    public $staffReturnSearch = '';
    public $statusFilter = '';
    public $staffFilter = '';
    public $selectedStaffReturn = null;
    public $perPage = 10;

    public function updatingStaffReturnSearch()
    {
        $this->resetPage();
    }

    public function showStaffReturnDetails($id)
    {
        $this->selectedStaffReturn = StaffReturn::with(['sale.customer', 'product', 'staff', 'customer'])->find($id);
        $this->dispatch('showModal', 'staffReturnDetailsModal');
    }

    public function approveStaffReturn($id)
    {
        $this->selectedStaffReturn = StaffReturn::with(['sale', 'product'])->find($id);
        $this->dispatch('showModal', 'approveStaffReturnModal');
    }

    public function confirmApproveStaffReturn()
    {
        try {
            DB::beginTransaction();

            if ($this->selectedStaffReturn) {
                // Update status to approved
                $this->selectedStaffReturn->status = 'approved';
                $this->selectedStaffReturn->save();

                // Restore stock - increase available stock
                $productStock = ProductStock::where('product_id', $this->selectedStaffReturn->product_id)->first();
                if ($productStock) {
                    $productStock->available_stock += $this->selectedStaffReturn->quantity;
                    if ($productStock->sold_count >= $this->selectedStaffReturn->quantity) {
                        $productStock->sold_count -= $this->selectedStaffReturn->quantity;
                    }
                    // Sync total stock
                    $productStock->total_stock = $productStock->available_stock + $productStock->damage_stock;
                    $productStock->save();
                }

                // Reduce due amount from sale if exists
                if ($this->selectedStaffReturn->sale_id) {
                    $sale = Sale::find($this->selectedStaffReturn->sale_id);
                    if ($sale && $sale->due_amount > 0) {
                        $returnAmount = floatval($this->selectedStaffReturn->total_amount);
                        $currentDue = floatval($sale->due_amount);
                        
                        // Reduce due amount by return total
                        $newDue = max(0, $currentDue - $returnAmount);
                        $sale->due_amount = $newDue;
                        
                        // Update payment status if due is cleared
                        if ($newDue == 0) {
                            $sale->payment_status = 'paid';
                        }
                        $sale->save();
                    }
                }

                DB::commit();

                $this->dispatch('hideModal', 'approveStaffReturnModal');
                $this->js("Swal.fire('Success', 'Staff return approved successfully!', 'success');");
                $this->selectedStaffReturn = null;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->js("Swal.fire('Error', 'Error approving return: " . addslashes($e->getMessage()) . "', 'error');");
        }
    }

    public function rejectStaffReturn($id)
    {
        $this->selectedStaffReturn = StaffReturn::with(['sale', 'product', 'staff'])->find($id);
        $this->dispatch('showModal', 'rejectStaffReturnModal');
    }

    public function confirmRejectStaffReturn()
    {
        try {
            if ($this->selectedStaffReturn) {
                $this->selectedStaffReturn->status = 'rejected';
                $this->selectedStaffReturn->save();

                $this->dispatch('hideModal', 'rejectStaffReturnModal');
                $this->js("Swal.fire('Rejected', 'Staff return rejected.', 'warning');");
                $this->selectedStaffReturn = null;
            }
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error', 'Error rejecting return: " . addslashes($e->getMessage()) . "', 'error');");
        }
    }

    public function deleteStaffReturn($id)
    {
        $this->selectedStaffReturn = StaffReturn::find($id);
        $this->dispatch('showModal', 'deleteStaffReturnModal');
    }

    public function confirmDeleteStaffReturn()
    {
        try {
            if ($this->selectedStaffReturn) {
                // If it was approved, restore the stock changes
                if ($this->selectedStaffReturn->status === 'approved') {
                    $productStock = ProductStock::where('product_id', $this->selectedStaffReturn->product_id)->first();
                    if ($productStock) {
                        $productStock->available_stock -= $this->selectedStaffReturn->quantity;
                        if ($productStock->sold_count !== null) {
                            $productStock->sold_count += $this->selectedStaffReturn->quantity;
                        }
                        // Sync total stock
                        $productStock->total_stock = $productStock->available_stock + $productStock->damage_stock;
                        $productStock->save();
                    }

                    // Restore due amount
                    if ($this->selectedStaffReturn->sale_id) {
                        $sale = Sale::find($this->selectedStaffReturn->sale_id);
                        if ($sale) {
                            $sale->due_amount += floatval($this->selectedStaffReturn->total_amount);
                            $sale->payment_status = 'partial';
                            $sale->save();
                        }
                    }
                }

                $this->selectedStaffReturn->delete();
                $this->dispatch('hideModal', 'deleteStaffReturnModal');
                $this->js("Swal.fire('Deleted', 'Staff return deleted successfully!', 'success');");
                $this->selectedStaffReturn = null;
            }
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error', 'Error deleting return: " . addslashes($e->getMessage()) . "', 'error');");
        }
    }

    public function closeModal()
    {
        $this->selectedStaffReturn = null;
        $this->dispatch('hideModal', 'staffReturnDetailsModal');
        $this->dispatch('hideModal', 'approveStaffReturnModal');
        $this->dispatch('hideModal', 'rejectStaffReturnModal');
        $this->dispatch('hideModal', 'deleteStaffReturnModal');
    }

    public function render()
    {
        $staffQuery = StaffReturn::with(['sale.customer', 'product', 'staff', 'customer'])
            ->orderByDesc('created_at');

        if (!empty($this->staffReturnSearch)) {
            $search = '%' . $this->staffReturnSearch . '%';
            $staffQuery->where(function ($q) use ($search) {
                $q->whereHas('sale', function ($sq) use ($search) {
                    $sq->where('invoice_number', 'like', $search);
                })->orWhereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', $search)
                        ->orWhere('code', 'like', $search);
                })->orWhereHas('staff', function ($uq) use ($search) {
                    $uq->where('name', 'like', $search);
                });
            });
        }

        if (!empty($this->statusFilter)) {
            $staffQuery->where('status', $this->statusFilter);
        }

        if (!empty($this->staffFilter)) {
            $staffQuery->where('staff_id', $this->staffFilter);
        }
        
        $staffReturns = $staffQuery->paginate($this->perPage);

        $allStaff = \App\Models\User::where('role', 'staff')->orderBy('name')->get();

        return view('livewire.admin.staff-customer-return-list', [
            'staffReturns' => $staffReturns,
            'allStaff' => $allStaff,
        ])->layout('components.layouts.admin');
    }
}
