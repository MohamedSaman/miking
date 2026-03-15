<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\Cheque;
use App\Models\ReturnsProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("Add Customer Receipt")]
class AddCustomerReceipt extends Component
{
    use WithDynamicLayout;

    use WithPagination;

    public $search = '';
    public $selectedCustomer = null;
    public $customerSales = [];
    public $selectedInvoices = [];
    public $paymentData = [
        'payment_date' => '',
        'payment_method' => 'cash',
        'reference_number' => '',
        'notes' => ''
    ];

    // Cheque related properties
    public $cheque = [
        'cheque_number' => '',
        'bank_name' => '',
        'cheque_date' => '',
        'amount' => 0
    ];

    public $bankTransfer = [
        'bank_name' => '',
        'transfer_date' => '',
        'reference_number' => ''
    ];

    public $allocations = [];
    public $totalDueAmount = 0;
    public $totalPaymentAmount = 0;
    public $remainingAmount = 0;
    public $showPaymentModal = false;
    public $showViewModal = false;
    public $showReceiptModal = false;
    public $selectedSale = null;
    public $latestPayment = null;
    public $paymentSuccess = false;

    public $customerOpeningBalance = 0;
    public $customerOverpaidAmount = 0;
    public $customerOpeningRemarks = '';

    public $applyOverpaid = false;
    public $appliedOverpaidAmount = 0;
    public $maxPaymentAllowed = 0;

    protected $rules = [
        'paymentData.payment_date' => 'required|date',
        'paymentData.payment_method' => 'required|in:cash,cheque,bank_transfer',
        'paymentData.reference_number' => 'nullable|string|max:100',
        'paymentData.notes' => 'nullable|string|max:500',
        'totalPaymentAmount' => 'nullable|numeric|min:0',
        'cheque.cheque_number' => 'required_if:paymentData.payment_method,cheque|string|max:50',
        'cheque.bank_name' => 'required_if:paymentData.payment_method,cheque|string|max:100',
        'cheque.cheque_date' => 'required_if:paymentData.payment_method,cheque|date',
        'bankTransfer.bank_name' => 'required_if:paymentData.payment_method,bank_transfer|string|max:100',
        'bankTransfer.transfer_date' => 'required_if:paymentData.payment_method,bank_transfer|date',
        'bankTransfer.reference_number' => 'required_if:paymentData.payment_method,bank_transfer|string|max:100',
    ];

    protected $messages = [
        'paymentData.payment_date.required' => 'Payment date is required.',
        'paymentData.payment_method.required' => 'Payment method is required.',
        'totalPaymentAmount.required' => 'Payment amount is required.',
        'totalPaymentAmount.min' => 'Payment amount must be at least Rs. 0.01',
        'cheque.cheque_number.required_if' => 'Cheque number is required for cheque payments.',
        'cheque.bank_name.required_if' => 'Bank name is required for cheque payments.',
        'cheque.cheque_date.required_if' => 'Cheque date is required for cheque payments.',
        'bankTransfer.bank_name.required_if' => 'Bank name is required for bank transfer.',
        'bankTransfer.transfer_date.required_if' => 'Transfer date is required for bank transfer.',
        'bankTransfer.reference_number.required_if' => 'Reference number is required for bank transfer.',
    ];

    public function mount()
    {
        $this->paymentData['payment_date'] = now()->format('Y-m-d');
        $this->cheque['cheque_date'] = now()->format('Y-m-d');
        $this->bankTransfer['transfer_date'] = now()->format('Y-m-d');
        $this->totalPaymentAmount = 0;
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->selectedCustomer = null;
        $this->customerSales = [];
        $this->resetPaymentData();
    }

    public function updatedTotalPaymentAmount()
    {
        $this->updateBoundaries();
        $this->calculateRemainingAmount();
        $this->autoAllocatePayment();

        // Update cheque amount if payment method is cheque
        if ($this->paymentData['payment_method'] === 'cheque') {
            $this->cheque['amount'] = $this->totalPaymentAmount;
        }
    }

