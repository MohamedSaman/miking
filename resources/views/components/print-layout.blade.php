<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Print Document' }}</title>
    <style>
        /* ============================================
           A5 PRINT LAYOUT - CURTAINPLUS STYLE
           ============================================ */
        @page {
            size: A5 portrait;
            margin: 6mm 8mm 6mm 8mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', 'Trebuchet MS', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.35;
            background: white;
            color: #000;
        }

        .print-container {
            width: 100%;
            background: white;
            position: relative;
            box-sizing: border-box;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            /* A5 height (210mm) minus top+bottom margins (6mm+6mm) */
            min-height: 198mm;
        }

        /* ============================================
           HEADER SECTION
           ============================================ */
        .global-header {
            text-align: center;
            padding: 0 0 6px 0;
            margin: 0;
            background: white;
        }

        .global-header .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1a237e;
            letter-spacing: 1.5px;
            margin-bottom: 0;
            line-height: 1.2;
        }

        .global-header .company-subtitle {
            font-size: 9px;
            color: #c0392b;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 600;
            margin: 2px 0;
        }

        .global-header .company-address {
            font-size: 10px;
            color: #333;
            margin: 2px 0 0 0;
        }

        .global-header .company-tel {
            font-size: 10px;
            color: #333;
            margin: 1px 0 0 0;
        }

        .header-line {
            border: none;
            border-top: 2px solid #000;
            margin: 5px 0 0 0;
        }

        .document-type {
            font-size: 13px;
            font-weight: bold;
            color: #1a237e;
            letter-spacing: 1px;
            margin: 3px 0 0 0;
            text-transform: uppercase;
        }

        /* ============================================
           CONTENT AREA
           ============================================ */
        .print-content {
            padding: 8px 0;
            flex: 1 1 auto;
        }

        /* ============================================
           INVOICE INFO ROW (Customer + Invoice Details)
           ============================================ */
        .invoice-info-row {
            display: flex;
            margin-bottom: 10px;
            font-size: 11px;
            page-break-inside: avoid;
            border: 1px solid #999;
            padding: 8px 10px;
        }

        .invoice-info-row .col-left {
            flex: 0 0 50%;
            max-width: 50%;
        }

        .invoice-info-row .col-right {
            flex: 0 0 50%;
            max-width: 50%;
        }

        .invoice-info-row p {
            margin: 2px 0;
            line-height: 1.5;
            font-size: 11px;
        }

        .invoice-info-row .label {
            font-weight: bold;
            display: inline-block;
            min-width: 85px;
        }

        .invoice-info-row .info-table {
            width: 100%;
            font-size: 11px;
            border: none;
        }

        .invoice-info-row .info-table td {
            padding: 2px 4px;
            border: none;
            vertical-align: top;
        }

        .invoice-info-row .info-table .info-label {
            font-weight: bold;
            white-space: nowrap;
            padding-right: 8px;
        }

        .invoice-info-row .info-table .info-value {
            text-align: right;
        }

        /* ============================================
           ITEMS TABLE
           ============================================ */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 10px 0;
            font-size: 11px;
            page-break-inside: auto;
        }

        .invoice-table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 5px 6px;
            font-weight: bold;
            text-align: center;
            font-size: 10px;
            page-break-after: avoid;
        }

        .invoice-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 11px;
        }

        .invoice-table tbody tr {
            page-break-inside: avoid;
        }

        .invoice-table tfoot .totals-row td {
            border-top: 1px solid #000;
            padding: 4px 6px;
            font-weight: bold;
            page-break-inside: avoid;
        }

        .invoice-table tfoot .grand-total td {
            border-top: 2px solid #000;
            font-size: 12px;
            padding: 5px 6px;
            font-weight: bold;
            page-break-inside: avoid;
        }

        /* ============================================
           PAYMENT + ORDER SUMMARY SECTION
           ============================================ */
        .payment-summary-row {
            display: flex;
            margin-bottom: 8px;
            font-size: 11px;
            page-break-inside: avoid;
        }

        .payment-info-col {
            flex: 0 0 40%;
            max-width: 40%;
            border: 1px solid #000;
            padding: 6px 8px;
        }

        .order-summary-col {
            flex: 0 0 60%;
            max-width: 60%;
            border: 1px solid #000;
            border-left: none;
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

        .summary-table {
            width: 100%;
            font-size: 11px;
            border: none;
        }

        .summary-table td {
            padding: 2px 0;
            border: none;
        }

        .summary-table .summary-label {
            text-align: left;
            padding-left: 10px;
        }

        .summary-table .summary-value {
            text-align: right;
            font-weight: bold;
        }

        .summary-table .grand-total-row td {
            font-size: 13px;
            font-weight: bold;
            padding-top: 4px;
            border-top: 1px solid #ccc;
        }

        .summary-table .amount-due-row td {
            color: #c0392b;
            font-weight: bold;
        }

        /* ============================================
           RETURNED ITEMS SECTION
           ============================================ */
        .returned-items-section {
            page-break-inside: avoid;
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px solid #000;
        }

        .returned-items-section h6 {
            font-size: 12px;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
            page-break-after: avoid;
        }

        .return-amount-row {
            text-align: right;
            font-size: 13px;
            font-weight: bold;
            padding: 4px 0;
        }

        .return-amount-row .return-label {
            color: #c0392b;
        }

        .return-amount-row .return-value {
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
           FOOTER SECTION
           ============================================ */
        .global-footer {
            background: white;
            padding: 0;
            margin: 0;
            margin-top: auto;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin: 15px 0 8px 0;
            padding: 0 15px;
        }

        .signature-block {
            text-align: center;
            width: 40%;
        }

        .signature-block .sig-dots {
            font-size: 11px;
            letter-spacing: 2px;
            margin-bottom: 3px;
        }

        .signature-block .sig-label {
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

        .footer-info a {
            color: #2980b9;
            text-decoration: none;
        }

        /* ============================================
           UTILITY CLASSES
           ============================================ */
        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .fw-bold {
            font-weight: bold;
        }

        .text-danger {
            color: #c0392b;
        }

        .text-success {
            color: #27ae60;
        }

        .text-primary {
            color: #1a237e;
        }

        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 1rem; }
        .mt-2 { margin-top: 0.5rem; }
        .pt-3 { padding-top: 1rem; }

        /* ============================================
           SCREEN PREVIEW
           ============================================ */
        @media screen {
            body {
                background: #e0e0e0;
                padding: 20px 0;
            }

            .print-container {
                box-shadow: 0 2px 15px rgba(0, 0, 0, 0.2);
                max-width: 148mm;
                min-height: 210mm;
                margin: 0 auto;
                padding: 6mm 8mm;
                display: flex;
                flex-direction: column;
            }
        }

        /* ============================================
           PRINT STYLES
           ============================================ */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .print-container {
                box-shadow: none !important;
                padding: 0;
                margin: 0;
                min-height: 198mm;
                display: flex;
                flex-direction: column;
            }

            .global-footer {
                margin-top: auto;
            }

            .global-header {
                margin: 0;
            }
        }
    </style>
</head>

<body>
    @if(isset($noPrint) && $noPrint)
    <div class="toolbar no-print" style="position: sticky; top: 0; background: #fff; padding: 15px; border-bottom: 2px solid #1a237e; display: flex; justify-content: center; gap: 15px; z-index: 1000; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
        <a href="/invoice/download/{{ $saleId ?? '' }}" style="background: #27ae60; color: #fff; padding: 10px 25px; text-decoration: none; border-radius: 6px; font-weight: bold; font-family: 'Segoe UI', Arial; font-size: 14px; transition: 0.3s; display: flex; align-items: center; gap: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
            </svg>
            Download PDF
        </a>
        <button onclick="window.print()" style="background: #1a237e; color: #fff; padding: 10px 25px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-family: 'Segoe UI', Arial; font-size: 14px; transition: 0.3s; display: flex; align-items: center; gap: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
            </svg>
            Print Receipt
        </button>
    </div>
    @endif
    <div class="print-container">
        <!-- Global Header -->
        <div class="global-header">
            <div class="company-name">MI-KING</div>
            <div class="company-subtitle">BEST IN BOYS</div>
            <div class="company-address">No.122/10A, Super Paradise Market, Keyzer Street, Colombo 11.</div>
            <div class="company-tel">TEL : (076) 1234567</div>
            @if(isset($documentType) && $documentType)
            <div class="document-type">{{ $documentType }}</div>
            @endif
            <hr class="header-line">
        </div>

        <!-- Dynamic Content Area -->
        <div class="print-content">
            {{ $slot }}
        </div>

        <!-- Global Footer -->
        <div class="global-footer">
            <div class="signature-section">
                <div class="signature-block">
                    <div class="sig-dots">.............................</div>
                    <div class="sig-label">Authorized Signature</div>
                </div>
                <div class="signature-block">
                    <div class="sig-dots">.............................</div>
                    <div class="sig-label">Customer Signature</div>
                </div>
            </div>
            <div class="footer-thankyou">Thank you for your business!</div>
            <div class="footer-info">
                <span>122/10A, Super Paradise Market, Keyzer Street, Colombo 11</span> |
                <span>Tel: (076) 1234567</span>
            </div>
        </div>
    </div>

    <!-- Auto-print script -->
    <script>
        // Auto-trigger print when page loads
        window.onload = function() {
            @if(!isset($noPrint) || !$noPrint)
                // Small delay to ensure content is fully rendered
                setTimeout(function() {
                    window.print();
                }, 500);
            @endif
        };

        // Optional: Close window after printing or canceling
        window.onafterprint = function() {
            // You can uncomment the line below to auto-close the window after printing
            // window.close();
        };
    </script>
</body>

</html>