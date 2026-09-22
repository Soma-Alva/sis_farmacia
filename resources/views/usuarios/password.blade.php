@extends('layouts.app')

@section('content')

<div class="container">

    <h3>Cambiar Contraseña</h3>

    <form action="{{ route('usuarios.password.update', $usuario->id_usuario) }}"
          method="POST">

        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Nueva Contraseña</label>

            <input type="password"
                   name="password"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>Confirmar Contraseña</label>

            <input type="password"
                   name="password_confirmation"
                   class="form-control">
        </div>

        <button class="btn btn-success">
            Guardar
        </button>
        <a href="{{ route('usuarios.index') }}"
           class="btn btn-secondary">
            Cancelar
        </a>

    </form>

</div>

@endsection