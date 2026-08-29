<?php

namespace App\Http\Controllers;

use App\Models\Notebook;
use Illuminate\Http\Request;

class NotebookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id = null)
    {
        $notebooks = Notebook::orderBy('id')->get(['id', 'name']);

        $targetId = $id ?: optional($notebooks->first())->id;
        $notebook = $targetId ? Notebook::find($targetId) : null;

        $stored = $notebook->data ?? [];

        return view('notebook', [
            'notebooks' => $notebooks,
            'activeId' => $notebook->id ?? null,
            'values' => $stored['values'] ?? [],
            'style' => $stored['style'] ?? [],
            'columns' => $stored['columns'] ?? [],
            'rows' => $stored['rows'] ?? [],
            'updatedAt' => $notebook->updated_at ?? null,
            'updatedBy' => optional(optional($notebook)->updater)->name,
        ]);
    }

    public function save(Request $request, $id)
    {
        $notebook = Notebook::find($id);
        if (!$notebook) {
            return response()->json(['success' => false, 'message' => 'Notebook not found.'], 404);
        }

        $validated = $request->validate([
            'data' => 'required|array',
            'style' => 'nullable|array',
            'columns' => 'nullable|array',
            'rows' => 'nullable|array',
        ]);

        $notebook->data = [
            'values'  => $validated['data'],
            'style'   => $validated['style'] ?? [],
            'columns' => $validated['columns'] ?? [],
            'rows'    => $validated['rows'] ?? [],
        ];
        $notebook->updated_by = auth()->id();
        $notebook->save();

        return response()->json([
            'success' => true,
            'updated_at' => $notebook->updated_at->format('d M Y, H:i:s'),
            'updated_by' => auth()->user()->name,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $notebook = Notebook::create([
            'name' => $data['name'],
            'data' => ['values' => [], 'style' => [], 'columns' => [], 'rows' => []],
            'updated_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'id' => $notebook->id, 'name' => $notebook->name]);
    }

    public function rename(Request $request, $id)
    {
        $notebook = Notebook::find($id);
        if (!$notebook) {
            return response()->json(['success' => false, 'message' => 'Notebook not found.'], 404);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $notebook->name = $data['name'];
        $notebook->save();

        return response()->json(['success' => true, 'name' => $notebook->name]);
    }

    public function destroy($id)
    {
        $notebook = Notebook::find($id);
        if (!$notebook) {
            return response()->json(['success' => false, 'message' => 'Notebook not found.'], 404);
        }

        if (Notebook::count() <= 1) {
            return response()->json(['success' => false, 'message' => 'Cannot delete the only remaining notebook.'], 422);
        }

        $notebook->delete();

        return response()->json(['success' => true]);
    }
}
