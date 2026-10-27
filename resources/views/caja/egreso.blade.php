@extends('layouts.app')

@section('content')

<style>
    .gf-form-label{
        font-size:12.5px;
        font-weight:600;
        color:var(--verde-oscuro);
        margin-bottom:6px;
        display:block;
    }
    .gf-form-control{
        width:100%;
        border-radius:10px;
        border:1px solid rgba(47,79,62,0.15);
        padding:10px 14px;
        font-size:13.5px;
        color:var(--carbon);
    }
    .gf-form-control:focus{
        outline:none;
        border-color:var(--verde-medio);
        box-shadow:0 0 0 .2rem rgba(75,122,97,0.15);
    }
    .gf-form-group{ margin-bottom:16px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="gf-page-title mb-0">Nuevo egreso</h4>
        <p class="gf-page-subtitle mb-0">Dinero que sale de la caja fuera de una venta</p>
    </div>
    <a href="{{ route('caja.index') }}" class="gf-btn-soft gf-btn-apagado">
        <i class="bi bi-arrow-left"></i> Volver a caja
    </a>
</div>

<div class="gf-card" style="max-width:560px;">
    <div class="gf-card-header">
        <i class="bi bi-dash-circle" style="color:#8C3A2A;"></i> Registrar egreso
    </div>

    <div class="p-4">

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('caja.egreso.guardar') }}" method="POST">
            @csrf

            <div class="gf-form-group">
                <label class="gf-form-label">Monto (C$)</label>
                <input type="number" name="monto" step="0.01" min="0.01"
                       class="gf-form-control" value="{{ old('monto') }}" required>
            </div>

            <div class="gf-form-group">
                <label class="gf-form-label">Motivo</label>
                <input type="text" name="motivo" class="gf-form-control"
                       value="{{ old('motivo') }}"
                       placeholder="Ej. Pago de servicios" required>
            </div>

            <div class="gf-form-group">
                <label class="gf-form-label">Descripción (opcional)</label>
                <textarea name="descripcion" class="gf-form-control" rows="3"
                          placeholder="Detalle adicional...">{{ old('descripcion') }}</textarea>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="gf-btn-primario" style="background:#8C3A2A;">
                    <i class="bi bi-check-lg"></i> Guardar egreso
                </button>
                <a href="{{ route('caja.index') }}" class="gf-btn-soft gf-btn-apagado">
                    Cancelar
                </a>
            </div>
        </form>

    </div>
</div>

@endsection
