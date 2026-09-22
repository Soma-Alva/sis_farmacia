<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = [
            'username' => $request->username,
            'password' => $request->password
        ];

        if (Auth::attempt($credentials)) {

            $user = Auth::user();

            // Guardar en tu tabla empresarial
            DB::table('sesiones')->insert([
                'id_usuario' => $user->id_usuario,
                'token' => Str::uuid(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'expiracion' => now()->addHours(8)
            ]);

            return redirect()->route('dashboard');
        }

        return back()->with('error', 'Credenciales incorrectas');
    }

    public function logout()
    {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect('/');
    }

}