    public function updatedApplyOverpaid()
    {
        $this->updateBoundaries();
        $this->calculateRemainingAmount();
        $this->autoAllocatePayment();
    }

    private function updateBoundaries()
    {
        if ($this->applyOverpaid) {
            $this->appliedOverpaidAmount = min((float)$this->totalDueAmount, (float)$this->customerOverpaidAmount);
        } else {
            $this->appliedOverpaidAmount = 0;
        }

        $this->maxPaymentAllowed = max(0, (float)$this->totalDueAmount - $this->appliedOverpaidAmount);

        if ($this->totalPaymentAmount > $this->maxPaymentAllowed) {
            $this->totalPaymentAmount = $this->maxPaymentAllowed;
        }

        if ($this->totalPaymentAmount < 0) {
            $this->totalPaymentAmount = 0;
        }
    }

    public function updatedPaymentDataPaymentMethod($value)
    {
        // Reset all payment method specific fields first
        $this->cheque = [
            'cheque_number' => '',
            'bank_name' => '',
            'cheque_date' => now()->format('Y-m-d'),
            'amount' => $this->totalPaymentAmount
        ];

        $this->bankTransfer = [
            'bank_name' => '',
            'transfer_date' => now()->format('Y-m-d'),
            'reference_number' => ''
        ];
    }

    public function selectCustomer($customerId)
    {
        $this->selectedCustomer = Customer::find($customerId);
        if ($this->selectedCustomer) {
            $this->customerOpeningBalance = (float)($this->selectedCustomer->opening_balance ?? 0);
            $this->customerOverpaidAmount = (float)($this->selectedCustomer->overpaid_amount ?? 0);
            $this->customerOpeningRemarks = $this->selectedCustomer->opening_remarks ?? '';
            $this->loadCustomerSales();
        }
        $this->selectedInvoices = [];
        $this->totalPaymentAmount = 0;
        $this->totalDueAmount = 0;
        $this->appliedOverpaidAmount = 0;
        $this->applyOverpaid = false;
        $this->initializeAllocations();
    }

    public function clearSelectedCustomer()
    {
        $this->selectedCustomer = null;
        $this->customerSales = [];
        $this->selectedInvoices = [];
        $this->allocations = [];
        $this->totalDueAmount = 0;
        $this->totalPaymentAmount = 0;
        $this->remainingAmount = 0;
        $this->resetPaymentData();
    }

    /**
     * Toggle invoice selection
     */
    public function toggleInvoiceSelection($saleId)
    {
        if (in_array($saleId, $this->selectedInvoices)) {
            $this->selectedInvoices = array_values(array_diff($this->selectedInvoices, [$saleId]));
        } else {
            $this->selectedInvoices[] = $saleId;
        }

        $this->calculateTotalDue();
        $this->updateBoundaries();
        $this->totalPaymentAmount = 0;
        $this->remainingAmount = $this->totalDueAmount;
        $this->initializeAllocations();
    }

    /**
     * Select all invoices
     */
    public function selectAllInvoices()
    {
        $this->selectedInvoices = array_column($this->customerSales, 'id');
        $this->calculateTotalDue();
        $this->totalPaymentAmount = 0;
        $this->remainingAmount = $this->totalDueAmount;
        $this->initializeAllocations();
    }

    /**
     * Clear invoice selection
     */
    public function clearInvoiceSelection()
    {
        $this->selectedInvoices = [];
        $this->totalDueAmount = 0;
        $this->totalPaymentAmount = 0;
        $this->remainingAmount = 0;
        $this->allocations = [];
    }

    /**
     * Calculate total return amount for a specific sale
     */
    private function calculateReturnAmount($saleId)
    {
        return ReturnsProduct::where('sale_id', $saleId)
            ->sum('total_amount');
    }

