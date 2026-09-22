@extends('layouts.app')

@section('content')

    <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Productos</h3>

                <a href="{{ route('productos.create') }}" class="btn btn-primary">
                    + Nuevo producto
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card shadow">
                <div class="card-body">

                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Proveedor</th>
                                <th>Concentración</th>
                                <th>Presentación</th>
                                <th>Precio Compra</th>
                                <th>Precio Venta</th>
                                <th>Stock Mínimo</th>
                                <th>Stock Actual</th>
                                <th>Descripción</th>
                                <th>Creado</th>
                                <th>Actualizado</th>
                                <th>Acciones</th>
                            
                            </tr>
                        </thead>

                        <tbody>
            @foreach($productos as $p)
            <tr>
                <td>{{ $p->codigo_barra }}</td> 
                <td>{{ $p->nombre }}</td>
                <td>{{ $p->categoria->nombre ?? 'N/A' }}</td>
                <td>{{ $p->proveedor->nombre ?? 'N/A' }}</td>
                <td>{{ $p->concentracion }}</td>
                <td>{{ $p->presentacion }}</td>
                <td>C$ {{ number_format($p->precio_compra, 2) }}</td>
                <td>C$ {{ number_format($p->precio_venta, 2) }}</td>
                <td>{{ $p->stock_minimo }}</td>
                <td>{{ $p->stock_actual }}</td>
                <td title="{{ $p->descripcion }}">
                    {{ \Illuminate\Support\Str::limit($p->descripcion, 50, '...') }}
                </td>
                <td>{{ $p->creado_en }}</td>
                <td>{{ $p->actualizado_en }}</td>

                <td>
                    <a href="{{ route('productos.edit', $p->id_producto) }}"
                    class="btn btn-warning btn-sm">
                        Editar
                    </a>
                    <form action="{{ route('productos.destroy', $p->id_producto) }}"
                            method="POST"
                            class="d-inline">
                        @csrf
                        @method('DELETE')

                        <button type="submit"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('¿Está seguro de eliminar este producto?')">
                        Eliminar
                        </button>
                    </form>

                </td>
            </tr>
            @endforeach
            </tbody>

                        </table>

                    </div>
                </div>

            </div>
    </div> 
@endsection
