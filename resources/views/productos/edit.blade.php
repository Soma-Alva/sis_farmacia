    @extends('layouts.app')

    @section('content')

    <div class="container">

        <h3>Editar Producto</h3>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('productos.update', $producto->id_producto) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Código de Barras</label>

                <input type="text"
                    name="codigo_barra"
                    class="form-control"
                    value="{{ $producto->codigo_barra }}" required>

            </div>

            <div class="mb-3">
                <label>Nombre</label>
                <input type="text"
                    name="nombre"
                    class="form-control"
                    value="{{ $producto->nombre }}" required>
            </div>

            <div class="mb-3">
                <label>Categoría</label>

                <select name="id_categoria" class="form-select" required>

                    <option value="">Seleccione</option>

                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id_categoria }}"
                            {{ $producto->id_categoria == $categoria->id_categoria ? 'selected' : '' }}>
                            {{ $categoria->nombre }}
                        </option>
                    @endforeach

                </select >
            </div>

            <div class="mb-3">
                <label>Proveedor</label>

                <select name="id_proveedor" class="form-select" required>

                    <option value="">Seleccione</option>

                    @foreach($proveedores as $proveedor)
                        <option value="{{ $proveedor->id_proveedor }}"
                            {{ $producto->id_proveedor == $proveedor->id_proveedor ? 'selected' : '' }}>
                            {{ $proveedor->nombre }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div class="mb-3">
                <label>Concentración</label>

                <input type="text"
                    name="concentracion"
                    class="form-control"
                    value="{{ $producto->concentracion }}" required> 
            </div>

            <div class="mb-3">
                <label>Presentación</label>

                <input type="text"
                    name="presentacion"
                    class="form-control"
                    value="{{ $producto->presentacion }}" required>          
            </div>

            <div class="mb-3">
                <label>Precio Compra</label>
        
                <input type="number"
                    name="precio_compra"
                    class="form-control"
                    step="0.01"
                    min="0"
                    value="{{ $producto->precio_compra }}" required> 
            </div>

            <div class="mb-3">
                <label>Precio Venta</label>
        
                <input type="number"
                    name="precio_venta"
                    class="form-control"
                    step="0.01"
                    min="0"
                    value="{{ $producto->precio_venta }}" required>
            </div>

            <div class="mb-3">
                <label>Stock Mínimo</label>
        
                <input type="number"
                    name="stock_minimo"
                    class="form-control"
                    min="0"
                    value="{{ $producto->stock_minimo }}" required>  
            </div>

            <div class="mb-3">
                <label>Stock Actual (calculado desde lotes)</label>

                <input type="text"
                    class="form-control"
                    value="{{ $producto->stock_actual }} unidades"
                    disabled>

                <div class="form-text">
                    Este valor se calcula automáticamente sumando los lotes
                    activos del producto. Para aumentarlo, registra una
                    <a href="{{ route('compras.create') }}">compra</a>; para
                    ver el detalle por lote, ve a
                    <a href="{{ route('lotes.index', ['buscar' => $producto->nombre]) }}">Lotes</a>.
                </div>
            </div>

            <div class="mb-3">
                <label>Descripción</label>
                <input type="text"
                    name="descripcion"
                    class="form-control"
                    value="{{ $producto->descripcion }}" required>
            </div>

            <button class="btn btn-success">
                Actualizar
            </button>

            <a href="{{ route('productos.index') }}"
            class="btn btn-secondary">
                Cancelar
            </a>

        </form>

    </div>

    @endsection