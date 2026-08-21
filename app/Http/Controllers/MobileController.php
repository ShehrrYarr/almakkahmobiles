<?php

namespace App\Http\Controllers;

use App\Models\MobileUnit;
use Illuminate\Http\Request;

class MobileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function units(Request $request)
    {
        $units = MobileUnit::with(['images', 'user'])
            ->where('shop_id', session('current_shop_id'))
            ->latest()
            ->get();

        return view('mobile.units', compact('units'));
    }
}
