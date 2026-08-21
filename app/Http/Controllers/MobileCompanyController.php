<?php

namespace App\Http\Controllers;

use App\Models\MobileCompany;
use Illuminate\Http\Request;

class MobileCompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showCompanies()
    {
        $company = MobileCompany::all();
        return view('mobile.showCompanies', compact('company'));
    }

    public function storeCompany(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        if (MobileCompany::where('name', $validated['name'])->exists()) {
            return redirect()->back()->withInput()->withErrors([
                'name' => 'Company with this name already exists.',
            ]);
        }

        MobileCompany::create(['name' => $validated['name']]);

        return redirect()->back()->with('success', 'Company added successfully!');
    }

    public function editCompany($id)
    {
        $filterId = MobileCompany::find($id);
        if (!$filterId) {
            return response()->json(['message' => 'Id not found'], 404);
        }

        return response()->json(['result' => $filterId]);
    }

    public function updateCompany(Request $request)
    {
        $data = MobileCompany::findOrFail($request->id);
        $data->name = $request->input('name');
        $data->save();

        return redirect()->back()->with('success', 'Company updated successfully.');
    }

    public function destroyCompany(Request $request)
    {
        $company = MobileCompany::findOrFail($request->id);
        $company->delete();

        return redirect()->back()->with('success', 'Company deleted successfully!');
    }
}
