@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Categorías</h3>
        <a href="{{ route('categorias.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva categoría
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form method="GET" action="{{ route('categorias.index') }}" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="search" name="buscar" value="{{ $buscar }}" class="form-control"
                           placeholder="Buscar por nombre o descripción">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    @if($buscar !== '')
                        <a href="{{ route('categorias.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
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
                                <td>{{ $categoria->nombre }}</td>
                                <td>{{ $categoria->descripcion ?: '—' }}</td>
                                <td>
                                    <span class="badge {{ $categoria->activo ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $categoria->activo ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('categorias.edit', $categoria) }}"
                                       class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                    @if($categoria->activo)
                                        <form action="{{ route('categorias.destroy', $categoria) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('¿Desea inactivar esta categoría?')">
                                                <i class="bi bi-x-circle"></i> Inactivar
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
</div>
@endsection
