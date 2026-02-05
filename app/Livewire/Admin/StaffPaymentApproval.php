<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Payment;
use App\Models\Cheque;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Layout('components.layouts.admin')]
#[Title('Payment Approvals')]
class StaffPaymentApproval extends Component
{
    use WithPagination;

    public $pendingCount = 0;
    public $approvedCount = 0;
    public $rejectedCount = 0;
    public $searchTerm = '';
    public $filterStatus = 'pending'; // pending, approved, rejected
    public $filterPaymentMethod = 'all'; // all, cash, cheque, bank_transfer, credit
    public $perPage = 15;
    
    // Invoice Modal Properties
    public $showInvoiceModal = false;
    public $selectedPayment = null;

    public function mount()
    {
        $this->updateCounts();
    }

    public function getPaymentsProperty()
    {
        // Get all payments with pending, approved, rejected statuses
        // Include payments with sale_id OR payments with allocations (from AddCustomerReceipt)
        $query = Payment::with(['sale.customer', 'sale.user', 'customer', 'allocations'])
            ->where(function ($q) {
                $q->whereNotNull('sale_id')
                  ->orWhereHas('allocations'); // Include payments with allocations
            });

        // Filter by status
        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        // Filter by payment method
        if ($this->filterPaymentMethod !== 'all') {
            $query->where('payment_method', $this->filterPaymentMethod);
        }

        // Search term
        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('id', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('payment_reference', 'like', '%' . $this->searchTerm . '%')
                    ->orWhereHas('sale', function ($q) {
                        $q->where('invoice_number', 'like', '%' . $this->searchTerm . '%');
                    })
                    ->orWhereHas('sale.customer', function ($q) {
                        $q->where('name', 'like', '%' . $this->searchTerm . '%');
                    })
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%' . $this->searchTerm . '%');
                    });
            });
        }

        return $query->orderByDesc('created_at')->paginate($this->perPage);
    }

    private function updateCounts()
    {
        // Get counts - include payments with sale_id OR allocations
        $baseQuery = function () {
            return Payment::where(function ($q) {
                $q->whereNotNull('sale_id')
                  ->orWhereHas('allocations');
            });
        };
        
        $this->pendingCount = $baseQuery()->where('status', 'pending')->count();
        $this->approvedCount = $baseQuery()->where('status', 'approved')->count();
        $this->rejectedCount = $baseQuery()->where('status', 'rejected')->count();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
        $this->updateCounts();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
        $this->updateCounts();
    }

    public function updatedFilterPaymentMethod()
    {
        $this->resetPage();
        $this->updateCounts();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function approvePayment($paymentId)
    {
        try {
            DB::beginTransaction();

            $payment = Payment::findOrFail($paymentId);
            
            // Simply update payment status - sale's due_amount was already reduced when staff made the payment
            $payment->update([
                'status' => 'approved',
                'is_completed' => true,
            ]);

            // If payment method is cheque, approve cheques too
            if ($payment->payment_method === 'cheque') {
                Cheque::where('payment_id', $paymentId)->update([
                    'status' => 'complete',
                ]);
            }

            DB::commit();

            // Calculate and record staff bonuses
            if ($payment->sale) {
                \App\Services\StaffBonusService::calculateBonusesForSale($payment->sale);
            }

            $this->js("Swal.fire('success', 'Payment approved successfully!', 'success')");
            $this->updateCounts();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment approval error: ' . $e->getMessage());
            $this->js("Swal.fire('error', 'Failed to approve payment: " . $e->getMessage() . "', 'error')");
        }
    }

    public function rejectPayment($paymentId)
    {
        try {
            DB::beginTransaction();

            $payment = Payment::findOrFail($paymentId);
            
            // Check if this payment has allocations (from AddCustomerReceipt)
            $allocations = DB::table('payment_allocations')
                ->where('payment_id', $paymentId)
                ->get();

            if ($allocations->count() > 0) {
                // Restore due_amount for each allocated sale
                foreach ($allocations as $allocation) {
                    $sale = \App\Models\Sale::find($allocation->sale_id);
                    if ($sale) {
                        $newDueAmount = $sale->due_amount + $allocation->allocated_amount;
                        
                        // Determine new payment status
                        $paymentStatus = 'pending';
                        if ($newDueAmount >= $sale->total_amount) {
                            $paymentStatus = 'pending';
                        } elseif ($newDueAmount > 0) {
                            $paymentStatus = 'partial';
                        }
                        
                        $sale->update([
                            'due_amount' => $newDueAmount,
                            'payment_status' => $paymentStatus,
                            'notes' => ($sale->notes ? $sale->notes . "\n" : '') . 
                                "Payment of Rs. " . number_format($allocation->allocated_amount, 2) . " rejected on " . now()->format('Y-m-d H:i') . ".",
                        ]);
                    }
                }
                
                // Delete the allocations since payment is rejected
                DB::table('payment_allocations')->where('payment_id', $paymentId)->delete();
            } elseif ($payment->sale) {
                // Handle direct sale payments - restore due_amount
                $sale = $payment->sale;
                $newDueAmount = $sale->due_amount + $payment->amount;
                
                // Determine new payment status
                $paymentStatus = 'pending';
                if ($newDueAmount >= $sale->total_amount) {
                    $paymentStatus = 'pending';
                } elseif ($newDueAmount > 0) {
                    $paymentStatus = 'partial';
                }
                
                $sale->update([
                    'due_amount' => $newDueAmount,
                    'payment_status' => $paymentStatus,
                    'notes' => ($sale->notes ? $sale->notes . "\n" : '') . 
                        "Payment of Rs. " . number_format($payment->amount, 2) . " rejected on " . now()->format('Y-m-d H:i') . ".",
                ]);
            }
            
            $payment->update([
                'status' => 'rejected',
                'is_completed' => false,
            ]);

            // If payment method is cheque, reject cheques too
            if ($payment->payment_method === 'cheque') {
                Cheque::where('payment_id', $paymentId)->update([
                    'status' => 'cancelled',
                ]);
            }

            DB::commit();

            $this->js("Swal.fire('success', 'Payment rejected successfully!', 'success')");
            $this->updateCounts();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment rejection error: ' . $e->getMessage());
            $this->js("Swal.fire('error', 'Failed to reject payment: " . $e->getMessage() . "', 'error')");
        }
    }

    public function getPaymentMethodBadge($method)
    {
        return match ($method) {
            'cash' => ['class' => 'bg-success', 'text' => 'Cash'],
            'cheque' => ['class' => 'bg-info', 'text' => 'Cheque'],
            'bank_transfer' => ['class' => 'bg-warning', 'text' => 'Bank Transfer'],
            'credit' => ['class' => 'bg-danger', 'text' => 'Credit'],
            default => ['class' => 'bg-secondary', 'text' => 'Unknown']
        };
    }

    public function getStatusBadge($status)
    {
        return match ($status) {
            'pending' => ['class' => 'bg-warning', 'text' => 'Pending Approval'],
            'approved' => ['class' => 'bg-success', 'text' => 'Approved'],
            'rejected' => ['class' => 'bg-danger', 'text' => 'Rejected'],
            'paid' => ['class' => 'bg-success', 'text' => 'Paid'],
            default => ['class' => 'bg-secondary', 'text' => 'Unknown']
        };
    }

    public function viewInvoice($paymentId)
    {
        try {
            $this->selectedPayment = Payment::with([
                'sale.customer',
                'sale.user',
                'sale.items',
                'customer',
                'allocations',
                'creator'
            ])->findOrFail($paymentId);
            
            $this->showInvoiceModal = true;
        } catch (\Exception $e) {
            Log::error('Error loading invoice details: ' . $e->getMessage());
            $this->js("Swal.fire('error', 'Failed to load invoice details: " . $e->getMessage() . "', 'error')");
        }
    }

    public function closeInvoiceModal()
    {
        $this->showInvoiceModal = false;
        $this->selectedPayment = null;
    }

    public function render()
    {
        return view('livewire.admin.staff-payment-approval', [
            'payments' => $this->payments,
        ]);
    }
}
