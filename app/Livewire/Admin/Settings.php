<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Setting;
use App\Models\User;
use App\Models\StaffPermission;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ProductDetail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("System Settings")]
class Settings extends Component
{
    use WithDynamicLayout;

    public $settings = [];
    public $key;
    public $value;
    public $showModal = false;
    public $isEdit = false;
    public $editingId = null;
    public $deleteId = null;

    // Staff Permission Management
    public $staffMembers = [];
    public $selectedStaffId = null;
    public $selectedStaffName = '';
    public $staffPermissions = [];
    public $showPermissionModal = false;
    public $availablePermissions = [];
    public $permissionCategories = [];

    // Expense Management
    public $expenses = [];
    public $expenseCategories = [];
    public $expenseTypes = [];
    public $expenseCategory = '';
    public $expenseType = '';
    public $expenseAmount = '';
    public $expenseDate = '';
    public $expenseStatus = 'pending';
    public $expenseDescription = '';
    public $showExpenseModal = false;
    public $isEditExpense = false;
    public $editingExpenseId = null;
    public $deleteExpenseId = null;
    
    // New Expense Category Management
    public $showCategoryModal = false;
    public $newExpenseCategory = '';
    public $newExpenseType = '';
    public $customExpenseCategory = '';
    public $isEditCategory = false;
    public $editingCategoryId = null;
    public $deleteCategoryTypeId = null;

    // Sales Bonus Management
    public $bulkRetailCashBonusType = 'percentage';
    public $bulkRetailCashBonusValue = 0;
    public $bulkRetailCreditBonusType = 'percentage';
    public $bulkRetailCreditBonusValue = 0;

    public $bulkWholesaleCashBonusType = 'percentage';
    public $bulkWholesaleCashBonusValue = 0;
    public $bulkWholesaleCreditBonusType = 'percentage';
    public $bulkWholesaleCreditBonusValue = 0;
    
    public $bonusSearch = '';
    public $bonusProducts = [];
    public $editingBonusProductId = null;
    
    public $editBonusRetailCash = 0;
    public $editBonusRetailCashType = 'fixed';
    public $editBonusRetailCredit = 0;
    public $editBonusRetailCreditType = 'fixed';
    public $editBonusWholesaleCash = 0;
    public $editBonusWholesaleCashType = 'fixed';
    public $editBonusWholesaleCredit = 0;
    public $editBonusWholesaleCreditType = 'fixed';
    public $showBonusModal = false;

    protected $listeners = [
        'deleteConfirmed' => 'deleteConfiguration', 
        'deleteExpenseConfirmed' => 'deleteExpense',
        'deleteCategoryTypeConfirmed' => 'deleteCategoryType',
        'applyBulkBonusConfirmed' => 'applyBulkBonus'
    ];

    protected $rules = [
        'key' => 'required|string|max:255|unique:settings,key',
        'value' => 'required|string|max:255',
    ];

    protected $messages = [
        'key.required' => 'The configuration key is required.',
        'key.unique' => 'This configuration key already exists. Please use a different key.',
        'key.max' => 'The configuration key cannot exceed 255 characters.',
        'value.required' => 'The configuration value is required.',
        'value.max' => 'The configuration value cannot exceed 255 characters.',
    ];

    public function mount()
    {
        $this->loadSettings();
        $this->loadStaffMembers();
        $this->loadExpenses();
        $this->loadExpenseCategories();
        $this->availablePermissions = StaffPermission::availablePermissions();
        $this->permissionCategories = StaffPermission::permissionCategories();
        $this->expenseDate = now()->format('Y-m-d');
        
        // Load bulk bonus settings from database
        $this->loadBulkBonusSettings();
    }

    public function loadBulkBonusSettings()
    {
        $this->bulkRetailCashBonusType = Setting::where('key', 'bulk_retail_cash_bonus_type')->value('value') ?? 'percentage';
        $this->bulkRetailCashBonusValue = Setting::where('key', 'bulk_retail_cash_bonus_value')->value('value') ?? 0;
        
        $this->bulkRetailCreditBonusType = Setting::where('key', 'bulk_retail_credit_bonus_type')->value('value') ?? 'percentage';
        $this->bulkRetailCreditBonusValue = Setting::where('key', 'bulk_retail_credit_bonus_value')->value('value') ?? 0;
        
        $this->bulkWholesaleCashBonusType = Setting::where('key', 'bulk_wholesale_cash_bonus_type')->value('value') ?? 'percentage';
        $this->bulkWholesaleCashBonusValue = Setting::where('key', 'bulk_wholesale_cash_bonus_value')->value('value') ?? 0;
        
        $this->bulkWholesaleCreditBonusType = Setting::where('key', 'bulk_wholesale_credit_bonus_type')->value('value') ?? 'percentage';
        $this->bulkWholesaleCreditBonusValue = Setting::where('key', 'bulk_wholesale_credit_bonus_value')->value('value') ?? 0;
    }

