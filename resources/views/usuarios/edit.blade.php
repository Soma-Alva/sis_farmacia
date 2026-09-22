@extends('layouts.app')

@section('content')

<div class="container">

    <h2>Editar Usuario</h2>

    <form action="{{ route('usuarios.update', $usuario->id_usuario) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Usuario</label>
            <input type="text"
                   name="username"
                   class="form-control"
                   value="{{ $usuario->username }}"
                   required>
        </div>

        <div class="mb-3">
            <label>Nombre completo</label>
            <input type="text"
                   name="nombre_completo"
                   class="form-control"
                   value="{{ $usuario->nombre_completo }}"
                   required>
        </div>

        <div class="mb-3">
            <label>Correo</label>
            <input type="email"
                   name="email"
                   class="form-control"
                   value="{{ $usuario->email }}"
                   required>
        </div>

        <div class="mb-3">
            <label>Rol</label>

            <select name="id_rol" class="form-select">

                @foreach($roles as $rol)

                    <option value="{{ $rol->id_rol }}"
                        {{ $usuario->id_rol == $rol->id_rol ? 'selected' : '' }}>
                        {{ $rol->nombre }}
                    </option>

                @endforeach

            </select>
        </div>

        <button type="submit" class="btn btn-success">
            Actualizar Usuario
        </button>

        <a href="{{ route('usuarios.index') }}"
           class="btn btn-secondary">
            Cancelar
        </a>

    </form>

</div>

@endsection