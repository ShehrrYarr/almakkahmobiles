<?php

namespace App\Http\Controllers;

use App\Models\MobileBank;
use Illuminate\Http\Request;

class MobileBankController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $banks = MobileBank::where('shop_id', session('current_shop_id'))->get();
        return view('mobile.banks.index', compact('banks'));
    }

    public function storeBank(Request $request)
    {
        $bank = new MobileBank();
        $bank->shop_id = session('current_shop_id');
        $bank->name = $request->name;
        $bank->account_no = $request->account_no;
        $bank->branch = $request->branch ?? 'No Branch';
        $bank->iban = $request->iban ?? 'No IBAN';
        $bank->swift = $request->swift ?? 'No swift';
        $bank->save();

        return redirect()->back()->with('success', 'Bank Stored Successfully');
    }

    public function getBank($id)
    {
        $filterId = MobileBank::where('shop_id', session('current_shop_id'))->find($id);
        if (!$filterId) {
            return response()->json(['message' => 'Id not found'], 404);
        }

        return response()->json(['result' => $filterId]);
    }

    public function updateBank(Request $request)
    {
        $bank = MobileBank::where('shop_id', session('current_shop_id'))->findOrFail($request->id);
        $bank->name = $request->name;
        $bank->account_no = $request->account_no;
        $bank->branch = $request->branch;
        $bank->save();

        return redirect()->back()->with('success', 'Bank Updated Successfully');
    }
}
