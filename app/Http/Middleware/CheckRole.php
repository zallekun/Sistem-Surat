<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user || !$user->role) {
            return redirect()->route('login');
        }

        if (in_array($user->role->name, $roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
