<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckSection
{
    public function handle($request, Closure $next, string $section)
    {
        $user = Auth::user();
        $hasAccess = $user && ($section === 'mobile' ? $user->hasMobileAccess() : $user->hasAccessoryAccess());

        if (!$hasAccess) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            return redirect()->route('user.index')->with('danger', 'You do not have access to that section.');
        }

        return $next($request);
    }
}
