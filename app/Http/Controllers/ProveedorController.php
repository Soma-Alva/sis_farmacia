<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('buscar'));

        $proveedores = Proveedor::query()
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('ruc', 'like', "%{$buscar}%")
                        ->orWhere('telefono', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%");
                });
            })
            ->orderByDesc('activo')
            ->orderBy('nombre')
            ->get();

        return view('proveedores.index', compact('proveedores', 'buscar'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $datos = $this->validateProveedor($request);
        $datos['activo'] = $request->boolean('activo', true);

        Proveedor::create($datos);

        return redirect()
            ->route('proveedores.index')
            ->with('success', 'Proveedor registrado correctamente.');
    }

    public function edit(Proveedor $proveedor)
    {
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $datos = $this->validateProveedor($request, $proveedor);
        $datos['activo'] = $request->boolean('activo');

        $proveedor->update($datos);

        return redirect()
            ->route('proveedores.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->update(['activo' => false]);

        return redirect()
            ->route('proveedores.index')
            ->with('success', 'Proveedor inactivado correctamente.');
    }

    private function validateProveedor(Request $request, ?Proveedor $proveedor = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'ruc' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('proveedores', 'ruc')->ignore(
                    $proveedor?->id_proveedor,
                    'id_proveedor'
                ),
            ],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:1000'],
            'activo' => ['nullable', 'boolean'],
        ], [
            'nombre.required' => 'El nombre del proveedor es obligatorio.',
            'nombre.max' => 'El nombre no puede superar 150 caracteres.',
            'ruc.unique' => 'El RUC ingresado ya pertenece a otro proveedor.',
            'ruc.max' => 'El RUC no puede superar 20 caracteres.',
            'telefono.max' => 'El teléfono no puede superar 20 caracteres.',
            'email.email' => 'Ingrese una dirección de correo válida.',
        ]);
    }
}
