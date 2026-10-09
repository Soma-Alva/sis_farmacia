@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="gf-page-title">Categorías</h4>
    <a href="{{ route('categorias.create') }}" class="gf-btn-primario">
        <i class="bi bi-plus-lg"></i> Nueva categoría
    </a>
</div>

<div class="gf-filtros">
    <form method="GET" action="{{ route('categorias.index') }}" class="row g-2">
        <div class="col-md-6">
            <input type="search" name="buscar" value="{{ $buscar }}" class="form-control"
                   placeholder="Buscar por nombre o descripción">
        </div>
        <div class="col-auto">
            <button class="gf-btn-soft gf-btn-neutro" type="submit" style="padding:9px 16px;">
                <i class="bi bi-search"></i> Buscar
            </button>
            @if($buscar !== '')
                <a href="{{ route('categorias.index') }}" class="gf-btn-soft gf-btn-apagado">Limpiar</a>
            @endif
        </div>
    </form>
</div>

<div class="gf-card">
    <div class="p-3">
        <div class="table-responsive">
            <table class="gf-table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categorias as $categoria)
                        <tr>
                            <td class="fw-semibold">{{ $categoria->nombre }}</td>
                            <td>{{ $categoria->descripcion ?: '—' }}</td>
                            <td>
                                <span class="gf-badge {{ $categoria->activo ? 'gf-badge-exito' : 'gf-badge-apagado' }}">
                                    {{ $categoria->activo ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('categorias.edit', $categoria) }}" class="gf-btn-soft gf-btn-neutro">
                                    Editar
                                </a>
                                @if($categoria->activo)
                                    <form action="{{ route('categorias.destroy', $categoria) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="gf-btn-soft gf-btn-peligro"
                                                onclick="return confirm('¿Desea inactivar esta categoría?')">
                                            Inactivar
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No se encontraron categorías.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
