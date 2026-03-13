<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer {{ $type }} Report - {{ $customer->name }}</title>
    @php
        $isA5 = true;
        $pageWidth = '148mm';
        $baseFontSize = '10px';
        $headerFontSize = '17px';
        $titleFontSize = '13px';
        $thFontSize = '9px';
        $tdFontSize = '9px';
    @endphp
    <style>
        @page {
            size: A5;
            margin: 6mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: {{ $baseFontSize }};
            line-height: 1.3;
            color: #000;
            background: white;
            padding: 0;
        }

        .report-container {
            width: 100%;
            max-width: {{ $pageWidth }};
            margin: 0 auto;
            background: white;
        }

        /* Header Section */
        .header-section {
            text-align: center;
            margin-bottom: 5px;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
        }

        .company-name {
            font-size: {{ $headerFontSize }};
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 1px;
        }

        .company-info {
            font-size: {{ $isA5 ? '8px' : '10px' }};
            color: #333;
        }

        .report-title {
            font-size: {{ $titleFontSize }};
            font-weight: bold;
            margin: 4px 0 2px;
            text-decoration: underline;
            text-transform: uppercase;
        }

        /* Customer Information */
        .customer-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            background: #f9f9f9;
            padding: 5px 7px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .customer-info div {
            margin-bottom: 2px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: {{ $isA5 ? '65px' : '80px' }};
        }

        /* Table Styles */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .report-table th {
            background-color: #f2f2f2 !important;
            border: 1px solid #000;
            padding: {{ $isA5 ? '4px 3px' : '6px 4px' }};
            text-align: left;
            font-weight: bold;
            font-size: {{ $thFontSize }};
            text-transform: uppercase;
            -webkit-print-color-adjust: exact;
        }

        .report-table td {
            border: 1px solid #000;
            padding: {{ $isA5 ? '3px' : '4px' }};
            font-size: {{ $tdFontSize }};
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }

        .report-table tbody tr:nth-child(even) {
            background-color: #fafafa !important;
            -webkit-print-color-adjust: exact;
        }

        /* Totals / Summary */
        .summary-section {
            margin-top: 4px;
            text-align: right;
        }

        .summary-table {
            display: inline-block;
            min-width: 220px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            border-bottom: 1px solid #ddd;
        }

        .summary-label {
            font-weight: bold;
        }

        .summary-value {
            min-width: 80px;
        }

        .grand-total {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-weight: bold;
            font-size: 10px;
            padding: 3px 0;
            margin-top: 2px;
        }

        /* Footer */
        .footer-section {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            border-top: 1px solid #ddd;
            padding-top: 4px;
            padding-bottom: 2px;
            background: white;
        }

        .print-date {
            font-style: italic;
            margin-top: 3px;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .report-container {
                max-width: 100%;
                width: 100%;
            }
            .report-table {
                page-break-inside: auto;
            }
            .report-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="report-container">
        {{-- Header --}}
        <div class="header-section">
            <div class="company-name">MIKING</div>
            <div class="company-info">
                122/10A, Super Paradise Market, Keyzer Street, Colombo 11 . | Phone: (076) 1234567
            </div>
            <div class="report-title">Customer History - {{ $type }}</div>
        </div>

        {{-- Customer Info --}}
        <div class="customer-section">
            <div class="customer-info">
                <div><span class="info-label">Customer:</span> <span class="fw-bold">{{ $customer->name }}</span></div>
                <div><span class="info-label">Business:</span> {{ $customer->business_name ?? 'N/A' }}</div>
                <div><span class="info-label">Contact:</span> {{ $customer->phone ?? 'N/A' }}</div>
                <div><span class="info-label">Address:</span> {{ $customer->address ?? 'N/A' }}</div>
            </div>
            <div class="customer-info text-right">
                <div><span class="info-label">Type:</span> <span style="text-transform: uppercase;">{{ $customer->type }}</span></div>
                <div><span class="info-label">Report Date:</span> {{ date('d/m/Y h:i A') }}</div>
            </div>
        </div>

        {{-- Dynamic Data Table --}}
        @if($type === 'Sales' || $type === 'Dues')
            <table class="report-table">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">#</th>
                        <th width="15%">Invoice No</th>
                        <th width="15%">Date</th>
                        <th width="10%" class="text-center">Items</th>
                        <th width="15%" class="text-right">Total Amount</th>
                        <th width="15%" class="text-right">Paid Amount</th>
                        <th width="15%" class="text-right">Due Amount</th>
                        <th width="10%" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php $tTotal = 0; $tPaid = 0; $tDue = 0; @endphp
                    @foreach($data as $index => $sale)
                        @php $tTotal += $sale->total_amount; $tPaid += $sale->paid_amount; $tDue += $sale->due_amount; @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="fw-bold">{{ $sale->invoice_no ?? $sale->invoice_number ?? sprintf('%04d', $sale->id) }}</td>
                            <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y h:i A') }}</td>
                            <td class="text-center">{{ $sale->items ? $sale->items->count() : '-' }}</td>
                            <td class="text-right">{{ number_format($sale->total_amount, 2) }}</td>
                            <td class="text-right">{{ number_format($sale->paid_amount, 2) }}</td>
                            <td class="text-right fw-bold">{{ number_format($sale->due_amount, 2) }}</td>
                            <td class="text-center">{{ ucfirst($sale->payment_status ?? 'N/A') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold bg-light">
                        <td colspan="4" class="text-right">TOTALS:</td>
                        <td class="text-right">{{ number_format($tTotal, 2) }}</td>
                        <td class="text-right">{{ number_format($tPaid, 2) }}</td>
                        <td class="text-right">{{ number_format($tDue, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        @elseif($type === 'Payments')
            <table class="report-table">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">#</th>
                        <th width="15%">Date</th>
                        <th width="15%">Invoice No</th>
                        <th width="15%">Method</th>
                        <th width="20%">Reference</th>
                        <th width="15%" class="text-right">Amount</th>
                        <th width="15%">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @php $tAmount = 0; @endphp
                    @foreach($data as $index => $payment)
                        @php $tAmount += $payment->amount; @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y h:i A') }}</td>
                            <td class="fw-bold">{{ $payment->sale->invoice_number ?? $payment->sale->invoice_no ?? '-' }}</td>
                            <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                            <td>{{ $payment->payment_reference ?? '-' }}</td>
                            <td class="text-right fw-bold">{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold bg-light">
                        <td colspan="5" class="text-right">TOTAL PAID:</td>
                        <td class="text-right">{{ number_format($tAmount, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        @elseif($type === 'Ledger')
            <table class="report-table">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">#</th>
                        <th width="15%">Date</th>
                        <th width="15%">Invoice No</th>
                        <th width="20%">Description</th>
                        <th width="15%" class="text-right">Debit</th>
                        <th width="15%" class="text-right">Credit</th>
                        <th width="15%" class="text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($item['date'])->format('d/m/Y h:i A') }}</td>
                            <td class="fw-bold">{{ $item['invoice_no'] }}</td>
                            <td>{{ $item['description'] }}</td>
                            <td class="text-right">{{ $item['debit'] > 0 ? number_format($item['debit'], 2) : '-' }}</td>
                            <td class="text-right">{{ $item['credit'] > 0 ? number_format($item['credit'], 2) : '-' }}</td>
                            <td class="text-right fw-bold {{ $item['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format(abs($item['balance']), 2) }}{{ $item['balance'] >= 0 ? ' Dr' : ' Cr' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Summary Stats Recap (optional but good for professionalism) --}}
        <div class="summary-section">
            <div class="summary-table">
                <div class="summary-row">
                    <span class="summary-label">Report Type:</span>
                    <span class="summary-value">{{ $type }}</span>
                </div>
                <div class="summary-row grand-total">
                    <span class="summary-label">Total Outstanding:</span>
                    @php
                        $totalDue = ($customer->opening_balance + $customer->sales->sum('due_amount')) - $customer->overpaid_amount;
                    @endphp
                    <span class="summary-value">Rs. {{ number_format($totalDue, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="footer-section">
            <p>Thank you for your business!</p>
            <div class="print-date">Generated on {{ date('M d, Y h:i A') }}</div>
        </div>
    </div>

    <script>
        window.onload = function() { window.print(); };
    </script>
</body>
</html>
