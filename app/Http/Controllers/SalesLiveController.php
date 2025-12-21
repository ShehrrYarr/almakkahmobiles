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


// public function feed(Request $request)
// {
//     // Validate inputs
//     $request->validate([
//         'start_date' => 'required|date_format:Y-m-d',
//         'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
//         'vendor_id'  => 'nullable|exists:vendors,id',
//     ]);

//     // Build datetime range (00:00:00 to 23:59:59)
//     $start = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $request->start_date . ' 00:00:00');
//     $end   = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $request->end_date   . ' 23:59:59');

//     $vendorId = $request->vendor_id;

//     // 1) Load sales with items + batches + payments
//     $sales = \App\Models\Sale::with([
//             'vendor:id,name',
//             'user:id,name',
//             'payments.bank:id,name',
//             'items' => function ($q) {
//                 $q->select(
//                     'id',
//                     'sale_id',
//                     'accessory_batch_id',
//                     'quantity',
//                     'price_per_unit',
//                     'subtotal'
//                 );
//             },
//             'items.batch:id,purchase_price'
//         ])
//         ->when($vendorId, fn($q) => $q->where('vendor_id', $vendorId))
//         ->whereBetween('sale_date', [$start, $end])
//         ->orderByDesc('sale_date')
//         ->limit(300)
//         ->get();

//     if ($sales->isEmpty()) {
//         return response()->json([
//             'success'      => true,
//             'data'         => [],
//             'totals'       => ['count' => 0, 'net_sum' => 0.00, 'profit_sum' => 0.00],
//             'refreshed_at' => now()->format('H:i:s'),
//         ]);
//     }

//     // 2) Returns per sale_item_id
//     $saleItemIds = $sales->flatMap->items->pluck('id')->values();

//     $returnsByItem = \DB::table('sale_return_items')
//         ->select('sale_item_id', \DB::raw('SUM(quantity) as returned_qty'))
//         ->whereIn('sale_item_id', $saleItemIds)
//         ->groupBy('sale_item_id')
//         ->pluck('returned_qty', 'sale_item_id'); // [sale_item_id => returned_qty]

//     // 3) Build rows
//     $rows = $sales->map(function ($s) use ($returnsByItem) {

//         $discountTotal = (float) ($s->discount_amount ?? 0); // informational

//         // Because your checkout stores NET in price_per_unit and subtotal:
//         $netSoldTotal = 0.0;   // net amount for full sold qty (before returns)
//         $netKeptTotal = 0.0;   // net amount after returns
//         $profitKept   = 0.0;   // profit after discount (because net sell is stored)
//         $netMarginKept = 0.0;  // (netUnit - cost)*keptQty

//         foreach ($s->items as $item) {
//             $soldQty     = (int) $item->quantity;
//             $returnedQty = (int) ($returnsByItem[$item->id] ?? 0);
//             $keptQty     = max($soldQty - $returnedQty, 0);

//             $netUnitSell = (float) $item->price_per_unit; // ✅ already net after discount
//             $cost        = (float) (optional($item->batch)->purchase_price ?? 0);

//             $netSoldTotal += ($netUnitSell * $soldQty);
//             $netKeptTotal += ($netUnitSell * $keptQty);

//             $netMarginKept += ($netUnitSell - $cost) * $keptQty;
//         }

//         // For display: "subtotal before discount"
//         // Gross(before discount) ≈ netSoldTotal + discountTotal
//         $grossBeforeDiscount = $netSoldTotal + $discountTotal;

//         // Optional: allocate discount to kept qty just for reporting (DO NOT subtract again)
//         $discountOnKept = 0.0;
//         if ($discountTotal > 0 && $netSoldTotal > 0 && $netKeptTotal > 0) {
//             $discountOnKept = $discountTotal * ($netKeptTotal / $netSoldTotal);
//         }

//         // Profit after discount (since net sell is stored)
//         $profitAfterDiscount = $netMarginKept;

//         // Optional: estimate profit before discount
//         $profitBeforeDiscount = $profitAfterDiscount + $discountOnKept;

//         $payments = $s->payments->map(function($p){
//             return [
//                 'method'  => $p->method,
//                 'bank'    => optional($p->bank)->name,
//                 'amount'  => (float) $p->amount,
//                 'ref'     => $p->reference_no,
//                 'paid_at' => optional($p->paid_at)->format('Y-m-d H:i'),
//             ];
//         });

//         return [
//             'id'          => $s->id,
//             'sale_date'   => optional($s->sale_date)->format('Y-m-d H:i'),
//             'who'         => $s->vendor
//                             ? ('Vendor: '.$s->vendor->name)
//                             : ($s->customer_name ? ('Customer: '.$s->customer_name) : 'Walk-in'),

