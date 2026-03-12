<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $sale->invoice_number }}</title>
    <style>
        /* ============================================
           A5 INVOICE LAYOUT - PDF VERSION
           Matches print-layout.blade.php exactly
           ============================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 8mm;
            size: A5 portrait;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 12px;
            line-height: 1.35;
            background: white;
            color: #000;
        }

        .invoice-container {
            width: 100%;
            padding: 0;
            margin: 0;
            background: white;
        }

        /* ============================================
           HEADER - matches print-layout exactly
           ============================================ */
        .global-header {
            text-align: center;
            padding-bottom: 6px;
            margin-bottom: 0;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1a237e;
            letter-spacing: 1.5px;
            margin-bottom: 0;
            line-height: 1.2;
        }

        .company-subtitle {
            font-size: 9px;
            color: #c0392b;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 600;
            margin: 2px 0;
        }

        .company-address {
            font-size: 10px;
            color: #333;
            margin: 2px 0 0 0;
        }

        .company-tel {
            font-size: 10px;
            color: #333;
            margin: 1px 0 0 0;
        }

        .document-type {
            font-size: 13px;
            font-weight: bold;
            color: #1a237e;
            letter-spacing: 1px;
            margin: 3px 0 0 0;
            text-transform: uppercase;
        }

        .header-line {
            border: none;
            border-top: 2px solid #000;
            margin: 5px 0 0 0;
        }

        /* ============================================
           CUSTOMER + INVOICE INFO ROW
           ============================================ */
        .info-row-table {
            width: 100%;
            border: 1px solid #999;
            margin: 8px 0 10px 0;
            font-size: 11px;
        }

        .info-row-table td {
            vertical-align: top;
            padding: 8px 10px;
            border: none;
        }

        .info-row-table .col-left {
            width: 50%;
        }

        .info-row-table .col-right {
            width: 50%;
        }

        .info-row-table p {
            margin: 2px 0;
            line-height: 1.5;
            font-size: 11px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 85px;
        }

        .invoice-detail-table {
            width: 100%;
            font-size: 11px;
        }

        .invoice-detail-table td {
            padding: 2px 4px;
            border: none;
            vertical-align: top;
        }

        .invoice-detail-table .detail-label {
            font-weight: bold;
            white-space: nowrap;
            padding-right: 8px;
        }

        .invoice-detail-table .detail-value {
            text-align: right;
        }

        /* ============================================
           ITEMS TABLE
           ============================================ */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 10px 0;
            font-size: 11px;
        }

        .items-table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 5px 6px;
            font-weight: bold;
            text-align: center;
            font-size: 10px;
        }

        .items-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 11px;
        }

        /* ============================================
           PAYMENT + ORDER SUMMARY (side by side table)
           ============================================ */
        .payment-summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 11px;
        }

        .payment-summary-table > tbody > tr > td {
            vertical-align: top;
            border: 1px solid #000;
            padding: 6px 8px;
        }

        .section-title {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            padding-bottom: 3px;
            border-bottom: 1px solid #ccc;
        }

        .summary-inner-table {
            width: 100%;
            font-size: 11px;
        }

        .summary-inner-table td {
            padding: 2px 0;
            border: none;
        }

        .summary-label {
            text-align: left;
            padding-left: 10px;
        }

        .summary-value {
            text-align: right;
            font-weight: bold;
        }

        /* ============================================
           RETURNED ITEMS
           ============================================ */
        .returned-section {
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px solid #000;
        }

        .returned-section h4 {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .return-amount-row {
            text-align: right;
            font-size: 13px;
            font-weight: bold;
            padding: 4px 0;
            color: #c0392b;
        }

        .net-amount-row {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
            padding: 4px 0;
            border-top: 1px solid #000;
        }

        /* ============================================
           FOOTER
           ============================================ */
        .global-footer {
            margin-top: 20px;
        }

        .sig-table {
            width: 100%;
            border: none;
        }

        .sig-table td {
            text-align: center;
            vertical-align: bottom;
            border: none;
            padding: 5px;
            width: 50%;
        }

        .sig-dots {
            font-size: 11px;
            letter-spacing: 2px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .sig-label {
            font-size: 10px;
            font-weight: bold;
            font-style: italic;
        }

        .footer-thankyou {
            text-align: center;
            font-size: 12px;
            font-style: italic;
            font-weight: bold;
            color: #1a237e;
            margin: 8px 0 5px 0;
        }

        .footer-info {
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 4px;
        }

        /* ============================================
           UTILITY
           ============================================ */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-danger { color: #c0392b; }
        .text-success { color: #27ae60; }
        .fw-bold { font-weight: bold; }
    </style>
</head>

<body>
    <div class="invoice-container">

        <!-- Header - exact match with print-layout -->
        <div class="global-header">
            <div class="company-name">MI-KING</div>
            <div class="company-subtitle">BEST IN BOYS</div>
            <div class="company-address">No.122/10A, Super Paradise Market, Keyzer Street, Colombo 11.</div>
            <div class="company-tel">TEL : (076) 1234567</div>
            <div class="document-type">{{ in_array($sale->sale_price_type ?? '', ['cash', 'cash_credit']) ? 'CASH INVOICE' : 'CREDIT INVOICE' }}</div>
            <hr class="header-line">
        </div>

        <!-- Customer & Invoice Info - table layout for DomPDF -->
        <table class="info-row-table">
            <tr>
                <td class="col-left">
                    <p><span class="info-label">Name:</span> {{ $sale->customer->name ?? 'Walking Customer' }}</p>
                    <p><span class="info-label">Phone:</span> {{ $sale->customer->phone ?? 'N/A' }}</p>
                    <p><span class="info-label">Address:</span> {{ $sale->customer->address ?? 'N/A' }}</p>
                    <p><span class="info-label">Type:</span> {{ ucfirst($sale->customer->type ?? 'Retail') }}</p>
                    <p><span class="info-label">Salesman:</span> {{ $sale->user->name ?? 'Admin' }}</p>
                </td>
                <td class="col-right">
                    <table class="invoice-detail-table">
                        <tr>
                            <td class="detail-label">Invoice Number:</td>
                            <td class="detail-value">{{ $sale->invoice_number }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">Sale ID:</td>
                            <td class="detail-value">{{ $sale->sale_id }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">Date:</td>
                            <td class="detail-value">{{ $sale->created_at->format('d/m/Y h:i A') }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">Payment Status:</td>
                            <td class="detail-value" style="color: {{ $sale->payment_status === 'paid' ? '#27ae60' : ($sale->payment_status === 'pending' ? '#e67e22' : '#c0392b') }}; font-weight: bold;">
                                {{ ucfirst($sale->payment_status) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 12%;">Code</th>
                    <th style="width: 30%;">Item</th>
                    <th style="width: 10%;">Qty</th>
                    <th style="width: 17%;">Price</th>
                    <th style="width: 12%;">Discount</th>
                    <th style="width: 17%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product_code }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-right">Rs.{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-center">
                        @if($item->discount_per_unit > 0)
                            {{ number_format($item->discount_per_unit, 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">Rs.{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Payment + Order Summary Side by Side -->
        @php
            $itemDiscountTotal = $sale->items->sum(function($item) {
                return ($item->discount_per_unit ?? 0) * $item->quantity;
            });
            $totalDiscount = ($sale->discount_amount ?? 0) + $itemDiscountTotal;
        @endphp
        <table class="payment-summary-table">
            <tr>
                <td style="width: 40%;">
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
                </td>
                <td style="width: 60%;">
                    <div class="section-title">ORDER SUMMARY</div>
                    <table class="summary-inner-table">
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
                        <tr>
                            <td class="summary-label" style="font-size: 13px; border-top: 1px solid #ccc; padding-top: 4px;"><strong>Grand Total:</strong></td>
                            <td class="summary-value" style="font-size: 13px; border-top: 1px solid #ccc; padding-top: 4px;">Rs.{{ number_format($sale->total_amount, 2) }}</td>
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
                        <tr>
                            <td class="summary-label" style="color: #c0392b; font-weight: bold;">Amount Due:</td>
                            <td class="summary-value" style="color: #c0392b;">Rs.{{ number_format($sale->due_amount, 2) }}</td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>

        {{-- Returned Items --}}
        @if(isset($sale->returns) && count($sale->returns) > 0)
        @php $returnAmount = 0; @endphp
        <div class="returned-section">
            <h4>RETURNED ITEMS</h4>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 12%;">Code</th>
                        <th style="width: 22%;">Item</th>
                        <th style="width: 12%;">Reason</th>
                        <th style="width: 12%;">Qty / Length</th>
                        <th style="width: 17%;">Unit Price</th>
                        <th style="width: 17%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->returns as $index => $return)
                    @php $returnAmount += $return->total_amount; @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $return->product->code ?? '-' }}</td>
                        <td>{{ $return->product->name ?? '-' }}</td>
                        <td class="text-center" style="font-size: 10px; color: #888;">Return</td>
                        <td class="text-center">{{ number_format($return->return_quantity, 2) }}</td>
                        <td class="text-right">Rs.{{ number_format($return->selling_price, 2) }}</td>
                        <td class="text-right">Rs.{{ number_format($return->total_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="return-amount-row">
                Return Amount: &nbsp;&nbsp;-Rs.{{ number_format($returnAmount, 2) }}
            </div>
            <div class="net-amount-row">
                Net Amount: &nbsp;&nbsp;Rs.{{ number_format((($sale->subtotal ?? $sale->total_amount) - ($sale->discount_amount ?? 0) - $returnAmount), 2) }}
            </div>
            @php $paidAfterReturn = max(0, $sale->total_amount - $returnAmount - ($sale->due_amount ?? 0)); @endphp
            @if($paidAfterReturn > 0)
            <div style="text-align: right; font-size: 11px; padding: 2px 0; color: #27ae60; font-weight: bold;">
                Paid Amount: &nbsp;&nbsp;Rs.{{ number_format($paidAfterReturn, 2) }}
            </div>
            @endif
            @if(($sale->due_amount ?? 0) > 0)
            <div style="text-align: right; font-size: 11px; padding: 2px 0; color: #c0392b; font-weight: bold;">
                Due Amount: &nbsp;&nbsp;Rs.{{ number_format($sale->due_amount, 2) }}
            </div>
            @endif
        </div>
        @endif

        {{-- Staff Returned Items --}}
        @if(isset($sale->staffReturns) && count($sale->staffReturns) > 0)
        @php $staffReturnAmount = 0; @endphp
        <div class="returned-section">
            <h4>CUSTOMER RETURNED ITEMS</h4>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 12%;">Code</th>
                        <th style="width: 22%;">Item</th>
                        <th style="width: 12%;">Reason</th>
                        <th style="width: 12%;">Qty / Length</th>
                        <th style="width: 17%;">Unit Price</th>
                        <th style="width: 17%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->staffReturns as $index => $staffReturn)
                    @php $staffReturnAmount += $staffReturn->total_amount; @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
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
                        <td class="text-right">Rs.{{ number_format($staffReturn->unit_price, 2) }}</td>
                        <td class="text-right">Rs.{{ number_format($staffReturn->total_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="return-amount-row">
                Return Amount: &nbsp;&nbsp;-Rs.{{ number_format($staffReturnAmount, 2) }}
            </div>
            <div class="net-amount-row">
                Net Amount: &nbsp;&nbsp;Rs.{{ number_format($sale->total_amount - $staffReturnAmount, 2) }}
            </div>
            @php $staffPaidAfterReturn = max(0, $sale->total_amount - $staffReturnAmount - ($sale->due_amount ?? 0)); @endphp
            @if($staffPaidAfterReturn > 0)
            <div style="text-align: right; font-size: 11px; padding: 2px 0; color: #27ae60; font-weight: bold;">
                Paid Amount: &nbsp;&nbsp;Rs.{{ number_format($staffPaidAfterReturn, 2) }}
            </div>
            @endif
            @if(($sale->due_amount ?? 0) > 0)
            <div style="text-align: right; font-size: 11px; padding: 2px 0; color: #c0392b; font-weight: bold;">
                Due Amount: &nbsp;&nbsp;Rs.{{ number_format($sale->due_amount, 2) }}
            </div>
            @endif
        </div>
        @endif

        @if($sale->notes)
        <div style="margin-top: 6px; padding: 5px 8px; background: #f8f9fa; border: 1px solid #dee2e6; font-size: 10px;">
            <strong>Notes:</strong> {{ $sale->notes }}
        </div>
        @endif

        <!-- Footer - exact match with print-layout -->
        <div class="global-footer">
            <table class="sig-table">
                <tr>
                    <td>
                        <p class="sig-dots">.............................</p>
                        <p class="sig-label">Authorized Signature</p>
                    </td>
                    <td>
                        <p class="sig-dots">.............................</p>
                        <p class="sig-label">Customer Signature</p>
                    </td>
                </tr>
            </table>
            <p class="footer-thankyou">Thank you for your business!</p>
            <div class="footer-info">
                <p>122/10A, Super Paradise Market, Keyzer Street, Colombo 11 | Tel: (076) 1234567</p>
            </div>
        </div>
    </div>
</body>

</html>