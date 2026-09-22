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
       $buscar = $request->buscar;

        $clientes = Cliente::where('nombres', 'LIKE', "%$buscar%")
            ->orWhere('apellidos', 'LIKE', "%$buscar%")
            ->orWhere('cedula', 'LIKE', "%$buscar%")
            ->get();

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