//             // ✅ Correct net total after returns (discount already included in stored net prices)
//             'total'       => round($netKeptTotal, 2),

//             // ✅ Display subtotal before discount (original sale)
//             'subtotal'    => round($grossBeforeDiscount, 2),

//             // informational
//             'discount'    => round($discountTotal, 2),

//             'status'      => $s->status,
//             'user'        => optional($s->user)->name,
//             'comment'     => $s->comment,
//             'items_count' => $s->items->count(),
//             'payments'    => $payments,
//             'invoice_url' => route('sales.invoice', $s->id),

//             // ✅ Profit after discount (most accurate with current stored fields)
//             'profit'                 => round($profitAfterDiscount, 2),

//             // Optional fields (if you want to show them later)
//             'profit_before_discount' => round($profitBeforeDiscount, 2),
//         ];
//     });

//     $totals = [
//         'count'      => $rows->count(),
//         'net_sum'    => round($rows->sum('total'), 2),
//         'profit_sum' => round($rows->sum('profit'), 2),
//     ];

//     return response()->json([
//         'success'      => true,
//         'data'         => $rows,
//         'totals'       => $totals,
//         'refreshed_at' => now()->format('H:i:s'),
//     ]);
// }

public function feed(Request $request)
{
    $request->validate([
        'start_date' => 'required|date_format:Y-m-d',
        'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
        'vendor_id'  => 'nullable|exists:vendors,id',
    ]);

    $start = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $request->start_date.' 00:00:00');
    $end   = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $request->end_date.' 23:59:59');
    $vendorId = $request->vendor_id;

    $sales = \App\Models\Sale::with([
            'vendor:id,name',
            'user:id,name',
            'payments.bank:id,name',
            'items' => function ($q) {
                $q->select('id','sale_id','accessory_batch_id','quantity','price_per_unit','subtotal');
            },
            'items.batch:id,purchase_price'
        ])
        ->when($vendorId, fn($q) => $q->where('vendor_id', $vendorId))
        ->whereBetween('sale_date', [$start, $end])
        ->orderByDesc('sale_date')
        ->limit(300)
        ->get();

    if ($sales->isEmpty()) {
        return response()->json([
            'success'      => true,
            'data'         => [],
            'totals'       => ['count' => 0, 'net_sum' => 0.00, 'profit_sum' => 0.00],
            'refreshed_at' => now()->format('H:i:s'),
        ]);
    }

    // Returns per item
    $saleItemIds = $sales->flatMap->items->pluck('id')->values();

    $returnsByItem = \DB::table('sale_return_items')
        ->select('sale_item_id', \DB::raw('SUM(quantity) as returned_qty'))
        ->whereIn('sale_item_id', $saleItemIds)
        ->groupBy('sale_item_id')
        ->pluck('returned_qty', 'sale_item_id');

    // 1) Build per-sale rows (IMPORTANT: include `id` for Blade)
    $perSaleRows = $sales->map(function ($s) use ($returnsByItem) {

        $discountTotal = (float) ($s->discount_amount ?? 0);

        $netSoldTotal  = 0.0;  // for display only
        $netKeptTotal  = 0.0;  // net after returns
        $profitKept    = 0.0;  // (netUnit - cost) * keptQty

        foreach ($s->items as $item) {
            $soldQty     = (int) $item->quantity;
            $returnedQty = (int) ($returnsByItem[$item->id] ?? 0);
            $keptQty     = max($soldQty - $returnedQty, 0);

            $netUnitSell = (float) $item->price_per_unit; // already NET (discount included)
            $cost        = (float) (optional($item->batch)->purchase_price ?? 0);

            $netSoldTotal += ($netUnitSell * $soldQty);
            $netKeptTotal += ($netUnitSell * $keptQty);

            $profitKept   += ($netUnitSell - $cost) * $keptQty;
        }

        // Display subtotal before discount (approx)
        $grossBeforeDiscount = $netSoldTotal + $discountTotal;

        $payments = $s->payments->map(function($p){
            return [
                'method'  => $p->method,
                'bank'    => optional($p->bank)->name,
                'amount'  => (float) $p->amount,
                'ref'     => $p->reference_no,
                'paid_at' => optional($p->paid_at)->format('Y-m-d H:i'),
            ];
        })->values()->all();

       $saleDt   = \Carbon\Carbon::parse($s->sale_date); // works even if string
$saleDate = $saleDt->format('Y-m-d H:i');
$saleTs   = $saleDt->timestamp;

        return [
            // ✅ required for your table
            'type'        => $s->vendor_id ? 'vendor_sale' : 'walkin_sale',
            'id'          => $s->id,           // ✅ THIS FIXES "Sales ID missing"
            'sale_id'     => $s->id,
            'sale_date'   => $saleDate,
            'sale_date_ts'=> $saleTs,

            'vendor_id'   => $s->vendor_id,
            'vendor_name' => optional($s->vendor)->name,

            'who'         => $s->vendor
                                ? ('Vendor: '.$s->vendor->name)
                                : ($s->customer_name ? ('Customer: '.$s->customer_name) : 'Walk-in'),

            'total'       => round($netKeptTotal, 2),         // ✅ net after returns (discount already included)
            'subtotal'    => round($grossBeforeDiscount, 2),  // display only
            'discount'    => round($discountTotal, 2),        // informational

            'status'      => $s->status,
            'user'        => optional($s->user)->name,
            'comment'     => $s->comment,
            'items_count' => $s->items->count(),
            'payments'    => $payments,
            'invoice_url' => route('sales.invoice', $s->id),

            'profit'      => round($profitKept, 2),
        ];
    });

    // 2) Group vendor sales into one row per vendor
    $vendorGroups = $perSaleRows
        ->whereNotNull('vendor_id')
        ->groupBy('vendor_id')
        ->map(function ($group, $vendorId) {

            $vendorName = $group->first()['vendor_name'] ?? ('Vendor #'.$vendorId);

            // Date range for vendor (so user can see multiple dates)
            $minTs = (int) ($group->min('sale_date_ts') ?? 0);
            $maxTs = (int) ($group->max('sale_date_ts') ?? 0);

            $dateFrom = $group->sortBy('sale_date_ts')->first()['sale_date'] ?? null;
            $dateTo   = $group->sortByDesc('sale_date_ts')->first()['sale_date'] ?? null;

            // Aggregate payments (sum by method+bank)
            $payMap = [];
            foreach ($group as $row) {
                foreach (($row['payments'] ?? []) as $p) {
                    $key = ($p['method'] ?? 'counter').'|'.($p['bank'] ?? '');
                    if (!isset($payMap[$key])) {
                        $payMap[$key] = [
                            'method' => $p['method'] ?? 'counter',
                            'bank'   => $p['bank'] ?? null,
                            'amount' => 0.0,
                        ];
                    }
                    $payMap[$key]['amount'] += (float) ($p['amount'] ?? 0);
                }
            }
            $paymentsAgg = array_values($payMap);

            // ✅ Keep list of invoices with id + date (so you can show all sales on same row)
            $invoices = $group->map(function($r){
                return [
                    'id'    => $r['sale_id'],
                    'date' => $r['sale_date'], 
                    'url'   => $r['invoice_url'],
                    'total' => $r['total'],
                ];
            })->values()->all();

            // Status (if any pending -> pending)
            $status = $group->contains(fn($r) => strtolower((string)$r['status']) !== 'approved')
                ? 'pending'
                : 'approved';

            return [
                'type'        => 'vendor_group',
                'vendor_id'   => (int)$vendorId,
                'id'          => 'V-'.$vendorId,     // ✅ visible id for grouped row

                'sale_date'   => $dateTo,            // latest date for sorting display
                'date_from'   => $dateFrom,
                'date_to'     => $dateTo,
                'sale_date_ts'=> $maxTs,

                'who'         => 'Vendor: '.$vendorName,

                'sales_count' => $group->count(),
                'items_count' => (int) $group->sum('items_count'),

                'total'       => round($group->sum('total'), 2),
                'subtotal'    => round($group->sum('subtotal'), 2),
                'discount'    => round($group->sum('discount'), 2),
                'profit'      => round($group->sum('profit'), 2),

                'status'      => $status,
                'user'        => $group->sortByDesc('sale_date_ts')->first()['user'] ?? null,

                'payments'    => $paymentsAgg,
                'invoices'    => $invoices,
            ];
        })
        ->values();

    // Walk-ins stay per sale
    $walkins = $perSaleRows->whereNull('vendor_id')->values();

    $finalRows = $vendorGroups
        ->concat($walkins)
        ->sortByDesc('sale_date_ts')
        ->values();

    $totals = [
        'count'      => $finalRows->count(),
        'net_sum'    => round($finalRows->sum('total'), 2),
        'profit_sum' => round($finalRows->sum('profit'), 2),
    ];

    return response()->json([
        'success'      => true,
        'data'         => $finalRows,
        'totals'       => $totals,
        'refreshed_at' => now()->format('H:i:s'),
    ]);
}




}
