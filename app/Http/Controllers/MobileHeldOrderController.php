<?php

namespace App\Http\Controllers;

use App\Models\MobileHeldOrder;
use Illuminate\Http\Request;

class MobileHeldOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cart_items'                    => 'required|array|min:1',
            'cart_items.*.mobile_unit_id'   => 'required|integer',
            'cart_items.*.imei'             => 'required|string',
            'cart_items.*.mobile_name'      => 'required|string',
            'cart_items.*.price'            => 'required|numeric|min:0',
            'cart_items.*.discount'         => 'nullable|numeric|min:0',
            'vendor_id'       => 'nullable|exists:mobile_vendors,id',
            'customer_name'   => 'nullable|string|max:255',
            'customer_mobile' => 'nullable|string|max:20',
            'comment'         => 'nullable|string|max:1000',
        ]);

        $held = MobileHeldOrder::create([
            'user_id'          => auth()->id(),
            'mobile_vendor_id' => $data['vendor_id'] ?? null,
            'customer_name'    => $data['customer_name'] ?? null,
            'customer_mobile'  => $data['customer_mobile'] ?? null,
            'comment'          => $data['comment'] ?? null,
            'cart_items'       => $data['cart_items'],
            'held_at'          => now(),
        ]);

        return response()->json(['success' => true, 'id' => $held->id]);
    }

    public function index()
    {
        $orders = MobileHeldOrder::where('user_id', auth()->id())
            ->orderByDesc('held_at')
            ->get()
            ->map(function ($h) {
                $items = $h->cart_items ?? [];
                $total = array_sum(array_map(function ($i) {
                    $price = (float) ($i['price'] ?? 0);
                    $discount = (float) ($i['discount'] ?? 0);
                    return max(0, $price - $discount);
                }, $items));

                return [
                    'id'              => $h->id,
                    'held_at'         => $h->held_at->format('d M Y, H:i'),
                    'item_count'      => count($items),
                    'total'           => number_format($total, 2),
                    'customer'        => optional($h->vendor)->name ?? $h->customer_name ?? 'Walk-in',
                    'comment'         => $h->comment,
                    'cart_items'      => $items,
                    'vendor_id'       => $h->mobile_vendor_id,
                    'customer_name'   => $h->customer_name,
                    'customer_mobile' => $h->customer_mobile,
                ];
            });

        return response()->json(['success' => true, 'orders' => $orders]);
    }

    public function destroy($id)
    {
        MobileHeldOrder::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail()
            ->delete();

        return response()->json(['success' => true]);
    }
}
