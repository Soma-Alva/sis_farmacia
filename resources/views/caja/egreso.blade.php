@extends('layouts.app')

@section('content')

<div class="container">

    <div class="card shadow">

        <div class="card-header bg-danger text-white">

            Nuevo Egreso

        </div>

        <div class="card-body">

            <form action="{{ route('caja.egreso.guardar') }}" method="POST">

                @csrf

                <div class="mb-3">

                    <label>Monto</label>

                    <input
                        type="number"
                        name="monto"
                        class="form-control"
                        min="1"
                        step="0.01"
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
                        class="form-control"></textarea>

                </div>

                <button class="btn btn-danger">

                    Guardar Egreso

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