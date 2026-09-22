@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Usuarios</h3>

        <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
            + Nuevo Usuario
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">

            <table class="table table-hover">

                <thead class="table-dark">
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
                        <td>{{ $usuario->nombre_completo }}</td>
                        <td>{{ $usuario->email }}</td>
                        <td>{{ $usuario->rol->nombre }}</td>

                        <td>
                            @if($usuario->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </td>
                        <td>
                                    @if(auth()->user()->rol->nombre == 'ADMINISTRADOR')

                                        <a href="{{ route('usuarios.edit', $usuario->id_usuario) }}"
                                        class="btn btn-warning btn-sm">
                                            Editar
                                        </a>
                                        <a href="{{ route('usuarios.password', $usuario->id_usuario) }}"
                                            class="btn btn-info btn-sm">
                                            Contraseña
                                        </a>
                                        <form action="{{ route('usuarios.destroy', $usuario->id_usuario) }}"
                                            method="POST"
                                            class="d-inline">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('¿Desea eliminar este usuario?')">
                                                Eliminar
                                            </button>

                                        </form>

                                    @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">
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