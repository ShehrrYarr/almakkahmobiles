<?php

namespace App\Http\Controllers;

use App\Models\MobileBank;
use App\Models\MobileBuyback;
use App\Models\MobileUnit;
use Illuminate\Http\Request;

class MobileBuybackController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        $shopId = session('current_shop_id');
        $banks = MobileBank::where('shop_id', $shopId)->where('is_active', true)->orderBy('name')->get(['id', 'name', 'account_no']);

        return view('mobile.buyback', compact('banks'));
    }

    /**
     * Live search-as-you-type: matches a sold unit by its own IMEI/IMEI2,
     * or by the sale number it was sold on (any still-sold, non-returned
     * item from that sale).
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $shopId = session('current_shop_id');

        if ($q === '') {
            return response()->json(['success' => true, 'results' => []]);
        }

        $units = MobileUnit::where('shop_id', $shopId)
            ->where('status', 'sold')
            ->where(function ($w) use ($q) {
                $w->where('imei', 'like', "%{$q}%")
                  ->orWhere('imei2', 'like', "%{$q}%");

                if (ctype_digit($q)) {
                    $w->orWhereHas('saleItems', function ($si) use ($q) {
                        $si->where('mobile_sale_id', $q)->whereDoesntHave('returnItems');
                    });
                }
            })
            ->with(['saleItems' => function ($si) {
                $si->whereDoesntHave('returnItems')->with('sale')->latest();
            }])
            ->limit(20)
            ->get();

        $results = $units->map(function ($unit) {
            $activeItem = $unit->saleItems->first();
            $sale = $activeItem->sale ?? null;

            return [
                'unit_id'       => $unit->id,
                'name'          => $unit->name,
                'imei'          => $unit->imei,
                'imei2'         => $unit->imei2,
                'storage'       => $unit->storage,
                'color'         => $unit->color,
                'pta_status'    => $unit->pta_status,
                'battery'       => $unit->battery,
                'battery_cycle' => $unit->battery_cycle,
                'has_box'       => (bool) $unit->has_box,
                'sale_id'       => $sale->id ?? null,
                'sale_date'     => $sale ? \Carbon\Carbon::parse($sale->sale_date)->format('d M Y, H:i') : null,
                'customer_name' => $sale->customer_name ?? null,
                'sold_price'    => $activeItem->price ?? null,
            ];
        });

        return response()->json(['success' => true, 'results' => $results]);
    }

    public function store(Request $request)
    {
        $shopId = session('current_shop_id');

        $data = $request->validate([
            'mobile_unit_id'     => 'required|integer|exists:mobile_units,id',
            'seller_name'        => 'required|string|max:255',
            'seller_cnic'        => 'nullable|digits:13',
            'seller_phone'       => 'nullable|string|max:20',
            'seller_address'     => 'nullable|string|max:500',
            'seller_description' => 'nullable|string',
            'battery'            => 'nullable|string|max:50',
            'battery_cycle'      => 'nullable|integer|min:0',
            'has_box'            => 'nullable|boolean',
            'buyback_price'      => 'required|numeric|min:0',
            'new_selling_price'  => 'required|numeric|min:0',
            'payment_method'     => 'required|in:counter,bank',
            'mobile_bank_id'     => 'required_if:payment_method,bank|nullable|exists:mobile_banks,id',
        ]);

        try {
            $buyback = \DB::transaction(function () use ($data, $shopId, $request) {
                $unit = MobileUnit::where('id', $data['mobile_unit_id'])
                    ->where('shop_id', $shopId)
                    ->lockForUpdate()
                    ->first();

                if (!$unit) {
                    throw new \Exception('Mobile unit not found.');
                }
                if ($unit->status !== 'sold') {
                    throw new \Exception("This unit is not currently marked as sold (already bought back?) — refresh and try again.");
                }

                $activeItem = $unit->saleItems()->whereDoesntHave('returnItems')->latest()->first();

                $buyback = MobileBuyback::create([
                    'shop_id'            => $shopId,
                    'mobile_unit_id'     => $unit->id,
                    'mobile_sale_id'     => $activeItem->mobile_sale_id ?? null,
                    'user_id'            => auth()->id(),
                    'seller_name'        => $data['seller_name'],
                    'seller_cnic'        => $data['seller_cnic'] ?? null,
                    'seller_phone'       => $data['seller_phone'] ?? null,
                    'seller_address'     => $data['seller_address'] ?? null,
                    'seller_description' => $data['seller_description'] ?? null,
                    'battery'            => $data['battery'] ?? null,
                    'battery_cycle'      => $data['battery_cycle'] ?? null,
                    'has_box'            => $request->boolean('has_box'),
                    'buyback_price'      => $data['buyback_price'],
                    'new_selling_price'  => $data['new_selling_price'],
                    'payment_method'     => $data['payment_method'],
                    'mobile_bank_id'     => $data['payment_method'] === 'bank' ? $data['mobile_bank_id'] : null,
                    'buyback_date'       => now(),
                ]);

                $unit->update([
                    'seller_name'        => $data['seller_name'],
                    'seller_cnic'        => $data['seller_cnic'] ?? null,
                    'seller_phone'       => $data['seller_phone'] ?? null,
                    'seller_address'     => $data['seller_address'] ?? null,
                    'seller_description' => $data['seller_description'] ?? null,
                    'battery'            => $data['battery'] ?? null,
                    'battery_cycle'      => $data['battery_cycle'] ?? null,
                    'has_box'            => $request->boolean('has_box'),
                    'purchase_price'     => $data['buyback_price'],
                    'selling_price'      => $data['new_selling_price'],
                    'purchase_date'      => now(),
                    'status'             => 'in_stock',
                    'user_id'            => auth()->id(),
                ]);

                return $buyback;
            });
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('danger', $e->getMessage());
        }

        return redirect()->route('mobile.units')->with('success', 'Mobile bought back successfully — now available in stock.');
    }

    public function report(Request $request)
    {
        $shopId = session('current_shop_id');
        $start = $request->input('start_date');
        $end   = $request->input('end_date');

        $buybacks = MobileBuyback::with(['unit', 'sale', 'bank', 'user'])
            ->where('shop_id', $shopId)
            ->when($start && $end, fn ($q) => $q->whereBetween('buyback_date', ["$start 00:00:00", "$end 23:59:59"]))
            ->orderByDesc('buyback_date')
            ->orderByDesc('id')
            ->get();

        $totalBuybackAmount = (float) $buybacks->sum('buyback_price');

        return view('mobile.buybackReport', compact('buybacks', 'totalBuybackAmount', 'start', 'end'));
    }
}
