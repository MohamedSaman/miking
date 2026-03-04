<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Livewire\Concerns\WithDynamicLayout;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrder;
use App\Models\ProductDetail;
use App\Models\SaleItem;
use App\Models\ProductSupplier;
use Illuminate\Support\Facades\DB;

#[Title("Purchase History")]
class PurchaseHistory extends Component
{
    use WithDynamicLayout, WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public string $search       = '';
    public string $supplierFilter = '';
    public string $statusFilter   = '';
    public string $dateFrom       = '';
    public string $dateTo         = '';

    // Per-page
    public int $perPage = 15;

    // Sales modal
    public ?int    $modalProductId   = null;
    public string  $modalProductName = '';
    public array   $productSales     = [];

    // Summary stats
    public int   $totalOrders    = 0;
    public int   $totalItems     = 0;
    public float $totalCostValue = 0;

    public array $suppliers = [];

    public function mount(): void
    {
        $this->suppliers = ProductSupplier::orderBy('name')->get(['id', 'name'])->toArray();
        $this->dateFrom  = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo    = now()->format('Y-m-d');
        $this->refreshSummary();
    }

    // Reset pagination whenever a filter changes
    public function updatedSearch():       void { $this->resetPage(); }
    public function updatedSupplierFilter():void { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }
    public function updatedDateFrom():     void { $this->resetPage(); $this->refreshSummary(); }
    public function updatedDateTo():       void { $this->resetPage(); $this->refreshSummary(); }
    public function updatedPerPage():      void { $this->resetPage(); }

    /** Recalculate the header summary cards. */
    private function refreshSummary(): void
    {
        $query = PurchaseOrder::query()
            ->when($this->dateFrom, fn($q) => $q->whereDate('order_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('order_date', '<=', $this->dateTo));

        $this->totalOrders = $query->count();

        $itemQuery = PurchaseOrderItem::query()
            ->join('purchase_orders', 'purchase_order_items.order_id', '=', 'purchase_orders.id')
            ->when($this->dateFrom, fn($q) => $q->whereDate('purchase_orders.order_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('purchase_orders.order_date', '<=', $this->dateTo));

        $this->totalItems     = $itemQuery->count();
        $this->totalCostValue = (float) $itemQuery->sum(DB::raw('purchase_order_items.received_quantity * purchase_order_items.unit_price'));
    }

    /** Build the paginated purchase-item query. */
    private function buildQuery()
    {
        return PurchaseOrderItem::query()
            ->with(['order.supplier', 'product'])
            ->join('purchase_orders', 'purchase_order_items.order_id', '=', 'purchase_orders.id')
            ->join('product_details', 'purchase_order_items.product_id', '=', 'product_details.id')
            ->leftJoin('product_suppliers', 'purchase_orders.supplier_id', '=', 'product_suppliers.id')
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('product_details.name', 'like', '%' . $this->search . '%')
                       ->orWhere('product_details.code', 'like', '%' . $this->search . '%')
                       ->orWhere('product_details.model', 'like', '%' . $this->search . '%')
                       ->orWhere('purchase_orders.order_code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->supplierFilter, fn($q) => $q->where('purchase_orders.supplier_id', $this->supplierFilter))
            ->when($this->statusFilter,   fn($q) => $q->where('purchase_orders.status', $this->statusFilter))
            ->when($this->dateFrom, fn($q) => $q->whereDate('purchase_orders.order_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('purchase_orders.order_date', '<=', $this->dateTo))
            ->select(
                'purchase_order_items.*',
                'purchase_orders.order_code',
                'purchase_orders.order_date',
                'purchase_orders.received_date',
                'purchase_orders.status as order_status',
                'product_details.name as product_name',
                'product_details.code as product_code',
                'product_details.model as product_model',
                'product_suppliers.name as supplier_name',
            )
            ->orderBy('purchase_orders.order_date', 'desc')
            ->orderBy('purchase_order_items.id', 'desc');
    }

    /**
     * Open the sales-tracking modal for a given product.
     */
    public function viewProductSales(int $productId, string $productName): void
    {
        $this->modalProductId   = $productId;
        $this->modalProductName = $productName;

        $this->productSales = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sale_items.product_id', $productId)
            ->whereNotIn('sales.status', ['cancelled', 'rejected', 'refunded'])
            ->select(
                'sales.invoice_number',
                'sales.created_at as sale_date',
                'sales.payment_status',
                'sales.sale_price_type',
                'sale_items.quantity',
                'sale_items.unit_price',
                'sale_items.total',
                'sale_items.discount_per_unit',
            )
            ->orderBy('sales.created_at', 'desc')
            ->get()
            ->toArray();

        $this->dispatch('open-sales-modal');
    }

    public function clearFilters(): void
    {
        $this->search         = '';
        $this->supplierFilter = '';
        $this->statusFilter   = '';
        $this->dateFrom       = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo         = now()->format('Y-m-d');
        $this->resetPage();
        $this->refreshSummary();
    }

    public function render()
    {
        $items = $this->buildQuery()->paginate($this->perPage);

        return view('livewire.admin.purchase-history', [
            'items' => $items,
        ])->layout($this->layout);
    }
}
