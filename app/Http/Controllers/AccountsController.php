<?php

namespace App\Http\Controllers;

use App\Models\Accounts;
use App\Models\vendor;
use Illuminate\Http\Request;
use App\Models\MasterPassword;


class AccountsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    //    public function showAccounts($id)
// {
//     $accounts = Accounts::where('vendor_id', $id)->get();
//     $vendor = vendor::find($id);

    //     $formatted = $accounts->map(function ($item) {
//         return [
//             'created_at' => $item->created_at->format('Y-m-d H:i'),
//             'cr' => $item->category === 'CR' ? $item->amount : null,
//             'db' => $item->category === 'DB' ? $item->amount : null,
//             'description' => $item->description ?? '-',
//         ];
//     });

    //     $totalCredit = $accounts->where('category', 'CR')->sum('amount');
//     $totalDebit = $accounts->where('category', 'DB')->sum('amount');

    //     return view('showAccounts', compact('formatted', 'vendor', 'totalCredit', 'totalDebit'));
// }

    public function showAccounts($id)
    {
        
        $accounts = \App\Models\Accounts::with('creator')
        ->where('vendor_id', $id)
        ->orderBy('created_at','asc')
        ->get();

    $vendor = \App\Models\vendor::findOrFail($id);

    return view('showAccounts', compact('accounts', 'vendor'));
    }







    public function creditAmount(Request $request)
{
    $request->validate([
        'vendor_id' => 'required|exists:vendors,id',
        'amount' => 'required|numeric|min:1',
        'description' => 'nullable|string|max:255',
    ]);

    $userId = auth()->id();
    $description = $request->description ?? 'Manual credit entry';

    // Guard against duplicate inserts from a double-submitted form (e.g. double-click)
    $duplicate = \App\Models\Accounts::where('vendor_id', $request->vendor_id)
        ->where('Credit', $request->amount)
        ->where('description', $description)
        ->where('created_by', $userId)
        ->where('created_at', '>=', now()->subSeconds(10))
        ->exists();

    if (!$duplicate) {
        \App\Models\Accounts::create([
            'vendor_id'   => $request->vendor_id,
            'Credit'      => $request->amount,
            'Debit'       => 0,
            'description' => $description,
            'created_by'  => $userId,
        ]);
    }

    return redirect()->back()->with('success', 'Credit amount recorded successfully.');
}




public function debitAmount(Request $request)
{
    $request->validate([
        'vendor_id' => 'required|exists:vendors,id',
        'amount' => 'required|numeric|min:1',
        'description' => 'nullable|string|max:255',
    ]);

    $userId = auth()->id();
    $description = $request->description ?? 'Manual debit entry';

    // Guard against duplicate inserts from a double-submitted form (e.g. double-click)
    $duplicate = \App\Models\Accounts::where('vendor_id', $request->vendor_id)
        ->where('Debit', $request->amount)
        ->where('description', $description)
        ->where('created_by', $userId)
        ->where('created_at', '>=', now()->subSeconds(10))
        ->exists();

    if (!$duplicate) {
        \App\Models\Accounts::create([
            'vendor_id'   => $request->vendor_id,
            'Credit'      => 0,
            'Debit'       => $request->amount,
            'description' => $description,
            'created_by'  => $userId,
        ]);
    }

    return redirect()->back()->with('success', 'Debit amount recorded successfully.');
}


    public function getaccount($id)
    {
        $filterId = Accounts::find($id);
        // dd($filterId);
        if (!$filterId) {

            return response()->json(['message' => 'Id not found'], 404);
        }

        return response()->json(['result' => $filterId]);

    }

    public function destroyAccount(Request $request)
    {
        $account = Accounts::findOrFail($request->id);

        $password = $request->input('password');
        $masterPassword = MasterPassword::first();

        // Check against delete_password
        if ($password === $masterPassword->delete_password) {
            $account->delete();

            return redirect()->back()->with('success', 'Account deleted successfully.');
        } else {
            return redirect()->back()->with('danger', 'Incorrect delete password.');
        }

        // Delete the vendor record

        return redirect()->back()->with('success', 'vendor deleted successfully!');
    }



}
