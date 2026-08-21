<?php

namespace App\Http\Controllers;

use App\Models\Mobile;
use App\Models\MobileCompany;
use App\Models\MobileGroup;
use App\Models\MobileUnit;
use App\Models\MasterPassword;
use Illuminate\Http\Request;

class MobileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $companies = MobileCompany::all();
        $groups = MobileGroup::all();
        $mobiles = Mobile::with(['company', 'group', 'user', 'units'])->get();

        return view('mobile.index', compact('mobiles', 'companies', 'groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string',
            'min_qty'           => 'nullable|integer|min:0',
            'mobile_group_id'   => 'required|exists:mobile_groups,id',
            'mobile_company_id' => 'required|exists:mobile_companies,id',
        ]);

        $validated['user_id'] = auth()->id();

        Mobile::create($validated);

        return redirect()->back()->with('success', 'Mobile Created Successfully.');
    }

    public function edit($id)
    {
        $filterId = Mobile::find($id);
        if (!$filterId) {
            return response()->json(['message' => 'Id not found'], 404);
        }

        return response()->json(['result' => $filterId]);
    }

    public function update(Request $request)
    {
        $password = $request->input('password');
        $masterPassword = MasterPassword::first();

        $mobile = Mobile::findOrFail($request->id);

        if ($password === $masterPassword->update_password) {
            $validated = $request->validate([
                'name'              => 'required|string|max:255',
                'description'       => 'nullable|string',
                'min_qty'           => 'nullable|integer|min:0',
                'mobile_group_id'   => 'required|exists:mobile_groups,id',
                'mobile_company_id' => 'required|exists:mobile_companies,id',
            ]);

            $mobile->update($validated);

            return redirect()->back()->with('success', 'Mobile Updated Successfully.');
        }

        return redirect()->back()->with('danger', 'Incorrect update password.');
    }

    public function units()
    {
        $units = MobileUnit::with(['mobile.company', 'mobile.group', 'vendor', 'images', 'user'])
            ->latest()
            ->get();

        return view('mobile.units', compact('units'));
    }
}
