<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Products List' }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2563EB;
        }
        .header h1 {
            margin: 0;
            color: #2563EB;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 11px;
        }
        .filter-info {
            background: #f3f4f6;
            padding: 8px 12px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 10px;
        }
        .filter-info span {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        thead tr {
            background: #2563EB;
            color: white;
        }
        th, td {
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        tbody tr:hover {
            background: #f3f4f6;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .stock-low {
            color: #f59e0b;
            font-weight: bold;
        }
        .stock-out {
            color: #ef4444;
            font-weight: bold;
        }
        .stock-ok {
            color: #10b981;
        }
        .status-active {
            color: #10b981;
            font-weight: bold;
        }
        .status-inactive {
            color: #ef4444;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        .summary {
            margin-top: 15px;
            background: #f3f4f6;
            padding: 10px;
            border-radius: 4px;
        }
        .summary-item {
            display: inline-block;
            margin-right: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title ?? 'Products List' }}</h1>
        <p>Generated on: {{ $date }}</p>
    </div>

    <div class="filter-info">
        <span>Filter:</span> 
        @if($stockFilter === 'all')
            All Products
        @elseif($stockFilter === 'low')
            Low Stock (< 5 items)
        @elseif($stockFilter === 'out')
            Out of Stock
        @elseif($stockFilter === 'in_stock')
            In Stock
        @endif
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <span>Total Products:</span> {{ count($products) }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 10%;">Code</th>
                <th style="width: 25%;">Product Name</th>
                <th style="width: 10%;">Brand</th>
                <th style="width: 10%;">Category</th>
                <th style="width: 10%;" class="text-right">Cost</th>
                <th style="width: 10%;" class="text-right">Selling</th>
                <th style="width: 8%;" class="text-center">Stock</th>
                <th style="width: 8%;" class="text-center">Damage</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $index => $product)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td><strong>{{ $product->code }}</strong></td>
                <td>{{ $product->product_name }}</td>
                <td>{{ $product->brand ?? '-' }}</td>
                <td>{{ $product->category ?? '-' }}</td>
                <td class="text-right">{{ number_format($product->supplier_price ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($product->selling_price ?? 0, 2) }}</td>
                <td class="text-center @if($product->available_stock == 0) stock-out @elseif($product->available_stock < 5) stock-low @else stock-ok @endif">
                    {{ $product->available_stock ?? 0 }}
                </td>
                <td class="text-center">{{ $product->damage_stock ?? 0 }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">No products found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        @php
            $totalStock = $products->sum('available_stock');
            $totalDamage = $products->sum('damage_stock');
            $outOfStock = $products->where('available_stock', 0)->count();
            $lowStock = $products->where('available_stock', '>', 0)->where('available_stock', '<', 5)->count();
        @endphp
        <span class="summary-item"><strong>Total Stock:</strong> {{ number_format($totalStock) }}</span>
        <span class="summary-item"><strong>Damage Stock:</strong> {{ number_format($totalDamage) }}</span>
        <span class="summary-item"><strong>Out of Stock:</strong> {{ $outOfStock }} products</span>
        <span class="summary-item"><strong>Low Stock:</strong> {{ $lowStock }} products</span>
    </div>

    <div class="footer">
        <p>MI KING - Product Inventory Report</p>
    </div>
</body>
</html>
