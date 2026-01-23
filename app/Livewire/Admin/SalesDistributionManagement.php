<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SalesDistribution;
use App\Models\User;
use App\Models\StaffPermission;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\ProductDetail;
use App\Models\StaffProduct;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("Sales Distribution")]
class SalesDistributionManagement extends Component
{
    use WithPagination, WithDynamicLayout;

    protected $paginationTheme = 'bootstrap';

    // Search and Filter
    public $search = '';
    public $statusFilter = '';
    public $dateFrom = '';
    public $dateTo = '';

    // Form Fields
    public $distributionId;
    public $staff_id;
    public $staff_name;
    public $dispatch_location;
    public $distance_km;
    public $travel_expense;
    public $handover_to;
    public $status = 'pending';
    public $description;
    public $distribution_date;
    public $products = []; // Array of products

    // Product search
    public $search_results = [];
    public $active_product_index = null;
    
    // Modal states
    public $showFormModal = false;
    public $isEdit = false;

    public function mount()
    {
        if ($this->isStaff()) {
            if (!StaffPermission::hasPermission(Auth::id(), 'sales_distribution_access')) {
                return redirect()->route('staff.dashboard')->with('error', 'Unauthorized access.');
            }
            $this->staff_id = Auth::id();
            $this->staff_name = Auth::user()->name;
        }
        $this->distribution_date = date('Y-m-d');
        $this->products = [['name' => '', 'quantity' => '']];
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function addProductRow()
    {
        $this->products[] = ['name' => '', 'quantity' => ''];
    }

    public function removeProductRow($index)
    {
        unset($this->products[$index]);
        $this->products = array_values($this->products);
        $this->active_product_index = null;
        $this->search_results = [];
    }

    public function searchProduct($index)
    {
        $this->active_product_index = $index;
        $query = $this->products[$index]['name'];

        if (strlen($query) >= 2) {
            $this->search_results = ProductDetail::with('stock')
                ->where(function($q) use ($query) {
                    $q->where('name', 'like', '%' . $query . '%')
                      ->orWhere('code', 'like', '%' . $query . '%')
                      ->orWhere('model', 'like', '%' . $query . '%');
                })
                ->limit(10)
                ->get()
                ->map(function($product) {
                    $availableInfo = $this->getAvailableStockInfo($product);
                    return [
                        'id' => $product->id,
                        'code' => $product->code,
                        'name' => $product->name,
                        'model' => $product->model,
                        'available_qty' => $availableInfo['quantity'],
                        'unit' => $product->unit ?? 'pcs',
                        'source' => $availableInfo['source'] // 'Stock' or 'My Allocation'
                    ];
                })
                ->toArray();
        } else {
            $this->search_results = [];
        }
    }

    public function selectProduct($index, $productName, $productId)
    {
        $product = ProductDetail::with('stock')->find($productId);
        $availableInfo = $this->getAvailableStockInfo($product);
        
        $this->products[$index] = [
            'name' => $productName,
            'quantity' => '',
            'max_qty' => $availableInfo['quantity'],
            'product_id' => $productId
        ];
        
        $this->search_results = [];
        $this->active_product_index = null;
    }

    private function getAvailableStockInfo($product)
    {
        if ($this->isStaff()) {
            // Check Staff Allocated Stock
            $staffProduct = StaffProduct::where('staff_id', Auth::id())
                ->where('product_id', $product->id)
                ->first();
            
            return [
                'quantity' => $staffProduct ? ($staffProduct->quantity - $staffProduct->sold_quantity) : 0,
                'source' => 'My Stock'
            ];
        }

        // Admin sees Global Stock
        return [
            'quantity' => $product->stock ? $product->stock->available_stock : 0,
            'source' => 'Global Stock'
        ];
    }

    public function updatedProducts($value, $key)
    {
        // Parse the key (e.g., "0.quantity")
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'quantity') {
            $index = $parts[0];
            $quantity = (float) $value;
            $maxQty = $this->products[$index]['max_qty'] ?? 0;

            if ($maxQty > 0 && $quantity > $maxQty) {
                $this->addError("products.$index.quantity", "Quantity cannot exceed available stock ($maxQty).");
            } else {
                $this->resetErrorBag("products.$index.quantity");
            }
        }
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->showFormModal = true;
        $this->isEdit = false;
        if ($this->isStaff()) {
            $this->staff_id = Auth::id();
            $this->staff_name = Auth::user()->name;
        }
    }

    public function openEditModal($id)
    {
        $record = SalesDistribution::findOrFail($id);
        
        // Authorization check for staff
        if ($this->isStaff() && $record->staff_id !== Auth::id()) {
            $this->js("swal.fire('Error!', 'You can only edit your own records.', 'error')");
            return;
        }

        $this->distributionId = $record->id;
        $this->staff_id = $record->staff_id;
        $this->staff_name = $record->staff_name;
        $this->dispatch_location = $record->dispatch_location;
        $this->distance_km = $record->distance_km;
        $this->travel_expense = $record->travel_expense;
        $this->handover_to = $record->handover_to;
        $this->status = $record->status;
        $this->description = $record->description;
        $this->distribution_date = $record->distribution_date->format('Y-m-d');
        $this->products = $record->products ?: [['name' => '', 'quantity' => '']];

        $this->showFormModal = true;
        $this->isEdit = true;
    }

