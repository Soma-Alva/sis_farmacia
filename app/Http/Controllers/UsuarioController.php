<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = Usuario::with('rol')->get();

        return view('usuarios.index', compact('usuarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         $roles = Rol::all();

         return view('usuarios.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
          $request->validate([
        'username' => 'required|unique:usuarios',
        'nombre_completo' => 'required',
        'email' => 'required|email|unique:usuarios',
        'password' => 'required|min:6',
        'id_rol' => 'required'
    ]);

        Usuario::create([
        'username' => $request->username,
        'nombre_completo' => $request->nombre_completo,
        'email' => $request->email,
        'password_hash' => bcrypt($request->password),
        'id_rol' => $request->id_rol,
        'activo' => 1
    ]);

    return redirect()
        ->route('usuarios.index')
        ->with('success', 'Usuario creado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $usuario = Usuario::findOrFail($id);

        $roles = Rol::all();

        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $usuario = Usuario::findOrFail($id);

        $usuario->update([
            'username' => $request->username,
            'nombre_completo' => $request->nombre_completo,
            'email' => $request->email,
            'id_rol' => $request->id_rol
            ]);

            $usuario->update([
            'activo' => 0
            ]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $usuario = Usuario::findOrFail($id);

        if($usuario->id_usuario == auth()->user()->id_usuario){
        return back()->with('error', 'No puede eliminar su propio usuario');
        }

        $usuario->delete();

        return redirect()
        ->route('usuarios.index')
        ->with('success', 'Usuario eliminado correctamente');
    }

    public function passwordForm($id)
    {
        $usuario = Usuario::findOrFail($id);

        return view(
            'usuarios.password',
            compact('usuario')
        );
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed'
        ]);

        $usuario = Usuario::findOrFail($id);

        $usuario->update([
            'password_hash' => bcrypt($request->password)
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Contraseña actualizada');
    }
}
