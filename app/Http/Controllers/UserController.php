<?php

namespace App\Http\Controllers;

use App\Models\Accounts;
use App\Models\UserPermission;
use App\Models\TransferRecord;
use Hash;
use Illuminate\Http\Request;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Accessory;
use App\Models\AccessoryBatch;
use App\Models\SaleItem;

class UserController extends Controller
{

public function index()
{
    $userId = auth()->id();

    $totalAccessoryCount = \App\Models\AccessoryBatch::sum('qty_remaining');
    $totalSoldAccessories = \App\Models\SaleItem::sum('quantity');

    $totalAccessoryAmount = \App\Models\Accessory::with('batches')
        ->get()
        ->reduce(function($carry, $accessory) {
            $amount = $accessory->batches->sum(function($batch) {
                return $batch->qty_remaining * $batch->purchase_price;
            });
            return $carry + $amount;
        }, 0);

    $totalSoldAmount = \App\Models\SaleItem::sum('subtotal');

    // Total Receivable from Vendors
    $vendorReceivables = \DB::table('accounts')
        ->select(
            'vendor_id',
            \DB::raw("SUM(Debit) AS total_debit"),
            \DB::raw("SUM(Credit) AS total_credit")
        )
        ->whereNotNull('vendor_id')
        ->groupBy('vendor_id')
        ->get();

    $totalReceivable = $vendorReceivables->reduce(function ($carry, $vendor) {
        $balance = $vendor->total_debit - $vendor->total_credit;
        return $balance > 0 ? $carry + $balance : $carry;
    }, 0);

    // ----- Low stock with company/group -----
    $lowStockAccessories = \App\Models\Accessory::with(['batches','company','group'])
        ->get()
        ->filter(function($accessory) {
            $totalStock = $accessory->batches->sum('qty_remaining');
            return $totalStock < $accessory->min_qty;
        })
        ->map(function($accessory) {
            return [
                'id' => $accessory->id,
                'name' => $accessory->name,
                'min_qty' => (int) $accessory->min_qty,
                'stock' => (int) $accessory->batches->sum('qty_remaining'),
                'company_id' => optional($accessory->company)->id,
                'company' => optional($accessory->company)->name ?? '-',
                'group_id' => optional($accessory->group)->id,
                'group' => optional($accessory->group)->name ?? '-',
            ];
        })
        ->values();

    $lowStockCompanies = $lowStockAccessories
        ->groupBy('company_id')
        ->map(function($items, $companyId) {
            return [
                'id' => $companyId,
                'name' => $items->first()['company'] ?? '-',
                'count' => $items->count(),
            ];
        })
        ->values()
        ->sortBy('name')
        ->values();

    $lowStockGroups = $lowStockAccessories
        ->groupBy('group_id')
        ->map(function($items, $groupId) {
            return [
                'id' => $groupId,
                'name' => $items->first()['group'] ?? '-',
                'count' => $items->count(),
            ];
        })
        ->values()
        ->sortBy('name')
        ->values();

    $totalApprovedSales = \App\Models\Sale::where('status', 'approved')->sum('total_amount');
    $totalPendingSales = \App\Models\Sale::where('status', 'pending')->sum('total_amount');
    $totalApprovedSalesCount = \App\Models\Sale::where('status', 'approved')->count();
    $totalPendingSalesCount = \App\Models\Sale::where('status', 'pending')->count();

    // Today's Debit & Credit totals
    $todayTotalDebit  = \App\Models\Accounts::whereDate('created_at', today())->sum('Debit');
    $todayTotalCredit = \App\Models\Accounts::whereDate('created_at', today())->sum('Credit');

    // Today's credit entries for the ledger table
    $allCreditEntries = \App\Models\Accounts::with('vendor', 'creator')
        ->whereDate('created_at', today())
        ->where('Credit', '>', 0)
        ->orderByDesc('created_at')
        ->get();

    return view('user_dashboard', compact(
        'totalAccessoryCount',
        'totalSoldAccessories',
        'totalSoldAmount',
        'totalReceivable',
        'userId',
        'lowStockAccessories',
        'totalAccessoryAmount',
        'totalApprovedSales',
        'totalPendingSales',
        'totalApprovedSalesCount',
        'totalPendingSalesCount',
        'lowStockCompanies',
        'lowStockGroups',
        'todayTotalDebit',
        'todayTotalCredit',
        'allCreditEntries'
    ));
}

// public function index()
// {
//     $userId = auth()->id();

//     $totalAccessoryCount = \App\Models\AccessoryBatch::sum('qty_remaining');
//     $totalSoldAccessories = \App\Models\SaleItem::sum('quantity');

//     $totalAccessoryAmount = \App\Models\Accessory::with('batches')
//         ->get()
//         ->reduce(function($carry, $accessory) {
//             $amount = $accessory->batches->sum(function($batch) {
//                 return $batch->qty_remaining * $batch->purchase_price;
//             });
//             return $carry + $amount;
//         }, 0);

//     $totalSoldAmount = \App\Models\SaleItem::sum('subtotal');

//     // Total Receivable from Vendors
//     $vendorReceivables = \DB::table('accounts')
//         ->select('vendor_id',
//             \DB::raw("SUM(Debit) AS total_debit"),
//             \DB::raw("SUM(Credit) AS total_credit"))
//         ->whereNotNull('vendor_id')
//         ->groupBy('vendor_id')
//         ->get();

//     $totalReceivable = $vendorReceivables->reduce(function ($carry, $vendor) {
//         $balance = $vendor->total_debit - $vendor->total_credit;
//         return $balance > 0 ? $carry + $balance : $carry;
//     }, 0);

//     // ----- Low stock with company/group -----
//     $lowStockAccessories = \App\Models\Accessory::with(['batches','company','group'])
//         ->get()
//         ->filter(function($accessory) {
//             $totalStock = $accessory->batches->sum('qty_remaining');
//             return $totalStock < $accessory->min_qty;
//         })
//         ->map(function($accessory) {
//             return [
//                 'id'         => $accessory->id,
//                 'name'       => $accessory->name,
//                 'min_qty'    => (int) $accessory->min_qty,
//                 'stock'      => (int) $accessory->batches->sum('qty_remaining'),
//                 'company_id' => optional($accessory->company)->id,
//                 'company'    => optional($accessory->company)->name ?? '-',
//                 'group_id'   => optional($accessory->group)->id,
//                 'group'      => optional($accessory->group)->name ?? '-',
//             ];
//         })
//         ->values();

//     // Build chips (company & group) from the low-stock list only
//     $lowStockCompanies = $lowStockAccessories
//         ->groupBy('company_id')
//         ->map(function($items, $companyId) {
//             return [
//                 'id'    => $companyId,
//                 'name'  => $items->first()['company'] ?? '-',
//                 'count' => $items->count(),
//             ];
//         })
//         ->values()
//         ->sortBy('name')
//         ->values();

//     $lowStockGroups = $lowStockAccessories
//         ->groupBy('group_id')
//         ->map(function($items, $groupId) {
//             return [
//                 'id'    => $groupId,
//                 'name'  => $items->first()['group'] ?? '-',
//                 'count' => $items->count(),
//             ];
//         })
//         ->values()
//         ->sortBy('name')
//         ->values();

//     $totalApprovedSales      = \App\Models\Sale::where('status', 'approved')->sum('total_amount');
//     $totalPendingSales       = \App\Models\Sale::where('status', 'pending')->sum('total_amount');
//     $totalApprovedSalesCount = \App\Models\Sale::where('status', 'approved')->count();
//     $totalPendingSalesCount  = \App\Models\Sale::where('status', 'pending')->count();

//     return view('user_dashboard', compact(
//         'totalAccessoryCount',
//         'totalSoldAccessories',
//         'totalSoldAmount',
//         'totalReceivable',
//         'userId',
//         'lowStockAccessories',
//         'totalAccessoryAmount',
//         'totalApprovedSales',
//         'totalPendingSales',
//         'totalApprovedSalesCount',
//         'totalPendingSalesCount',
//         'lowStockCompanies',
//         'lowStockGroups'
//     ));
// }


