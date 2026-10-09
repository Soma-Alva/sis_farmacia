@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="gf-page-title">Clientes</h4>
    <a href="{{ route('clientes.create') }}" class="gf-btn-primario">
        <i class="bi bi-plus-lg"></i> Nuevo Cliente
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
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Cédula</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($clientes as $cliente)
                        <tr>
                            <td class="fw-semibold">{{ $cliente->nombres }}</td>
                            <td>{{ $cliente->apellidos }}</td>
                            <td>{{ $cliente->cedula }}</td>
                            <td>{{ $cliente->telefono }}</td>
                            <td>{{ $cliente->email }}</td>

                            <td>
                                <span class="gf-badge {{ $cliente->estado ? 'gf-badge-exito' : 'gf-badge-apagado' }}">
                                    {{ $cliente->estado ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('clientes.edit', $cliente->id_cliente) }}" class="gf-btn-soft gf-btn-neutro">
                                    Editar
                                </a>

                                <form action="{{ route('clientes.destroy', $cliente->id_cliente) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="gf-btn-soft gf-btn-peligro" onclick="return confirm('¿Eliminar cliente?')">
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
