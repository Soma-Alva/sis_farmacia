@extends('layouts.app')

@section('content')

<div class="container">

    <div class="card shadow">

        <div class="card-header bg-success text-white">

            Nuevo Ingreso

        </div>

        <div class="card-body">

            <form action="{{ route('caja.ingreso.guardar') }}" method="POST">

                @csrf

                <div class="mb-3">

                    <label>Monto</label>

                    <input
                        type="number"
                        name="monto"
                        step="0.01"
                        min="1"
                        class="form-control"
                        required>

                </div>

                <div class="mb-3">

                    <label>Motivo</label>

                    <input
                        type="text"
                        name="motivo"
                        class="form-control"
                        required>

                </div>

                <div class="mb-3">

                    <label>Descripción</label>

                    <textarea
                        name="descripcion"
                        class="form-control"
                        rows="3"></textarea>

                </div>

                <button class="btn btn-success">

                    Guardar Ingreso

                </button>

                <a href="{{ route('caja.index') }}"
                   class="btn btn-secondary">

                    Cancelar

                </a>

            </form>

        </div>

    </div>

</div>

@endsection