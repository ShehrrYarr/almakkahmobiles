<?php

namespace App\Http\Controllers;

use App\Models\MobileGroup;
use Illuminate\Http\Request;

class MobileGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showGroups()
    {
        $group = MobileGroup::all();
        return view('mobile.showGroups', compact('group'));
    }

    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        if (MobileGroup::where('name', $validated['name'])->exists()) {
            return redirect()->back()->withInput()->withErrors([
                'name' => 'Group with this name already exists.',
            ]);
        }

        MobileGroup::create(['name' => $validated['name']]);

        return redirect()->back()->with('success', 'Group added successfully!');
    }

    public function editGroup($id)
    {
        $filterId = MobileGroup::find($id);
        if (!$filterId) {
            return response()->json(['message' => 'Id not found'], 404);
        }

        return response()->json(['result' => $filterId]);
    }

    public function updateGroup(Request $request)
    {
        $data = MobileGroup::findOrFail($request->id);
        $data->name = $request->input('name');
        $data->save();

        return redirect()->back()->with('success', 'Group updated successfully.');
    }

    public function destroyGroup(Request $request)
    {
        $group = MobileGroup::findOrFail($request->id);
        $group->delete();

        return redirect()->back()->with('success', 'Group deleted successfully!');
    }
}
