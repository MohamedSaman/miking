<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\StaffReturn;
use App\Models\ProductStock;
use App\Models\StaffProduct;
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

                $quantity   = $this->selectedStaffReturn->quantity;
                $productId  = $this->selectedStaffReturn->product_id;
                $staffId    = $this->selectedStaffReturn->staff_id;
                $isDamaged  = (bool) $this->selectedStaffReturn->is_damaged;

                if ($isDamaged) {
                    // ─── Damaged return ─── goes to admin damage stock ───────────────
                    $productStock = ProductStock::where('product_id', $productId)->first();
                    if ($productStock) {
                        $productStock->damage_stock  += $quantity;
                        // total_stock stays the same; damaged items are already out of available
                        $productStock->total_stock    = $productStock->available_stock + $productStock->damage_stock;
                        $productStock->save();
                    } else {
                        ProductStock::create([
                            'product_id'         => $productId,
                            'available_stock'     => 0,
                            'damage_stock'        => $quantity,
                            'total_stock'         => $quantity,
                            'sold_count'          => 0,
                            'restocked_quantity'  => 0,
                        ]);
                    }
                } else {
                    // ─── Good return ─── goes back to staff's allocated stock ────────
                    $staffProduct = StaffProduct::where('staff_id', $staffId)
                        ->where('product_id', $productId)
                        ->first();

                    if ($staffProduct) {
                        $staffProduct->sold_quantity = max(0, $staffProduct->sold_quantity - $quantity);
                        $staffProduct->sold_value    = max(0, $staffProduct->sold_value - floatval($this->selectedStaffReturn->total_amount));

                        // Update status
                        if ($staffProduct->sold_quantity <= 0) {
                            $staffProduct->status = 'assigned';
                        } elseif ($staffProduct->sold_quantity < $staffProduct->quantity) {
                            $staffProduct->status = 'partial';
                        }
                        $staffProduct->save();
                    }
                }

                // Reduce due amount from sale if exists
                if ($this->selectedStaffReturn->sale_id) {
                    $sale = Sale::find($this->selectedStaffReturn->sale_id);
                    if ($sale && $sale->due_amount > 0) {
                        $returnAmount = floatval($this->selectedStaffReturn->total_amount);
                        $currentDue   = floatval($sale->due_amount);
                        $newDue       = max(0, $currentDue - $returnAmount);
                        $sale->due_amount = $newDue;
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
            DB::beginTransaction();

            if ($this->selectedStaffReturn) {
                // If it was approved, we must undo the stock changes
                if ($this->selectedStaffReturn->status === 'approved') {
                    $quantity  = $this->selectedStaffReturn->quantity;
                    $productId = $this->selectedStaffReturn->product_id;
                    $staffId   = $this->selectedStaffReturn->staff_id;
                    $isDamaged = (bool) $this->selectedStaffReturn->is_damaged;

                    if ($isDamaged) {
                        // Undo: remove from damage stock
                        $productStock = ProductStock::where('product_id', $productId)->first();
                        if ($productStock) {
                            $productStock->damage_stock  = max(0, $productStock->damage_stock - $quantity);
                            $productStock->total_stock   = $productStock->available_stock + $productStock->damage_stock;
                            $productStock->save();
                        }
                    } else {
                        // Undo: re-add back to staff sold_quantity (item was returned to staff but now we're deleting that return)
                        $staffProduct = StaffProduct::where('staff_id', $staffId)
                            ->where('product_id', $productId)
                            ->first();

                        if ($staffProduct) {
                            $staffProduct->sold_quantity = $staffProduct->sold_quantity + $quantity;
                            $staffProduct->sold_value    = $staffProduct->sold_value + floatval($this->selectedStaffReturn->total_amount);

                            // Update status
                            if ($staffProduct->sold_quantity >= $staffProduct->quantity) {
                                $staffProduct->status = 'completed';
                            } else {
                                $staffProduct->status = 'partial';
                            }
                            $staffProduct->save();
                        }
                    }

                    // Restore due amount on the sale
                    if ($this->selectedStaffReturn->sale_id) {
                        $sale = Sale::find($this->selectedStaffReturn->sale_id);
                        if ($sale) {
                            $sale->due_amount    += floatval($this->selectedStaffReturn->total_amount);
                            $sale->payment_status = $sale->due_amount >= $sale->total_amount ? 'pending' : 'partial';
                            $sale->save();
                        }
                    }
                }

                $this->selectedStaffReturn->delete();
                DB::commit();

                $this->dispatch('hideModal', 'deleteStaffReturnModal');
                $this->js("Swal.fire('Deleted', 'Staff return deleted successfully!', 'success');");
                $this->selectedStaffReturn = null;
            }
        } catch (\Exception $e) {
            DB::rollBack();
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
