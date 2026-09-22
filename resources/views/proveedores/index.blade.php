@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Proveedores</h3>
        <a href="{{ route('proveedores.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo proveedor
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form method="GET" action="{{ route('proveedores.index') }}" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="search" name="buscar" value="{{ $buscar }}" class="form-control"
                           placeholder="Buscar por nombre, RUC, teléfono o correo">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    @if($buscar !== '')
                        <a href="{{ route('proveedores.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Nombre</th>
                            <th>RUC</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($proveedores as $proveedor)
                            <tr>
                                <td>{{ $proveedor->nombre }}</td>
                                <td>{{ $proveedor->ruc ?: '—' }}</td>
                                <td>{{ $proveedor->telefono ?: '—' }}</td>
                                <td>{{ $proveedor->email ?: '—' }}</td>
                                <td>
                                    <span class="badge {{ $proveedor->activo ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $proveedor->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('proveedores.edit', $proveedor) }}"
                                       class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                    @if($proveedor->activo)
                                        <form action="{{ route('proveedores.destroy', $proveedor) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('¿Desea inactivar este proveedor?')">
                                                <i class="bi bi-x-circle"></i> Inactivar
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No se encontraron proveedores.
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
