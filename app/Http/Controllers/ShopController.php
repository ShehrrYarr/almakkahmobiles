<?php

namespace App\Http\Controllers;

use App\Models\MobileSale;
use App\Models\MobileSaleItem;
use App\Models\MobileUnit;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $shops = Shop::withCount('users')->orderBy('name')->get();

        return view('shops.index', compact('shops'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|alpha_dash|max:255|unique:shops,slug',
        ]);

        $slug = $data['slug'] ?? Str::slug($data['name']);

        $base = $slug;
        $i = 1;
        while (Shop::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }

        Shop::create([
            'name' => $data['name'],
            'slug' => $slug,
        ]);

        return redirect()->back()->with('success', 'Shop created successfully.');
    }

    public function edit($id)
    {
        $shop = Shop::findOrFail($id);

        return response()->json(['result' => $shop]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'id'        => 'required|exists:shops,id',
            'name'      => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $shop = Shop::findOrFail($data['id']);
        $shop->update([
            'name'      => $data['name'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->back()->with('success', 'Shop updated successfully.');
    }

    /**
     * Admin clicking "Enter" on a shop from the Manage Shops page — sets
     * the shop context in session using the admin's existing login,
     * without needing a separate account per shop.
     */
    public function enter($id)
    {
        $shop = Shop::findOrFail($id);

        if (!auth()->user()->canAccessShop($shop->id)) {
            return redirect()->back()->with('danger', 'You do not have access to that shop.');
        }

        session(['current_shop_id' => $shop->id]);

        return redirect()->route('mobile.pos');
    }

    /**
     * Cross-shop overview for the admin — sales, profit, inventory, and
     * purchase totals per shop, without switching the admin's active shop
     * context (unlike enter()). Purely a read-only dashboard.
     */
    public function stats()
    {
        $shops = Shop::orderBy('name')->get()->map(function ($shop) {
            $unitsQuery = MobileUnit::where('shop_id', $shop->id);

            $shop->total_units          = (clone $unitsQuery)->count();
            $shop->in_stock_units       = (clone $unitsQuery)->where('status', 'in_stock')->count();
            $shop->sold_units_count     = (clone $unitsQuery)->where('status', 'sold')->count();
            $shop->total_purchase_value = (float) (clone $unitsQuery)->sum('purchase_price');

            $shop->total_sales = (float) MobileSale::where('shop_id', $shop->id)->sum('total_amount');

            $profitRow = DB::table('mobile_sale_items as si')
                ->join('mobile_sales as s', 's.id', '=', 'si.mobile_sale_id')
                ->leftJoin('mobile_units as mu', 'mu.id', '=', 'si.mobile_unit_id')
                ->leftJoin('mobile_sale_return_items as r', 'r.mobile_sale_item_id', '=', 'si.id')
                ->where('s.shop_id', $shop->id)
                ->whereNull('r.id')
                ->selectRaw('SUM(COALESCE(si.price,0) - COALESCE(mu.purchase_price,0)) as total_profit')
                ->first();
            $shop->total_profit = (float) ($profitRow->total_profit ?? 0);

            return $shop;
        });

        return view('shops.stats', compact('shops'));
    }

    /**
     * Sold units for one shop, date-filterable by sale date — reachable
     * from the Shop Stats page without the admin needing to "enter" that
     * shop first.
     */
    public function soldUnits(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);

        $start = $request->input('start_date');
        $end   = $request->input('end_date');

        $items = MobileSaleItem::with(['unit', 'sale', 'returnItems'])
            ->whereHas('sale', function ($q) use ($shop, $start, $end) {
                $q->where('shop_id', $shop->id);
                if ($start && $end) {
                    $q->whereBetween('sale_date', ["$start 00:00:00", "$end 23:59:59"]);
                }
            })
            ->get()
            ->sortByDesc(fn ($item) => $item->sale->sale_date);

        return view('shops.sold_units', compact('shop', 'items', 'start', 'end'));
    }
}
