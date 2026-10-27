@extends('layouts.app')

@section('content')

<style>
    .gf-tabla-kv th{
        background:var(--verde-pastel);
        color:var(--verde-oscuro);
        font-size:13px;
        font-weight:600;
        width:45%;
        border:none;
        padding:12px 16px;
    }
    .gf-tabla-kv td{
        font-size:14px;
        border:none;
        padding:12px 16px;
        border-bottom:1px solid rgba(47,79,62,0.08);
    }
    .gf-tabla-kv tr{ border-bottom:1px solid rgba(47,79,62,0.08); }

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
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="gf-page-title mb-0">Arqueo de caja</h4>
        <p class="gf-page-subtitle mb-0">Compara el efectivo esperado contra el efectivo contado a mitad de turno</p>
    </div>
    <a href="{{ route('caja.index') }}" class="gf-btn-soft gf-btn-apagado">
        <i class="bi bi-arrow-left"></i> Volver a caja
    </a>
</div>

<div class="gf-card" style="max-width:640px;">
    <div class="gf-card-header">Resumen del turno</div>

    <div class="p-3">
        <div class="table-responsive">
            <table class="table gf-tabla-kv mb-0">
                <tr>
                    <th>Saldo inicial</th>
                    <td>C$ {{ number_format($caja->saldo_inicial, 2) }}</td>
                </tr>
                <tr>
                    <th>Ventas del día</th>
                    <td>C$ {{ number_format($ventas, 2) }}</td>
                </tr>
                <tr>
                    <th>Ingresos manuales</th>
                    <td>C$ {{ number_format($ingresos, 2) }}</td>
                </tr>
                <tr>
                    <th>Egresos</th>
                    <td>C$ {{ number_format($egresos, 2) }}</td>
                </tr>
                <tr>
                    <th>Saldo esperado</th>
                    <td><strong style="color:var(--verde-oscuro);">C$ {{ number_format($saldoEsperado, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <form action="{{ route('caja.arqueo.guardar') }}" method="POST" class="p-2 pt-3">
            @csrf

            <div class="mb-3">
                <label class="gf-form-label">Efectivo contado</label>
                <input type="number" step="0.01" min="0" name="saldo_contado"
                       class="gf-form-control" required>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="gf-btn-primario" style="background:#C98A3B;">
                    <i class="bi bi-calculator"></i> Calcular diferencia
                </button>
                <a href="{{ route('caja.index') }}" class="gf-btn-soft gf-btn-apagado">
                    Regresar
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
