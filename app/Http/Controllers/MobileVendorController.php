<?php

namespace App\Http\Controllers;

use App\Models\MobileAccount;
use App\Models\MobileVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MobileVendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showVendors()
    {
        $vendors = MobileVendor::with('creator')->get();
        return view('mobile.showVendors', compact('vendors'));
    }

    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));
        return MobileVendor::query()
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                   ->orWhere('mobile_no', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn($v) => ['id' => $v->id, 'text' => $v->name . ' (' . $v->mobile_no . ')']);
    }

    public function storeVendor(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'office_address'  => 'nullable|string|max:255',
            'city'            => 'nullable|string|max:100',
            'mobile_no'       => 'required|string|max:20',
            'CNIC'            => 'nullable|string|max:25',
            'picture'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $userId = auth()->id();

        if (MobileVendor::where('name', $validated['name'])->exists()) {
            return redirect()->back()->withInput()->withErrors(['name' => 'Vendor with this name already exists.']);
        }
        if (MobileVendor::where('mobile_no', $validated['mobile_no'])->exists()) {
            return redirect()->back()->withInput()->withErrors(['mobile_no' => 'Vendor with this mobile number already exists.']);
        }
        if (!empty($validated['CNIC']) && MobileVendor::where('CNIC', $validated['CNIC'])->exists()) {
            return redirect()->back()->withInput()->withErrors(['CNIC' => 'Vendor with this CNIC already exists.']);
        }

        $filePath = null;
        if ($request->hasFile('picture')) {
            $filePath = $request->file('picture')->store('mobile_vendor_pictures', 'public');
        }

        MobileVendor::create([
            'name'           => $validated['name'],
            'office_address' => $validated['office_address'] ?? null,
            'city'           => $validated['city'] ?? null,
            'mobile_no'      => $validated['mobile_no'],
            'CNIC'           => $validated['CNIC'] ?? null,
            'picture'        => $filePath,
            'created_by'     => $userId,
        ]);

        return redirect()->back()->with('success', 'Vendor added successfully!');
    }

    public function editVendor($id)
    {
        $filterId = MobileVendor::find($id);
        if (!$filterId) {
            return response()->json(['message' => 'Id not found'], 404);
        }

        return response()->json(['result' => $filterId]);
    }

    public function updateVendor(Request $request)
    {
        $data = MobileVendor::findOrFail($request->id);

        if ($request->hasFile('picture')) {
            if ($data->picture && Storage::disk('public')->exists($data->picture)) {
                Storage::disk('public')->delete($data->picture);
            }
            $data->picture = $request->file('picture')->store('mobile_vendor_pictures', 'public');
        }

        $data->name = $request->input('name');
        $data->city = $request->input('city');
        $data->office_address = $request->input('office_address');
        $data->mobile_no = $request->input('mobile_no');
        $data->CNIC = $request->input('CNIC');
        $data->save();

        return redirect()->back()->with('success', 'Vendor updated successfully.');
    }

    public function destroyVendor(Request $request)
    {
        $vendor = MobileVendor::findOrFail($request->id);

        if ($vendor->picture && Storage::disk('public')->exists($vendor->picture)) {
            Storage::disk('public')->delete($vendor->picture);
        }

        $vendor->delete();

        return redirect()->back()->with('success', 'Vendor deleted successfully!');
    }

    public function getBalance($id)
    {
        $vendor = MobileVendor::findOrFail($id);

        $balance = MobileAccount::where('mobile_vendor_id', $id)
            ->selectRaw('COALESCE(SUM(credit),0) - COALESCE(SUM(debit),0) as balance')
            ->value('balance');

        return response()->json([
            'balance'     => $balance,
            'vendor_name' => $vendor->name,
        ]);
    }

    public function listReceivables()
    {
        $vendorReceivables = DB::table('mobile_accounts')
            ->select('mobile_vendor_id', DB::raw('SUM(debit) AS total_debit'), DB::raw('SUM(credit) AS total_credit'))
            ->whereNotNull('mobile_vendor_id')
            ->groupBy('mobile_vendor_id')
            ->get();

        $vendorsOwing = $vendorReceivables->filter(function ($vendor) {
            return ($vendor->total_debit - $vendor->total_credit) > 0;
        });

        $vendorsOwingDetails = MobileVendor::whereIn('id', $vendorsOwing->pluck('mobile_vendor_id'))->get();

        $vendorsOwingDetails = $vendorsOwingDetails->map(function ($vendor) use ($vendorReceivables) {
            $vendorReceivable = $vendorReceivables->firstWhere('mobile_vendor_id', $vendor->id);
            $vendor->amount_owed = $vendorReceivable->total_debit - $vendorReceivable->total_credit;
            return $vendor;
        });

        return view('mobile.receivableVendors', compact('vendorsOwingDetails'));
    }
}
