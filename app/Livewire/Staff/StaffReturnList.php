<?php

namespace App\Livewire\Staff;

use Livewire\Component;
use App\Models\StaffReturn;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Livewire\Concerns\WithDynamicLayout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

#[Title("Returns")]
class StaffReturnList extends Component
{
    use WithDynamicLayout, WithPagination;

    public $returnsCount = 0;
    public $returnSearch = '';
    public $selectedReturn = null;
    public $showReceiptModal = false;
    public $currentReturnId = null;
    public $perPage = 10;
    public $statusFilter = 'all';

    public function mount()
    {
        $this->loadReturns();
    }

    protected function loadReturns()
    {
        $query = StaffReturn::with(['sale', 'product', 'customer'])
            ->where('staff_id', Auth::id());

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if (!empty($this->returnSearch)) {
            $search = '%' . $this->returnSearch . '%';
            $query->where(function ($q) use ($search) {
                $q->whereHas('sale', function ($sq) use ($search) {
                    $sq->where('invoice_number', 'like', $search);
                })->orWhereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', $search)
                        ->orWhere('code', 'like', $search);
                })->orWhereHas('customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', $search);
                });
            });
        }

        $this->returnsCount = $query->count();
    }

    public function updatedReturnSearch()
    {
        $this->resetPage();
        $this->loadReturns();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
        $this->loadReturns();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function showReturnDetails($id)
    {
        $this->selectedReturn = StaffReturn::with(['sale', 'product', 'customer'])
            ->where('staff_id', Auth::id())
            ->find($id);

        if ($this->selectedReturn) {
            $this->dispatch('showModal', 'returnDetailsModal');
        } else {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Return not found or access denied.']);
        }
    }

    public function showReceipt($returnId)
    {
        $this->selectedReturn = StaffReturn::with(['sale.customer', 'product'])
            ->where('staff_id', Auth::id())
            ->find($returnId);

        if ($this->selectedReturn) {
            $this->currentReturnId = $returnId;
            $this->showReceiptModal = true;
            $this->dispatch('showModal', 'receiptModal');
        } else {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Return not found or access denied.']);
        }
    }

    public function deleteReturn($returnId)
    {
        $return = StaffReturn::where('staff_id', Auth::id())
            ->where('status', 'pending')
            ->find($returnId);

        if ($return) {
            $return->delete();
            $this->loadReturns();
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Return request deleted successfully.']);
        } else {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Cannot delete this return. It may have been approved or rejected.']);
        }
    }

    public function printReceipt()
    {
        $this->dispatch('printReceipt');
    }

    public function closeModal()
    {
        $this->selectedReturn = null;
        $this->currentReturnId = null;
        $this->showReceiptModal = false;
        $this->dispatch('hideModal', 'returnDetailsModal');
        $this->dispatch('hideModal', 'receiptModal');
    }

    public function render()
    {
        $query = StaffReturn::with(['sale', 'product', 'customer'])
            ->where('staff_id', Auth::id())
            ->orderByDesc('created_at');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if (!empty($this->returnSearch)) {
            $search = '%' . $this->returnSearch . '%';
            $query->where(function ($q) use ($search) {
                $q->whereHas('sale', function ($sq) use ($search) {
                    $sq->where('invoice_number', 'like', $search);
                })->orWhereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', $search)
                        ->orWhere('code', 'like', $search);
                })->orWhereHas('customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', $search);
                });
            });
        }

        $returns = $query->paginate($this->perPage);

        return view('livewire.staff.staff-return-list', [
            'returns' => $returns,
            'selectedReturn' => $this->selectedReturn,
            'currentReturnId' => $this->currentReturnId,
        ])->layout($this->layout);
    }
}
