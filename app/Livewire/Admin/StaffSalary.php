<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\Salary;
use App\Models\StaffBonus;
use App\Models\StaffAdvance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.admin')]
#[Title('Staff Salary Management')]
class StaffSalary extends Component
{
    use WithPagination;

    public $searchStaff = '';
    public $selectedMonth = '';
    public $perPage = 10;
    
    // Advance salary
    public $showAdvanceModal = false;
    public $advanceStaffId = null;
    public $advanceStaffName = '';
    public $advanceAmount = 0;
    public $advanceReason = '';
    public $advanceSalaryRecords = [];

    public function mount()
    {
        // Set selected month to current month
        $this->selectedMonth = now()->format('Y-m');
        $this->loadAdvanceSalaries();
    }

    public function loadAdvanceSalaries()
    {
        $this->advanceSalaryRecords = StaffAdvance::where('salary_month', $this->selectedMonth)
            ->with('staff')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updatedSelectedMonth()
    {
        $this->loadAdvanceSalaries();
    }

    #[\Livewire\Attributes\Computed]
    public function staffMembers()
    {
        $query = User::where('role', 'staff')
            ->with(['userDetail']);

        if ($this->searchStaff) {
            $searchTerm = '%' . $this->searchStaff . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm);
            });
        }

        return $query->orderBy('name')->paginate($this->perPage);
    }

    #[\Livewire\Attributes\Computed]
    public function staffSalaries()
    {
        $salaryMonth = $this->selectedMonth . '-01';
        
        $salaries = User::where('role', 'staff')
            ->with(['userDetail'])
            ->get()
            ->map(function($staff) use ($salaryMonth) {
                $userDetail = $staff->userDetail;
                
                // Handle null userDetail
                $basicSalary = $userDetail ? ($userDetail->basic_salary ?? 0) : 0;
                $allowance = 0;
                
                if ($userDetail && $userDetail->allowance) {
                    // Decode JSON if it's a string
                    $allowanceData = is_string($userDetail->allowance) 
                        ? json_decode($userDetail->allowance, true) 
                        : $userDetail->allowance;
                    
                    // Calculate allowance sum
                    $allowance = is_array($allowanceData) 
                        ? array_sum($allowanceData) 
                        : (is_numeric($allowanceData) ? $allowanceData : 0);
                }
                
                // Calculate sales bonus for this month
                $bonusAmount = $this->calculateMonthlyBonus($staff->id, $salaryMonth);
                
                // Get total advances for this staff in this month from staff_advances table
                $totalAdvance = StaffAdvance::where('staff_id', $staff->id)
                    ->where('salary_month', $this->selectedMonth)
                    ->where('status', 'paid')
                    ->sum('amount');
                
                // Get existing salary record
                $salaryRecord = Salary::where('user_id', $staff->id)
                    ->whereDate('salary_month', $salaryMonth)
                    ->first();
                
                // Count advances for this staff
                $advanceCount = StaffAdvance::where('staff_id', $staff->id)
                    ->where('salary_month', $this->selectedMonth)
                    ->where('status', 'paid')
                    ->count();
                
                $totalSalary = $basicSalary + $allowance + $bonusAmount - $totalAdvance;
                
                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'email' => $staff->email,
                    'contact' => $staff->contact,
                    'basic_salary' => $basicSalary,
                    'allowance' => $allowance,
                    'bonus' => $bonusAmount,
                    'advance_salary' => $totalAdvance,
                    'advance_count' => $advanceCount,
                    'total_salary' => $totalSalary,
                    'salary_record_id' => $salaryRecord->salary_id ?? null,
                    'payment_status' => $salaryRecord->payment_status ?? 'pending',
                ];
            });

        return collect($salaries);
    }

    public function calculateMonthlyBonus($staffId, $salaryMonth)
    {
        // Get all bonuses for this staff in the given month
        $startDate = $salaryMonth;
        $endDate = date('Y-m-t', strtotime($salaryMonth)); // Last day of month
        
        $totalBonus = StaffBonus::where('staff_id', $staffId)
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->sum('total_bonus');
        
        return $totalBonus ?? 0;
    }

    public function openAdvanceModal($staffId, $staffName = '')
    {
        $this->advanceStaffId = $staffId;
        $this->advanceStaffName = $staffName ?: (User::find($staffId)->name ?? 'Staff');
        $this->advanceAmount = 0;
        $this->advanceReason = '';
        $this->showAdvanceModal = true;
    }

    public function closeAdvanceModal()
    {
        $this->showAdvanceModal = false;
        $this->advanceStaffId = null;
        $this->advanceStaffName = '';
        $this->advanceAmount = 0;
        $this->advanceReason = '';
    }

    public function processAdvanceSalary()
    {
        $this->validate([
            'advanceAmount' => 'required|numeric|min:1',
            'advanceReason' => 'nullable|string|max:255',
        ]);

        try {
            // Capture amount before closing modal (which resets it)
            $amount = $this->advanceAmount;
            
            // Create advance record in staff_advances table
            StaffAdvance::create([
                'staff_id' => $this->advanceStaffId,
                'advance_date' => now()->toDateString(),
                'salary_month' => $this->selectedMonth,
                'amount' => $amount,
                'reason' => $this->advanceReason,
                'status' => 'paid',
                'created_by' => Auth::id(),
            ]);
            
            $this->loadAdvanceSalaries();
            $this->closeAdvanceModal();
            $this->js("Swal.fire('success', 'Advance salary of Rs." . number_format($amount, 2) . " processed successfully!', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('error', 'Failed to process advance salary: " . $e->getMessage() . "', 'error')");
        }
    }

    public function confirmCancelAdvance($advanceId)
    {
        $this->js("
            Swal.fire({
                title: 'Cancel Advance Payment?',
                text: 'Are you sure you want to cancel this advance payment?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Cancel It',
                cancelButtonText: 'No, Keep It'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('cancel-advance-payment', { advanceId: " . $advanceId . " });
                }
            });
        ");
    }

    #[\Livewire\Attributes\On('cancel-advance-payment')]
    public function cancelAdvance($advanceId)
    {
        try {
            $advance = StaffAdvance::find($advanceId);
            if ($advance) {
                $advance->update(['status' => 'cancelled']);
                $this->loadAdvanceSalaries();
                $this->js("Swal.fire('success', 'Advance cancelled successfully!', 'success')");
            }
        } catch (\Exception $e) {
            $this->js("Swal.fire('error', 'Failed to cancel advance: " . $e->getMessage() . "', 'error')");
        }
    }

    public function processSalaryPayment($staffId)
    {
        try {
            $salaryMonth = $this->selectedMonth . '-01';
            
            $salaryRecord = Salary::where('user_id', $staffId)
                ->whereDate('salary_month', $salaryMonth)
                ->first();
            
            if (!$salaryRecord) {
                $this->js("Swal.fire('error', 'No salary record found for this month!', 'error')");
                return;
            }
            
            $salaryRecord->update(['payment_status' => 'paid']);
            
            $this->js("Swal.fire('success', 'Salary payment marked as paid!', 'success')");
            $this->dispatch('refresh-page');
        } catch (\Exception $e) {
            $this->js("Swal.fire('error', 'Failed to process payment: " . $e->getMessage() . "', 'error')");
        }
    }

    public function render()
    {
        return view('livewire.admin.staff-salary', [
            'staffList' => $this->staffSalaries,
            'staffMembers' => $this->staffMembers,
        ]);
    }
}
