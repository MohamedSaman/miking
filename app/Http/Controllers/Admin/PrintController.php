<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function printSale($id)
    {
        // Load sale with all necessary relationships including returns
        $sale = Sale::with(['customer', 'items.product', 'payments', 'user', 'returns' => function ($q) {
            $q->with('product');
        }, 'staffReturns' => function ($q) {
            $q->where('status', 'approved')->with('product');
        }])->findOrFail($id);

        // Return the print view
        return view('components.sale-receipt-print', compact('sale'));
    }

    public function printCustomerReport($type, $id)
    {
        $customer = \App\Models\Customer::with(['sales', 'user'])->findOrFail($id);
        $data = null;

        if ($type === 'Sales' || $type === 'Dues') {
            $query = \App\Models\Sale::where('customer_id', $id)->with('items');
            if ($type === 'Dues') {
                $query->where('due_amount', '>', 0);
            }
            $data = $query->latest()->get();
        } elseif ($type === 'Payments') {
            $data = \App\Models\Payment::where('customer_id', $id)->with('sale')->latest()->get();
        } elseif ($type === 'Ledger') {
            $sales = \App\Models\Sale::where('customer_id', $id)->get();
            $payments = \App\Models\Payment::where('customer_id', $id)->with('sale')->get();
            $returns = \App\Models\StaffReturn::where('customer_id', $id)->with('sale')->get();

            $ledger = collect([]);
            foreach($sales as $s) {
                $status = ($s->due_amount > 0) ? ($s->paid_amount > 0 ? 'Partial' : 'Due') : 'Paid';
                $ledger->push([
                    'date' => $s->created_at, 
                    'invoice_no' => $s->invoice_number ?? '-', 
                    'description' => 'Sale Record (' . $status . ')', 
                    'debit' => $s->total_amount, 
                    'credit' => 0
                ]);
            }
            foreach($payments as $p) {
                $ledger->push([
                    'date' => $p->created_at, 
                    'invoice_no' => $p->sale->invoice_number ?? '-', 
                    'description' => 'Payment Received (' . str_replace('_', ' ', $p->payment_method) . ')', 
                    'debit' => 0, 
                    'credit' => $p->amount
                ]);
            }
            foreach($returns as $r) {
                $ledger->push([
                    'date' => $r->created_at, 
                    'invoice_no' => $r->sale->invoice_number ?? '-', 
                    'description' => 'Product Return', 
                    'debit' => 0, 
                    'credit' => $r->total_amount
                ]);
            }

            $sortedLedger = $ledger->sortBy('date');

            $runningBalance = $customer->opening_balance - $customer->overpaid_amount;
            $data = $sortedLedger->map(function($item) use (&$runningBalance) {
                $runningBalance += $item['debit'];
                $runningBalance -= $item['credit'];
                $item['balance'] = $runningBalance;
                return $item;
            });
        }

        return view('reports.customer-report', compact('customer', 'type', 'data'));
    }
}
