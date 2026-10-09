@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="gf-page-title">Productos</h4>
        <p class="gf-page-subtitle">{{ $productos->count() }} producto(s) registrado(s)</p>
    </div>

    <a href="{{ route('productos.create') }}" class="gf-btn-primario">
        <i class="bi bi-plus-lg"></i> Nuevo producto
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="gf-card">
    <div class="p-3">
        <div class="table-responsive">
            <table class="gf-table table-hover">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Proveedor</th>
                        <th>Concentración</th>
                        <th>Presentación</th>
                        <th>Precio Compra</th>
                        <th>Precio Venta</th>
                        <th>Stock Mín.</th>
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
                        <td class="fw-semibold">{{ $p->nombre }}</td>
                        <td>{{ $p->categoria->nombre ?? 'N/A' }}</td>
                        <td>{{ $p->proveedor->nombre ?? 'N/A' }}</td>
                        <td>{{ $p->concentracion }}</td>
                        <td>{{ $p->presentacion }}</td>
                        <td>C$ {{ number_format($p->precio_compra, 2) }}</td>
                        <td>C$ {{ number_format($p->precio_venta, 2) }}</td>
                        <td>{{ $p->stock_minimo }}</td>
                        <td>
                            <span class="gf-badge {{ $p->stock_actual <= $p->stock_minimo ? 'gf-badge-peligro' : 'gf-badge-neutro' }}">
                                {{ $p->stock_actual }}
                            </span>
                        </td>
                        <td title="{{ $p->descripcion }}" style="white-space:normal;max-width:220px;">
                            {{ \Illuminate\Support\Str::limit($p->descripcion, 50, '...') }}
                        </td>
                        <td>{{ $p->creado_en }}</td>
                        <td>{{ $p->actualizado_en }}</td>

                        <td>
                            <a href="{{ route('productos.edit', $p->id_producto) }}" class="gf-btn-soft gf-btn-neutro">
                                Editar
                            </a>
                            <form action="{{ route('productos.destroy', $p->id_producto) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="gf-btn-soft gf-btn-peligro"
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

@endsection
