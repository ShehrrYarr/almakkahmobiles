<?php

namespace App\Http\Controllers;

use App\Models\MobileAccount;
use App\Models\MobileVendor;
use App\Models\MasterPassword;
use Illuminate\Http\Request;

class MobileAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showAccounts($id)
    {
        $accounts = MobileAccount::with('creator')
            ->where('mobile_vendor_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        $vendor = MobileVendor::findOrFail($id);

        return view('mobile.showAccounts', compact('accounts', 'vendor'));
    }

    public function creditAmount(Request $request)
    {
        $request->validate([
            'vendor_id'   => 'required|exists:mobile_vendors,id',
            'amount'      => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $userId = auth()->id();
        $description = $request->description ?? 'Manual credit entry';

        // Same duplicate guard as the accessory ledger's credit endpoint
        $duplicate = MobileAccount::where('mobile_vendor_id', $request->vendor_id)
            ->where('credit', $request->amount)
            ->where('description', $description)
            ->where('created_by', $userId)
            ->where('created_at', '>=', now()->subSeconds(10))
            ->exists();

        if (!$duplicate) {
            MobileAccount::create([
                'mobile_vendor_id' => $request->vendor_id,
                'credit'           => $request->amount,
                'debit'            => 0,
                'description'      => $description,
                'created_by'       => $userId,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Credit amount recorded successfully.');
    }

    public function debitAmount(Request $request)
    {
        $request->validate([
            'vendor_id'   => 'required|exists:mobile_vendors,id',
            'amount'      => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $userId = auth()->id();
        $description = $request->description ?? 'Manual debit entry';

        $duplicate = MobileAccount::where('mobile_vendor_id', $request->vendor_id)
            ->where('debit', $request->amount)
            ->where('description', $description)
            ->where('created_by', $userId)
            ->where('created_at', '>=', now()->subSeconds(10))
            ->exists();

        if (!$duplicate) {
            MobileAccount::create([
                'mobile_vendor_id' => $request->vendor_id,
                'credit'           => 0,
                'debit'            => $request->amount,
                'description'      => $description,
                'created_by'       => $userId,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Debit amount recorded successfully.');
    }

    public function getaccount($id)
    {
        $filterId = MobileAccount::find($id);
        if (!$filterId) {
            return response()->json(['message' => 'Id not found'], 404);
        }

        return response()->json(['result' => $filterId]);
    }

    public function destroyAccount(Request $request)
    {
        $account = MobileAccount::findOrFail($request->id);

        $password = $request->input('password');
        $masterPassword = MasterPassword::first();

        if ($password === $masterPassword->delete_password) {
            $account->delete();
            return redirect()->back()->with('success', 'Account deleted successfully.');
        }

        return redirect()->back()->with('danger', 'Incorrect delete password.');
    }
}