    /**
     * Load customer sales with return amounts calculated
     */
    private function loadCustomerSales()
    {
        if (!$this->selectedCustomer) return;

        $query = Sale::with(['items', 'returns', 'staffReturns' => function ($q) {
                $q->where('status', 'approved');
            }])
            ->where('customer_id', $this->selectedCustomer->id)
            ->where(function ($query) {
                $query->where('payment_status', 'pending')
                    ->orWhere('payment_status', 'partial');
            });

        // Filter by user for staff
        if ($this->isStaff()) {
            $query->where('user_id', Auth::id())
                  ->whereIn('sale_type', ['staff', 'pos']);
        }

        $sales = $query->orderBy('created_at', 'asc')
            ->get();

        $salesData = $sales->map(function ($sale) {
            // Admin returns (ReturnsProduct) now reduce due_amount directly in DB at return time
            $adminReturnAmount = $this->calculateReturnAmount($sale->id);
            // Staff returns are ALREADY reflected in due_amount (approval reduces it directly)
            $staffReturnAmount = $sale->staffReturns->sum('total_amount');
            $totalReturnAmount = $adminReturnAmount + $staffReturnAmount;

            // Adjusted amounts after returns
            $adjustedTotalAmount = $sale->total_amount - $totalReturnAmount;
            // due_amount already incorporates both admin and staff return reductions
            $adjustedDueAmount = $sale->due_amount;

            // Paid = adjusted total minus adjusted due (accounts for both return types correctly)
            $paidAmount = max(0, $adjustedTotalAmount - $adjustedDueAmount);

            // If adjusted due amount is 0 or negative, update the sale status
            if ($adjustedDueAmount <= 0.01) {
                $this->autoMarkSaleAsPaid($sale->id, $adminReturnAmount);
            }

            return [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'sale_id' => $sale->sale_id,
                'sale_date' => $sale->created_at->format('M d, Y'),
                'original_total_amount' => (float)$sale->total_amount,
                'total_amount' => (float)$adjustedTotalAmount,
                'original_due_amount' => (float)$sale->due_amount,
                'due_amount' => (float)$adjustedDueAmount,
                'return_amount' => (float)$totalReturnAmount,
                'paid_amount' => (float)$paidAmount,
                'payment_status' => $adjustedDueAmount <= 0.01 ? 'paid' : $sale->payment_status,
                'items_count' => $sale->items->count(),
                'has_returns' => $totalReturnAmount > 0,
            ];
        })->filter(function ($sale) {
            // Only show sales with due amount > 0 after returns
            return $sale['due_amount'] > 0.01;
        })->values()->toArray();

        // MANUALLY APPEND OPENING BALANCE AS A VIRTUAL SALE
        if ($this->customerOpeningBalance > 0) {
            array_unshift($salesData, [
                'id' => 'opening_' . $this->selectedCustomer->id,
                'invoice_number' => 'OPENING-BALANCE',
                'sale_id' => 'PREVIOUS-REC',
                'sale_date' => '-',
                'original_total_amount' => (float)$this->customerOpeningBalance,
                'total_amount' => (float)$this->customerOpeningBalance,
                'original_due_amount' => (float)$this->customerOpeningBalance,
                'due_amount' => (float)$this->customerOpeningBalance,
                'return_amount' => 0,
                'paid_amount' => 0,
                'payment_status' => 'pending',
                'items_count' => 0,
                'has_returns' => false,
                'is_opening_balance' => true,
                'remarks' => $this->customerOpeningRemarks
            ]);
        }

        $this->customerSales = $salesData;

        $this->calculateTotalDue();
    }

    /**
     * Calculate total due amount for selected invoices
     */
    private function calculateTotalDue()
    {
        $this->totalDueAmount = (float)collect($this->customerSales)
            ->whereIn('id', $this->selectedInvoices)
            ->sum('due_amount');
        $this->remainingAmount = $this->totalDueAmount;
        $this->updateBoundaries();
    }

