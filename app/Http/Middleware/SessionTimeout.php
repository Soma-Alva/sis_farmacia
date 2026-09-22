<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SessionTimeout
{

    public function handle($request, Closure $next)
{
    if (auth()->check()) {

        if (session()->has('lastActivityTime')) {

            $inactive = time() - session('lastActivityTime');

            if ($inactive > 60) { // 🚀 1 minuto para prueba

                auth()->logout();
                session()->flush();

                return redirect('/')
                    ->with('error', 'Sesión expirada por inactividad JC');
            }
        }

        session(['lastActivityTime' => time()]);
    }

    return $next($request);
}



}
