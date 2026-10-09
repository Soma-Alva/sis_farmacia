@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="gf-page-title">Usuarios</h4>
    <a href="{{ route('usuarios.create') }}" class="gf-btn-primario">
        <i class="bi bi-plus-lg"></i> Nuevo Usuario
    </a>
</div>

<div class="gf-card">
    <div class="p-3">
        <div class="table-responsive">
            <table class="gf-table table-hover">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->username }}</td>
                        <td class="fw-semibold">{{ $usuario->nombre_completo }}</td>
                        <td>{{ $usuario->email }}</td>
                        <td>{{ $usuario->rol->nombre }}</td>

                        <td>
                            <span class="gf-badge {{ $usuario->activo ? 'gf-badge-exito' : 'gf-badge-peligro' }}">
                                {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            @if(auth()->user()->rol->nombre == 'ADMINISTRADOR')
                                <a href="{{ route('usuarios.edit', $usuario->id_usuario) }}" class="gf-btn-soft gf-btn-neutro">
                                    Editar
                                </a>
                                <a href="{{ route('usuarios.password', $usuario->id_usuario) }}" class="gf-btn-soft gf-btn-neutro">
                                    Contraseña
                                </a>
                                <form action="{{ route('usuarios.destroy', $usuario->id_usuario) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="gf-btn-soft gf-btn-peligro"
                                            onclick="return confirm('¿Desea eliminar este usuario?')">
                                        Eliminar
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No hay usuarios registrados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
