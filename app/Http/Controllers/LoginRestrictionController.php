<?php

namespace App\Http\Controllers;

use App\Models\LoginRestriction;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LoginRestrictionController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    // public function handle($request, Closure $next)
    // {
    //     $user = Auth::user();

    //     if ($user && !in_array($user->id, [1, 2])) {
    //         $restriction = LoginRestriction::latest()->first(); // You could also scope it if you want per-role restrictions

    //         if ($restriction) {
    //             $now = Carbon::now();
    //             $start = Carbon::createFromTimeString($restriction->start_time);
    //             $end = Carbon::createFromTimeString($restriction->end_time);

    //             if (!$now->between($start, $end)) {
    //                 Auth::logout();
    //                 return redirect()->route('login')->withErrors([
    //                     'email' => 'Login is allowed only between ' . $start->format('h:i A') . ' and ' . $end->format('h:i A'),
    //                 ]);
    //             }
    //         }
    //     }

    //     return $next($request);
    // }

    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        dd($user);

        // Skip for admin users (IDs 1 and 2)
        if ($user && !in_array($user->id, [1, 2])) {

            // Check if the user is active
            if ($user->is_active == 0) {
                // Prevent login if the user is inactive
                Auth::logout(); // Logout the user immediately if they are inactive
                return redirect()->route('login')->withErrors([
                    'email' => 'Your account is inactive. Please contact support.',
                ]);
            }

            // Fetch the latest login restriction settings
            $restriction = LoginRestriction::latest()->first();

            if ($restriction) {
                // Get the current time and restriction time window
                $now = Carbon::now();
                $start = Carbon::createFromTimeString($restriction->start_time);
                $end = Carbon::createFromTimeString($restriction->end_time);

                // If current time is not within the allowed time range
                if (!$now->between($start, $end)) {
                    Auth::logout(); // Logout the user if they're trying to log in outside of allowed hours
                    return redirect()->route('login')->withErrors([
                        'email' => 'Login is allowed only between ' . $start->format('h:i A') . ' and ' . $end->format('h:i A'),
                    ]);
                }
            }
        }

        return $next($request);
    }




    public function showLogin()
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('user.index')->with('danger', 'You cannot view this page.');
        }

        return view('showLogin');
    }


    public function updateLoginWindow(Request $request)
    {
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        // Only one login-restriction row should ever exist. Order by id (not
        // created_at) so this always targets the same row deterministically,
        // and drop any accidental duplicates so a stale extra row can never
        // shadow the one being edited here.
        $restriction = LoginRestriction::orderBy('id')->first();

        if ($restriction) {
            LoginRestriction::where('id', '!=', $restriction->id)->delete();

            $restriction->update([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);
        } else {
            LoginRestriction::create([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);
        }

        return back()->with('success', 'Login timing updated successfully.');
    }



}
