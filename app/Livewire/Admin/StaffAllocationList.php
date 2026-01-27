<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\StaffProduct;
use Illuminate\Support\Facades\DB;
use App\Livewire\Concerns\WithDynamicLayout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.admin')]
#[Title('Staff Allocation List')]

class StaffAllocationList extends Component
{
    use WithPagination, WithDynamicLayout;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $staffAllocations = User::where('role', 'staff')
            ->where(function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('contact', 'like', '%' . $this->search . '%');
            })
            ->withCount(['staffProducts as total_allocated' => function($query) {
                $query->select(DB::raw('COALESCE(SUM(quantity), 0)'));
            }])
            ->withCount(['staffProducts as total_sold' => function($query) {
                $query->select(DB::raw('COALESCE(SUM(sold_quantity), 0)'));
            }])
            ->with(['staffProducts' => function($query) {
                $query->select('staff_id', DB::raw('COALESCE(SUM(total_value), 0) as total_value'))
                    ->groupBy('staff_id');
            }])
            ->paginate($this->perPage);

        // Calculate available quantity for each staff
        foreach ($staffAllocations as $staff) {
            $staff->total_available = $staff->total_allocated - $staff->total_sold;
            $staff->total_value = $staff->staffProducts->sum('total_value');
        }

        return view('livewire.admin.staff-allocation-list', [
            'staffAllocations' => $staffAllocations
        ]);
    }
}
