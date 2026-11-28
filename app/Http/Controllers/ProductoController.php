<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\Categoria;
use App\Models\Proveedor;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('buscar'));

        $productos = Producto::with(['categoria', 'proveedor'])
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%{$buscar}%")
                      ->orWhere('codigo_barra', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        // El buscador del topbar pide esta misma vista por AJAX (con el
        // encabezado X-GF-Partial) en cada tecla que el usuario escribe,
        // para filtrar contra TODA la tabla en el servidor en vez de
        // solo las 15 filas visibles en pantalla. En ese caso devolvemos
        // nada más el bloque de la tabla+paginación, no la página entera.
        if ($request->header('X-GF-Partial')) {
            return view('productos._tabla', compact('productos'));
        }

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
        'concentracion' => 'nullable|string|max:50',
        'presentacion' => 'nullable|string|max:100',
        'precio_compra' => 'required|numeric',
        'precio_venta' => 'required|numeric', 
        'stock_minimo' => 'required|integer',
        'descripcion' => 'nullable|string',
        'presentaciones.*.nombre' => 'nullable|string|max:100',
        'presentaciones.*.unidades' => 'nullable|integer|min:2',
        'presentaciones.*.precio_venta' => 'nullable|numeric|min:0',
        ],[
            'codigo_barra.unique' => 'Este código de barras ya está registrado en otro producto.',
            'codigo_barra.required' => 'Debe ingresar un código de barras.'
        ]);

        // El stock inicial NUNCA se toma del formulario: un producto
        // nuevo nace en 0 y solo sube cuando se aprueba una compra
        // real (que crea su lote). Así stock_actual queda siempre
        // sincronizado con la suma de lotes, sin que nadie pueda
        // escribir un número arbitrario.
        $filasPres = $this->filasPresentaciones($request);
        $datos = $request->except('stock_actual');
        $datos['stock_actual'] = 0;

        unset($datos['presentaciones']);
        $producto = Producto::create($datos);
        $this->guardarPresentaciones($producto, $filasPres);

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
        'concentracion' => 'nullable|string|max:50',
        'presentacion' => 'nullable|string|max:100',
        'precio_compra' => 'required|numeric',
        'precio_venta' => 'required|numeric',
        'stock_minimo' => 'required|integer',
        'descripcion' => 'nullable|string',
        'presentaciones.*.nombre' => 'nullable|string|max:100',
        'presentaciones.*.unidades' => 'nullable|integer|min:2',
        'presentaciones.*.precio_venta' => 'nullable|numeric|min:0',
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
        $filasPres = $this->filasPresentaciones($request);
        $producto->update($request->except('stock_actual', 'presentaciones'));
        $this->guardarPresentaciones($producto, $filasPres);
        
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

    /**
     * Filas de presentaciones por paquete del formulario (se ignoran
     * las filas vacías). Una fila a medias se rechaza con un mensaje
     * claro, ANTES de guardar el producto.
     */
    private function filasPresentaciones(Request $request)
    {
        $filas = collect($request->input('presentaciones', []))
            ->filter(fn ($f) => filled($f['nombre'] ?? null) || filled($f['unidades'] ?? null) || filled($f['precio_venta'] ?? null))
            ->values();

        foreach ($filas as $f) {
            if (!filled($f['nombre'] ?? null) || !filled($f['unidades'] ?? null) || !filled($f['precio_venta'] ?? null)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'presentaciones' => 'Cada presentación por caja necesita nombre, unidades y precio.',
                ]);
            }
        }

        return $filas;
    }

    /** Reemplaza las presentaciones del producto con las ya validadas. */
    private function guardarPresentaciones(Producto $producto, $filas): void
    {
        ProductoPresentacion::where('id_producto', $producto->id_producto)->delete();

        foreach ($filas as $f) {
            ProductoPresentacion::create([
                'id_producto' => $producto->id_producto,
                'nombre' => trim($f['nombre']),
                'unidades' => (int) $f['unidades'],
                'precio_venta' => (float) $f['precio_venta'],
                'activo' => 1,
            ]);
        }
    }
}
