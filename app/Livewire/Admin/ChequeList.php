<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cheque;
use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Log;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Cheque List')]
class ChequeList extends Component
{
    use WithDynamicLayout;

    use WithPagination;
    public $perPage = 10;
    public $search = '';
    public $statusFilter = 'all';
    public $selectedSale = null;

    public function viewSale($saleId)
    {
        $this->selectedSale = \App\Models\Sale::with([
            'customer',
            'items',
            'user',
            'returns' => function ($q) {
                $q->with('product');
            },
            'staffReturns' => function ($q) {
                $q->with('product');
            },
            'payments.cheques'
        ])->find($saleId);

        if ($this->selectedSale) {
            $this->dispatch('showModal', 'viewModal');
        }
    }

    public function closeModals()
    {
        $this->selectedSale = null;
        $this->dispatch('hideModal', 'viewModal');
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function getChequesProperty()
    {
        // Show pending cheques first, then others by cheque_date desc
        $query = Cheque::with(['customer', 'payment.allocations.sale', 'payment.sale'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END ASC")
            ->orderByDesc('cheque_date');

        if (!empty($this->search)) {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('cheque_number', 'like', $term)
                    ->orWhere('bank_name', 'like', $term)
                    ->orWhereHas('customer', function ($cq) use ($term) {
                        $cq->where('name', 'like', $term)
                            ->orWhere('phone', 'like', $term);
                    });
            });
        }
        // Apply status filter if set
        if (!empty($this->statusFilter) && $this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return $query->paginate($this->perPage);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function getPendingCountProperty()
    {
        return Cheque::where('status', 'pending')->count();
    }

    public function getCompleteCountProperty()
    {
        return Cheque::where('status', 'complete')->count();
    }

    public function getOverdueCountProperty()
    {
        return Cheque::where('status', 'overdue')->count();
    }

    public function confirmComplete($id)
    {
        $this->js("
            Swal.fire({
                title: 'Mark as Complete?',
                text: 'Are you sure you want to mark this cheque as complete?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, mark as complete!'
            }).then((result) => {
                if (result.isConfirmed) {
                    \$wire.completeCheque({$id});
                }
            });
        ");
    }

    public function confirmReturn($id)
    {
        $this->js("
            Swal.fire({
                title: 'Return Cheque?',
                text: 'Are you sure you want to return this cheque?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, return cheque!'
            }).then((result) => {
                if (result.isConfirmed) {
                    \$wire.returnCheque({$id});
                }
            });
        ");
    }

    public function completeCheque($id)
    {
        try {
            $cheque = Cheque::with('payment')->find($id);

            if (!$cheque) {
                $this->js("Swal.fire('Error', 'Cheque not found!', 'error');");
                return;
            }

            // Only admin can mark cheque as complete
            if (auth()->user()->role !== 'admin') {
                $this->js("Swal.fire('Error', 'Only admin can mark cheques as complete!', 'error');");
                return;
            }

            // Cheque must be in pending status to mark complete
            if ($cheque->status !== 'pending') {
                $this->js("Swal.fire('Error', 'This cheque cannot be marked as complete!', 'error');");
                return;
            }

            // Check if payment is approved (staff payments: 'approved', admin payments: 'paid')
            if ($cheque->payment && !in_array($cheque->payment->status, ['approved', 'paid'])) {
                $this->js("Swal.fire('Error', 'Cannot mark as complete. Payment has not been approved yet!', 'error');");
                return;
            }

            // Check if cheque date has passed
            if ($cheque->cheque_date && \Carbon\Carbon::parse($cheque->cheque_date)->startOfDay()->isAfter(now()->startOfDay())) {
                $chequeDate = \Carbon\Carbon::parse($cheque->cheque_date)->format('M d, Y');
                $this->js("Swal.fire('Error', 'Cannot mark as complete before the cheque date (" . $chequeDate . ")!', 'error');");
                return;
            }

            $cheque->status = 'complete';
            $cheque->save();

            // Refresh the data


            $this->js("
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Cheque marked as complete successfully!',
                    timer: 2000,
                    showConfirmButton: false
                });
            ");
        } catch (\Exception $e) {
            Log::error("Error completing cheque: " . $e->getMessage());
            $this->js("Swal.fire('Error', 'Failed to mark cheque as complete!', 'error');");
        }
    }

    public function returnCheque($id)
    {
        try {
            $cheque = Cheque::find($id);

            if (!$cheque) {
                $this->js("Swal.fire('Error', 'Cheque not found!', 'error');");
                return;
            }

            $cheque->status = 'return';
            $cheque->save();

            // Refresh the data


            $this->js("
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Cheque returned successfully!',
                    timer: 2000,
                    showConfirmButton: false
                });
            ");
        } catch (\Exception $e) {
            Log::error("Error returning cheque: " . $e->getMessage());
            $this->js("Swal.fire('Error', 'Failed to return cheque!', 'error');");
        }
    }

    public function render()
    {
        return view('livewire.admin.cheque-list', [
            'cheques' => $this->cheques,
            'pendingCount' => $this->pendingCount,
            'completeCount' => $this->completeCount,
            'overdueCount' => $this->overdueCount,
        ])->layout($this->layout);
    }
}
