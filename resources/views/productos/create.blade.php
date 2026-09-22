@extends('layouts.app')

@section('content')

<div class="container">

    <h3>Nuevo Producto</h3>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

    <form action="{{ route('productos.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Código de Barras</label>

            <input type="text"
                name="codigo_barra"
                class="form-control"
                required>

                @error('codigo_barra')
                    <div class="text-danger mt-1">
                        {{ $message }}
                    </div>
                @enderror
        </div>

        <div class="mb-3">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Categoría</label>

            <select name="id_categoria" class="form-select" required>

            <option value="">Seleccione</option>

            @foreach($categorias as $categoria)
                <option value="{{ $categoria->id_categoria }}">
                    {{ $categoria->nombre }}
                </option>
            @endforeach

            </select>
        </div>

        <div class="mb-3">
            <label>Proveedor</label>

                <select name="id_proveedor" class="form-select" required>

                    <option value="">Seleccione</option>

                    @foreach($proveedores as $proveedor)
                        <option value="{{ $proveedor->id_proveedor }}">
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
                placeholder="500mg">
        </div>

        <div class="mb-3">
            <label>Presentación</label>

            <input type="text"
                name="presentacion"
                class="form-control"
                placeholder="Caja x 20 tabletas">
        </div>
        <div class="mb-3">
            <label>Precio Compra</label>

            <input  type="number"
                    step="0.01"
                    name="precio_compra"
                    class="form-control"
                    required>
        </div>
    
        <div class="mb-3">
            <label>Precio Venta</label>

            <input  type="number"
                    step="0.01"
                    name="precio_venta"
                    class="form-control"
                    required>
        </div>

        <div class="mb-3">
            <label>Stock Mínimo</label>

            <input type="number"
                name="stock_minimo"
                class="form-control"
                min="0"
                required>
        </div>
        <div class="mb-3">
            <label>Stock Actual</label>

            <input type="number"
                name="stock_actual"
                class="form-control"
                min="0"
                required>
        
        <div class="mb-3">
            <label>Descripción</label>
            <input type="text" name="descripcion" class="form-control" required>
        </div>

        <button class="btn btn-success">Guardar</button>
        <button class="btn btn-secondary" type="button" onclick="window.history.back()">Cancelar</button>

    </form>

</div>

@endsection
