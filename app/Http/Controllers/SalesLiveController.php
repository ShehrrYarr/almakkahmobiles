<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\vendor;
use Carbon\Carbon;

class SalesLiveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

 public function index(Request $request)
    {
        // Default: Today 00:00 → 23:59
        $today = Carbon::today(); // uses app timezone
        $start = $request->query('start_date', $today->copy()->format('Y-m-d'));
        $end   = $request->query('end_date',   $today->copy()->format('Y-m-d'));
        $vendorId = $request->query('vendor_id');

        $vendors = Vendor::orderBy('name')->get(['id','name']);

        return view('sales.live', compact('vendors', 'start', 'end', 'vendorId'));
    }

    public function feed(Request $request)
    {
        // Validate inputs
        $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'vendor_id'  => 'nullable|exists:vendors,id',
        ]);

        // Build concrete datetime range (00:00:00 to 23:59:59)
        $start = Carbon::createFromFormat('Y-m-d H:i:s', $request->start_date.' 00:00:00');
        $end   = Carbon::createFromFormat('Y-m-d H:i:s', $request->end_date.' 23:59:59');

        $vendorId = $request->vendor_id;

        $sales = Sale::with([
                'vendor:id,name',
                'user:id,name',
                'payments.bank:id,name',
                'items' => function ($q) { $q->select('id','sale_id','quantity','price_per_unit','subtotal'); },
            ])
            ->when($vendorId, fn($q) => $q->where('vendor_id', $vendorId))
            ->whereBetween('sale_date', [$start, $end])
            ->orderByDesc('sale_date')
            ->limit(300) // keep it snappy; adjust as needed
            ->get();

        // Build lightweight payload
        $rows = $sales->map(function ($s) {
            $subtotal = $s->items->sum('subtotal');
            $discount = (float) ($s->discount_amount ?? 0);
            $net      = max($subtotal - $discount, 0);

            $payments = $s->payments->map(function($p){
                return [
                    'method'  => $p->method,
                    'bank'    => optional($p->bank)->name,
                    'amount'  => (float)$p->amount,
                    'ref'     => $p->reference_no,
                    'paid_at' => optional($p->paid_at)->format('Y-m-d H:i'),
                ];
            });

            return [
                'id'            => $s->id,
                'sale_date'     => optional($s->sale_date)->format('Y-m-d H:i'),
                'who'           => $s->vendor ? ('Vendor: '.$s->vendor->name) : ($s->customer_name ? ('Customer: '.$s->customer_name) : 'Walk-in'),
                'total'         => (float)$net,
                'subtotal'      => (float)$subtotal,
                'discount'      => (float)$discount,
                'status'        => $s->status,
                'user'          => optional($s->user)->name,
                'comment'       => $s->comment,
                'items_count'   => $s->items->count(),
                'payments'      => $payments,
                'invoice_url'   => route('sales.invoice', $s->id),
            ];
        });

        // Totals
        $totals = [
            'count'   => $rows->count(),
            'net_sum' => round($rows->sum('total'), 2),
        ];

        return response()->json([
            'success' => true,
            'data'    => $rows,
            'totals'  => $totals,
            'refreshed_at' => now()->format('H:i:s'),
        ]);
    }

}
