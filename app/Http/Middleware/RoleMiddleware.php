<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect('/');
        }

        $user = Auth::user();

        if (!$user->rol || $user->rol->nombre !== $role) {
            abort(403, 'No autorizado');
        }

        return $next($request);
    }
}
