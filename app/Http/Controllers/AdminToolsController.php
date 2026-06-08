<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AdminToolsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        return view('admin_tools');
    }

    public function migrate()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            return back()->with('tool_success', 'Migrations ran successfully.')
                         ->with('tool_output', $output ?: 'Nothing to migrate.');
        } catch (\Throwable $e) {
            return back()->with('tool_danger', 'Migration failed: ' . $e->getMessage());
        }
    }

    public function pull()
    {
        try {
            $output = [];
            $code   = 0;
            exec('git -C ' . escapeshellarg(base_path()) . ' pull 2>&1', $output, $code);
            $text = implode("\n", $output);

            if ($code === 0) {
                return back()->with('tool_success', 'Git pull completed.')
                             ->with('tool_output', $text);
            }
            return back()->with('tool_danger', 'Git pull failed.')
                         ->with('tool_output', $text);
        } catch (\Throwable $e) {
            return back()->with('tool_danger', 'Git pull error: ' . $e->getMessage());
        }
    }

    public function optimize()
    {
        try {
            Artisan::call('optimize');
            $output = Artisan::output();
            return back()->with('tool_success', 'Optimize completed.')
                         ->with('tool_output', $output ?: 'Done.');
        } catch (\Throwable $e) {
            return back()->with('tool_danger', 'Optimize failed: ' . $e->getMessage());
        }
    }
}
