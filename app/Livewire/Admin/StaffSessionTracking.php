<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use App\Livewire\Concerns\WithDynamicLayout;
use Livewire\Attributes\Title;

#[Title('Staff Session & Sales Tracking')]
class StaffSessionTracking extends Component
{
    use WithPagination, WithDynamicLayout;

    public $search = '';
    public $dateFilter = 'today';
    public $selectedStaffId = null;
    public $showSessionDetails = false;

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    public function getTargetDateProperty()
    {
        switch ($this->dateFilter) {
            case 'yesterday':
                return now()->subDay()->toDateString();
            case 'today':
            default:
                return now()->toDateString();
        }
    }

    public function selectStaff($staffId)
    {
        $this->selectedStaffId = $staffId;
        $this->showSessionDetails = true;
    }

    public function closeDetails()
    {
        $this->showSessionDetails = false;
        $this->selectedStaffId = null;
    }

    public function markAsPresent($staffId)
    {
        $user = User::find($staffId);
        if ($user) {
            $user->markAttendance('present', $this->targetDate);
            session()->flash('message', "{$user->name} marked as Present for " . $this->targetDate . ".");
        }
    }

    public function markAsAbsent($staffId)
    {
        $user = User::find($staffId);
        if ($user) {
            $user->markAttendance('absent', $this->targetDate);
            session()->flash('message', "{$user->name} marked as Absent for " . $this->targetDate . ".");
        }
    }

    public function getStaffMembersProperty()
    {
        return User::where('role', 'staff')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->withCount(['sales' => function($query) {
                $this->applyDateFilter($query);
            }])
            ->with(['attendances' => function($query) {
                $query->whereDate('date', $this->targetDate);
            }])
            ->paginate(10);
    }

    public function getSelectedStaffProperty()
    {
        if (!$this->selectedStaffId) return null;
        return User::find($this->selectedStaffId);
    }

    public function getStaffSessionsProperty()
    {
        if (!$this->selectedStaffId) return collect();

        return DB::table('sessions')
            ->where('user_id', $this->selectedStaffId)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function($session) {
                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'last_activity' => \Carbon\Carbon::createFromTimestamp($session->last_activity),
                ];
            });
    }

    public function getStaffSalesProperty()
    {
        if (!$this->selectedStaffId) return collect();

        $query = Sale::where('user_id', $this->selectedStaffId)
            ->with(['customer'])
            ->orderBy('created_at', 'desc');

        $this->applyDateFilter($query);

        return $query->get();
    }

    private function applyDateFilter($query)
    {
        switch ($this->dateFilter) {
            case 'today':
                $query->whereDate('created_at', now()->toDateString());
                break;
            case 'yesterday':
                $query->whereDate('created_at', now()->subDay()->toDateString());
                break;
            case 'this_week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'this_month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
        }
    }

    public function render()
    {
        return view('livewire.admin.staff-session-tracking', [
            'staffMembers' => $this->staffMembers,
            'selectedStaff' => $this->selectedStaff,
            'sessions' => $this->staffSessions,
            'sales' => $this->staffSales,
        ])->layout($this->layout);
    }
}
