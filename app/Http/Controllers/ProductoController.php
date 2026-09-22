<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Proveedor;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with([
            'categoria',
            'proveedor'
        ])->get();
        return view('productos.index', compact('productos'));
    }

    public function create()
    {
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();

        return view('productos.create', compact(
            'categorias',
            'proveedores'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
        'codigo_barra' => 'required|unique:productos,codigo_barra',
        'nombre' => 'required',
        'id_categoria' => 'required',
        'id_proveedor' => 'required',
        'concentracion' => 'required',
        'presentacion' => 'required',
        'precio_compra' => 'required|numeric',
        'precio_venta' => 'required|numeric', 
        'stock_actual' => 'required|integer',
        'stock_minimo' => 'required|integer',
        'descripcion' => 'required',
        ],[
            'codigo_barra.unique' => 'Este código de barras ya está registrado en otro producto.',
            'codigo_barra.required' => 'Debe ingresar un código de barras.'
        ]);
        
        Producto::create($request->all());

        return redirect()->route('productos.index')
            ->with('success', 'Producto creado correctamente');
    }

    public function edit($id)
   {
        $producto = Producto::findOrFail($id);

        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();

        return view('productos.edit', compact(
            'producto',
            'categorias',
            'proveedores'
        ));
    }
   
 
    
    public function update(Request $request, $id)
    {
        $request->validate([
         'codigo_barra' =>'required|unique:productos,codigo_barra,' . $id . ',id_producto',
        'nombre' => 'required',
        'id_categoria' => 'required',
        'id_proveedor' => 'required',
        'concentracion' => 'required',
        'presentacion' => 'required',
        'precio_compra' => 'required|numeric',
        'precio_venta' => 'required|numeric',
        'stock_actual' => 'required|integer',
        'stock_minimo' => 'required|integer',
        'descripcion' => 'required',
        ],[
            'codigo_barra.unique' => 'Este código de barras ya está registrado en otro producto.',
            'codigo_barra.required' => 'Debe ingresar un código de barras.'
        ]);

        $producto = Producto::findOrFail($id);
        $producto->update($request->all());
        
        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado correctamente');
    }

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado correctamente');
    }
}