    private function calculateRemainingAmount()
    {
        $this->remainingAmount = $this->totalDueAmount - ($this->totalPaymentAmount + $this->appliedOverpaidAmount);
    }

    private function initializeAllocations()
    {
        $this->allocations = [];

        foreach ($this->customerSales as $sale) {
            if (in_array($sale['id'], $this->selectedInvoices)) {
                $this->allocations[$sale['id']] = [
                    'sale_id' => $sale['id'],
                    'invoice_number' => $sale['invoice_number'],
                    'due_amount' => $sale['due_amount'],
                    'payment_amount' => 0,
                    'is_fully_paid' => false
                ];
            }
        }
    }

    private function autoAllocatePayment()
    {
        $remainingPayment = (float)$this->totalPaymentAmount + (float)$this->appliedOverpaidAmount;

        foreach ($this->customerSales as $sale) {
            $saleId = $sale['id'];

            // Only allocate to selected invoices
            if (!in_array($saleId, $this->selectedInvoices)) {
                continue;
            }

            $dueAmount = $sale['due_amount'];

            if ($remainingPayment <= 0) {
                $this->allocations[$saleId]['payment_amount'] = 0;
                $this->allocations[$saleId]['is_fully_paid'] = false;
            } elseif ($remainingPayment >= $dueAmount) {
                $this->allocations[$saleId]['payment_amount'] = $dueAmount;
                $this->allocations[$saleId]['is_fully_paid'] = true;
                $remainingPayment -= $dueAmount;
            } else {
                $this->allocations[$saleId]['payment_amount'] = $remainingPayment;
                $this->allocations[$saleId]['is_fully_paid'] = false;
                $remainingPayment = 0;
            }
        }
    }

