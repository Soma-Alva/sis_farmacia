@extends('layouts.app')

@section('content')

<div class="container">

    <h2>Nuevo Usuario</h2>

    <form action="{{ route('usuarios.store') }}" method="POST">
        @csrf


        <div class="mb-3">
            <label>Usuario</label>
            <input  type="text"
                    name="username"
                    class="form-control"
                    required>
</div>
        <div class="mb-3">
            <label>Nombre completo</label>
            <input type="text"
                   name="nombre_completo"
                   class="form-control"
                   required>
        </div>

        <div class="mb-3">
            <label>Correo</label>
            <input type="email"
                   name="email"
                   class="form-control"
                   required>
        </div>

        <div class="mb-3">
            <label>Contraseña</label>
            <input type="password"
                   name="password"
                   class="form-control"
                   required>
        </div>

        <div class="mb-3">
            <label>Rol</label>

            <select name="id_rol"
                    class="form-select">

                @foreach($roles as $rol)
                    <option value="{{ $rol->id_rol }}">
                        {{ $rol->nombre }}
                    </option>
                @endforeach

            </select>
        </div>

        <button class="btn btn-success">
            Guardar Usuario
        </button>

    </form>

</div>

@endsection