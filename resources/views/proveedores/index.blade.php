@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="gf-page-title">Proveedores</h4>
    <a href="{{ route('proveedores.create') }}" class="gf-btn-primario">
        <i class="bi bi-plus-lg"></i> Nuevo proveedor
    </a>
</div>

<div class="gf-filtros">
    <form method="GET" action="{{ route('proveedores.index') }}" class="row g-2">
        <div class="col-md-6">
            <input type="search" name="buscar" value="{{ $buscar }}" class="form-control"
                   placeholder="Buscar por nombre, RUC, teléfono o correo">
        </div>
        <div class="col-auto">
            <button class="gf-btn-soft gf-btn-neutro" type="submit" style="padding:9px 16px;">
                <i class="bi bi-search"></i> Buscar
            </button>
            @if($buscar !== '')
                <a href="{{ route('proveedores.index') }}" class="gf-btn-soft gf-btn-apagado">Limpiar</a>
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
                            <td class="fw-semibold">{{ $proveedor->nombre }}</td>
                            <td>{{ $proveedor->ruc ?: '—' }}</td>
                            <td>{{ $proveedor->telefono ?: '—' }}</td>
                            <td>{{ $proveedor->email ?: '—' }}</td>
                            <td>
                                <span class="gf-badge {{ $proveedor->activo ? 'gf-badge-exito' : 'gf-badge-apagado' }}">
                                    {{ $proveedor->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('proveedores.edit', $proveedor) }}" class="gf-btn-soft gf-btn-neutro">
                                    Editar
                                </a>
                                @if($proveedor->activo)
                                    <form action="{{ route('proveedores.destroy', $proveedor) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="gf-btn-soft gf-btn-peligro"
                                                onclick="return confirm('¿Desea inactivar este proveedor?')">
                                            Inactivar
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

@endsection
