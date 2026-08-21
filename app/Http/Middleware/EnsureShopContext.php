<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class EnsureShopContext
{
    /**
     * Every Mobile-section route needs an active "current shop" in the
     * session before it can run — set when the user logs in through that
     * shop's own /shop/{slug}/login page. A salesman is tied to exactly one
     * shop (users.shop_id); an admin can enter any shop and this is how we
     * know which one they're currently looking at.
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $shopId = session('current_shop_id');

        // Fallback: a shop-assigned salesman whose session lost the marker
        // (e.g. an old session predating this feature) gets it re-derived
        // from their own account rather than being locked out.
        if (!$shopId && $user->shop_id) {
            $shopId = $user->shop_id;
            session(['current_shop_id' => $shopId]);
        }

        if (!$shopId) {
            return redirect()->route('shops.index')
                ->with('danger', 'Select a shop to continue.');
        }

        $shop = Shop::find($shopId);

        if (!$shop || !$shop->is_active || !$user->canAccessShop($shop->id)) {
            session()->forget('current_shop_id');
            return redirect()->route('shops.index')
                ->with('danger', 'You do not have access to that shop.');
        }

        $request->attributes->set('currentShop', $shop);
        View::share('currentShop', $shop);

        return $next($request);
    }
}
