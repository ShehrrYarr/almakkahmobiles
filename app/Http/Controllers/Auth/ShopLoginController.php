<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopLoginController extends Controller
{
    public function showLoginForm(string $slug)
    {
        $shop = Shop::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('auth.shop_login', compact('shop'));
    }

    public function login(Request $request, string $slug)
    {
        $shop = Shop::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        $user = Auth::user();

        if (!$user->canAccessShop($shop->id)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->withErrors(['email' => 'This account does not have access to this shop.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        session(['current_shop_id' => $shop->id]);

        return redirect()->route('mobile.pos');
    }

    public function logout(Request $request, string $slug)
    {
        Auth::logout();
        session()->forget('current_shop_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('shop.login', $slug);
    }
}
