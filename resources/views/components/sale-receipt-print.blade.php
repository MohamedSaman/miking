<x-print-layout title="Sale Receipt - {{ $sale->invoice_number }}" :documentType="in_array($sale->sale_price_type ?? '', ['cash', 'cash_credit']) ? 'CASH INVOICE' : 'CREDIT INVOICE'">
    <!-- Customer & Sale Details - Two Column Layout -->
    <div class="invoice-info-row">
        <div class="col-left">
            <p><span class="label">Name:</span> {{ $sale->customer->name ?? 'Walking Customer' }}</p>
            <p><span class="label">Phone:</span> {{ $sale->customer->phone ?? 'N/A' }}</p>
            <p><span class="label">Address:</span> {{ $sale->customer->address ?? 'N/A' }}</p>
            <p><span class="label">Type:</span> {{ ucfirst($sale->customer->type ?? 'Retail') }}</p>
            <p><span class="label">Salesman:</span> {{ $sale->user->name ?? 'Admin' }}</p>
        </div>
        <div class="col-right">
            <table class="info-table">
                <tr>
                    <td class="info-label">Invoice Number:</td>
                    <td class="info-value">{{ $sale->invoice_number }}</td>
                </tr>
                <tr>
                    <td class="info-label">Sale ID:</td>
                    <td class="info-value">{{ $sale->sale_id }}</td>
                </tr>
                <tr>
                    <td class="info-label">Date:</td>
                    <td class="info-value">{{ $sale->created_at->format('d/m/Y h:i A') }}</td>
                </tr>
                <tr>
                    <td class="info-label">Payment Status:</td>
                    <td class="info-value" style="color: {{ $sale->payment_status === 'paid' ? '#27ae60' : ($sale->payment_status === 'pending' ? '#e67e22' : '#c0392b') }}; font-weight: bold;">
                        {{ ucfirst($sale->payment_status) }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Items Table -->
    <table class="invoice-table">
        <thead>
            <tr>
                <th style="width: 20px;">#</th>
                <th style="width: 50px;">Code</th>
                <th>Item</th>
                <th style="width: 45px;">Qty</th>
                <th style="width: 70px;">Price</th>
                <th style="width: 55px;">Discount</th>
                <th style="width: 75px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->product_code }}</td>
                <td>{{ $item->product_name }}</td>
                <td class="text-center">{{ number_format($item->quantity, 2) }} {{ $item->quantity >= 1 ? '' : '' }}</td>
                <td class="text-end">Rs.{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-center">
                    @if($item->discount_per_unit > 0)
                        {{ number_format($item->discount_per_unit, 2) }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-end">Rs.{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Payment Information + Order Summary Side by Side -->
    @php
        $itemDiscountTotal = $sale->items->sum(function($item) {
            return ($item->discount_per_unit ?? 0) * $item->quantity;
        });
        $totalDiscount = ($sale->discount_amount ?? 0) + $itemDiscountTotal;
    @endphp
    <div class="payment-summary-row">
        <div class="payment-info-col">
            <div class="section-title">PAYMENT INFORMATION</div>
            @if($sale->payments && count($sale->payments) > 0)
                @foreach($sale->payments as $payment)
                <p style="font-size: 10px; margin: 2px 0;">
                    {{ ucfirst($payment->payment_method ?? 'Cash') }}:
                    Rs.{{ number_format($payment->amount, 2) }}
                    <span style="color: #888; font-size: 9px;">
                        ({{ $payment->created_at->format('d/m/Y') }})
                    </span>
                </p>
                @endforeach
            @else
                <p style="font-size: 10px; color: #888; margin: 2px 0;">No payment information available</p>
            @endif
        </div>
        <div class="order-summary-col">
            <div class="section-title">ORDER SUMMARY</div>
            <table class="summary-table">
                <tr>
                    <td class="summary-label">Subtotal:</td>
                    <td class="summary-value">Rs.{{ number_format($sale->subtotal + $itemDiscountTotal, 2) }}</td>
                </tr>
                @if($totalDiscount > 0)
                <tr>
                    <td class="summary-label">Discount:</td>
                    <td class="summary-value text-danger">-Rs.{{ number_format($totalDiscount, 2) }}</td>
                </tr>
                @endif
                <tr class="grand-total-row">
                    <td class="summary-label" style="font-size: 13px;"><strong>Grand Total:</strong></td>
                    <td class="summary-value" style="font-size: 13px;">Rs.{{ number_format($sale->total_amount, 2) }}</td>
                </tr>
                @php
                    $totalReturnAmt = 0;
                    if(isset($sale->returns) && count($sale->returns) > 0) {
                        $totalReturnAmt += $sale->returns->sum('total_amount');
                    }
                    if(isset($sale->staffReturns) && count($sale->staffReturns) > 0) {
                        $totalReturnAmt += $sale->staffReturns->sum('total_amount');
                    }
                    $paidAmount = max(0, $sale->total_amount - $totalReturnAmt - ($sale->due_amount ?? 0));
                @endphp
                @if($paidAmount > 0 && ($sale->due_amount ?? 0) > 0)
                <tr>
                    <td class="summary-label" style="color: #27ae60;">Paid Amount:</td>
                    <td class="summary-value" style="color: #27ae60;">Rs.{{ number_format($paidAmount, 2) }}</td>
                </tr>
                @endif
                @if(($sale->due_amount ?? 0) > 0)
                <tr class="amount-due-row">
                    <td class="summary-label">Amount Due:</td>
                    <td class="summary-value">Rs.{{ number_format($sale->due_amount, 2) }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Admin Returned Items Table --}}
    @if(isset($sale->returns) && count($sale->returns) > 0)
    <div class="returned-items-section">
        <h6>RETURNED ITEMS</h6>
        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width: 20px;">#</th>
                    <th style="width: 50px;">Code</th>
                    <th>Item</th>
                    <th style="width: 50px;">Reason</th>
                    <th style="width: 50px;">Qty / Length</th>
                    <th style="width: 65px;">Unit Price</th>
                    <th style="width: 70px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $adminReturnAmount = 0; @endphp
                @foreach($sale->returns as $rIndex => $return)
                @php $adminReturnAmount += $return->total_amount; @endphp
                <tr>
                    <td class="text-center">{{ $rIndex + 1 }}</td>
                    <td>{{ $return->product->code ?? '-' }}</td>
                    <td>{{ $return->product->name ?? '-' }}</td>
                    <td class="text-center" style="font-size: 10px; color: #888;">Return</td>
                    <td class="text-center">{{ number_format($return->return_quantity, 2) }}</td>
                    <td class="text-end">Rs.{{ number_format($return->selling_price, 2) }}</td>
                    <td class="text-end">Rs.{{ number_format($return->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="return-amount-row">
            <span class="return-label">Return Amount:</span>
            <span class="return-value">&nbsp;&nbsp;-Rs.{{ number_format($adminReturnAmount, 2) }}</span>
        </div>
        <div class="net-amount-row">
            <span>Net Amount:</span>
            <span>&nbsp;&nbsp;Rs.{{ number_format(($sale->subtotal - ($sale->discount_amount ?? 0)) - $adminReturnAmount, 2) }}</span>
        </div>
        @php $paidAfterReturn = max(0, $sale->total_amount - $adminReturnAmount - ($sale->due_amount ?? 0)); @endphp
        @if($paidAfterReturn > 0)
        <div style="text-align: right; font-size: 11px; padding: 2px 0;">
            <span style="color: #27ae60; font-weight: bold;">Paid Amount:</span>
            <span style="color: #27ae60; font-weight: bold;">&nbsp;&nbsp;Rs.{{ number_format($paidAfterReturn, 2) }}</span>
        </div>
        @endif
        @if(($sale->due_amount ?? 0) > 0)
        <div style="text-align: right; font-size: 11px; padding: 2px 0;">
            <span style="color: #c0392b; font-weight: bold;">Due Amount:</span>
            <span style="color: #c0392b; font-weight: bold;">&nbsp;&nbsp;Rs.{{ number_format($sale->due_amount, 2) }}</span>
        </div>
        @endif
    </div>
    @endif

    {{-- Staff/Customer Returned Items Table --}}
    @if(isset($sale->staffReturns) && count($sale->staffReturns) > 0)
    <div class="returned-items-section">
        <h6>CUSTOMER RETURNED ITEMS</h6>
        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width: 20px;">#</th>
                    <th style="width: 50px;">Code</th>
                    <th>Item</th>
                    <th style="width: 55px;">Reason</th>
                    <th style="width: 50px;">Qty / Length</th>
                    <th style="width: 65px;">Unit Price</th>
                    <th style="width: 70px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $staffReturnAmount = 0; @endphp
                @foreach($sale->staffReturns as $srIndex => $staffReturn)
                @php $staffReturnAmount += $staffReturn->total_amount; @endphp
                <tr>
                    <td class="text-center">{{ $srIndex + 1 }}</td>
                    <td>{{ $staffReturn->product->code ?? '-' }}</td>
                    <td>{{ $staffReturn->product->name ?? '-' }}</td>
                    <td class="text-center" style="font-size: 10px; {{ $staffReturn->is_damaged ? 'color: #c0392b; font-weight: bold;' : 'color: #888;' }}">
                        @if($staffReturn->is_damaged)
                            Damaged
                        @elseif($staffReturn->reason)
                            {{ ucfirst($staffReturn->reason) }}
                        @else
                            Return
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($staffReturn->quantity, 2) }}</td>
                    <td class="text-end">Rs.{{ number_format($staffReturn->unit_price, 2) }}</td>
                    <td class="text-end">Rs.{{ number_format($staffReturn->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="return-amount-row">
            <span class="return-label">Return Amount:</span>
            <span class="return-value">&nbsp;&nbsp;-Rs.{{ number_format($staffReturnAmount, 2) }}</span>
        </div>
        <div class="net-amount-row">
            <span>Net Amount:</span>
            <span>&nbsp;&nbsp;Rs.{{ number_format($sale->total_amount - $staffReturnAmount, 2) }}</span>
        </div>
        @php $staffPaidAfterReturn = max(0, $sale->total_amount - $staffReturnAmount - ($sale->due_amount ?? 0)); @endphp
        @if($staffPaidAfterReturn > 0)
        <div style="text-align: right; font-size: 11px; padding: 2px 0;">
            <span style="color: #27ae60; font-weight: bold;">Paid Amount:</span>
            <span style="color: #27ae60; font-weight: bold;">&nbsp;&nbsp;Rs.{{ number_format($staffPaidAfterReturn, 2) }}</span>
        </div>
        @endif
        @if(($sale->due_amount ?? 0) > 0)
        <div style="text-align: right; font-size: 11px; padding: 2px 0;">
            <span style="color: #c0392b; font-weight: bold;">Due Amount:</span>
            <span style="color: #c0392b; font-weight: bold;">&nbsp;&nbsp;Rs.{{ number_format($sale->due_amount, 2) }}</span>
        </div>
        @endif
    </div>
    @endif

    @if($sale->notes)
    <div style="margin-top: 6px; padding: 5px 8px; background: #f8f9fa; border: 1px solid #dee2e6; font-size: 10px;">
        <strong>Notes:</strong> {{ $sale->notes }}
    </div>
    @endif
</x-print-layout>