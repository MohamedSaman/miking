{{-- Report Content Router --}}
@switch($selectedReport)
    {{-- Sales Reports --}}
    @case('transaction-history')
        @include('livewire.admin.reports.sales.transaction-history')
        @break

    @case('sales-payment')
        @include('livewire.admin.reports.sales.sales-payment')
        @break

    @case('product-report')
        @include('livewire.admin.reports.sales.product-report')
        @break

    @case('sales-by-staff')
        @include('livewire.admin.reports.sales.sales-by-staff')
        @break

    @case('sales-by-product')
        @include('livewire.admin.reports.sales.sales-by-product')
        @break

    @case('invoice-aging')
        @include('livewire.admin.reports.sales.invoice-aging')
        @break

    @case('detailed-sales')
        @include('livewire.admin.reports.sales.detailed-sales')
        @break

    @case('sales-return')
        @include('livewire.admin.reports.sales.sales-return')
        @break

    {{-- Purchases Reports --}}
    @case('purchases-payment')
        @include('livewire.admin.reports.purchases.purchases-payment')
        @break

    @case('detailed-purchases')
        @include('livewire.admin.reports.purchases.detailed-purchases')
        @break

    {{-- Inventory Valuation Reports --}}
    @case('product-wise-cogs')
        @include('livewire.admin.reports.inventory.product-wise-cogs')
        @break

    @case('year-wise-cogs')
        @include('livewire.admin.reports.inventory.year-wise-cogs')
        @break

    {{-- Profit & Loss Reports --}}
    @case('pl-cogs')
        @include('livewire.admin.reports.profit-loss.pl-cogs')
        @break

    @case('pl-opening-closing')
        @include('livewire.admin.reports.profit-loss.pl-opening-closing')
        @break

    @case('pl-period-cogs')
        @include('livewire.admin.reports.profit-loss.pl-period-cogs')
        @break

    @case('pl-period-stock')
        @include('livewire.admin.reports.profit-loss.pl-period-stock')
        @break

    @case('productwise-pl')
        @include('livewire.admin.reports.profit-loss.productwise-pl')
        @break

    @case('invoicewise-pl')
        @include('livewire.admin.reports.profit-loss.invoicewise-pl')
        @break

    @case('customerwise-pl')
        @include('livewire.admin.reports.profit-loss.customerwise-pl')
        @break

    {{-- Other Reports --}}
    @case('expense-report')
        @include('livewire.admin.reports.other.expense-report')
        @break

    @case('commission-report')
        @include('livewire.admin.reports.other.commission-report')
        @break

    @case('payment-mode-report')
        @include('livewire.admin.reports.other.payment-mode-report')
        @break

    @default
        <div class="text-center py-5">
            <div class="text-muted mb-3">
                <i class="bi bi-exclamation-circle display-4"></i>
            </div>
            <h5 class="text-muted">Report Not Found</h5>
            <p class="text-muted">The selected report is not available.</p>
        </div>
@endswitch