    public function loadExpenseCategories()
    {
        $this->expenseCategories = ExpenseCategory::select('expense_category')
            ->distinct()
            ->pluck('expense_category')
            ->toArray();
    }

    public function updatedExpenseCategory()
    {
        // When category changes, load its types
        $this->expenseTypes = ExpenseCategory::where('expense_category', $this->expenseCategory)
            ->pluck('type')
            ->toArray();
        $this->expenseType = ''; // Reset selected type
    }

    public function loadStaffMembers()
    {
        $this->staffMembers = User::where('role', 'staff')->get();
    }

    public function loadSettings()
    {
        $this->settings = Setting::orderBy('created_at', 'desc')->get();
    }

    public function resetForm()
    {
        $this->reset(['key', 'value', 'editingId', 'isEdit', 'deleteId']);
        $this->resetErrorBag();
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->isEdit = false;
    }

    public function openEditModal($id)
    {
        $setting = Setting::findOrFail($id);
        $this->editingId = $id;
        $this->key = $setting->key;
        $this->value = $setting->value;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function saveConfiguration()
    {
        try {
            $this->validate();

            Setting::create([
                'key' => $this->key,
                'value' => $this->value,
                'date' => now(),
            ]);

            $this->closeModal();
            $this->loadSettings();

            $this->js("Swal.fire('Success!', 'Configuration has been added successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to add configuration. Please try again.', 'error')");
        }
    }

    public function updateConfiguration()
    {
        try {
            $this->validate([
                'key' => 'required|string|max:255|unique:settings,key,' . $this->editingId,
                'value' => 'required|string|max:255',
            ]);

            $setting = Setting::findOrFail($this->editingId);
            $setting->update([
                'key' => $this->key,
                'value' => $this->value,
            ]);

            $this->closeModal();
            $this->loadSettings();

            $this->js("Swal.fire('Success!', 'Configuration has been updated successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to update configuration. Please try again.', 'error')");
        }
    }

    public function confirmDelete($id = null)
    {
        $this->deleteId = $id;
        $this->dispatch('swal:confirm-delete', ['id' => $id]);
    }

    public function deleteConfiguration($id = null)
    {
        try {
            $deleteId = $id ?? $this->deleteId;
            
            if (!$deleteId) {
                throw new \Exception('No configuration selected for deletion.');
            }

            $setting = Setting::findOrFail($deleteId);
            $setting->delete();
            $this->loadSettings();

            $this->deleteId = null;

            $this->js("Swal.fire('Success!', 'Configuration has been deleted successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to delete configuration. Please try again.', 'error')");
        }
    }

    // Staff Permission Management Methods
    public function openPermissionModal($staffId)
    {
        $staff = User::findOrFail($staffId);
        $this->selectedStaffId = $staffId;
        $this->selectedStaffName = $staff->name;
        
        // Load current permissions for this staff
        $this->staffPermissions = StaffPermission::getUserPermissions($staffId);
        
        $this->showPermissionModal = true;
    }

    public function closePermissionModal()
    {
        $this->showPermissionModal = false;
        $this->selectedStaffId = null;
        $this->selectedStaffName = '';
        $this->staffPermissions = [];
    }

    public function togglePermission($permissionKey)
    {
        if (in_array($permissionKey, $this->staffPermissions)) {
            // Remove permission
            $this->staffPermissions = array_diff($this->staffPermissions, [$permissionKey]);
        } else {
            // Add permission
            $this->staffPermissions[] = $permissionKey;
        }
    }

    public function savePermissions()
    {
        try {
            if (!$this->selectedStaffId) {
                throw new \Exception('No staff member selected.');
            }

            StaffPermission::syncPermissions($this->selectedStaffId, $this->staffPermissions);

            $this->closePermissionModal();
            $this->loadStaffMembers();

            $this->js("Swal.fire('Success!', 'Staff permissions have been updated successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to update permissions. Please try again.', 'error')");
        }
    }

    public function selectAllPermissions()
    {
        $this->staffPermissions = array_keys($this->availablePermissions);
    }

    public function clearAllPermissions()
    {
        $this->staffPermissions = [];
    }

    // Expense Management Methods
    public function loadExpenses()
    {
        $this->expenses = Expense::orderBy('date', 'desc')->get();
    }

    public function resetExpenseForm()
    {
        $this->reset(['expenseCategory', 'expenseType', 'expenseAmount', 'expenseDate', 'expenseStatus', 'expenseDescription', 'editingExpenseId', 'isEditExpense', 'deleteExpenseId']);
        $this->expenseDate = now()->format('Y-m-d');
        $this->expenseStatus = 'pending';
        $this->resetErrorBag();
    }

    public function openAddExpenseModal()
    {
        $this->resetExpenseForm();
        $this->showExpenseModal = true;
        $this->isEditExpense = false;
    }

    public function openEditExpenseModal($id)
    {
        $expense = Expense::findOrFail($id);
        $this->editingExpenseId = $id;
        $this->expenseCategory = $expense->category;
        
        // Load types for this category
        $this->expenseTypes = ExpenseCategory::where('expense_category', $this->expenseCategory)
            ->pluck('type')
            ->toArray();
            
        $this->expenseType = $expense->expense_type;
        $this->expenseAmount = $expense->amount;
        $this->expenseDate = $expense->date->format('Y-m-d');
        $this->expenseStatus = $expense->status;
        $this->expenseDescription = $expense->description;
        $this->isEditExpense = true;
        $this->showExpenseModal = true;
    }

    public function closeExpenseModal()
    {
        $this->showExpenseModal = false;
        $this->resetExpenseForm();
    }

    public function saveExpense()
    {
        try {
            $this->validate([
                'expenseCategory' => 'required|string|max:255',
                'expenseType' => 'required|string|max:255',
                'expenseAmount' => 'required|numeric|min:0',
                'expenseDate' => 'required|date',
                'expenseStatus' => 'required|in:pending,approved,rejected',
                'expenseDescription' => 'nullable|string|max:1000',
            ]);

            Expense::create([
                'category' => $this->expenseCategory,
                'expense_type' => $this->expenseType,
                'amount' => $this->expenseAmount,
                'date' => $this->expenseDate,
                'status' => $this->expenseStatus,
                'description' => $this->expenseDescription,
            ]);

            $this->closeExpenseModal();
            $this->loadExpenses();

            $this->js("Swal.fire('Success!', 'Expense has been added successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to add expense. Please try again.', 'error')");
        }
    }

    public function updateExpense()
    {
        try {
            $this->validate([
                'expenseCategory' => 'required|string|max:255',
                'expenseType' => 'required|string|max:255',
                'expenseAmount' => 'required|numeric|min:0',
                'expenseDate' => 'required|date',
                'expenseStatus' => 'required|in:pending,approved,rejected',
                'expenseDescription' => 'nullable|string|max:1000',
            ]);

            $expense = Expense::findOrFail($this->editingExpenseId);
            $expense->update([
                'category' => $this->expenseCategory,
                'expense_type' => $this->expenseType,
                'amount' => $this->expenseAmount,
                'date' => $this->expenseDate,
                'status' => $this->expenseStatus,
                'description' => $this->expenseDescription,
            ]);

            $this->closeExpenseModal();
            $this->loadExpenses();

            $this->js("Swal.fire('Success!', 'Expense has been updated successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to update expense. Please try again.', 'error')");
        }
    }

    public function confirmDeleteExpense($id)
    {
        $this->deleteExpenseId = $id;
        $this->dispatch('swal:confirm-delete-expense', ['id' => $id]);
    }

    public function deleteExpense($id = null)
    {
        try {
            $deleteId = $id ?? $this->deleteExpenseId;
            
            if (!$deleteId) {
                throw new \Exception('No expense selected for deletion.');
            }

            $expense = Expense::findOrFail($deleteId);
            $expense->delete();
            $this->loadExpenses();

            $this->deleteExpenseId = null;

            $this->js("Swal.fire('Success!', 'Expense has been deleted successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to delete expense. Please try again.', 'error')");
        }
    }

    // Expense Category Management Methods
    public function openAddCategoryModal()
    {
        $this->reset(['newExpenseCategory', 'newExpenseType', 'customExpenseCategory']);
        $this->showCategoryModal = true;
    }

    public function closeCategoryModal()
    {
        $this->showCategoryModal = false;
        $this->reset(['newExpenseCategory', 'newExpenseType', 'customExpenseCategory']);
        $this->resetErrorBag();
    }

    public function saveCategoryType()
    {
        try {
            $rules = [
                'newExpenseType' => 'required|string|max:255',
            ];

            // If creating new category
            if ($this->newExpenseCategory === '__new__') {
                $rules['customExpenseCategory'] = 'required|string|max:255';
                $this->validate($rules);
                $categoryName = $this->customExpenseCategory;
            } else {
                $rules['newExpenseCategory'] = 'required|string|max:255';
                $this->validate($rules);
                $categoryName = $this->newExpenseCategory;
            }

            // Check if this combination already exists
            $exists = ExpenseCategory::where('expense_category', $categoryName)
                ->where('type', $this->newExpenseType)
                ->exists();

            if ($exists) {
                $this->js("Swal.fire('Warning!', 'This category and type combination already exists.', 'warning')");
                return;
            }

            // Create new expense category/type
            ExpenseCategory::create([
                'expense_category' => $categoryName,
                'type' => $this->newExpenseType,
            ]);

            $this->closeCategoryModal();
            $this->loadExpenseCategories();

            $this->js("Swal.fire('Success!', 'Expense category/type has been added successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to add category/type. Please try again.', 'error')");
        }
    }

    public function confirmDeleteCategoryType($id)
    {
        $this->deleteCategoryTypeId = $id;
        $this->dispatch('swal:confirm-delete-category-type', ['id' => $id]);
    }

    public function deleteCategoryType($id = null)
    {
        try {
            $deleteId = $id ?? $this->deleteCategoryTypeId;
            
            if (!$deleteId) {
                throw new \Exception('No category type selected for deletion.');
            }

            $categoryType = ExpenseCategory::findOrFail($deleteId);
            $categoryType->delete();
            $this->loadExpenseCategories();

            $this->deleteCategoryTypeId = null;

            $this->js("Swal.fire('Success!', 'Expense category/type has been deleted successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to delete category/type. Please try again.', 'error')");
        }
    }

    // Sales Bonus Management Methods
    public function updatedBonusSearch()
    {
        if (empty($this->bonusSearch)) {
            $this->bonusProducts = [];
            return;
        }

        $this->bonusProducts = ProductDetail::where('name', 'like', '%' . $this->bonusSearch . '%')
            ->orWhere('code', 'like', '%' . $this->bonusSearch . '%')
            ->take(10)
            ->get();
    }

    public function confirmBulkUpdate()
    {
        $this->dispatch('swal:confirm-bulk-update');
    }

    public function applyBulkBonus()
    {
        try {
            $this->validate([
                'bulkRetailCashBonusValue' => 'required|numeric|min:0',
                'bulkRetailCreditBonusValue' => 'required|numeric|min:0',
                'bulkWholesaleCashBonusValue' => 'required|numeric|min:0',
                'bulkWholesaleCreditBonusValue' => 'required|numeric|min:0',
            ]);

            // Save these as defaults first
            $this->saveBulkDefaults();

            $products = ProductDetail::with('price')->get();
            $count = 0;

            foreach ($products as $product) {
                $sellingPrice = $product->price->selling_price ?? 0;

                // Retail Cash Bonus
                if ($this->bulkRetailCashBonusType === 'percentage') {
                    $product->retail_cash_bonus = ($sellingPrice * $this->bulkRetailCashBonusValue) / 100;
                } else {
                    $product->retail_cash_bonus = $this->bulkRetailCashBonusValue;
                }

                // Retail Credit Bonus
                if ($this->bulkRetailCreditBonusType === 'percentage') {
                    $product->retail_credit_bonus = ($sellingPrice * $this->bulkRetailCreditBonusValue) / 100;
                } else {
                    $product->retail_credit_bonus = $this->bulkRetailCreditBonusValue;
                }

                // Wholesale Cash Bonus
                if ($this->bulkWholesaleCashBonusType === 'percentage') {
                    $product->wholesale_cash_bonus = ($sellingPrice * $this->bulkWholesaleCashBonusValue) / 100;
                } else {
                    $product->wholesale_cash_bonus = $this->bulkWholesaleCashBonusValue;
                }

                // Wholesale Credit Bonus
                if ($this->bulkWholesaleCreditBonusType === 'percentage') {
                    $product->wholesale_credit_bonus = ($sellingPrice * $this->bulkWholesaleCreditBonusValue) / 100;
                } else {
                    $product->wholesale_credit_bonus = $this->bulkWholesaleCreditBonusValue;
                }

                $product->save();
                $count++;
            }

            $this->js("Swal.fire('Success!', 'Sales bonus updated for {$count} products.', 'success')");
            
            // Re-run search if active
            $this->updatedBonusSearch();

        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to apply bulk bonus. Please check values.', 'error')");
        }
    }

    public function saveBulkDefaults()
    {
        $settings = [
            'bulk_retail_cash_bonus_type' => $this->bulkRetailCashBonusType,
            'bulk_retail_cash_bonus_value' => $this->bulkRetailCashBonusValue,
            'bulk_retail_credit_bonus_type' => $this->bulkRetailCreditBonusType,
            'bulk_retail_credit_bonus_value' => $this->bulkRetailCreditBonusValue,
            'bulk_wholesale_cash_bonus_type' => $this->bulkWholesaleCashBonusType,
            'bulk_wholesale_cash_bonus_value' => $this->bulkWholesaleCashBonusValue,
            'bulk_wholesale_credit_bonus_type' => $this->bulkWholesaleCreditBonusType,
            'bulk_wholesale_credit_bonus_value' => $this->bulkWholesaleCreditBonusValue,
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'date' => now()]
            );
        }
        
