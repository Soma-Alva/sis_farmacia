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
        'stock_minimo' => 'required|integer',
        'descripcion' => 'required',
        ],[
            'codigo_barra.unique' => 'Este código de barras ya está registrado en otro producto.',
            'codigo_barra.required' => 'Debe ingresar un código de barras.'
        ]);

        // El stock inicial NUNCA se toma del formulario: un producto
        // nuevo nace en 0 y solo sube cuando se aprueba una compra
        // real (que crea su lote). Así stock_actual queda siempre
        // sincronizado con la suma de lotes, sin que nadie pueda
        // escribir un número arbitrario.
        $datos = $request->except('stock_actual');
        $datos['stock_actual'] = 0;

        Producto::create($datos);

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
        'stock_minimo' => 'required|integer',
        'descripcion' => 'required',
        ],[
            'codigo_barra.unique' => 'Este código de barras ya está registrado en otro producto.',
            'codigo_barra.required' => 'Debe ingresar un código de barras.'
        ]);

        $producto = Producto::findOrFail($id);

        // stock_actual jamás se actualiza desde este formulario: solo
        // lo tocan las compras aprobadas, las ventas y las
        // anulaciones, que son las únicas operaciones que saben de
        // lotes. Se excluye explícitamente por si alguien manipula el
        // HTML y envía el campo de todas formas.
        $producto->update($request->except('stock_actual'));
        
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
