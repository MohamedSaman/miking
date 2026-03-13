<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class ExternalInvoiceController extends Controller
{
    public function viewInvoice($id)
    {
        // Load sale with all necessary relationships
        $sale = Sale::with(['customer', 'items.product', 'payments', 'user', 'returns' => function ($q) {
            $q->with('product');
        }, 'staffReturns' => function ($q) {
            $q->where('status', 'approved')->with('product');
        }])->findOrFail($id);

        // Return the print view but tell it not to auto-print
        return view('components.sale-receipt-print', [
            'sale' => $sale,
            'noPrint' => true
        ]);
    }

    public function downloadInvoice($id)
    {
        $sale = Sale::with(['customer', 'items.product', 'payments', 'user', 'returns' => function ($q) {
            $q->with('product');
        }, 'staffReturns' => function ($q) {
            $q->where('status', 'approved')->with('product');
        }])->findOrFail($id);

        $pdf = \PDF::loadView('admin.sales.invoice', compact('sale'));
        $pdf->setPaper('a5', 'portrait');
        $pdf->setOption('dpi', 150);
        $pdf->setOption('defaultFont', 'sans-serif');

        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf->output();
            },
            'invoice-' . $sale->invoice_number . '.pdf'
        );
    }
}
