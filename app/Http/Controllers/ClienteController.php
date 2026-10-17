<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('buscar'));

        $clientes = Cliente::when($buscar !== '', function ($query) use ($buscar) {
                // Agrupado en un where() para que las tres condiciones
                // viajen juntas; sueltas como orWhere() al nivel de la
                // consulta, cualquier otro where() que se agregue más
                // adelante dejaría de aplicar a todas las filas.
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombres', 'like', "%{$buscar}%")
                      ->orWhere('apellidos', 'like', "%{$buscar}%")
                      ->orWhere('cedula', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('nombres')
            ->paginate(15)
            ->withQueryString();

        // El buscador del topbar pide esta vista por AJAX en cada tecla
        // (encabezado X-GF-Partial) para buscar en TODOS los clientes,
        // no solo los 15 de la página actual.
        if ($request->header('X-GF-Partial')) {
            return view('clientes._tabla', compact('clientes'));
        }

        return view('clientes.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return view('clientes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
        'nombres' => 'required',
        'apellidos' => 'required',
        'cedula' => 'nullable|unique:clientes,cedula',
        'telefono' => 'nullable',
        'direccion' => 'nullable',
        'email' => 'nullable|email'
        ]);

        Cliente::create([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'cedula' => $request->cedula,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'email' => $request->email,
            'estado' => 1
        ]);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente registrado correctamente');
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
        $cliente = Cliente::findOrFail($id);

        return view('clientes.edit', compact('cliente'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
        'nombres' => 'required',
        'apellidos' => 'required',
        'cedula' => 'nullable|unique:clientes,cedula,' . $id . ',id_cliente',
        'email' => 'nullable|email'
        ]);

        $cliente = Cliente::findOrFail($id);

        $cliente->update([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'cedula' => $request->cedula,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'email' => $request->email
        ]);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente actualizado correctamente'); 

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cliente = Cliente::findOrFail($id);

        $cliente->delete();

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente eliminado correctamente');
    }
}
