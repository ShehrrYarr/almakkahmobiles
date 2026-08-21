<?php

namespace App\Http\Controllers;

use App\Models\MobileImage;
use App\Models\MobileUnit;
use Illuminate\Http\Request;

class MobilePurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * One phone per purchase: who you bought it from (captured directly on
     * the unit — there's no reusable vendor/customer record) plus the
     * phone's own details and up to 5 photos.
     */
    public function create()
    {
        return view('mobile.purchase');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'seller_name'        => 'required|string|max:255',
            'seller_cnic'        => 'nullable|string|max:50',
            'seller_phone'       => 'nullable|string|max:20',
            'seller_address'     => 'nullable|string|max:500',
            'seller_description' => 'nullable|string',

            'name'           => 'required|string|max:255',
            'imei'           => 'required|string|max:32|unique:mobile_units,imei',
            'imei2'          => 'nullable|string|max:32|different:imei|unique:mobile_units,imei2',
            'storage'        => 'nullable|string|max:50',
            'battery'        => 'nullable|string|max:50',
            'battery_cycle'  => 'nullable|integer|min:0',
            'has_box'        => 'nullable|boolean',
            'pta_status'     => 'required|in:PTA,Non PTA',
            'description'    => 'nullable|string',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price'  => 'required|numeric|min:0',
            'purchase_date'  => 'required|date',
            'images.*'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $shopId = session('current_shop_id');

        $unit = MobileUnit::create([
            'shop_id'            => $shopId,
            'name'               => $data['name'],
            'imei'               => $data['imei'],
            'imei2'              => $data['imei2'] ?? null,
            'storage'            => $data['storage'] ?? null,
            'pta_status'         => $data['pta_status'],
            'battery'            => $data['battery'] ?? null,
            'battery_cycle'      => $data['battery_cycle'] ?? null,
            'has_box'            => $request->boolean('has_box'),
            'purchase_price'     => $data['purchase_price'],
            'selling_price'      => $data['selling_price'],
            'purchase_date'      => $data['purchase_date'],
            'description'        => $data['description'] ?? null,
            'seller_name'        => $data['seller_name'],
            'seller_cnic'        => $data['seller_cnic'] ?? null,
            'seller_phone'       => $data['seller_phone'] ?? null,
            'seller_address'     => $data['seller_address'] ?? null,
            'seller_description' => $data['seller_description'] ?? null,
            'status'             => 'in_stock',
            'user_id'            => auth()->id(),
        ]);

        $images = $request->file('images', []);
        if (is_array($images)) {
            foreach (array_slice($images, 0, 5) as $img) {
                if ($img && $img->isValid()) {
                    $path = $img->store('mobile_units', 'public');
                    MobileImage::create(['mobile_unit_id' => $unit->id, 'path' => $path]);
                }
            }
        }

        return redirect()->route('mobile.units')->with('success', 'Mobile purchased successfully.');
    }

    public function report(Request $request)
    {
        $start = $request->input('start_date');
        $end   = $request->input('end_date');
        $shopId = session('current_shop_id');

        $units = MobileUnit::with(['images'])
            ->where('shop_id', $shopId)
            ->when($start && $end, fn ($q) => $q->whereBetween('purchase_date', [$start, $end]))
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->get();

        $totalPurchaseAmount = (float) $units->sum('purchase_price');
        $totalSellingAmount  = (float) $units->sum('selling_price');

        return view('mobile.purchaseReport', compact(
            'units', 'totalPurchaseAmount', 'totalSellingAmount', 'start', 'end'
        ));
    }
}
