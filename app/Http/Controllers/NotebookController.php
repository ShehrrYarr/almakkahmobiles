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

    public function index()
    {
        $notebook = Notebook::first();
        $stored = $notebook->data ?? [];

        return view('notebook', [
            'values' => $stored['values'] ?? [],
            'style' => $stored['style'] ?? [],
            'updatedAt' => $notebook->updated_at ?? null,
            'updatedBy' => optional(optional($notebook)->updater)->name,
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'data' => 'required|array',
            'style' => 'nullable|array',
        ]);

        $notebook = Notebook::first() ?? new Notebook();
        $notebook->data = [
            'values' => $validated['data'],
            'style'  => $validated['style'] ?? [],
        ];
        $notebook->updated_by = auth()->id();
        $notebook->save();

        return response()->json([
            'success' => true,
            'updated_at' => $notebook->updated_at->format('d M Y, H:i:s'),
            'updated_by' => auth()->user()->name,
        ]);
    }
}
