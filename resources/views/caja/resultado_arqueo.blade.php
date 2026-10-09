@extends('layouts.app')

@section('content')

<style>
    .gf-tabla-kv th{
        background:var(--verde-pastel);
        color:var(--verde-oscuro);
        font-size:13px;
        font-weight:600;
        width:40%;
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
</style>

<div class="gf-card">
    <div class="gf-card-header">Resultado del Arqueo de Caja</div>

    <div class="p-3">
        <div class="table-responsive">
            <table class="table gf-tabla-kv mb-0">
                <tr>
                    <th>Saldo Inicial</th>
                    <td>C$ {{ number_format($caja->saldo_inicial,2) }}</td>
                </tr>
                <tr>
                    <th>Ventas del día</th>
                    <td>C$ {{ number_format($ventas,2) }}</td>
                </tr>
                <tr>
                    <th>Ingresos</th>
                    <td>C$ {{ number_format($ingresos,2) }}</td>
                </tr>
                <tr>
                    <th>Egresos</th>
                    <td>C$ {{ number_format($egresos,2) }}</td>
                </tr>
                <tr>
                    <th>Saldo Esperado</th>
                    <td><strong>C$ {{ number_format($saldoEsperado,2) }}</strong></td>
                </tr>
                <tr>
                    <th>Efectivo contado</th>
                    <td>C$ {{ number_format($contado,2) }}</td>
                </tr>
                <tr>
                    <th>Diferencia</th>
                    <td>
                        @if($diferencia == 0)
                            <span class="gf-badge gf-badge-exito">Caja Cuadrada</span>
                        @elseif($diferencia > 0)
                            <span class="gf-badge gf-badge-neutro">Sobrante: C$ {{ number_format($diferencia,2) }}</span>
                        @else
                            <span class="gf-badge gf-badge-peligro">Faltante: C$ {{ number_format(abs($diferencia),2) }}</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="{{ route('caja.index') }}" class="gf-btn-soft gf-btn-apagado">
                Volver
            </a>

            <form action="{{ route('caja.cerrar') }}" method="POST">
                @csrf
                <input type="hidden" name="saldo_contado" value="{{ $contado }}">
                <button class="gf-btn-primario" style="background:#8C3A2A;">
                    <i class="bi bi-lock"></i> Cerrar Caja
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