        $this->loadSettings(); // Refresh general settings list if visible
    }

    public function updateBonusDefaults()
    {
        try {
            $this->validate([
                'bulkRetailCashBonusValue' => 'required|numeric|min:0',
                'bulkRetailCreditBonusValue' => 'required|numeric|min:0',
                'bulkWholesaleCashBonusValue' => 'required|numeric|min:0',
                'bulkWholesaleCreditBonusValue' => 'required|numeric|min:0',
            ]);

            $this->saveBulkDefaults();
            $this->js("Swal.fire('Success!', 'Bonus defaults updated successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to update defaults.', 'error')");
        }
    }

    public function editProductBonus($id)
    {
        $product = ProductDetail::findOrFail($id);
        $this->editingBonusProductId = $id;
        $this->editBonusRetailCash = $product->retail_cash_bonus;
        $this->editBonusRetailCredit = $product->retail_credit_bonus;
        $this->editBonusWholesaleCash = $product->wholesale_cash_bonus;
        $this->editBonusWholesaleCredit = $product->wholesale_credit_bonus;
        
        // Default to fixed when opening, as we don't store the type in DB
        $this->editBonusRetailCashType = 'fixed';
        $this->editBonusRetailCreditType = 'fixed';
        $this->editBonusWholesaleCashType = 'fixed';
        $this->editBonusWholesaleCreditType = 'fixed';
        
        $this->showBonusModal = true;
    }

    public function updateProductBonus()
    {
        try {
            $this->validate([
                'editBonusRetailCash' => 'required|numeric|min:0',
                'editBonusRetailCredit' => 'required|numeric|min:0',
                'editBonusWholesaleCash' => 'required|numeric|min:0',
                'editBonusWholesaleCredit' => 'required|numeric|min:0',
            ]);

            $product = ProductDetail::with('price')->findOrFail($this->editingBonusProductId);
            $sellingPrice = $product->price->selling_price ?? 0;

            // Retail Cash
            if ($this->editBonusRetailCashType === 'percentage') {
                $product->retail_cash_bonus = ($sellingPrice * $this->editBonusRetailCash) / 100;
            } else {
                $product->retail_cash_bonus = $this->editBonusRetailCash;
            }

            // Retail Credit
            if ($this->editBonusRetailCreditType === 'percentage') {
                $product->retail_credit_bonus = ($sellingPrice * $this->editBonusRetailCredit) / 100;
            } else {
                $product->retail_credit_bonus = $this->editBonusRetailCredit;
            }

            // Wholesale Cash
            if ($this->editBonusWholesaleCashType === 'percentage') {
                $product->wholesale_cash_bonus = ($sellingPrice * $this->editBonusWholesaleCash) / 100;
            } else {
                $product->wholesale_cash_bonus = $this->editBonusWholesaleCash;
            }

            // Wholesale Credit
            if ($this->editBonusWholesaleCreditType === 'percentage') {
                $product->wholesale_credit_bonus = ($sellingPrice * $this->editBonusWholesaleCredit) / 100;
            } else {
                $product->wholesale_credit_bonus = $this->editBonusWholesaleCredit;
            }

            $product->save();

            $this->closeBonusModal();
            
            // Update the list
            $this->updatedBonusSearch();

            $this->js("Swal.fire('Success!', 'Product bonus updated successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to update product bonus. Error: {$e->getMessage()}', 'error')");
        }
    }

    public function closeBonusModal()
    {
        $this->showBonusModal = false;
        $this->editingBonusProductId = null;
        $this->editBonusRetailCash = 0;
        $this->editBonusRetailCredit = 0;
        $this->editBonusWholesaleCash = 0;
        $this->editBonusWholesaleCredit = 0;
        
        $this->editBonusRetailCashType = 'fixed';
        $this->editBonusRetailCreditType = 'fixed';
        $this->editBonusWholesaleCashType = 'fixed';
        $this->editBonusWholesaleCreditType = 'fixed';
    }

    public function render()
    {
        return view('livewire.admin.settings')->layout($this->layout);
    }
}