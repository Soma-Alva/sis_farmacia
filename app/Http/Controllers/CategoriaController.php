<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('buscar'));

        $categorias = Categoria::query()
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('descripcion', 'like', "%{$buscar}%");
                });
            })
            ->orderByDesc('activo')
            ->orderBy('nombre')
            ->get();

        return view('categorias.index', compact('categorias', 'buscar'));
    }

    public function create()
    {
        return view('categorias.create');
    }

    public function store(Request $request)
    {
        $datos = $this->validateCategoria($request);
        $datos['activo'] = $request->boolean('activo', true);

        Categoria::create($datos);

        return redirect()
            ->route('categorias.index')
            ->with('success', 'Categoría registrada correctamente.');
    }

    public function edit(Categoria $categoria)
    {
        return view('categorias.edit', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $datos = $this->validateCategoria($request, $categoria);
        $datos['activo'] = $request->boolean('activo');

        $categoria->update($datos);

        return redirect()
            ->route('categorias.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->update(['activo' => false]);

        return redirect()
            ->route('categorias.index')
            ->with('success', 'Categoría inactivada correctamente.');
    }

    private function validateCategoria(Request $request, ?Categoria $categoria = null): array
    {
        return $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categorias', 'nombre')->ignore(
                    $categoria?->id_categoria,
                    'id_categoria'
                ),
            ],
            'descripcion' => ['nullable', 'string', 'max:200'],
            'activo' => ['nullable', 'boolean'],
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.max' => 'El nombre no puede superar 100 caracteres.',
            'nombre.unique' => 'Ya existe una categoría con este nombre.',
            'descripcion.max' => 'La descripción no puede superar 200 caracteres.',
        ]);
    }
}