    public function openPaymentModal()
    {
        if (empty($this->selectedInvoices)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Please select at least one invoice to make a payment.'
            ]);
            return;
        }

        // Validate payment amount
        if (!$this->totalPaymentAmount || $this->totalPaymentAmount <= 0) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Please enter a payment amount greater than zero.'
            ]);
            return;
        }

        if ($this->totalPaymentAmount > $this->totalDueAmount) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Payment amount cannot exceed total due amount.'
            ]);
            return;
        }

        // Validate cheque amounts if payment method is cheque
        if ($this->paymentData['payment_method'] === 'cheque') {
            if ($this->cheque['amount'] != $this->totalPaymentAmount) {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'Cheque amount must equal the payment amount.'
                ]);
                return;
            }
        }

        // Allocate payment
        $this->autoAllocatePayment();

        // Show modal
        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->paymentSuccess = false;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedSale = null;
    }

    public function openReceiptModal()
    {
        $this->showReceiptModal = true;
    }

    public function closeReceiptModal()
    {
        $this->showReceiptModal = false;
        $this->latestPayment = null;

        // Reset everything
        $this->selectedCustomer = null;
        $this->customerSales = [];
        $this->selectedInvoices = [];
        $this->allocations = [];
        $this->totalDueAmount = 0;
        $this->totalPaymentAmount = 0;
        $this->remainingAmount = 0;
        $this->applyOverpaid = false;
        $this->appliedOverpaidAmount = 0;
        $this->cheque = [
            'cheque_number' => '',
            'bank_name' => '',
            'cheque_date' => now()->format('Y-m-d'),
            'amount' => 0
        ];
        $this->search = '';
        $this->resetPaymentData();

        // Reset page
        $this->resetPage();

        // Dispatch event to refresh the page
        $this->dispatch('payment-completed');
    }

    private function resetPaymentData()
    {
        $this->paymentData = [
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'cash',
            'reference_number' => '',
            'notes' => ''
        ];
        $this->totalPaymentAmount = 0;
        $this->cheque = [
            'cheque_number' => '',
            'bank_name' => '',
            'cheque_date' => now()->format('Y-m-d'),
            'amount' => 0
        ];
        $this->bankTransfer = [
            'bank_name' => '',
            'transfer_date' => now()->format('Y-m-d'),
            'reference_number' => ''
        ];
    }

    public function viewSale($saleId)
    {
        $this->selectedSale = Sale::with(['customer', 'items', 'payments', 'returns.product'])->find($saleId);

        // Calculate return amount for display
        if ($this->selectedSale) {
            $this->selectedSale->return_amount = $this->calculateReturnAmount($saleId);
            $this->selectedSale->adjusted_total = $this->selectedSale->total_amount - $this->selectedSale->return_amount;
            $this->selectedSale->adjusted_due = max(0, $this->selectedSale->due_amount - $this->selectedSale->return_amount);
        }

        $this->showViewModal = true;
    }

    /**
     * Automatically mark sale as paid if returns cover the full due amount
     */
    private function autoMarkSaleAsPaid($saleId, $returnAmount)
    {
        try {
            $sale = Sale::find($saleId);
            if ($sale && $sale->due_amount <= $returnAmount) {
                DB::beginTransaction();

                // Create a system payment record for the return adjustment
                $payment = Payment::create([
                    'customer_id' => $sale->customer_id,
                    'amount' => min($sale->due_amount, $returnAmount),
                    'payment_method' => 'return_adjustment',
                    'payment_reference' => 'AUTO-RETURN-' . $sale->invoice_number,
                    'payment_date' => now(),
                    'status' => 'paid',
                    'is_completed' => 1,
                    'notes' => 'Automatically adjusted due to product returns covering the full amount',
                    'created_by' => Auth::id() ?? 1,
                ]);

                // Create payment allocation
                DB::table('payment_allocations')->insert([
                    'payment_id' => $payment->id,
                    'sale_id' => $saleId,
                    'allocated_amount' => min($sale->due_amount, $returnAmount),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update sale
                $sale->due_amount = 0;
                $sale->payment_status = 'paid';
                $sale->save();

                DB::commit();

                Log::info('Sale automatically marked as paid due to returns', [
                    'sale_id' => $saleId,
                    'return_amount' => $returnAmount,
                    'payment_id' => $payment->id
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to auto-mark sale as paid', [
                'sale_id' => $saleId,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function processPayment()
    {
        Log::info('Payment processing started', [
            'customer_id' => $this->selectedCustomer->id,
            'amount' => $this->totalPaymentAmount,
            'method' => $this->paymentData['payment_method']
        ]);

        // Validate inputs
        try {
            $this->validate();
            
            if (($this->totalPaymentAmount <= 0) && ($this->appliedOverpaidAmount <= 0)) {
                 $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'Please enter a payment amount or apply credit.'
                ]);
                return;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);

            // Get first error message
            $firstError = collect($e->errors())->flatten()->first();

            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => $firstError ?? 'Please fill all required fields correctly.'
            ]);
            return;
        }

        // Check payment amount
        if ($this->totalPaymentAmount > $this->totalDueAmount) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Payment amount cannot exceed total due amount.'
            ]);
            return;
        }

        // Additional validation: Check for duplicate cheque numbers in database
        if ($this->paymentData['payment_method'] === 'cheque') {
            $existingCheque = Cheque::where('cheque_number', $this->cheque['cheque_number'])->first();
            if ($existingCheque) {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => "Cheque number '{$this->cheque['cheque_number']}' already exists in the system. Please use a different cheque number."
                ]);
                return;
            }
        }

        try {
            DB::beginTransaction();

            $totalProcessed = 0;
            $processedInvoices = [];
            $isStaffUser = $this->isStaff();
            $paymentStatus = $isStaffUser ? 'pending' : 'paid';
            $isCompleted = $isStaffUser ? false : true;

            $creditPayment = null;
            $actualPayment = null;

            // 1. Handle Credit Redemption (Overpaid Balance)
            if ($this->appliedOverpaidAmount > 0) {
                $creditPayment = Payment::create([
                    'customer_id' => $this->selectedCustomer->id,
                    'amount' => $this->appliedOverpaidAmount,
                    'payment_method' => 'credit_adjustment',
                    'payment_reference' => 'CREDIT-REDEMPTION',
                    'payment_date' => $this->paymentData['payment_date'],
                    'status' => 'paid', // Credit is already "paid" in the past
                    'is_completed' => true,
                    'notes' => 'Redeemed from overpaid balance',
                    'created_by' => Auth::id(),
                ]);

                // Update customer overpaid balance
                $customer = Customer::find($this->selectedCustomer->id);
                if ($customer) {
                    $customer->overpaid_amount = max(0, $customer->overpaid_amount - $this->appliedOverpaidAmount);
                    $customer->save();
                }
                Log::info('Credit redemption payment created', ['payment_id' => $creditPayment->id]);
            }

            // 2. Handle Actual Payment (Cash/Cheque/Bank)
            if ($this->totalPaymentAmount > 0) {
                $pData = [
                    'customer_id' => $this->selectedCustomer->id,
                    'amount' => $this->totalPaymentAmount,
                    'payment_method' => $this->paymentData['payment_method'],
                    'payment_reference' => $this->paymentData['reference_number'] ?? null,
                    'payment_date' => $this->paymentData['payment_date'],
                    'status' => $paymentStatus,
                    'is_completed' => $isCompleted,
                    'notes' => $this->paymentData['notes'] ?? null,
                    'created_by' => Auth::id(),
                ];

                if ($this->paymentData['payment_method'] === 'bank_transfer') {
                    $pData['bank_name'] = $this->bankTransfer['bank_name'];
                    $pData['transfer_date'] = $this->bankTransfer['transfer_date'];
                    $pData['transfer_reference'] = $this->bankTransfer['reference_number'];
                }

                $actualPayment = Payment::create($pData);

                if ($this->paymentData['payment_method'] === 'cheque') {
                    Cheque::create([
                        'payment_id' => $actualPayment->id,
                        'cheque_number' => $this->cheque['cheque_number'],
                        'bank_name' => $this->cheque['bank_name'],
                        'cheque_date' => $this->cheque['cheque_date'],
                        'cheque_amount' => $this->cheque['amount'],
                        'status' => 'pending',
                        'customer_id' => $this->selectedCustomer->id,
                    ]);
                }
                Log::info('Actual payment created', ['payment_id' => $actualPayment->id]);
            }

            $remainingCreditToAllocate = (float)$this->appliedOverpaidAmount;
            $remainingActualToAllocate = (float)$this->totalPaymentAmount;

            // Process each sale allocation
            foreach ($this->allocations as $allocation) {
                $saleId = $allocation['sale_id'];
                $paymentAmount = (float)$allocation['payment_amount'];

                if ($paymentAmount <= 0) continue;

                // How much of this allocation comes from credit?
                $fromCredit = 0;
                if ($remainingCreditToAllocate > 0) {
                    $fromCredit = min($paymentAmount, $remainingCreditToAllocate);
                    $remainingCreditToAllocate -= $fromCredit;
                }

                // How much from actual cash/etc?
                $fromActual = $paymentAmount - $fromCredit;
                $remainingActualToAllocate -= $fromActual;

                if (is_string($saleId) && strpos($saleId, 'opening_') === 0) {
                    // Update customer opening balance
                    $customer = Customer::find($this->selectedCustomer->id);
                    if ($customer) {
                        $customer->opening_balance = max(0, $customer->opening_balance - $paymentAmount);
                        $customer->save();
                    }
                } else {
                    $saleModel = Sale::find($saleId);
                    if ($saleModel) {
                        $newDueAmount = max(0, $saleModel->due_amount - $paymentAmount);
                        $saleModel->update([
                            'due_amount' => $newDueAmount,
                            'payment_status' => $newDueAmount <= 0.01 ? 'paid' : 'partial'
                        ]);

                        // Allocation for credit part
                        if ($fromCredit > 0 && $creditPayment) {
                            DB::table('payment_allocations')->insert([
                                'payment_id' => $creditPayment->id,
                                'sale_id' => $saleId,
                                'allocated_amount' => $fromCredit,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        // Allocation for actual part
                        if ($fromActual > 0 && $actualPayment) {
                            DB::table('payment_allocations')->insert([
                                'payment_id' => $actualPayment->id,
                                'sale_id' => $saleId,
                                'allocated_amount' => $fromActual,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }

                $totalProcessed += $paymentAmount;
                $invoiceNum = $allocation['invoice_number'] ?? 'Unknown';
                if (!in_array($invoiceNum, $processedInvoices)) {
                    $processedInvoices[] = $invoiceNum;
                }
            }

            DB::commit();

            $this->paymentSuccess = true;
            $this->showPaymentModal = false;
            $this->latestPayment = $actualPayment ?: $creditPayment;

            $successMessage = "Rs." . number_format($totalProcessed, 2) . " processed successfully!";
            if ($isStaffUser && $actualPayment) {
                $successMessage .= " Awaiting admin approval for cash/cheque.";
            }

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => $successMessage
            ]);

            $this->openReceiptModal();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payment processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Check if it's a duplicate entry error for cheque number
            $errorMessage = $e->getMessage();
            if (strpos($errorMessage, 'Duplicate entry') !== false && strpos($errorMessage, 'cheques_cheque_number_unique') !== false) {
                // Extract cheque number from error message
                preg_match("/Duplicate entry '([^']+)'/", $errorMessage, $matches);
                $chequeNumber = $matches[1] ?? 'unknown';

                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => "Cheque number '{$chequeNumber}' already exists in the system. Please use a different cheque number."
                ]);
            } else {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'Failed to process payment. Please check your input and try again.'
                ]);
            }
        }
    }

    public function sendWhatsApp($paymentId)
    {
        $payment = Payment::with(['customer', 'cheques'])->findOrFail($paymentId);
        $customer = $payment->customer;

        if (!$customer || !$customer->phone) {
            $this->js("Swal.fire('Error', 'Customer phone number not found.', 'error')");
            return;
        }

        $phone = $customer->phone;
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 10 && $phone[0] === '0') {
            $phone = '94' . substr($phone, 1);
        } elseif (strlen($phone) === 9 && $phone[0] !== '0') {
            $phone = '94' . $phone;
        }

        // Calculate total outstanding balance
        $salesDue = Sale::where('customer_id', $customer->id)->sum('due_amount');
        $totalOutstanding = $salesDue + ($customer->opening_balance ?? 0) - ($customer->overpaid_amount ?? 0);
        
        // Find allocations for this payment
        $allocations = DB::table('payment_allocations')
            ->join('sales', 'payment_allocations.sale_id', '=', 'sales.id')
            ->where('payment_allocations.payment_id', $payment->id)
            ->select('sales.invoice_number', 'payment_allocations.allocated_amount')
            ->get();

        $message = "Hello " . ($customer->name ?? 'Customer') . ",\n\n";
        $message .= "Thank you for your payment. Here is your receipt summary:\n\n";
        $message .= "*Receipt ID:* RCPT-" . str_pad($payment->id, 5, '0', STR_PAD_LEFT) . "\n";
        $message .= "*Date:* " . date('d-m-Y', strtotime($payment->payment_date)) . "\n";
        $message .= "*Amount Paid:* Rs." . number_format($payment->amount, 2) . "\n";
        $message .= "*Payment Method:* " . ucfirst(str_replace('_', ' ', $payment->payment_method)) . "\n";
        
        if ($payment->payment_method === 'cheque' && $payment->cheques->count() > 0) {
            $cheque = $payment->cheques->first();
            $message .= "*Cheque No:* " . $cheque->cheque_number . " (" . $cheque->bank_name . ")\n";
        }

        if ($allocations->count() > 0) {
            $message .= "\n*Allocations:*\n";
            foreach ($allocations as $allocation) {
                $message .= "- " . $allocation->invoice_number . ": Rs." . number_format($allocation->allocated_amount, 2) . "\n";
            }
        }

        $message .= "\n*Total Outstanding Balance:* Rs." . number_format($totalOutstanding, 2) . "\n\n";
        $message .= "Thank you!";

        $whatsappUrl = "https://wa.me/" . $phone . "?text=" . urlencode($message);
        
        $this->js("window.open('$whatsappUrl', '_blank');");
    }

    public function downloadReceipt()
    {
        if (!$this->latestPayment) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No payment receipt available to download.'
            ]);
            return;
        }

        try {
            // Load payment with relationships
            $payment = Payment::with(['cheques'])
                ->find($this->latestPayment->id);

            $allocations = DB::table('payment_allocations')
                ->join('sales', 'payment_allocations.sale_id', '=', 'sales.id')
                ->where('payment_allocations.payment_id', $payment->id)
                ->select(
                    'sales.id as sale_id',
                    'sales.invoice_number',
                    'sales.total_amount',
                    'payment_allocations.allocated_amount'
                )
                ->get()
                ->map(function ($allocation) {
                    $returnAmount = $this->calculateReturnAmount($allocation->sale_id);
                    $allocation->return_amount = (float)$returnAmount;
                    $allocation->adjusted_total = (float)($allocation->total_amount - $returnAmount);
                    return $allocation;
                });

            // If there's unallocated amount, it was for the opening balance
            $totalAllocated = $allocations->sum('allocated_amount');
            if ($payment->amount > $totalAllocated + 0.01) {
                $unallocated = $payment->amount - $totalAllocated;
                $allocations->push((object)[
                    'sale_id' => 0,
                    'invoice_number' => 'OPENING-BALANCE',
                    'total_amount' => $unallocated,
                    'allocated_amount' => $unallocated,
                    'return_amount' => 0,
                    'adjusted_total' => $unallocated,
                ]);
            }

            $receiptData = [
                'payment' => $payment,
                'customer' => $this->selectedCustomer,
                'received_by' => Auth::user()->name,
                'payment_date' => $payment->payment_date,
                'allocations' => $allocations,
            ];

            $pdf = PDF::loadView('admin.receipts.payment-receipt', $receiptData);
            $pdf->setPaper('a4', 'portrait');

            $filename = 'payment-receipt-' . $payment->id . '-' . date('Y-m-d') . '.pdf';

            return response()->streamDownload(
                function () use ($pdf) {
                    echo $pdf->output();
                },
                $filename
            );
        } catch (\Exception $e) {
            Log::error('Receipt download failed', [
                'error' => $e->getMessage(),
                'payment_id' => $this->latestPayment->id
            ]);

            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Failed to generate receipt: ' . $e->getMessage()
            ]);
        }
    }

    public function getCustomersProperty()
    {
        $query = Customer::with(['sales' => function ($query) {
            $query->where(function ($q) {
                $q->where('payment_status', 'pending')
                    ->orWhere('payment_status', 'partial');
            });
            
            // Filter sales by user for staff
            if ($this->isStaff()) {
                $query->where('user_id', Auth::id())
                      ->whereIn('sale_type', ['staff', 'pos']);
            }
        }])
            ->where(function($query) {
                $query->whereHas('sales', function ($q) {
                    $q->where(function ($sq) {
                        $sq->where('payment_status', 'pending')
                            ->orWhere('payment_status', 'partial');
                    });
                    
                    // Filter sales by user for staff
                    if ($this->isStaff()) {
                        $q->where('user_id', Auth::id())
                          ->whereIn('sale_type', ['staff', 'pos']);
                    }
                })
                ->orWhere('opening_balance', '>', 0);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10);
            
        return $query;
    }

    public function render()
    {
        return view('livewire.admin.add-customer-receipt', [
            'customers' => $this->customers
        ])->layout($this->layout);
    }
}
