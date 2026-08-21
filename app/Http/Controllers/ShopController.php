<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
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
}
