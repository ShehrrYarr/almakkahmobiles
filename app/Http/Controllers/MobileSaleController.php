<?php

namespace App\Http\Controllers;

use App\Models\MobileBank;
use App\Models\MobileSale;
use App\Models\MobileSaleItem;
use App\Models\MobileSalePayment;
use App\Models\MobileSaleReturn;
use App\Models\MobileUnit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MobileSaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function pos(Request $request)
    {
        $shopId = session('current_shop_id');

        $units = MobileUnit::where('shop_id', $shopId)
            ->where('status', 'in_stock')
            ->get();

        $startOfDay = Carbon::now('Asia/Karachi')->startOfDay();
        $endOfDay   = Carbon::now('Asia/Karachi')->endOfDay();

        $sales = MobileSale::with(['items.unit', 'user', 'payments.bank'])
            ->where('shop_id', $shopId)
            ->whereBetween('sale_date', [$startOfDay, $endOfDay])
            ->orderByDesc('id')
            ->get();

        $banks = MobileBank::where('shop_id', $shopId)->where('is_active', true)->orderBy('name')->get(['id', 'name', 'account_no']);

        $totalSellingPrice = $sales->sum('total_amount');
        $totalPaidPrice = (float) $sales->sum('total_amount');

        $allPayments = $sales->flatMap->payments;
        $counterTotal = (float) $allPayments->where('method', 'counter')->sum('amount');
        $bankTotal = (float) $allPayments->where('method', 'bank')->sum('amount');
        $bankBreakdown = $allPayments->where('method', 'bank')->groupBy('bank_id')->map(function ($group) {
            $first = $group->first();
            return ['name' => optional($first->bank)->name ?? 'Unknown Bank', 'total' => (float) $group->sum('amount')];
        });

        // Sell button on the Mobile Units page: /mobile/pos?add_unit=123 prefills the cart.
        $prefillUnit = null;
        if ($request->filled('add_unit')) {
            $prefillUnit = MobileUnit::where('shop_id', $shopId)
                ->where('status', 'in_stock')
                ->find($request->input('add_unit'));
        }

        return view('mobile.pos', compact(
            'units', 'sales', 'totalSellingPrice', 'totalPaidPrice',
            'banks', 'counterTotal', 'bankTotal', 'bankBreakdown', 'prefillUnit'
        ));
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'customer_name'     => 'nullable|string|max:255',
            'customer_mobile'   => 'nullable|string|max:20',
            'items'             => 'required|array|min:1',
            'items.*.mobile_unit_id' => 'required|integer|exists:mobile_units,id',
            'items.*.price'     => 'required|numeric|min:0',
            'items.*.discount'  => 'nullable|numeric|min:0',
            'comment'           => 'nullable|string|max:1000',
            'payment_method'    => 'nullable|in:counter,bank',
            'bank_id'           => 'nullable|exists:mobile_banks,id',
            'reference_no'      => 'nullable|string|max:255',
            'payments'                => 'sometimes|array',
            'payments.*.method'       => 'required_with:payments|in:counter,bank',
            'payments.*.bank_id'      => 'nullable|exists:mobile_banks,id',
            'payments.*.amount'       => 'required_with:payments|numeric|min:0.01',
            'payments.*.reference_no' => 'nullable|string|max:255',
            'client_ref'        => 'nullable|string|max:64',
        ]);

        // Idempotency: same protection as accessory sales — see client_ref fix.
        if (!empty($data['client_ref'])) {
            $existing = MobileSale::where('client_ref', $data['client_ref'])->first();
            if ($existing) {
                return response()->json(['success' => true, 'invoice_number' => $existing->id]);
            }
        }

        $shopId = session('current_shop_id');
        $customerName   = $data['customer_name']   ?? '' ?: 'Walk In Customer';
        $customerMobile = $data['customer_mobile'] ?? '' ?: '00000000';

        try {
            $sale = \DB::transaction(function () use ($data, $shopId, $customerName, $customerMobile) {
                $sale = MobileSale::create([
                    'shop_id'         => $shopId,
                    'client_ref'      => $data['client_ref'] ?? null,
                    'customer_name'   => $customerName,
                    'customer_mobile' => $customerMobile,
                    'sale_date'       => now(),
                    'total_amount'    => 0,
                    'discount_amount' => 0,
                    'pay_amount'      => 0,
                    'user_id'         => auth()->id(),
                    'comment'         => $data['comment'] ?? null,
                ]);

                $netTotal = 0.0;
                $discountTotal = 0.0;

                foreach ($data['items'] as $item) {
                    $unit = MobileUnit::where('id', $item['mobile_unit_id'])->where('shop_id', $shopId)->lockForUpdate()->first();
                    if (!$unit) throw new \Exception('Mobile unit not found.');
                    if ($unit->status !== 'in_stock') {
                        throw new \Exception("IMEI {$unit->imei} is no longer available (already sold).");
                    }

                    $unitPrice = (float) $item['price'];
                    $unitDisc  = min(max((float) ($item['discount'] ?? 0), 0), $unitPrice);
                    $netUnit   = max($unitPrice - $unitDisc, 0);

                    $netTotal      += $netUnit;
                    $discountTotal += $unitDisc;

                    MobileSaleItem::create([
                        'mobile_sale_id' => $sale->id,
                        'mobile_unit_id' => $unit->id,
                        'price'          => $netUnit,
                        'discount'       => $unitDisc,
                        'user_id'        => auth()->id(),
                    ]);

                    $unit->status = 'sold';
                    $unit->save();
                }

                $sale->total_amount    = $netTotal;
                $sale->discount_amount = $discountTotal;
                $sale->save();

                $paymentsInput = request()->input('payments', []);
                $hasPayments = is_array($paymentsInput) && count($paymentsInput) > 0;

                if (!$hasPayments) {
                    $method = in_array($data['payment_method'] ?? '', ['counter', 'bank'], true) ? $data['payment_method'] : 'counter';
                    $paymentsInput = [[
                        'method'       => $method,
                        'amount'       => $sale->total_amount,
                        'bank_id'      => $method === 'bank' ? ($data['bank_id'] ?? null) : null,
                        'reference_no' => $method === 'bank' ? ($data['reference_no'] ?? null) : null,
                    ]];
                }

                foreach ($paymentsInput as $p) {
                    $method = $p['method'] ?? 'counter';
                    $amount = isset($p['amount']) ? (float) $p['amount'] : 0.0;
                    if ($amount <= 0) continue;

                    MobileSalePayment::create([
                        'mobile_sale_id' => $sale->id,
                        'method'         => $method,
                        'mobile_bank_id' => $method === 'bank' ? ($p['bank_id'] ?? null) : null,
                        'amount'         => $amount,
                        'reference_no'   => $p['reference_no'] ?? null,
                        'processed_by'   => auth()->id(),
                        'paid_at'        => now(),
                    ]);
                }

                $sale->pay_amount = $sale->total_amount;
                $sale->save();

                return $sale;
            });

            return response()->json(['success' => true, 'invoice_number' => $sale->id]);
        } catch (\Illuminate\Database\QueryException $e) {
            if (!empty($data['client_ref']) && str_contains($e->getMessage(), 'client_ref')) {
                $existing = MobileSale::where('client_ref', $data['client_ref'])->first();
                if ($existing) {
                    return response()->json(['success' => true, 'invoice_number' => $existing->id]);
                }
            }
            \Log::error('Mobile Checkout Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            \Log::error('Mobile Checkout Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function invoice($id)
    {
        $sale = MobileSale::with(['items.unit', 'user', 'payments.bank'])
            ->where('shop_id', session('current_shop_id'))
            ->findOrFail($id);

        return view('mobile.invoice', compact('sale'));
    }

    public function allSales(Request $request)
    {
        $shopId = session('current_shop_id');
        $start = null;
        $end   = null;

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = $request->input('start_date') . ' 00:00:00';
            $end   = $request->input('end_date')   . ' 23:59:59';
        }

        $salesQuery = MobileSale::query()
            ->with(['user', 'items.unit', 'items.returnItems', 'payments.bank'])
            ->where('shop_id', $shopId)
            ->when($start && $end, fn ($q) => $q->whereBetween('sale_date', [$start, $end]))
            ->orderByDesc('id');

        $sales = $salesQuery->paginate(1000)->withQueryString();

        // total_amount is already decremented on return, so summing it nets out returns.
        $totalSellingPrice = (float) MobileSale::query()
            ->where('shop_id', $shopId)
            ->when($start && $end, fn ($q) => $q->whereBetween('sale_date', [$start, $end]))
            ->sum('total_amount');

        $totalPaidPrice = (float) MobileSale::query()
            ->where('shop_id', $shopId)
            ->when($start && $end, fn ($q) => $q->whereBetween('sale_date', [$start, $end]))
            ->sum('total_amount');

        // Payment amounts already go negative on refund, so bank/counter totals net out returns too.
        $paymentsAgg = \DB::table('mobile_sale_payments as sp')
            ->join('mobile_sales as s', 's.id', '=', 'sp.mobile_sale_id')
            ->where('s.shop_id', $shopId)
            ->when($start && $end, fn ($q) => $q->whereBetween('s.sale_date', [$start, $end]))
            ->selectRaw("
                SUM(CASE WHEN sp.method = 'bank' THEN COALESCE(sp.amount,0) ELSE 0 END) as bank_total,
                SUM(CASE WHEN sp.method = 'counter' THEN COALESCE(sp.amount,0) ELSE 0 END) as counter_total
            ")
            ->first();

        $totalTransferredBank    = (float) ($paymentsAgg->bank_total ?? 0);
        $totalTransferredCounter = (float) ($paymentsAgg->counter_total ?? 0);

        // Profit per sold unit (price - purchase cost), excluding any item that was returned.
        $profitRow = \DB::table('mobile_sale_items as si')
            ->join('mobile_sales as s', 's.id', '=', 'si.mobile_sale_id')
            ->leftJoin('mobile_units as mu', 'mu.id', '=', 'si.mobile_unit_id')
            ->leftJoin('mobile_sale_return_items as r', 'r.mobile_sale_item_id', '=', 'si.id')
            ->where('s.shop_id', $shopId)
            ->when($start && $end, fn ($q) => $q->whereBetween('s.sale_date', [$start, $end]))
            ->whereNull('r.id')
            ->selectRaw('SUM(COALESCE(si.price,0) - COALESCE(mu.purchase_price,0)) as total_profit')
            ->first();

        $totalProfit = (float) ($profitRow->total_profit ?? 0);

        return view('mobile.allSales', compact(
            'sales',
            'totalSellingPrice',
            'totalPaidPrice',
            'totalProfit',
            'totalTransferredBank',
            'totalTransferredCounter'
        ));
    }

    public function refundsPage()
    {
        $shopId = session('current_shop_id');

        $refunds = MobileSaleReturn::with(['sale', 'items.saleItem.unit', 'user'])
            ->whereHas('sale', fn ($q) => $q->where('shop_id', $shopId))
            ->latest()
            ->get();

        return view('mobile.refunds', compact('refunds'));
    }

    public function salesReport(Request $request)
    {
        $shopId = session('current_shop_id');
        $start = $request->input('start_date');
        $end   = $request->input('end_date');

        $sales = MobileSale::with(['user', 'items.unit', 'items.returnItems'])
            ->where('shop_id', $shopId)
            ->when($start && $end, fn ($q) => $q->whereBetween('sale_date', ["$start 00:00:00", "$end 23:59:59"]))
            ->orderByDesc('id')
            ->get();

        $totalSelling = 0.0;
        $totalProfit  = 0.0;

        $rows = $sales->map(function ($sale) use (&$totalSelling, &$totalProfit) {
            $rowProfit     = 0.0;
            $returnedCount = 0;

            foreach ($sale->items as $item) {
                if ($item->returnItems->isNotEmpty()) {
                    $returnedCount++;
                    continue;
                }
                $cost = (float) ($item->unit->purchase_price ?? 0);
                $rowProfit += (float) $item->price - $cost;
            }

            $totalSelling += (float) $sale->total_amount;
            $totalProfit  += $rowProfit;

            return [
                'sale'           => $sale,
                'profit'         => $rowProfit,
                'returned_count' => $returnedCount,
                'item_count'     => $sale->items->count(),
            ];
        });

        return view('mobile.salesReport', compact('rows', 'totalSelling', 'totalProfit', 'start', 'end'));
    }
}
