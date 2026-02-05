<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\StaffProduct;
use App\Livewire\Concerns\WithDynamicLayout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.admin')]
#[Title('Allocated Products')]

class StaffAllocatedProducts extends Component
{
    use WithPagination, WithDynamicLayout;

    protected $paginationTheme = 'bootstrap';

    public $staffId;
    public $staffName;
    public $search = '';
    public $statusFilter = 'all';
    public $perPage = 10;

    public function mount($staffId)
    {
        $this->staffId = $staffId;
        $staff = User::find($staffId);
        $this->staffName = $staff ? $staff->name : 'Unknown Staff';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = StaffProduct::where('staff_id', $this->staffId)
            ->with(['product']);

        // Search filter
        if ($this->search) {
            $query->whereHas('product', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        // Status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $allocatedProducts = $query->paginate($this->perPage);

        return view('livewire.admin.staff-allocated-products', [
            'allocatedProducts' => $allocatedProducts
        ]);
    }
}
