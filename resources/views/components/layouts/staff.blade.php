<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Page Title' }}</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Barcode scanner library -->
    <script src="https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js" defer></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>

    <!-- Inter font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Theme tokens: Blue & White theme */
        :root {
            /* Backgrounds - Page background uses a subtle off-white for theme */
            --page-bg: #f7f8fbff;
            --surface: #ffffff;

            /* Primary / Brand - Blue */
            --primary: #2a83df;
            --primary-600: #1a5fb8;
            --primary-100: #e3f2fd;

            /* Accent - keep for amounts */
            --accent: #198754;

            /* Muted / borders / text - Blue-gray tones */
            --muted: #64748b;
            --muted-2: #475569;
            --border: #cbd5e1;
            --muted-3: #e2e8f0;

            /* Status colors - keep distinct */
            --success-bg: #d1e7dd;
            --success-text: #0f5132;
            --warning-bg: #fff3cd;
            --warning-text: #664d03;
            --danger-bg: #f8d7da;
            --danger-text: #842029;

            /* Topbar / sidebar - Blue */
            --sidebar-bg: #2a83df;
            --topbar-bg: #ffffff;

            /* Text - Dark */
            --text: #1e293b;

            /* Avatars */
            --avatar-bg: #2a83df;
            --avatar-text: #ffffff;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--page-bg);
            color: var(--text);
            letter-spacing: -0.01em;
        }

        /* Ensure dropdowns in table are not clipped */
        .table-responsive {
            overflow: visible !important;
        }

        .dropdown-menu {
            position: absolute !important;

            left: auto !important;
            right: 0 !important;
            top: 30% !important;
            margin-top: 0.2rem;
            min-width: 160px;
            z-index: 9999 !important;
            background: #fff !important;
            box-shadow: 0 12px 32px 0 rgba(0, 0, 0, 0.22), 0 2px 8px 0 rgba(0, 0, 0, 0.10);
            border-radius: 8px !important;
            border: 1px solid #e2e8f0 !important;
            overflow: visible !important;
            filter: none !important;
        }

        .dropdown-menu>li>.dropdown-item {
            background: #fff !important;
            z-index: 9999 !important;
        }

        .dropdown-menu>li>.dropdown-item:active,
        .dropdown-menu>li>.dropdown-item:focus {
            background: #f0f7ff !important;
            color: #222 !important;
        }

        .dropdown {
            position: relative !important;
        }

        .container-fluid,
        .card,
        .modal-content {
            font-size: 13px !important;
        }

        .table th,
        .table td {
            font-size: 12px !important;
            padding: 0.35rem 0.5rem !important;
        }

        .modal-header {
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
            margin-bottom: 0.25rem !important;
        }

        .modal-footer,
        .card-header,
        .card-body,
        .row,
        .col-md-6,
        .col-md-4,
        .col-md-2,
        .col-md-12 {
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
            margin-top: 0.25rem !important;
            margin-bottom: 0.25rem !important;
        }

        .form-control,
        .form-select {
            font-size: 12px !important;
            padding: 0.35rem 0.5rem !important;
        }

        .btn,
        .btn-sm,
        .btn-primary,
        .btn-secondary,
        .btn-outline-danger,
        .btn-outline-secondary {
            font-size: 12px !important;
            padding: 0.25rem 0.5rem !important;
        }

        .badge {
            font-size: 11px !important;
            padding: 0.25em 0.5em !important;
        }

        .list-group-item,
        .dropdown-item {
            font-size: 12px !important;
            padding: 0.35rem 0.5rem !important;
        }

        .summary-card,
        .card {
            border-radius: 8px !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
        }

        .icon-container {
            width: 36px !important;
            height: 36px !important;
            font-size: 1.1rem !important;
        }

        /* Sidebar styles */
        .sidebar {
            width: 265px;
            height: 100vh;
            background: linear-gradient(180deg, #2a83df 0%, #1a5fb8 100%);
            color: #ffffff;

            padding: 0 0 20px;
            position: fixed;
            transition: all 0.3s ease;
            z-index: 1040;
            overflow-y: auto;
            /* Enable vertical scrolling */
            overflow-x: hidden;
            /* Hide horizontal overflow */
            box-shadow: 2px 0 8px rgba(42, 131, 223, 0.25);
        }

        /* Add custom scrollbar styling for sidebar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        /* Add padding to the bottom of sidebar to ensure last items are visible */
        .sidebar .nav {
            padding-bottom: 50px;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar.collapsed .sidebar-title,
        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0;
            font-size: 1.25rem;
        }

        .sidebar.collapsed .nav-link {
            text-align: center;
            padding: 10px;
        }

        .sidebar.collapsed .nav-link.dropdown-toggle::after {
            display: none;
        }

        .sidebar-header {
            padding: 20px 20px 0;
            margin-bottom: 5px;
            
        }

        .sidebar-title {
            font-weight: 600;
            font-size: 1.2rem;
            color: #ffffff;
            letter-spacing: -0.02em;
            
        }

        /* Navigation styles */
        .nav-item {
            margin: 2px 0;
            /* Reduced from 5px to 2px */
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9);
            padding: 8px 20px;
            transition: all 0.2s;
        }


        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.15) 100%);
            color: #ffffff;
            font-weight: 500;
            border-left: 3px solid #ffffff;
        }

        .nav-link:focus,
        .nav-link:hover,
        .nav-link:focus-visible {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
            outline: none;
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .nav-link.dropdown-toggle::after {
            display: inline-block;
            margin-left: 0.255em;
            content: "";
            border-top: 0.3em solid;
            border-right: 0.3em solid transparent;
            border-bottom: 0;
            border-left: 0.3em solid transparent;
            float: right;
            margin-top: 8px;
        }

        #inventorySubmenu .nav-link,
        #hrSubmenu .nav-link,
        #salesSubmenu .nav-link,
        #stockSubmenu .nav-link,
        #purchaseSubmenu .nav-link {
            padding: 5px 15px;
            /* Reduced padding for all submenu links */
            font-size: 0.9rem;
        }

        /* Add these styles to further improve submenu spacing */
        .collapse .nav-item {
            margin: 1px 0;
            /* Even more compact spacing for submenu items */
        }

        .collapse .nav.flex-column {
            padding-bottom: 0;
            /* Remove extra bottom padding from nested menus */
            padding-top: 2px;
            /* Add small top padding to separate from parent */
        }

        .collapse .nav-item:last-child {
            margin-bottom: 3px;
            /* Add small space after last submenu item */
        }

        /* Disabled menu item styles */
        .nav-link.disabled {
            color: rgba(255, 255, 255, 0.4) !important;
            cursor: not-allowed !important;
            opacity: 0.6;
            pointer-events: none;
        }

        .nav-link.disabled i {
            color: rgba(255, 255, 255, 0.4) !important;
        }

        .nav-link.disabled:hover {
            background-color: transparent !important;
            color: rgba(255, 255, 255, 0.4) !important;
        }

        /* Top bar styles */
        .top-bar {
            height: 60px;
            background: linear-gradient(135deg, #2a83df 0%, #1a5fb8 100%);
            border-bottom: none;
            padding: 0 20px;
            position: fixed;
            top: 0;
            right: 0;
            left: 250px;
            z-index: 1000;
            display: flex;
            align-items: center;
            transition: left 0.3s ease;
            box-shadow: 0 2px 8px rgba(42, 131, 223, 0.3);
        }

        .top-bar.collapsed {
            left: 70px;
        }

        .top-bar .title {
            color: #ffffff;
        }

        /* User info styles */
        .admin-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px;
            border-radius: 5px;
            transition: background-color 0.2s;
            color: #ffffff;
        }

        .admin-info:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .admin-avatar,
        .staff-avatar,
        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #ffffff;
            color: #2a83df;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            letter-spacing: -0.03em;
            border: 2px solid #ffffff;
        }

        .admin-name {
            font-weight: 500;
        }

        /* Dropdown menu styles */
        .dropdown-toggle {
            cursor: pointer;
        }

        .dropdown-toggle::after {
            display: none;
        }

        .dropdown-menu {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 8px 0;
            margin-top: 10px;
            min-width: 200px;
        }

        .dropdown-item {
            padding: 8px 16px;
            display: flex;
            align-items: center;
        }

        .dropdown-item:hover {
            background-color: var(--primary-100);
        }

        .dropdown-item i {
            font-size: 1rem;
        }

        /* Main content styles */
        .main-content {
            margin-left: 260px;
            margin-top: 60px;
            padding: 20px;
            background-color: var(--page-bg);
            min-height: calc(100vh - 60px);
            width: calc(100% - 250px);
            transition: all 0.3s ease;
        }



        .main-content.collapsed {
            margin-left: 70px;
            width: calc(100% - 70px);
        }

        /* Card styles */
        .stat-card,
        .widget-container {
            background: var(--surface);
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            border: none;
            padding: 1.25rem;
            height: 100%;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--muted);
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .stat-change {
            color: var(--accent);
            font-size: 13px;
        }

        .stat-change-alert {
            color: var(--danger-text);
            font-size: 13px;
        }

        /* Tab navigation */
        .content-tabs {
            display: flex;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }

        .content-tab {
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 500;
            color: var(--muted-2);
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }

        .content-tab.active {
            color: #2a83df;
            border-bottom-color: #2a83df;
            font-weight: 600;
        }

        .content-tab:hover:not(.active) {
            color: #2a83df;
            border-bottom-color: var(--border);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Chart cards */
        .chart-card {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .chart-header {
            background-color: var(--surface);
            padding: 1.25rem;
            border-bottom: 1px solid var(--border);
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .chart-container {
            position: relative;
            height: 300px;
            padding: 1.5rem;
        }

        /* Recent sales */
        .recent-sales-card {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-radius: 10px;
            height: 380px;
            width: 100%;
        }

        .avatar {
            width: 40px;
            height: 40px;
            margin-right: 15px;
        }

        .amount {
            font-weight: bold;
            color: var(--accent);
        }

        /* Widget components */
        .widget-header h6 {
            font-size: 1.25rem;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--text);
            letter-spacing: -0.02em;
        }

        .widget-header p {
            font-size: 0.875rem;
            color: var(--muted-2);
            margin-bottom: 0;
        }

        /* Item rows in widgets */
        .item-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .item-details {
            flex-grow: 1;
            margin-right: 10px;
        }

        .item-details h6 {
            font-size: 1rem;
            margin-bottom: 3px;
            color: var(--text);
        }

        .item-details p {
            font-size: 0.875rem;
            color: var(--muted);
            margin-bottom: 0;
        }

        /* Status badges */
        .status-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .in-stock {
            background-color: #d1e7dd;
            color: var(--success-text);
        }

        .low-stock {
            background-color: var(--warning-bg);
            color: var(--warning-text);
        }

        .out-of-stock {
            background-color: var(--danger-bg);
            color: var(--danger-text);
        }

        /* Progress bars */
        .progress {
            height: 0.5rem;
            margin-top: 5px;
            background-color: var(--muted-3);
            border-radius: 0.25rem;
            overflow: hidden;
        }

        .progress-bar {
            height: 0.5rem;
        }

        /* Scrollable containers */
        .inventory-container,
        .staff-sales-container,
        .chart-scroll-container {
            scrollbar-width: thin;
            scrollbar-color: #dee2e6 #f8f9fa;
            max-height: 400px;
            overflow-y: auto;
        }

        .chart-scroll-container {
            width: 100%;
            overflow-x: auto;
        }

        .inventory-container::-webkit-scrollbar,
        .staff-sales-container::-webkit-scrollbar,
        .chart-scroll-container::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .inventory-container::-webkit-scrollbar-track,
        .staff-sales-container::-webkit-scrollbar-track,
        .chart-scroll-container::-webkit-scrollbar-track {
            background: #f8f9fa;
            border-radius: 10px;
        }

        .inventory-container::-webkit-scrollbar-thumb,
        .staff-sales-container::-webkit-scrollbar-thumb,
        .chart-scroll-container::-webkit-scrollbar-thumb {
            background-color: var(--muted-3);
            border-radius: 10px;
        }

        .modal-backdrop.show {
            z-index: 1040 !important;
        }

        .modal.show {
            z-index: 1050 !important;
        }
        .table-responsive {
            min-height: 50vh;
            overflow-y: auto;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #ffffff;
            background: #2a83df;
            background: linear-gradient(135deg, #2a83df 0%, #1a5fb8 100%);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn {
            background: #2a83df;
            color: #ffffff;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2a83df 0%, #1a5fb8 100%);
            color: #ffffff;
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1a5fb8 0%, #2a83df 100%);
        }

        .btn-success {
            background: #198754;
            color: #ffffff;
        }

        .btn-danger {
            background: #dc3545;
            color: #ffffff;
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-secondary {
            background: #6c757d;
            color: #ffffff;
        }

        .btn-outline-primary {
            background: transparent;
            color: #2a83df;
            border: 1px solid #2a83df;
        }

        .btn-outline-primary:hover {
            background: #2a83df;
            color: #ffffff;
        }

        .btn-outline-secondary {
            background: transparent;
            color: #6c757d;
            border: 1px solid #6c757d;
        }

        .btn-outline-danger {
            background: transparent;
            color: #dc3545;
            border: 1px solid #dc3545;
        }

        .modal-header {
            background: linear-gradient(135deg, #2a83df 0%, #1a5fb8 100%);
            color: #ffffff;
        }




        /* Responsive styles */
        @media (max-width: 767.98px) {
            #sidebarToggler {
                width: 50px !important;
                height: 50px !important;
                
            }

            #sidebarToggler i {
                font-size: 1.75rem !important;
            }

            .sidebar {
                transform: translateX(-100%);
                width: 250px;
                /* Ensure sidebar takes full height but allows scrolling on mobile */
                height: 100%;
                bottom: 0;
                top: 0;
                overflow-y: auto;
            }

            .sidebar.show {
                transform: translateX(0);
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
            }

            .sidebar.collapsed.show {
                width: 250px;
            }
            .none{
                display:none;

            }

            .top-bar {
                left: 0;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
    @stack('styles')
    @livewireStyles
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header d-flex justify-content-center">
                <div class="sidebar-title">
                    <img src="{{ asset('images/mi-logo.png') }}" alt="Logo" width="200">
                    
                    
                </div>
            </div>
            <hr style="color:#fff;">

            <ul class="nav flex-column">
                {{-- Dashboard --}}
                <li>
                    <a class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}" href="{{ route('staff.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> <span>Overview</span>
                    </a>
                </li>

                {{-- Staff POS Sale - Under Overview --}}
                @if(auth()->user()->hasPermission('menu_sales_add'))
                <li>
                    <a class="nav-link {{ request()->routeIs('staff.billing') ? 'active' : '' }}" href="{{ route('staff.billing') }}">
                        <i class="bi bi-cart-plus"></i> <span>Staff POS Sale</span>
                    </a>
                </li>
                @endif

                {{-- Products Menu --}}
                @if(auth()->user()->hasPermission('menu_products'))
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle" href="#inventorySubmenu" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="inventorySubmenu">
                        <i class="bi bi-basket3"></i> <span>Products</span>
                    </a>
                    <div class="collapse" id="inventorySubmenu">
                        <ul class="nav flex-column ms-3">
                            @if(auth()->user()->hasPermission('menu_products_list'))
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.Productes') }}">
                                     <i class="bi bi-card-list"></i> <span>List Product</span>
                                 </a>
                             </li>
                             @endif
                             @if(auth()->user()->hasPermission('menu_stock'))
                             <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.staff-stock-overview') }}">
                                    <i class="bi bi-box-seam"></i> <span>Stock Overview</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.staff-stock-details') }}">
                                    <i class="bi bi-boxes"></i> <span>Stock Details</span>
                                </a>
                            </li>
                             @endif
                             @if(auth()->user()->hasPermission('staff_my_allocated_products'))
                             <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.allocated-products') }}">
                                    <i class="bi bi-person-check"></i> <span>Allocated Products</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                {{-- Sales Menu - Requires Permission --}}
                @if(auth()->user()->hasPermission('menu_sales'))
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle" href="#salesSubmenu" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="salesSubmenu">
                        <i class="bi bi-cash-stack"></i> <span>Sales</span>
                    </a>
                    <div class="collapse" id="salesSubmenu">
                        <ul class="nav flex-column ms-3">
                             @if(auth()->user()->hasPermission('menu_sales_list'))
                             <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.sales-list') }}">
                                    <i class="bi bi-table"></i> <span>Sales List</span>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->hasPermission('sales_distribution_access'))
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.sales-distribution') }}">
                                    <i class="bi bi-truck"></i> <span>Sales Distribution</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                {{-- Quotation Menu - Requires Permission --}}
                @if(auth()->user()->hasPermission('menu_quotation'))
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle" href="#stockSubmenu" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="stockSubmenu">
                        <i class="bi bi-file-earmark-text"></i> <span>Quotation</span>
                    </a>
                    <div class="collapse" id="stockSubmenu">
                        <ul class="nav flex-column ms-3">
                            @if(auth()->user()->hasPermission('menu_quotation_add'))
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.quotation-system') }}">
                                    <i class="bi bi-file-plus"></i> <span>Add Quotation</span>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->hasPermission('menu_quotation_list'))
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.quotation-list') }}">
                                    <i class="bi bi-card-list"></i> <span>List Quotation</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                {{-- Purchase Menu --}}
                @if(auth()->user()->hasPermission('menu_purchase'))
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle" href="#purchaseSubmenu" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="purchaseSubmenu">
                        <i class="bi bi-truck"></i><span>Purchase</span>
                    </a>
                    <div class="collapse" id="purchaseSubmenu">
                        <ul class="nav flex-column ms-3">
                            @if(auth()->user()->hasPermission('menu_purchase_order'))
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.purchase-order-list') }}">
                                    <i class="bi bi-journal-bookmark"></i> <span>Purchase Order</span>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->hasPermission('menu_purchase_grn'))
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.grn') }}">
                                    <i class="bi bi-boxes"></i><span>GRN</span>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->hasPermission('menu_people_suppliers'))
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.supplier-management') }}">
                                    <i class="bi bi-truck"></i> <span>Supplier Management</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                {{-- Return Menu - Requires Permission --}}
                @if(auth()->user()->hasPermission('menu_return'))
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle" href="#returnSubmenu" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="returnSubmenu">
                        <i class="bi bi-arrow-counterclockwise"></i> <span>Return</span>
                    </a>
                    <div class="collapse" id="returnSubmenu">
                        <ul class="nav flex-column ms-3">
                            @if(auth()->user()->hasPermission('menu_return_customer_add'))
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.return-add') }}">
                                    <i class="bi bi-arrow-return-left"></i> <span>Add Customer Return</span>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->hasPermission('menu_return_customer_list'))
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.return-list') }}">
                                     <i class="bi bi-list-check"></i> <span>Customer Return List</span>
                                 </a>
                             </li>
                             @endif
                             @if(auth()->user()->hasPermission('menu_return_supplier_add'))
                             <li class="nav-item">
                                 <a class="nav-link py-2" href="{{ route('staff.return-supplier') }}">
                                     <i class="bi bi-arrow-return-right"></i> <span>Add Supplier Return</span>
                                 </a>
                             </li>
                             @endif
                             @if(auth()->user()->hasPermission('menu_return_supplier_list'))
                             <li class="nav-item">
                                 <a class="nav-link py-2" href="{{ route('staff.list-supplier-return') }}">
                                     <i class="bi bi-journal-text"></i> <span>List Supplier Return</span>
                                 </a>
                             </li>
                             @endif
                        </ul>
                    </div>
                </li>
                @endif

                {{-- Cheque/Banks Menu --}}
                @if(auth()->user()->hasPermission('menu_banks'))
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle" href="#banksSubmenu" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="banksSubmenu">
                        <i class="bi bi-bank"></i> <span>Cheque / Banks</span>
                    </a>
                    <div class="collapse" id="banksSubmenu">
                        <ul class="nav flex-column ms-3">
                            @if(auth()->user()->hasPermission('menu_banks_deposit'))
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.income') }}">
                                    <i class="bi bi-cash-stack"></i> <span>Deposit By Cash</span>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->hasPermission('menu_banks_cheque_list'))
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.cheque-list') }}">
                                    <i class="bi bi-card-text"></i> <span>Cheque List</span>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->hasPermission('menu_banks_return_cheque'))
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.return-cheque') }}">
                                    <i class="bi bi-arrow-left-right"></i> <span>Return Cheque</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                {{-- Expenses Menu - Requires Permission --}}
                @if(auth()->user()->hasPermission('menu_expenses'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('staff.expenses') }}">
                        <i class="bi bi-wallet2"></i> <span>Expenses</span>
                    </a>
                </li>
                @endif

                {{-- Payment Management Menu - Requires Permission --}}
                @if(auth()->user()->hasPermission('menu_payment'))
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle" href="#paymentSubmenu" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="paymentSubmenu">
                        <i class="bi bi-receipt-cutoff"></i> <span>Payment Management</span>
                    </a>
                    <div class="collapse" id="paymentSubmenu">
                        <ul class="nav flex-column ms-3">
                            @if(auth()->user()->hasPermission('menu_payment_add'))
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.due-payments') }}">
                                    <i class="bi bi-cash-coin"></i> <span>Add Payment</span>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->hasPermission('menu_payment_list'))
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.payments-list') }}">
                                    <i class="bi bi-list-check"></i> <span>Payment List</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                {{-- Customer Management --}}
                @if(auth()->user()->hasPermission('menu_people_customers'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('staff.manage-customers') }}">
                        <i class="bi bi-people"></i> <span>Customer Management</span>
                    </a>
                </li>
                @endif

                {{-- Staff Management Menu --}}
                @if(auth()->user()->hasPermission('menu_people_staff'))
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle" href="#staffManagementSubmenu" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="staffManagementSubmenu">
                        <i class="bi bi-person-badge"></i> <span>Staff Management</span>
                    </a>
                    <div class="collapse" id="staffManagementSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.manage-staff') }}">
                                    <i class="bi bi-people-fill"></i> <span>List Staff</span>
                                </a>
                            </li>
                            @if(auth()->user()->hasPermission('menu_staff_attendance'))
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.staff-attendance') }}">
                                    <i class="bi bi-calendar-check"></i> <span>Attendance</span>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->hasPermission('menu_staff_salary'))
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.staff-salary') }}">
                                    <i class="bi bi-cash"></i> <span>Salary (Calc)</span>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->hasPermission('menu_loan_management'))
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('staff.loan-management') }}">
                                    <i class="bi bi-bank"></i> <span>Loan Management</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif



                {{-- Reports --}}
                @if(auth()->user()->hasPermission('menu_reports'))
                <li>
                    <a class="nav-link" href="{{ route('staff.reports') }}">
                        <i class="bi bi-file-earmark-bar-graph"></i> <span>Reports</span>
                    </a>
                </li>
                @endif

                {{-- Analytics --}}
                @if(auth()->user()->hasPermission('menu_analytics'))
                <li>
                    <a class="nav-link" href="{{ route('staff.analytics') }}">
                        <i class="bi bi-bar-chart"></i> <span>Analytics</span>
                    </a>
                </li>
                @endif

                {{-- <li>
                    <a class="nav-link" href="{{ route('staff.settings') }}">
                        <i class="bi bi-gear"></i> <span>Settings</span>
                    </a>
                </li> --}}
            </ul>
        </div>

        <!-- Top Navigation Bar -->
        <nav class="top-bar">
            <!-- Add toggle button at the start of the navbar -->
            <button id="sidebarToggler" class="btn btn-sm px-2 py-1 me-auto d-flex align-items-center" style="color:#ffffff; border-color:#ffffff;">
                <i class="bi bi-list fs-5"></i>
            </button>

            <div class="dropdown">
                <div class="admin-info dropdown-toggle" id="adminDropdown" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <div class="admin-avatar">S</div>
                    <div class="admin-name">Staff</div>
                </div>

                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.show') }}">
                            <i class="bi bi-person me-2"></i>My Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('staff.settings') }}">
                            <i class="bi bi-gear me-2"></i>Settings
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="mb-0">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>
        <!-- Main Content -->
        <main class="main-content">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 from CDN (only need this one line) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Include jQuery (required by Bootstrap 4 modal) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Define all elements once
            const sidebarToggler = document.getElementById('sidebarToggler');
            const sidebar = document.querySelector('.sidebar');
            const topBar = document.querySelector('.top-bar');
            const mainContent = document.querySelector('.main-content');

            // Tab Switching Functionality
            const tabs = document.querySelectorAll('.content-tab');
            if (tabs.length > 0) {
                tabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        // Remove active class from all tabs
                        tabs.forEach(t => t.classList.remove('active'));

                        // Add active class to clicked tab
                        this.classList.add('active');

                        // Hide all tab contents
                        document.querySelectorAll('.tab-content').forEach(content => {
                            content.classList.remove('active');
                        });

                        // Show the selected tab content
                        const tabId = this.getAttribute('data-tab');
                        document.getElementById(tabId).classList.add('active');
                    });
                });
            }

            // Improved menu activation logic
            function setActiveMenu() {
                const currentPath = window.location.pathname;
                let activeSubmenuFound = false;

                // First, check all menu links in the sidebar
                document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                    // Reset all links to inactive state first
                    link.classList.remove('active');

                    // Get the link's href attribute
                    const href = link.getAttribute('href');
                    if (href && href !== '#' && !href.startsWith('#')) {
                        // Extract just the path portion of the href
                        const hrefPath = href.replace(/^(https?:\/\/[^\/]+)/, '').split('?')[0];

                        // Use more precise path matching logic
                        const isActive = currentPath === hrefPath ||
                            (currentPath.startsWith(hrefPath + '/') && hrefPath !== '/') ||
                            (currentPath === hrefPath + '.php');

                        if (isActive) {
                            // This link is active
                            link.classList.add('active');

                            // If this is a submenu link, expand and highlight the parent menu
                            const submenu = link.closest('.collapse');
                            if (submenu) {
                                activeSubmenuFound = true;

                                // Add 'show' class to submenu to keep it expanded
                                submenu.classList.add('show');

                                // Find and activate the parent dropdown toggle
                                const parentToggle = document.querySelector(`[data-bs-toggle="collapse"][href="#${submenu.id}"]`);
                                if (parentToggle) {
                                    parentToggle.classList.add('active');
                                    parentToggle.setAttribute('aria-expanded', 'true');
                                }
                            }
                        }
                    }
                });

                // If no submenu item is active, check if we need to activate a main nav item
                if (!activeSubmenuFound) {
                    // Get the route base path segments (e.g., /staff/billing → ["staff", "billing"])
                    const pathSegments = currentPath.split('/').filter(Boolean);

                    // Only check main items if we have path segments
                    if (pathSegments.length > 0) {
                        document.querySelectorAll('.sidebar > .sidebar-content > .nav > .nav-item > .nav-link:not(.dropdown-toggle)').forEach(link => {
                            const href = link.getAttribute('href');
                            if (href && href !== '#') {
                                const hrefPath = href.replace(/^(https?:\/\/[^\/]+)/, '').split('?')[0];
                                const hrefSegments = hrefPath.split('/').filter(Boolean);

                                // Only match exact routes or next level child routes
                                const isActive = hrefPath === currentPath ||
                                    (hrefSegments.length > 0 &&
                                        pathSegments.length > 0 &&
                                        hrefSegments[hrefSegments.length - 1] === pathSegments[pathSegments.length - 1]);

                                if (isActive) {
                                    link.classList.add('active');
                                }
                            }
                        });
                    }
                }
            }

            // Call the improved function instead of the old ones
            setActiveMenu();

            // Initialize sidebar state based on screen size
            function initializeSidebar() {
                const isMobile = window.innerWidth < 768;

                if (isMobile) {
                    // Mobile: default collapsed (hidden)
                    sidebar.classList.remove('show');
                    sidebar.classList.remove('collapsed'); // Clean classes
                    topBar.classList.remove('collapsed');
                    mainContent.classList.remove('collapsed');
                } else {
                    // Desktop: check localStorage
                    const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';

                    if (isCollapsed) {
                        sidebar.classList.add('collapsed');
                        topBar.classList.add('collapsed');
                        mainContent.classList.add('collapsed');
                    } else {
                        sidebar.classList.remove('collapsed');
                        topBar.classList.remove('collapsed');
                        mainContent.classList.remove('collapsed');
                    }
                }
                
                // Adjust height after initialization
                setTimeout(adjustSidebarHeight, 100);
            }

            // Toggle sidebar function - unified for mobile and desktop
            function toggleSidebar(event) {
                if (event) {
                    event.stopPropagation();
                }

                const isMobile = window.innerWidth < 768;

                if (isMobile) {
                    // Mobile behavior - toggle show class
                    sidebar.classList.toggle('show');

                    // Ensure no collapsed classes are present on mobile
                    sidebar.classList.remove('collapsed');
                    topBar.classList.remove('collapsed');
                    mainContent.classList.remove('collapsed');
                } else {
                    // Desktop behavior - toggle collapsed classes
                    sidebar.classList.toggle('collapsed');
                    topBar.classList.toggle('collapsed');
                    mainContent.classList.toggle('collapsed');

                    // Save state to localStorage
                    localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
                }
            }

            // Adjust sidebar height
            function adjustSidebarHeight() {
                if (sidebar) {
                    // Ensure sidebar takes full viewport height
                    sidebar.style.height = `${window.innerHeight}px`;

                    // Check if content is taller than viewport
                    const sidebarNav = sidebar.querySelector('.nav.flex-column');
                    if (sidebarNav) {
                        const needsScroll = sidebarNav.scrollHeight > window.innerHeight;
                        if (needsScroll) {
                            sidebar.classList.add('scrollable');
                        } else {
                            sidebar.classList.remove('scrollable');
                        }
                    }
                }
            }

            // Initialize sidebar
            if (sidebar) {
                initializeSidebar();

                // Attach toggle event listener (single source of truth)
                if (sidebarToggler) {
                    sidebarToggler.addEventListener('click', toggleSidebar);
                }

                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(event) {
                    const isMobile = window.innerWidth < 768;
                    const isClickInsideSidebar = sidebar.contains(event.target);
                    const isClickOnToggler = sidebarToggler && sidebarToggler.contains(event.target);

                    if (isMobile &&
                        sidebar.classList.contains('show') &&
                        !isClickInsideSidebar &&
                        !isClickOnToggler) {
                        sidebar.classList.remove('show');
                    }
                });

                // Handle window resize - switch between mobile and desktop modes
                window.addEventListener('resize', function() {
                    const wasMobile = mainContent.style.marginLeft === '0px' || mainContent.style.marginLeft === '';
                    const isMobile = window.innerWidth < 768;

                    // Only run when crossing the mobile/desktop threshold
                    if (wasMobile !== isMobile) {
                        initializeSidebar();
                    }
                });

                // Adjust sidebar height initially and on resize
                adjustSidebarHeight();
                window.addEventListener('resize', adjustSidebarHeight);

                // Fix submenu scroll visibility
                const dropdownToggles = document.querySelectorAll('.nav-link.dropdown-toggle');
                dropdownToggles.forEach(toggle => {
                    toggle.addEventListener('click', function(event) {
                        // Wait for submenu to fully appear
                        setTimeout(() => {
                            const submenu = this.nextElementSibling;
                            if (submenu && submenu.classList.contains('show')) {
                                // Check if submenu bottom is out of view
                                const submenuRect = submenu.getBoundingClientRect();
                                const sidebarRect = sidebar.getBoundingClientRect();

                                if (submenuRect.bottom > sidebarRect.bottom) {
                                    // Scroll to make submenu visible
                                    submenu.scrollIntoView({
                                        behavior: 'smooth',
                                        block: 'end'
                                    });
                                }
                            }
                        }, 300);
                    });
                });
            }
        });
    </script>
    @stack('scripts')
</body>

</html>