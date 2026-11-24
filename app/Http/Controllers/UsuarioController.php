<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = Usuario::with('rol')
            ->orderBy('nombre_completo')
            ->paginate(15)
            ->withQueryString();

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

        $request->validate([
            'username' => 'required|unique:usuarios,username,' . $usuario->id_usuario . ',id_usuario',
            'nombre_completo' => 'required',
            'email' => 'required|email|unique:usuarios,email,' . $usuario->id_usuario . ',id_usuario',
            'id_rol' => 'required',
        ]);

        $activo = $request->boolean('activo');
        $rolAdmin = Rol::where('nombre', 'ADMINISTRADOR')->value('id_rol');

        // Un usuario no puede desactivarse a sí mismo
        if (!$activo && $usuario->id_usuario == auth()->user()->id_usuario) {
            return back()->withInput()->with('error', 'No puede desactivar su propio usuario.');
        }

        // Siempre debe quedar al menos un administrador activo
        $dejaDeSerAdminActivo = $usuario->id_rol == $rolAdmin && $usuario->activo
            && (!$activo || $request->id_rol != $rolAdmin);
        if ($dejaDeSerAdminActivo && $this->administradoresActivos() <= 1) {
            return back()->withInput()->with('error', 'Debe quedar al menos un administrador activo.');
        }

        $usuario->update([
            'username' => $request->username,
            'nombre_completo' => $request->nombre_completo,
            'email' => $request->email,
            'id_rol' => $request->id_rol,
            'activo' => $activo ? 1 : 0,
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * Un usuario con historial (ventas, caja, compras, asientos, etc.) no se
     * puede borrar sin romper esos registros: en ese caso se desactiva, que
     * conserva el historial y le impide iniciar sesión.
     */
    public function destroy(string $id)
    {
        $usuario = Usuario::findOrFail($id);

        if ($usuario->id_usuario == auth()->user()->id_usuario) {
            return back()->with('error', 'No puede eliminar su propio usuario');
        }

        $rolAdmin = Rol::where('nombre', 'ADMINISTRADOR')->value('id_rol');
        if ($usuario->id_rol == $rolAdmin && $usuario->activo && $this->administradoresActivos() <= 1) {
            return back()->with('error', 'Debe quedar al menos un administrador activo.');
        }

        try {
            $usuario->delete();
        } catch (QueryException $e) {
            // 23000 = restricción de llave foránea: el usuario tiene registros asociados
            if ($e->getCode() !== '23000') {
                throw $e;
            }

            $usuario->update(['activo' => 0]);

            return redirect()
                ->route('usuarios.index')
                ->with('success', 'Este usuario tiene historial en el sistema (ventas, caja, compras o asientos), por lo que no se puede borrar. Se desactivó: ya no puede iniciar sesión y su historial se conserva.');
        }

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario eliminado correctamente');
    }

    private function administradoresActivos(): int
    {
        return Usuario::where('activo', 1)
            ->whereHas('rol', fn ($q) => $q->where('nombre', 'ADMINISTRADOR'))
            ->count();
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
