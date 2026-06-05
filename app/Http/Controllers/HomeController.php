<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Models\User;
use App\Models\LoginHistory;
use App\Models\Accounts;
use Jenssegers\Agent\Agent;


class HomeController extends Controller
{


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if (Auth::user() != null) {
            if (Auth::user()->isAdmin()) {
                $totalUsers = User::get();

                // Today's payments = Credit entries added today in the accounts ledger
                $todaysPayments = Accounts::with('vendor', 'creator')
                    ->whereDate('created_at', today())
                    ->where('Credit', '>', 0)
                    ->orderByDesc('created_at')
                    ->get();

                $todaysTotalPayments = $todaysPayments->sum('Credit');

                return view('admin_dashboard', compact(
                    'totalUsers',
                    'todaysPayments',
                    'todaysTotalPayments'
                ));
            }
            else if (Auth::user()->isSalesman()) {
                $agent = new Agent();

                $loginHistory = LoginHistory::create([
                    'name'        => Auth::user()->name,
                    'status'      => 'Logged In',
                    'ip'          => $request->ip(),
                    'user_agent'  => $request->header('User-Agent'),
                    'device'      => $agent->device(),    
                    'platform'    => $agent->platform(),  
                    'browser'     => $agent->browser(),  
                ]);
        
                // dd($loginHistory);
                return redirect()->route('user.index');
            }
        }
        $totalUsers = User::get();
        return view('home', compact('totalUsers'));
    }
    public function logout(Request $request)
    {
        $loginHistory = new LoginHistory;
                $loginHistory->name = Auth::user()->name;
                $loginHistory->status = "Logged Out";
                $loginHistory->save();
        Auth::logout();
        $request->session()->invalidate();
        return redirect('/');
    }
}