    public function showUsers()
    {
        $users = User::with('permissions')->get();
        return view('showUsers', compact('users'));
    }



    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,salesman',
        ]);

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'password_text' => $request->password,
            'role'          => $request->role,
        ]);

        if ($request->role === 'salesman') {
            $this->syncPermissions($user, $request->permissions ?? []);
        }

        return redirect()->back()->with('success', 'User added successfully.');
    }

    public function editUser($id)
    {
        $user = User::with('permissions')->find($id);

        if (!$user) {
            return response()->json(['message' => 'Id not found'], 404);
        }

        $user->permission_list = $user->permissions->pluck('permission');

        return response()->json(['result' => $user]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id'        => 'required|exists:users,id',
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $request->id,
            'password'  => 'nullable|string|min:6',
            'is_active' => 'nullable|boolean',
            'role'      => 'required|in:admin,salesman',
        ]);

        $user = User::findOrFail($request->id);

        $user->update([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => $request->password ? Hash::make($request->password) : $user->password,
            'password_text' => $request->password ?? $user->password_text,
            'is_active'     => $request->is_active,
            'role'          => $request->role,
        ]);

        if ($request->role === 'salesman') {
            $this->syncPermissions($user, $request->permissions ?? []);
        } else {
            // Admin has all permissions — clear any stored ones (not needed)
            $user->permissions()->delete();
        }

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    private function syncPermissions(User $user, array $permissions): void
    {
        $allowed = ['approve_sales', 'process_returns', 'manage_inventory', 'view_vendor_accounts', 'delete_records'];
        $toSync  = array_intersect($permissions, $allowed);

        $user->permissions()->delete();

        foreach ($toSync as $perm) {
            $user->permissions()->create(['permission' => $perm]);
        }
    }


    public function logoutUser($id)
    {
        // Find the user by ID
        $user = User::findOrFail($id);

        // Manually logging out the specified user by clearing their session
        // We can store the user's session ID or other identifier to be able to clear the correct session later.

        $sessionKey = 'user_session_' . $user->id;

        // Clear the specific user's session
        Session::forget($sessionKey);  // Remove session data related to the user

        // Optionally, if you're storing the user session manually (e.g., in a cache), you would invalidate it here.

        return redirect()->route('home')->with('success', 'User has been logged out successfully.');
    }


}
