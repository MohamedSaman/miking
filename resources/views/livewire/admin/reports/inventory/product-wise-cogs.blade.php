{{-- Product Wise COGS Report --}}
<div>
    <h6 class="fw-bold mb-3">
        <i class="bi bi-calculator text-primary me-2"></i>Product Wise - COGS Method
    </h6>

    @php $data = collect($reportData); @endphp
    @if($data->count() > 0)
    @php
        $totalInventoryValue = $data->sum('inventory_value');
        $totalStock = $data->sum('available_stock');
    @endphp
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card primary">
                <div class="stat-value">{{ $data->count() }}</div>
                <div class="stat-label">Products in Stock</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card success">
                <div class="stat-value">{{ number_format($totalStock) }}</div>
                <div class="stat-label">Total Units</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card warning">
                <div class="stat-value">Rs. {{ number_format($totalInventoryValue, 2) }}</div>
                <div class="stat-label">Total Inventory Value (COGS)</div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>Product Code</th>
                    <th>Product Name</th>
                    <th>Brand</th>
                    <th>Category</th>
                    <th>Cost Price</th>
                    <th>Available Stock</th>
                    <th>Inventory Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr>
                    <td><span class="badge bg-light text-dark">{{ $item['product']->code }}</span></td>
                    <td>{{ $item['product']->name }}</td>
                    <td>{{ $item['product']->brand->brand_name ?? '-' }}</td>
                    <td>{{ $item['product']->category->name ?? '-' }}</td>
                    <td>Rs. {{ number_format($item['cost_price'], 2) }}</td>
                    <td class="fw-bold">{{ number_format($item['available_stock']) }}</td>
                    <td class="fw-bold text-success">Rs. {{ number_format($item['inventory_value'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td colspan="5" class="fw-bold">Total Inventory Valuation</td>
                    <td class="fw-bold">{{ number_format($totalStock) }}</td>
                    <td class="fw-bold text-success">Rs. {{ number_format($totalInventoryValue, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="alert alert-info text-center py-4">
        <i class="bi bi-info-circle display-6 d-block mb-2"></i>
        No inventory data available.
    </div>
    @endif
</div>
