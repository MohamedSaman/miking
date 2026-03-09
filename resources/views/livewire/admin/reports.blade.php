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
            cursor: pointer;
            user-select: none;
            position: relative;
            white-space: nowrap;
        }

        .data-table th:hover {
            background: #edf0f5;
            color: #2a83df;
        }

        .data-table th .sort-icon {
            display: inline-block;
            margin-left: 5px;
            font-size: 11px;
            opacity: 0.3;
            vertical-align: middle;
        }

        .data-table th.sort-asc .sort-icon,
        .data-table th.sort-desc .sort-icon {
            opacity: 1;
            color: #2a83df;
        }

        .data-table th.no-sort {
            cursor: default;
        }

        .data-table th.no-sort:hover {
            background: #f7f8fb;
            color: #475569;
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
                        <div class="d-flex flex-wrap align-items-end justify-content-between gap-2">
                            <div class="d-flex flex-wrap align-items-end gap-2">
                                <div>
                                    <label class="form-label small fw-bold mb-1">Start Date</label>
                                    <input type="date" class="form-control" wire:model.live="reportStartDate" style="min-width: 150px;">
                                </div>
                                <div>
                                    <label class="form-label small fw-bold mb-1">End Date</label>
                                    <input type="date" class="form-control" wire:model.live="reportEndDate" style="min-width: 150px;">
                                </div>
                                <div>
                                    <button wire:click="clearFilters" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                            <div class="action-buttons d-flex gap-2">
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
    function getReportInfo() {
        var reportOutput = document.querySelector('.report-output');
        if (!reportOutput) return null;

        var titleEl = reportOutput.querySelector('h6');
        var reportTitle = titleEl ? titleEl.innerText.replace(/[\uD83C-\uDBFF\uDC00-\uDFFF]+/g, '').trim() : 'Report';

        var startDateInput = document.querySelector('input[wire\\:model\\.live="reportStartDate"]');
        var endDateInput = document.querySelector('input[wire\\:model\\.live="reportEndDate"]');
        var startDate = startDateInput ? startDateInput.value : '';
        var endDate = endDateInput ? endDateInput.value : '';
        var dateRange = (startDate && endDate) ? formatDate(startDate) + ' to ' + formatDate(endDate) : 'All Time';

        return { reportOutput: reportOutput, reportTitle: reportTitle, dateRange: dateRange };
    }

    function getSummaryHTML(reportOutput) {
        var html = '';
        var statCards = reportOutput.querySelectorAll('.stat-card');
        if (statCards.length > 0) {
            html += '<table style="width:100%;border-collapse:collapse;margin-bottom:20px;"><tr>';
            statCards.forEach(function(card) {
                var value = card.querySelector('.stat-value') ? card.querySelector('.stat-value').innerText : '';
                var label = card.querySelector('.stat-label') ? card.querySelector('.stat-label').innerText : '';
                html += '<td style="padding:12px 15px;background:#e3f2fd;border:1px solid #90caf9;text-align:center;">';
                html += '<div style="font-size:14px;font-weight:700;color:#1a5fb8;">' + value + '</div>';
                html += '<div style="font-size:9px;color:#64748b;text-transform:uppercase;margin-top:3px;">' + label + '</div>';
                html += '</td>';
            });
            html += '</tr></table>';
        }
        return html;
    }

    function getCleanTableHTML(reportOutput) {
        var html = '';
        var tables = reportOutput.querySelectorAll('.table-responsive table, table.table');
        tables.forEach(function(table) {
            var clonedTable = table.cloneNode(true);
            // Remove action columns
            var headerCells = clonedTable.querySelectorAll('thead th');
            var actionColIndex = -1;
            headerCells.forEach(function(th, idx) {
                if (th.innerText.toLowerCase().trim() === 'action' || th.innerText.toLowerCase().trim() === 'actions') {
                    actionColIndex = idx;
                }
            });
            if (actionColIndex >= 0) {
                clonedTable.querySelectorAll('tr').forEach(function(row) {
                    var cells = row.querySelectorAll('th, td');
                    if (cells[actionColIndex]) {
                        cells[actionColIndex].remove();
                    }
                });
            }
            // Remove sort icons
            clonedTable.querySelectorAll('.sort-icon').forEach(function(icon) {
                icon.remove();
            });
            // Also remove any cells with only buttons
            clonedTable.querySelectorAll('td').forEach(function(td) {
                if (td.querySelector('button') && td.innerText.trim() === '') {
                    td.remove();
                }
            });
            html += clonedTable.outerHTML;
        });
        return html;
    }

    function buildReportHTML(info, summaryHTML, tableHTML) {
        var html = '';
        html += '<!DOCTYPE html>';
        html += '<html><head><title>' + info.reportTitle + '</title>';
        html += '<style>';
        html += '* { margin: 0; padding: 0; box-sizing: border-box; }';
        html += 'body { font-family: Arial, "Segoe UI", sans-serif; background: #fff; color: #1e293b; font-size: 11px; line-height: 1.4; }';
        html += '.report-container { max-width: 100%; margin: 0 auto; padding: 15px 25px; }';
        html += '.report-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 3px solid #2a83df; }';
        html += '.company-logo { font-size: 28px; font-weight: 800; color: #2a83df; }';
        html += '.report-title { font-size: 22px; font-weight: 700; color: #2a83df; margin-bottom: 5px; }';
        html += '.report-period { font-size: 11px; color: #64748b; }';
        html += 'table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 10px; }';
        html += 'thead tr { background: #2a83df; }';
        html += 'th { padding: 10px 8px; text-align: left; font-weight: 600; color: #ffffff; border: 1px solid #1a5fb8; text-transform: uppercase; font-size: 9px; background: #2a83df; }';
        html += 'td { padding: 8px; border: 1px solid #cbd5e1; vertical-align: middle; }';
        html += 'tbody tr:nth-child(even) { background-color: #f7f8fb; }';
        html += 'tfoot { background: #e3f2fd; font-weight: 600; }';
        html += 'tfoot td { border-top: 2px solid #2a83df; }';
        html += '.badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 9px; font-weight: 600; }';
        html += '.bg-success { background: #198754 !important; color: white !important; }';
        html += '.bg-warning { background: #ffc107 !important; color: #333 !important; }';
        html += '.bg-danger { background: #dc3545 !important; color: white !important; }';
        html += '.bg-primary { background: #2a83df !important; color: white !important; }';
        html += '.bg-secondary { background: #64748b !important; color: white !important; }';
        html += '.bg-light { background: #f7f8fb !important; color: #333 !important; }';
        html += '.text-success { color: #198754 !important; }';
        html += '.text-danger { color: #dc3545 !important; }';
        html += '.text-muted { color: #64748b !important; }';
        html += '.fw-bold { font-weight: 600 !important; }';
        html += '.report-footer { margin-top: 25px; padding-top: 12px; border-top: 2px solid #2a83df; display: flex; justify-content: space-between; font-size: 9px; color: #64748b; }';
        html += '@' + 'media print {';
        html += '  body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }';
        html += '  .report-container { padding: 0; }';
        html += '  @' + 'page { margin: 10mm; size: A4; }';
        html += '}';
        html += '</style></head><body>';
        html += '<div class="report-container">';
        html += '<div class="report-header">';
        html += '<div><div class="company-logo">MIKING</div></div>';
        html += '<div style="text-align:right;">';
        html += '<div class="report-title">' + info.reportTitle + '</div>';
        html += '<div class="report-period">Period: ' + info.dateRange + '</div>';
        html += '</div></div>';
        html += summaryHTML;
        html += '<div class="table-content">' + tableHTML + '</div>';
        html += '<div class="report-footer">';
        html += '<div>Generated: ' + new Date().toLocaleString() + '</div>';
        html += '<div>MIKING - Business Management System</div>';
        html += '</div></div></body></html>';
        return html;
    }

    function printReport() {
        var info = getReportInfo();
        if (!info) { alert('No report content found'); return; }

        var summaryHTML = getSummaryHTML(info.reportOutput);
        var tableHTML = getCleanTableHTML(info.reportOutput);

        if (!tableHTML && !summaryHTML) {
            alert('No report data to print');
            return;
        }

        var fullHTML = buildReportHTML(info, summaryHTML, tableHTML);
        var printWindow = window.open('', '_blank');
        if (!printWindow) {
            alert('Please allow popups to print the report');
            return;
        }
        printWindow.document.write(fullHTML);
        printWindow.document.close();

        printWindow.onload = function() {
            printWindow.focus();
            printWindow.print();
        };
    }

    function downloadReport(type) {
        if (type === 'pdf') {
            downloadPDF();
        } else if (type === 'excel') {
            exportToExcel();
        }
    }

    function downloadPDF() {
        var info = getReportInfo();
        if (!info) { alert('No report content found'); return; }

        var summaryHTML = getSummaryHTML(info.reportOutput);
        var tableHTML = getCleanTableHTML(info.reportOutput);

        if (!tableHTML && !summaryHTML) {
            alert('No report data to download');
            return;
        }

        // Check if html2pdf is loaded
        if (typeof html2pdf === 'undefined') {
            alert('PDF library is loading. Please try again.');
            return;
        }

        // Build the content container
        var pdfContainer = document.createElement('div');
        pdfContainer.style.position = 'absolute';
        pdfContainer.style.left = '-9999px';
        pdfContainer.style.top = '0';
        pdfContainer.style.width = '277mm'; // A4 landscape width minus margins

        var containerDiv = document.createElement('div');
        containerDiv.style.fontFamily = 'Arial, sans-serif';
        containerDiv.style.padding = '15px';
        containerDiv.style.maxWidth = '100%';
        containerDiv.style.color = '#1e293b';
        containerDiv.style.fontSize = '10px';
        containerDiv.style.lineHeight = '1.4';

        // Header
        var headerHTML = '<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;padding-bottom:15px;border-bottom:3px solid #2a83df;">';
        headerHTML += '<div style="font-size:24px;font-weight:800;color:#2a83df;">MIKING</div>';
        headerHTML += '<div style="text-align:right;">';
        headerHTML += '<div style="font-size:18px;font-weight:700;color:#2a83df;margin-bottom:5px;">' + info.reportTitle + '</div>';
        headerHTML += '<div style="font-size:11px;color:#64748b;">Period: ' + info.dateRange + '</div>';
        headerHTML += '</div></div>';

        // Footer
        var footerHTML = '<div style="margin-top:25px;padding-top:12px;border-top:2px solid #2a83df;display:flex;justify-content:space-between;font-size:9px;color:#64748b;">';
        footerHTML += '<div>Generated: ' + new Date().toLocaleString() + '</div>';
        footerHTML += '<div>MIKING - Business Management System</div>';
        footerHTML += '</div>';

        containerDiv.innerHTML = headerHTML + summaryHTML + '<div>' + tableHTML + '</div>' + footerHTML;

        // Apply inline styles to tables for PDF
        var tables = containerDiv.querySelectorAll('table');
        tables.forEach(function(table) {
            table.style.width = '100%';
            table.style.borderCollapse = 'collapse';
            table.style.marginBottom = '15px';
            table.style.fontSize = '9px';
        });
        containerDiv.querySelectorAll('thead tr').forEach(function(tr) {
            tr.style.background = '#2a83df';
        });
        containerDiv.querySelectorAll('th').forEach(function(th) {
            th.style.padding = '8px 6px';
            th.style.textAlign = 'left';
            th.style.fontWeight = '600';
            th.style.color = '#ffffff';
            th.style.border = '1px solid #1a5fb8';
            th.style.textTransform = 'uppercase';
            th.style.fontSize = '8px';
            th.style.background = '#2a83df';
        });
        containerDiv.querySelectorAll('td').forEach(function(td) {
            td.style.padding = '6px';
            td.style.border = '1px solid #cbd5e1';
            td.style.verticalAlign = 'middle';
        });

        pdfContainer.appendChild(containerDiv);
        document.body.appendChild(pdfContainer);

        var filename = info.reportTitle.replace(/[^a-z0-9]/gi, '_') + '_' + new Date().toISOString().split('T')[0] + '.pdf';

        var opt = {
            margin: [10, 10, 10, 10],
            filename: filename,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true, letterRendering: true, scrollY: 0 },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(containerDiv).save().then(function() {
            document.body.removeChild(pdfContainer);
        }).catch(function(err) {
            console.error('PDF generation error:', err);
            document.body.removeChild(pdfContainer);
            alert('Failed to generate PDF. Please try again.');
        });
    }

    function exportToExcel() {
        var info = getReportInfo();
        if (!info) { alert('No report content found'); return; }

        var tables = info.reportOutput.querySelectorAll('.table-responsive table, table.table');
        if (tables.length === 0) {
            alert('No table data found to export');
            return;
        }

        // Build Excel-compatible HTML table
        // Note: x: namespace tags split with concatenation to prevent Blade from parsing them as components
        var excelHTML = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:' + 'x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        excelHTML += '<head><meta charset="utf-8">';
        excelHTML += '<!--[if gte mso 9]><' + 'xml><' + 'x:ExcelWorkbook><' + 'x:ExcelWorksheets><' + 'x:ExcelWorksheet>';
        excelHTML += '<' + 'x:Name>Report</' + 'x:Name>';
        excelHTML += '<' + 'x:WorksheetOptions><' + 'x:DisplayGridlines/></' + 'x:WorksheetOptions>';
        excelHTML += '</' + 'x:ExcelWorksheet></' + 'x:ExcelWorksheets></' + 'x:ExcelWorkbook></' + 'xml><![endif]-->';
        excelHTML += '<style>';
        excelHTML += 'table { border-collapse: collapse; width: 100%; }';
        excelHTML += 'th { background-color: #2a83df; color: #ffffff; font-weight: bold; padding: 8px; border: 1px solid #1a5fb8; text-align: left; }';
        excelHTML += 'td { padding: 6px; border: 1px solid #cbd5e1; vertical-align: middle; }';
        excelHTML += 'tr:nth-child(even) { background-color: #f7f8fb; }';
        excelHTML += '.header-cell { font-size: 18px; font-weight: bold; color: #2a83df; border: none; }';
        excelHTML += '.info-cell { font-size: 11px; color: #64748b; border: none; }';
        excelHTML += '</style></head><body>';

        // Title row
        excelHTML += '<table><tr><td class="header-cell" colspan="10">MIKING - ' + info.reportTitle + '</td></tr>';
        excelHTML += '<tr><td class="info-cell" colspan="10">Period: ' + info.dateRange + '</td></tr>';
        excelHTML += '<tr><td class="info-cell" colspan="10">Generated: ' + new Date().toLocaleString() + '</td></tr>';
        excelHTML += '<tr><td colspan="10"></td></tr></table>';

        tables.forEach(function(table) {
            var clonedTable = table.cloneNode(true);
            // Find and remove action columns
            var headerCells = clonedTable.querySelectorAll('thead th');
            var actionColIndex = -1;
            headerCells.forEach(function(th, idx) {
                if (th.innerText.toLowerCase().trim() === 'action' || th.innerText.toLowerCase().trim() === 'actions') {
                    actionColIndex = idx;
                }
            });
            if (actionColIndex >= 0) {
                clonedTable.querySelectorAll('tr').forEach(function(row) {
                    var cells = row.querySelectorAll('th, td');
                    if (cells[actionColIndex]) {
                        cells[actionColIndex].remove();
                    }
                });
            }
            // Remove sort icons
            clonedTable.querySelectorAll('.sort-icon').forEach(function(icon) {
                icon.remove();
            });
            // Remove buttons from cells
            clonedTable.querySelectorAll('button, a.btn').forEach(function(btn) {
                btn.remove();
            });
            excelHTML += clonedTable.outerHTML;
        });

        excelHTML += '</body></html>';

        var blob = new Blob(['\ufeff' + excelHTML], { type: 'application/vnd.ms-excel;charset=utf-8;' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = info.reportTitle.replace(/[^a-z0-9]/gi, '_') + '_' + new Date().toISOString().split('T')[0] + '.xls';
        document.body.appendChild(link);
        link.click();
        setTimeout(function() {
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        }, 100);
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        var date = new Date(dateStr + 'T00:00:00');
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    // ==================== SORTABLE TABLES ====================

    function initSortableTables() {
        var tables = document.querySelectorAll('.data-table');
        tables.forEach(function(table) {
            // Skip if already initialized
            if (table.getAttribute('data-sortable') === 'true') return;
            table.setAttribute('data-sortable', 'true');

            var headers = table.querySelectorAll('thead th');
            headers.forEach(function(th, colIndex) {
                // Skip Action columns
                var text = th.innerText.toLowerCase().trim();
                if (text === 'action' || text === 'actions' || text === 'notes') {
                    th.classList.add('no-sort');
                    return;
                }

                // Add sort icon
                if (!th.querySelector('.sort-icon')) {
                    var icon = document.createElement('span');
                    icon.className = 'sort-icon';
                    icon.innerHTML = '&#8597;'; // up-down arrow
                    th.appendChild(icon);
                }

                th.addEventListener('click', function() {
                    sortTable(table, colIndex, th);
                });
            });
        });
    }

    function sortTable(table, colIndex, clickedTh) {
        var tbody = table.querySelector('tbody');
        if (!tbody) return;

        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
        if (rows.length === 0) return;

        // Determine sort direction
        var isAsc = clickedTh.classList.contains('sort-asc');
        var direction = isAsc ? 'desc' : 'asc';

        // Remove sort classes from all headers in this table
        var allTh = table.querySelectorAll('thead th');
        allTh.forEach(function(th) {
            th.classList.remove('sort-asc', 'sort-desc');
            var icon = th.querySelector('.sort-icon');
            if (icon) icon.innerHTML = '&#8597;';
        });

        // Set current sort
        clickedTh.classList.add('sort-' + direction);
        var icon = clickedTh.querySelector('.sort-icon');
        if (icon) icon.innerHTML = direction === 'asc' ? '&#8593;' : '&#8595;';

        // Sort rows
        rows.sort(function(a, b) {
            var cellA = a.querySelectorAll('td')[colIndex];
            var cellB = b.querySelectorAll('td')[colIndex];
            if (!cellA || !cellB) return 0;

            var valA = getCellSortValue(cellA);
            var valB = getCellSortValue(cellB);

            // Try numeric comparison
            var numA = parseNumericValue(valA);
            var numB = parseNumericValue(valB);

            if (!isNaN(numA) && !isNaN(numB)) {
                return direction === 'asc' ? numA - numB : numB - numA;
            }

            // Try date comparison
            var dateA = parseDateValue(valA);
            var dateB = parseDateValue(valB);
            if (dateA && dateB) {
                return direction === 'asc' ? dateA - dateB : dateB - dateA;
            }

            // String comparison
            var strA = valA.toLowerCase();
            var strB = valB.toLowerCase();
            if (strA < strB) return direction === 'asc' ? -1 : 1;
            if (strA > strB) return direction === 'asc' ? 1 : -1;
            return 0;
        });

        // Re-append sorted rows
        rows.forEach(function(row) {
            tbody.appendChild(row);
        });
    }

    function getCellSortValue(cell) {
        // Get badge text if present
        var badge = cell.querySelector('.badge');
        if (badge) return badge.innerText.trim();
        return cell.innerText.trim();
    }

    function parseNumericValue(str) {
        // Remove currency symbols, commas, Rs., %, "items", "days" etc.
        var cleaned = str.replace(/[Rr][Ss]\.?\s*/g, '').replace(/,/g, '').replace(/%/g, '').replace(/\s*(items|days|units)\s*/gi, '').trim();
        // Handle parentheses for negative numbers
        if (cleaned.match(/^\(.*\)$/)) {
            cleaned = '-' + cleaned.replace(/[()]/g, '');
        }
        var num = parseFloat(cleaned);
        return num;
    }

    function parseDateValue(str) {
        // Try common date formats: "01 Jan 2025", "2025-01-01"
        var d = new Date(str);
        if (!isNaN(d.getTime()) && str.match(/[a-zA-Z]/) && str.length > 5) {
            return d.getTime();
        }
        return null;
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initSortableTables, 500);
    });

    // Re-initialize after Livewire updates
    document.addEventListener('livewire:init', function() {
        Livewire.hook('morph.updated', function() {
            setTimeout(initSortableTables, 300);
        });
    });

    // Also listen for generic Livewire v3 events
    if (typeof Livewire !== 'undefined') {
        document.addEventListener('livewire:navigated', function() {
            setTimeout(initSortableTables, 300);
        });
    }
</script>
@endpush