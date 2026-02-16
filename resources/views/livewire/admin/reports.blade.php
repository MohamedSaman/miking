<div>
    @push('styles')
    <style>
        .report-sidebar {
            background: linear-gradient(135deg, #2a83df 0%, #1a5fb8 100%);
            min-height: calc(100vh - 120px);
            border-radius: 12px;
        }

        .category-item {
            padding: 12px 16px;
            cursor: pointer;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
            color: rgba(255, 255, 255, 0.7);
        }

        .category-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .category-item.active {
            background: rgba(255, 255, 255, 0.15);
            border-left-color: #198754;
            color: #fff;
        }

        .category-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            margin-right: 12px;
        }

        .sub-reports-sidebar {
            margin-left: 20px;
            margin-top: 5px;
            margin-bottom: 10px;
            border-left: 2px solid rgba(255, 255, 255, 0.2);
        }

        .sub-report-sidebar-item {
            padding: 8px 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
            display: flex;
            align-items: center;
        }

        .sub-report-sidebar-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .sub-report-sidebar-item.active {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            border-left: 2px solid #198754;
            margin-left: -2px;
        }

        .report-content {
            background: white;
            border-radius: 12px;
            min-height: calc(100vh - 120px);
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            height: 100%;
            border-left: 4px solid;
        }

        .stat-card.primary { border-left-color: #2a83df; }
        .stat-card.success { border-left-color: #198754; }
        .stat-card.warning { border-left-color: #ff9800; }
        .stat-card.danger { border-left-color: #dc3545; }

        .stat-value {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #64748b;
            font-size: 14px;
        }

        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }

        .data-table {
            margin-bottom: 0;
        }

        .data-table th {
            background: #f7f8fb;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            color: #475569;
            padding: 12px 15px;
            border-bottom: 2px solid #cbd5e1;
        }

        .data-table td {
            padding: 12px 15px;
            vertical-align: middle;
        }

        .profit-positive { color: #198754; font-weight: 600; }
        .profit-negative { color: #dc3545; font-weight: 600; }

        .date-filter-card {
            background: #f7f8fb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .period-btn {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .period-btn.active {
            background: #2a83df;
            color: white;
        }

        .action-buttons .btn {
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
        }

        @media print {
            .report-sidebar,
            .date-filter-card,
            .action-buttons,
            .sub-report-list,
            .no-print {
                display: none !important;
            }
            .report-content {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 10px !important;
            }
            .col-lg-9 {
                width: 100% !important;
                max-width: 100% !important;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .stat-card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
            }
            .table {
                font-size: 12px;
            }
            .modal {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            .report-sidebar {
                min-height: auto;
                margin-bottom: 20px;
            }

            .stat-card {
                margin-bottom: 15px;
            }

            .stat-value {
                font-size: 20px;
            }
        }
    </style>
    @endpush

    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1">
                            <i class="bi bi-file-earmark-bar-graph text-success me-2"></i>Reports Center
                        </h4>
                        <p class="text-muted mb-0">Comprehensive business analytics and reports</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar - Report Categories -->
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="report-sidebar p-3">
                    <h6 class="text-white-50 text-uppercase small mb-3 px-2">Report Categories</h6>

                    @foreach($reportCategories as $key => $category)
                    <div class="category-item {{ $activeCategory === $key ? 'active' : '' }}"
                         wire:click="setCategory('{{ $key }}')">
                        <div class="d-flex align-items-center">
                            <div class="category-icon">
                                <i class="bi {{ $category['icon'] }}"></i>
                            </div>
                            <span>{{ $category['label'] }}</span>
                        </div>
                    </div>
                    
                    {{-- Sub-reports nested under active category --}}
                    @if($activeCategory === $key)
                    <div class="sub-reports-sidebar">
                        @foreach($category['reports'] as $reportKey => $label)
                        <div class="sub-report-sidebar-item {{ $selectedReport === $reportKey ? 'active' : '' }}"
                             wire:click.stop="selectReport('{{ $reportKey }}')">
                            <i class="bi bi-chevron-right me-2"></i>{{ $label }}
                        </div>
                        @endforeach
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9 col-md-8">
                <div class="report-content p-4 shadow-sm">
                    <!-- Date Filters -->
                    @if($selectedReport)
                    <div class="mb-3">
                        <h5 class="fw-bold">
                            <i class="bi {{ $reportCategories[$activeCategory]['icon'] }} text-primary me-2"></i>
                            {{ $reportCategories[$activeCategory]['reports'][$selectedReport] ?? 'Report' }}
                        </h5>
                    </div>
                    <div class="date-filter-card">
                        <div class="row align-items-end">
                            <div class="col-md-3 mb-2 mb-md-0">
                                <label class="form-label small fw-bold">Start Date</label>
                                <input type="date" class="form-control" wire:model.live="reportStartDate">
                            </div>
                            <div class="col-md-3 mb-2 mb-md-0">
                                <label class="form-label small fw-bold">End Date</label>
                                <input type="date" class="form-control" wire:model.live="reportEndDate">
                            </div>
                            <div class="col-md-2 mb-2 mb-md-0">
                                <label class="form-label small fw-bold d-block">&nbsp;</label>
                                <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Reset
                                </button>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold d-block">&nbsp;</label>
                                <div class="action-buttons d-flex gap-2 justify-content-end">
                                    <button onclick="printReport()" class="btn btn-outline-primary">
                                        <i class="bi bi-printer me-1"></i> Print
                                    </button>
                                    <button onclick="downloadReport('pdf')" class="btn btn-danger">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                    </button>
                                    <button onclick="downloadReport('excel')" class="btn btn-success">
                                        <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if(in_array($selectedReport, ['pl-period-cogs', 'pl-period-stock']))
                        <div class="mt-3">
                            <label class="form-label small fw-bold">Period Type</label>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn period-btn {{ $periodType === 'daily' ? 'active' : 'btn-outline-secondary' }}"
                                        wire:click="$set('periodType', 'daily')">Daily</button>
                                <button type="button" class="btn period-btn {{ $periodType === 'weekly' ? 'active' : 'btn-outline-secondary' }}"
                                        wire:click="$set('periodType', 'weekly')">Weekly</button>
                                <button type="button" class="btn period-btn {{ $periodType === 'monthly' ? 'active' : 'btn-outline-secondary' }}"
                                        wire:click="$set('periodType', 'monthly')">Monthly</button>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Report Content -->
                    <div class="report-output">
                        <div wire:loading wire:target="selectReport, generateReport" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">Loading report...</p>
                        </div>
                        <div wire:loading.remove wire:target="selectReport, generateReport">
                            @include('livewire.admin.reports.report-content')
                        </div>
                    </div>
                    @else
                    <!-- No Report Selected -->
                    <div class="text-center py-5">
                        <div class="text-muted mb-3">
                            <i class="bi bi-bar-chart-line display-1"></i>
                        </div>
                        <h5 class="text-muted">Select a Report</h5>
                        <p class="text-muted">Choose a report from the list above to view analytics</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function printReport() {
        generateProfessionalReport('print');
    }

    function downloadReport(type) {
        if (type === 'pdf') {
            downloadPDF();
        } else if (type === 'excel') {
            exportToExcel();
        }
    }

    function downloadPDF() {
        const reportOutput = document.querySelector('.report-output');
        if (!reportOutput) {
            alert('No report content to download');
            return;
        }

        // Get report info
        const reportTitleEl = reportOutput.querySelector('h6');
        let reportTitle = reportTitleEl ? reportTitleEl.innerText.replace(/[\uD83C-\uDBFF\uDC00-\uDFFF]+/g, '').trim() : 'Report';
        reportTitle = reportTitle.replace(/^[\s\S]*?([\w\s&-]+Report)[\s\S]*$/i, '$1').trim() || reportTitle;
        
        const startDate = document.querySelector('input[wire\\:model\\.live="reportStartDate"]')?.value || '';
        const endDate = document.querySelector('input[wire\\:model\\.live="reportEndDate"]')?.value || '';
        const dateRange = startDate && endDate ? `${formatDate(startDate)} to ${formatDate(endDate)}` : 'All Time';

        // Get summary stats
        let summaryHTML = '';
        const statCards = reportOutput.querySelectorAll('.stat-card');
        if (statCards.length > 0) {
            summaryHTML = '<div class="summary-section"><table class="summary-table"><tr>';
            statCards.forEach(card => {
                const value = card.querySelector('.stat-value')?.innerText || '';
                const label = card.querySelector('.stat-label')?.innerText || '';
                summaryHTML += `<td class="summary-cell"><div class="summary-value">${value}</div><div class="summary-label">${label}</div></td>`;
            });
            summaryHTML += '</tr></table></div>';
        }

        // Get table content
        let tableHTML = '';
        const tables = reportOutput.querySelectorAll('.table-responsive table, table.table');
        tables.forEach(table => {
            const clonedTable = table.cloneNode(true);
            clonedTable.querySelectorAll('th:last-child, td:last-child').forEach(cell => {
                if (cell.innerText.toLowerCase().includes('action') || cell.querySelector('button')) {
                    cell.remove();
                }
            });
            tableHTML += clonedTable.outerHTML;
        });

        // Create hidden container for PDF generation
        const pdfContainer = document.createElement('div');
        pdfContainer.innerHTML = `
            <div class="report-container" style="font-family: Arial, sans-serif; padding: 20px; max-width: 100%;">
                <div class="report-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 3px solid #2a83df;">
                    <div class="company-info">
                        <div style="font-size: 24px; font-weight: 800; color: #2a83df;">MIKING</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 18px; font-weight: 700; color: #2a83df; margin-bottom: 5px;">\${reportTitle}</div>
                        <div style="font-size: 11px; color: #64748b;">Period: \${dateRange}</div>
                    </div>
                </div>
                \${summaryHTML.replace(/class="summary-section"/g, 'style="margin-bottom: 20px;"')
                             .replace(/class="summary-table"/g, 'style="width: 100%; border-collapse: collapse;"')
                             .replace(/class="summary-cell"/g, 'style="padding: 12px 15px; background: #e3f2fd; border: 1px solid #90caf9; text-align: center;"')
                             .replace(/class="summary-value"/g, 'style="font-size: 14px; font-weight: 700; color: #1a5fb8;"')
                             .replace(/class="summary-label"/g, 'style="font-size: 9px; color: #64748b; text-transform: uppercase; margin-top: 3px;"')}
                <div class="table-content">
                    \${tableHTML}
                </div>
                <div style="margin-top: 25px; padding-top: 12px; border-top: 2px solid #2a83df; display: flex; justify-content: space-between; font-size: 9px; color: #64748b;">
                    <div>Generated: \${new Date().toLocaleString()}</div>
                    <div>MIKING - Business Management System</div>
                </div>
            </div>
        `;

        // Add table styles
        const style = document.createElement('style');
        style.textContent = \`
            .report-container table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 9px; }
            .report-container thead tr { background: #2a83df !important; }
            .report-container th { padding: 8px 6px; text-align: left; font-weight: 600; color: #ffffff !important; border: 1px solid #1a5fb8; text-transform: uppercase; font-size: 8px; background: #2a83df !important; }
            .report-container td { padding: 6px; border: 1px solid #cbd5e1; vertical-align: middle; }
            .report-container tbody tr:nth-child(even) { background-color: #f7f8fb; }
            .report-container tfoot { background: #e3f2fd; font-weight: 600; }
            .report-container tfoot td { border-top: 2px solid #2a83df; }
            .report-container .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: 600; }
            .report-container .bg-success { background: #198754 !important; color: white !important; }
            .report-container .bg-warning { background: #ffc107 !important; color: #333 !important; }
            .report-container .bg-danger { background: #dc3545 !important; color: white !important; }
            .report-container .bg-primary { background: #2a83df !important; color: white !important; }
            .report-container .bg-secondary { background: #64748b !important; color: white !important; }
            .report-container .text-success { color: #198754 !important; }
            .report-container .text-danger { color: #dc3545 !important; }
            .report-container .fw-bold { font-weight: 600 !important; }
        \`;
        pdfContainer.appendChild(style);
        document.body.appendChild(pdfContainer);

        // Generate PDF options
        const opt = {
            margin: [10, 10, 10, 10],
            filename: reportTitle.replace(/[^a-z0-9]/gi, '_') + '_' + new Date().toISOString().split('T')[0] + '.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true, letterRendering: true },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
        };

        // Generate and download PDF
        html2pdf().set(opt).from(pdfContainer.querySelector('.report-container')).save().then(() => {
            document.body.removeChild(pdfContainer);
        });
    }

    function generateProfessionalReport(action) {
        const reportOutput = document.querySelector('.report-output');
        if (!reportOutput) {
            alert('No report content to download');
            return;
        }

        // Get report info
        const reportTitleEl = reportOutput.querySelector('h6');
        let reportTitle = reportTitleEl ? reportTitleEl.innerText.replace(/[\uD83C-\uDBFF\uDC00-\uDFFF]+/g, '').trim() : 'Report';
        reportTitle = reportTitle.replace(/^[\s\S]*?([\w\s&-]+Report)[\s\S]*$/i, '$1').trim() || reportTitle;
        
        const startDate = document.querySelector('input[wire\\:model\\.live="reportStartDate"]')?.value || '';
        const endDate = document.querySelector('input[wire\\:model\\.live="reportEndDate"]')?.value || '';
        const dateRange = startDate && endDate ? \`\${formatDate(startDate)} to \${formatDate(endDate)}\` : 'All Time';

        // Get summary stats
        let summaryHTML = '';
        const statCards = reportOutput.querySelectorAll('.stat-card');
        if (statCards.length > 0) {
            summaryHTML = '<div class="summary-section"><table class="summary-table"><tr>';
            statCards.forEach(card => {
                const value = card.querySelector('.stat-value')?.innerText || '';
                const label = card.querySelector('.stat-label')?.innerText || '';
                summaryHTML += \`<td class="summary-cell"><div class="summary-value">\${value}</div><div class="summary-label">\${label}</div></td>\`;
            });
            summaryHTML += '</tr></table></div>';
        }

        // Get table content
        let tableHTML = '';
        const tables = reportOutput.querySelectorAll('.table-responsive table, table.table');
        tables.forEach(table => {
            const clonedTable = table.cloneNode(true);
            // Remove action column if exists
            clonedTable.querySelectorAll('th:last-child, td:last-child').forEach(cell => {
                if (cell.innerText.toLowerCase().includes('action') || cell.querySelector('button')) {
                    cell.remove();
                }
            });
            tableHTML += clonedTable.outerHTML;
        });

        // Create professional print window
        const printWindow = window.open('', '_blank');
        printWindow.document.write(\`
            <!DOCTYPE html>
            <html\u003E
            <head\u003E
                <title>\${reportTitle}</title\u003E
                <style\u003E
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { 
                        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                        background: #fff;
                        color: #1e293b;
                        font-size: 11px;
                        line-height: 1.4;
                    }
                    .report-container {
                        max-width: 100%;
                        margin: 0 auto;
                        padding: 15px 25px;
                    }
                    /* Header */
                    .report-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                        margin-bottom: 20px;
                        padding-bottom: 15px;
                        border-bottom: 3px solid #2a83df;
                    }
                    .company-info {
                        display: flex;
                        align-items: center;
                    }
                    .company-logo {
                        font-size: 28px;
                        font-weight: 800;
                        color: #2a83df;
                        letter-spacing: -1px;
                    }
                    .company-logo span {
                        color: #1a5fb8;
                    }
                    .report-title-section {
                        text-align: right;
                    }
                    .report-title {
                        font-size: 22px;
                        font-weight: 700;
                        color: #2a83df;
                        margin-bottom: 5px;
                    }
                    .report-period {
                        font-size: 11px;
                        color: #64748b;
                    }
                    /* Summary */
                    .summary-section {
                        margin-bottom: 20px;
                    }
                    .summary-table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    .summary-cell {
                        padding: 12px 15px;
                        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
                        border: 1px solid #90caf9;
                        text-align: center;
                    }
                    .summary-value {
                        font-size: 16px;
                        font-weight: 700;
                        color: #1a5fb8;
                    }
                    .summary-label {
                        font-size: 10px;
                        color: #64748b;
                        text-transform: uppercase;
                        margin-top: 3px;
                    }
                    /* Table */
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 15px;
                        font-size: 10px;
                    }
                    thead tr {
                        background: linear-gradient(135deg, #2a83df 0%, #1a5fb8 100%);
                    }
                    th {
                        padding: 10px 8px;
                        text-align: left;
                        font-weight: 600;
                        color: #ffffff;
                        border: 1px solid #1a5fb8;
                        text-transform: uppercase;
                        font-size: 9px;
                        letter-spacing: 0.5px;
                    }
                    td {
                        padding: 8px;
                        border: 1px solid #cbd5e1;
                        vertical-align: middle;
                    }
                    tbody tr:nth-child(even) {
                        background-color: #f7f8fb;
                    }
                    tbody tr:hover {
                        background-color: #e3f2fd;
                    }
                    tfoot {
                        background: #e3f2fd;
                        font-weight: 600;
                    }
                    tfoot td {
                        border-top: 2px solid #2a83df;
                    }
                    /* Badge styling */
                    .badge {
                        display: inline-block;
                        padding: 3px 8px;
                        border-radius: 3px;
                        font-size: 9px;
                        font-weight: 600;
                    }
                    .bg-success, .badge-success { background: #198754 !important; color: white; }
                    .bg-warning, .badge-warning { background: #ffc107 !important; color: #333; }
                    .bg-danger, .badge-danger { background: #dc3545 !important; color: white; }
                    .bg-primary, .badge-primary { background: #2a83df !important; color: white; }
                    .bg-secondary, .badge-secondary { background: #64748b !important; color: white; }
                    .bg-light { background: #f7f8fb !important; color: #333; }
                    .text-success { color: #198754 !important; }
                    .text-danger { color: #dc3545 !important; }
                    .text-muted { color: #64748b !important; }
                    .fw-bold { font-weight: 600 !important; }
                    /* Footer */
                    .report-footer {
                        margin-top: 25px;
                        padding-top: 12px;
                        border-top: 2px solid #2a83df;
                        display: flex;
                        justify-content: space-between;
                        font-size: 9px;
                        color: #64748b;
                    }
                    /* Print specific */
                    @media print {
                        body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
                        .report-container { padding: 0; }
                        @page { margin: 15mm 10mm; size: A4 landscape; }
                    }
                    /* Hide stat cards in print */
                    .stat-card, .row.mb-4 { display: none !important; }
                </style\u003E
            </head\u003E
            <body\u003E
                <div class="report-container"\u003E
                    <div class="report-header"\u003E
                        <div class="company-info"\u003E
                            <div class="company-logo"\u003EMIKING</div\u003E
                        </div\u003E
                        <div class="report-title-section"\u003E
                            <div class="report-title"\u003E\${reportTitle}</div\u003E
                            <div class="report-period"\u003EPeriod: \${dateRange}</div\u003E
                        </div\u003E
                    </div\u003E
                    \${summaryHTML}
                    <div class="table-content"\u003E
                        \${tableHTML}
                    </div\u003E
                    <div class="report-footer"\u003E
                        <div\u003EGenerated: \${new Date().toLocaleString()}</div\u003E
                        <div\u003EPage 1 of 1</div\u003E
                        <div\u003EMIKING - Business Management System</div\u003E
                    </div\u003E
                </div\u003E
            </body\u003E
            </html\u003E
        \`);
        printWindow.document.close();
        
        setTimeout(() => {
            if (action === 'print' || action === 'pdf') {
                printWindow.print();
            }
        }, 500);
    }

    function exportToExcel() {
        const reportOutput = document.querySelector('.report-output');
        if (!reportOutput) {
            alert('No report content to download');
            return;
        }

        const tables = reportOutput.querySelectorAll('table');
        if (tables.length === 0) {
            alert('No table data found to export');
            return;
        }

        const reportTitleEl = reportOutput.querySelector('h6');
        let reportTitle = reportTitleEl ? reportTitleEl.innerText.replace(/[\uD83C-\uDBFF\uDC00-\uDFFF]+/g, '').trim() : 'Report';
        
        let csvContent = '';
        csvContent += 'MIKING\\n';
        csvContent += reportTitle + '\\n';
        csvContent += 'Generated: ' + new Date().toLocaleString() + '\\n\\n';

        tables.forEach((table) => {
            const rows = table.querySelectorAll('tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('th, td');
                const rowData = [];
                let skipRow = false;
                
                cells.forEach((cell, index) => {
                    // Skip action columns
                    if (cell.innerText.toLowerCase().includes('action') || cell.querySelector('button')) {
                        if (index === cells.length - 1) return;
                        skipRow = true;
                    }
                    if (!cell.querySelector('button')) {
                        let text = cell.innerText.replace(/[\\n\\r]+/g, ' ').trim();
                        if (text.includes(',') || text.includes('"')) {
                            text = '"' + text.replace(/"/g, '""') + '"';
                        }
                        rowData.push(text);
                    }
                });
                
                if (rowData.length > 0) {
                    csvContent += rowData.join(',') + '\\n';
                }
            });
            csvContent += '\\n';
        });

        const blob = new Blob(["\\ufeff" + csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = reportTitle.replace(/[^a-z0-9]/gi, '_') + '_' + new Date().toISOString().split('T')[0] + '.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }
</script>
@endpush