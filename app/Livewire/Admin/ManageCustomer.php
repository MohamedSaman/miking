<?php

namespace App\Livewire\Admin;

use App\Models\Customer;
use Livewire\Component;
use Exception;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Livewire\Concerns\WithDynamicLayout;
use Illuminate\Support\Facades\Auth;

#[Title('Manage Customer')]
class ManageCustomer extends Component
{
    use WithDynamicLayout;

    public $name;
    public $contactNumber;
    public $address;
    public $email;
    public $customerType;
    public $businessName;
    public $openingBalance = 0;
    public $overpaidAmount = 0;
    public $openingRemarks;

    public $editCustomerId;
    public $editName;
    public $editContactNumber;
    public $editAddress;
    public $editEmail;
    public $editCustomerType;
    public $editBusinessName;
    public $editOpeningBalance = 0;
    public $editOverpaidAmount = 0;
    public $editOpeningRemarks;

    public $deleteId;
    public $showEditModal = false;
    public $showCreateModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false;
    public $viewCustomerDetail = [];
    public $perPage = 10;

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $customers = Customer::latest()->paginate($this->perPage);
        return view('livewire.admin.manage-customer', [
            'customers' => $customers,
        ])->layout($this->layout);
    }

    /** ----------------------------
     * Create Customer
     * ---------------------------- */
    public function createCustomer()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function resetForm()
    {
        $this->reset([
            'name',
            'contactNumber',
            'address',
            'email',
            'customerType',
            'businessName',
            'openingBalance',
            'overpaidAmount',
            'openingRemarks',
            'editCustomerId',
            'editName',
            'editContactNumber',
            'editAddress',
            'editEmail',
            'editCustomerType',
            'editBusinessName',
            'editOpeningBalance',
            'editOverpaidAmount',
            'editOpeningRemarks'
        ]);
        $this->resetErrorBag();
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showViewModal = false;
        $this->resetForm();
    }

    /** ----------------------------
     * View Customer Details
     * ---------------------------- */
    public function viewDetails($id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            $this->js("Swal.fire('Error!', 'Customer Not Found', 'error')");
            return;
        }

        $this->viewCustomerDetail = [
            'name' => $customer->name,
            'business_name' => $customer->business_name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'type' => $customer->type,
            'address' => $customer->address,
            'opening_balance' => $customer->opening_balance,
            'overpaid_amount' => $customer->overpaid_amount,
            'opening_remarks' => $customer->opening_remarks,
            'created_at' => $customer->created_at,
            'updated_at' => $customer->updated_at,
        ];

        $this->showViewModal = true;
    }

    public function saveCustomer()
    {
        $this->validate([
            'name' => 'required',
            'customerType' => 'required',
            'contactNumber' => 'nullable | max:10',
            'address' => 'nullable',
            'email' => 'nullable|email|unique:customers,email',
            'businessName' => 'nullable',
            'openingBalance' => 'nullable|numeric',
            'overpaidAmount' => 'nullable|numeric',
            'openingRemarks' => 'nullable|string',
        ]);

        try {
            Customer::create([
                'name' => $this->name,
                'phone' => $this->contactNumber,
                'address' => $this->address,
                'email' => $this->email,
                'type' => $this->customerType,
                'business_name' => $this->businessName,
                'opening_balance' => $this->openingBalance ?? 0,
                'overpaid_amount' => $this->overpaidAmount ?? 0,
                'opening_remarks' => $this->openingRemarks,
                'user_id' => Auth::id(),
            ]);

            $this->js("Swal.fire('Success!', 'Customer Created Successfully', 'success')");
            $this->closeModal();
        } catch (Exception $e) {
            $this->js("Swal.fire('Error!', '" . $e->getMessage() . "', 'error')");
        }
    }

    /** ----------------------------
     * Edit Customer
     * ---------------------------- */
    public function editCustomer($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            $this->js("Swal.fire('Error!', 'Customer not found.', 'error')");
            return;
        }

        $this->editCustomerId = $customer->id;
        $this->editName = $customer->name;
        $this->editContactNumber = $customer->phone;
        $this->editBusinessName = $customer->business_name;
        $this->editCustomerType = $customer->type;
        $this->editAddress = $customer->address;
        $this->editEmail = $customer->email;
        $this->editOpeningBalance = $customer->opening_balance;
        $this->editOverpaidAmount = $customer->overpaid_amount;
        $this->editOpeningRemarks = $customer->opening_remarks;

        $this->showEditModal = true;
    }

    public function updateCustomer()
    {
        $this->validate([
            'editName' => 'required',
            'editCustomerType' => 'required',
            'editBusinessName' => 'nullable',
            'editContactNumber' => 'nullable | max:10',
            'editAddress' => 'nullable',
            'editEmail' => 'nullable|email|unique:customers,email,' . $this->editCustomerId,
            'editOpeningBalance' => 'nullable|numeric',
            'editOverpaidAmount' => 'nullable|numeric',
            'editOpeningRemarks' => 'nullable|string',
        ]);

        try {
            $customer = Customer::find($this->editCustomerId);
            if (!$customer) {
                $this->js("Swal.fire('Error!', 'Customer not found.', 'error')");
                return;
            }

            $customer->update([
                'name' => $this->editName,
                'phone' => $this->editContactNumber,
                'business_name' => $this->editBusinessName,
                'type' => $this->editCustomerType,
                'address' => $this->editAddress,
                'email' => $this->editEmail,
                'opening_balance' => $this->editOpeningBalance ?? 0,
                'overpaid_amount' => $this->editOverpaidAmount ?? 0,
                'opening_remarks' => $this->editOpeningRemarks,
            ]);

            $this->js("Swal.fire('Success!', 'Customer Updated Successfully', 'success')");
            $this->closeModal();
        } catch (Exception $e) {
            $this->js("Swal.fire('Error!', '" . $e->getMessage() . "', 'error')");
        }
    }

    /** ----------------------------
     * Delete Customer
     * ---------------------------- */
    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function deleteCustomer()
    {
        try {
            Customer::where('id', $this->deleteId)->delete();
            $this->js("Swal.fire('Success!', 'Customer deleted successfully.', 'success')");
            $this->dispatch('refreshPage');
            $this->cancelDelete();
        } catch (Exception $e) {
            $this->js("Swal.fire('Error!', '" . $e->getMessage() . "', 'error')");
        }
    }
}
