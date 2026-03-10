<x-print-layout title="Sale Receipt - {{ $sale->invoice_number }}" :documentType="in_array($sale->sale_price_type ?? '', ['cash', 'cash_credit']) ? 'CASH INVOICE' : 'CREDIT INVOICE'">
    <!-- Sale Receipt Content -->

    <!-- Customer & Sale Details -->
    <div class="invoice-info-row">
        <div class="col-6">
            <p><strong>Customer:</strong></p>
            <p>{{ $sale->customer->name }}</p>
            <p>{{ $sale->customer->address }}</p>
            <p><strong>Tel:</strong> {{ $sale->customer->phone }}</p>
        </div>
        <div class="col-6 text-end">
            <table class="table-borderless ms-auto" style="width: auto; display: inline-table;">
                <tr>
                    <td style="padding-right: 15px;"><strong>Invoice #</strong></td>
                    <td>{{ $sale->invoice_number }}</td>
                </tr>
                <tr>
                    <td style="padding-right: 15px;"><strong>Sale Type</strong></td>
                    <td>{{ in_array($sale->sale_price_type ?? '', ['cash', 'cash_credit']) ? 'Cash Invoice' : 'Credit Invoice' }}</td>
                </tr>
                <tr>
                    <td style="padding-right: 15px;"><strong>Sale ID</strong></td>
                    <td>{{ $sale->sale_id }}</td>
                </tr>
                <tr>
                    <td style="padding-right: 15px;"><strong>Date</strong></td>
                    <td>{{ $sale->created_at->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="padding-right: 15px;"><strong>Time</strong></td>
                    <td>{{ $sale->created_at->format('H:i') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Items Table -->
    <table class="invoice-table">
        <thead>
            <tr>
                <th width="40">#</th>
                <th>ITEM CODE</th>
                <th>DESCRIPTION</th>
                <th width="80">QTY</th>
                <th width="120">UNIT PRICE</th>
                <th width="120">UNIT DISCOUNT</th>
                <th width="120">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->product_code }}</td>
                <td>{{ $item->product_name }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-end">Rs.{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-end">Rs.{{ number_format($item->discount_per_unit, 2) }}</td>
                <td class="text-end">Rs.{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="6" class="text-end"><strong>Subtotal</strong></td>
                <td class="text-end"><strong>Rs.{{ number_format($sale->subtotal, 2) }}</strong></td>
            </tr>
            @if($sale->discount_amount > 0)
            <tr class="totals-row">
                <td colspan="6" class="text-end"><strong>Discount</strong></td>
                <td class="text-end"><strong>-Rs.{{ number_format($sale->discount_amount, 2) }}</strong></td>
            </tr>
            @endif
            <tr class="totals-row grand-total">
                <td colspan="6" class="text-end"><strong>Grand Total</strong></td>
                <td class="text-end"><strong>Rs.{{ number_format($sale->total_amount, 2) }}</strong></td>
            </tr>
            @if((!isset($sale->returns) || count($sale->returns) == 0) && (!isset($sale->staffReturns) || count($sale->staffReturns) == 0))
            @php $noReturnPaid = max(0, $sale->total_amount - $sale->due_amount); @endphp
            @if($noReturnPaid > 0 && $sale->due_amount > 0)
            <tr class="totals-row">
                <td colspan="6" class="text-end"><strong>Paid Amount</strong></td>
                <td class="text-end"><strong>Rs.{{ number_format($noReturnPaid, 2) }}</strong></td>
            </tr>
            @endif
            @if($sale->due_amount > 0)
            <tr class="totals-row">
                <td colspan="6" class="text-end"><strong>Due Amount</strong></td>
                <td class="text-end"><strong>Rs.{{ number_format($sale->due_amount, 2) }}</strong></td>
            </tr>
            @endif
            @endif
        </tfoot>
    </table>

    {{-- Returned Items Table --}}
    @if(isset($sale->returns) && count($sale->returns) > 0)
    <div class="returned-items-section">
        <h6 style="margin-bottom: 10px; font-weight: bold; color: #000;">RETURNED ITEMS</h6>
        <table class="invoice-table">
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th>ITEM CODE</th>
                    <th>DESCRIPTION</th>
                    <th width="100">RETURN QTY</th>
                    <th width="120">UNIT PRICE</th>
                    <th width="120">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php $returnAmount = 0; @endphp
                @foreach($sale->returns as $rIndex => $return)
                @php $returnAmount += $return->total_amount; @endphp
                <tr>
                    <td class="text-center">{{ $rIndex + 1 }}</td>
                    <td>{{ $return->product?->code ?? '-' }}</td>
                    <td>{{ $return->product?->name ?? '-' }}</td>
                    <td class="text-center">{{ $return->return_quantity }}</td>
                    <td class="text-end">Rs.{{ number_format($return->selling_price, 2) }}</td>
                    <td class="text-end">Rs.{{ number_format($return->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="5" class="text-end"><strong>Return Amount:</strong></td>
                    <td class="text-end"><strong>-Rs.{{ number_format($returnAmount, 2) }}</strong></td>
                </tr>
                <tr class="totals-row grand-total">
                    <td colspan="5" class="text-end"><strong>Net Amount:</strong></td>
                    <td class="text-end"><strong>Rs.{{ number_format(($sale->subtotal - $sale->discount_amount) - $returnAmount, 2) }}</strong></td>
                </tr>
                @php $paidAfterReturn = max(0, $sale->total_amount - $returnAmount - $sale->due_amount); @endphp
                @if($paidAfterReturn > 0)
                <tr class="totals-row">
                    <td colspan="5" class="text-end"><strong>Paid Amount:</strong></td>
                    <td class="text-end"><strong>Rs.{{ number_format($paidAfterReturn, 2) }}</strong></td>
                </tr>
                @endif
                @if($sale->due_amount > 0)
                <tr class="totals-row">
                    <td colspan="5" class="text-end"><strong>Due Amount:</strong></td>
                    <td class="text-end"><strong>Rs.{{ number_format($sale->due_amount, 2) }}</strong></td>
                </tr>
                @endif
            </tfoot>
        </table>
    </div>
    @endif

    {{-- Staff Returned Items Table --}}
    @if(isset($sale->staffReturns) && count($sale->staffReturns) > 0)
    <div class="returned-items-section">
        <h6 style="margin-bottom: 10px; font-weight: bold; color: #000;">CUSTOMER RETURNED ITEMS</h6>
        <table class="invoice-table">
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th>ITEM CODE</th>
                    <th>DESCRIPTION</th>
                    <th width="100">RETURN QTY</th>
                    <th width="120">UNIT PRICE</th>
                    <th width="120">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php $staffReturnAmount = 0; @endphp
                @foreach($sale->staffReturns as $srIndex => $staffReturn)
                @php $staffReturnAmount += $staffReturn->total_amount; @endphp
                <tr>
                    <td class="text-center">{{ $srIndex + 1 }}</td>
                    <td>{{ $staffReturn->product?->code ?? '-' }}</td>
                    <td>{{ $staffReturn->product?->name ?? '-' }}</td>
                    <td class="text-center">{{ $staffReturn->quantity }}</td>
                    <td class="text-end">Rs.{{ number_format($staffReturn->unit_price, 2) }}</td>
                    <td class="text-end">Rs.{{ number_format($staffReturn->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="5" class="text-end"><strong>Return Amount:</strong></td>
                    <td class="text-end"><strong>-Rs.{{ number_format($staffReturnAmount, 2) }}</strong></td>
                </tr>
                <tr class="totals-row">
                    <td colspan="5" class="text-end"><strong>Total After Returns:</strong></td>
                    <td class="text-end"><strong>Rs.{{ number_format($sale->total_amount - $staffReturnAmount, 2) }}</strong></td>
                </tr>
                <tr class="totals-row">
                    <td colspan="5" class="text-end"><strong>Paid Amount:</strong></td>
                    <td class="text-end"><strong>Rs.{{ number_format(max(0, $sale->total_amount - $staffReturnAmount - $sale->due_amount), 2) }}</strong></td>
                </tr>
                <tr class="totals-row grand-total">
                    <td colspan="5" class="text-end"><strong>Due Amount:</strong></td>
                    <td class="text-end"><strong>Rs.{{ number_format($sale->due_amount, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    @if($sale->notes)
    <div style="margin-top: 20px; padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6;">
        <strong>Notes:</strong> {{ $sale->notes }}
    </div>
    @endif
</x-print-layout>