    public function resetForm()
    {
        $this->reset([
            'distributionId', 'dispatch_location', 'distance_km', 
            'travel_expense', 'handover_to', 'status', 'description'
        ]);
        $this->distribution_date = date('Y-m-d');
        $this->status = 'pending';
        $this->products = [['name' => '', 'quantity' => '']];
        $this->resetErrorBag();
    }

    public function save()
    {
        $rules = [
            'staff_name' => 'required|string|max:255',
            'dispatch_location' => 'required|string|max:255',
            'distance_km' => 'required|numeric|min:0',
            'travel_expense' => 'required|numeric|min:0',
            'handover_to' => 'required|string|max:255',
            'distribution_date' => 'required|date',
            'products.*.name' => 'required|string',
            'products.*.quantity' => 'required|numeric|min:0.01',
        ];

        // Additional validation for max quantity
        foreach ($this->products as $index => $product) {
            if (isset($product['max_qty']) && $product['max_qty'] > 0) {
                $rules["products.$index.quantity"] = "required|numeric|min:0.01|max:{$product['max_qty']}";
            }
        }

        if ($this->isAdmin()) {
            $rules['status'] = 'required|in:pending,completed,approved';
        }

        $this->validate($rules);

        $data = [
            'staff_id' => $this->staff_id ?: Auth::id(),
            'staff_name' => $this->staff_name,
            'dispatch_location' => $this->dispatch_location,
            'distance_km' => $this->distance_km,
            'travel_expense' => $this->travel_expense,
            'handover_to' => $this->handover_to,
            'description' => $this->description,
            'distribution_date' => $this->distribution_date,
            'products' => $this->products,
            'created_by' => Auth::id(),
        ];

        if ($this->isAdmin()) {
            $data['status'] = $this->status;
        }

        if ($this->isEdit) {
            $record = SalesDistribution::findOrFail($this->distributionId);
            
            // Authorization check for staff
            if ($this->isStaff() && $record->staff_id !== Auth::id()) {
                $this->js("swal.fire('Error!', 'Unauthorized action.', 'error')");
                return;
            }

            $record->update($data);
            $msg = 'Record updated successfully.';
        } else {
            SalesDistribution::create($data);
            $msg = 'Record created successfully.';
        }

        $this->showFormModal = false;
        $this->resetForm();
        $this->js("swal.fire('Success!', '$msg', 'success')");
    }

    public function confirmDelete($id)
    {
        $this->js("
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    \$wire.delete($id);
                }
            });
        ");
    }

    public function delete($id)
    {
        // Admin only delete or staff can delete their own? 
        // Requirement says Admin: "Can view, add, edit, and delete all sales distribution records"
        // Staff: "Can view, add, and edit ONLY their own records" (No delete mentioned for staff)
        
        if ($this->isStaff()) {
            $this->js("swal.fire('Error!', 'Staff cannot delete records.', 'error')");
            return;
        }

        SalesDistribution::findOrFail($id)->delete();
        $this->js("Swal.fire('Deleted!', 'Record has been deleted successfully.', 'success')");
    }

    public function approve($id)
    {
        if (!$this->isAdmin()) {
            $this->js("swal.fire('Error!', 'Only admins can approve records.', 'error')");
            return;
        }

        $record = SalesDistribution::findOrFail($id);
        $record->update(['status' => 'approved']);
        $this->js("swal.fire('Approved!', 'Record has been approved.', 'success')");
    }

    public function render()
    {
        $query = SalesDistribution::query();

        // Role-based scoping
        if ($this->isStaff()) {
            $query->where('staff_id', Auth::id());
        }

        // Search
        if ($this->search) {
            $query->where(function($q) {
                $q->where('staff_name', 'like', '%' . $this->search . '%')
                  ->orWhere('dispatch_location', 'like', '%' . $this->search . '%')
                  ->orWhere('handover_to', 'like', '%' . $this->search . '%');
            });
        }

        // Filters
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        if ($this->dateFrom) {
            $query->whereDate('distribution_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('distribution_date', '<=', $this->dateTo);
        }

        $distributions = $query->latest('distribution_date')->paginate(10);

        // For Admin staff list (if we want to allow admin to assign/select staff)
        $staffMembers = $this->isAdmin() ? User::where('role', 'staff')->get() : [];

        return view('livewire.admin.sales-distribution-management', [
            'distributions' => $distributions,
            'staffMembers' => $staffMembers
        ])->layout($this->layout);
    }
}
