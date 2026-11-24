@extends('layouts.app')

@section('content')

<style>
    .gf-rep-card{ display:block; background:#fff; border-radius:18px; padding:22px; box-shadow:0 2px 10px rgba(47,79,62,0.06); text-decoration:none; color:inherit; height:100%; transition:transform .12s ease, box-shadow .12s ease; }
    .gf-rep-card:hover{ transform:translateY(-3px); box-shadow:0 8px 18px rgba(47,79,62,0.14); color:inherit; }
    .gf-rep-card .ic{ width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:19px; margin-bottom:14px; }
    .gf-rep-card h5{ font-family:'Fraunces',serif; font-size:17px; margin-bottom:6px; color:var(--verde-oscuro); }
    .gf-rep-card p{ font-size:13px; color:rgba(36,36,32,0.6); margin:0 0 10px; }
    .gf-rep-card span{ font-size:12.5px; font-weight:600; color:#1E7B45; }
</style>

<div class="mb-3">
    <h4 class="gf-page-title">Reportes</h4>
    <p class="gf-page-subtitle">Consulta en pantalla y exporta a Excel</p>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <a href="{{ route('reportes.ingresos-egresos') }}" class="gf-rep-card">
            <div class="ic" style="background:var(--verde-medio);"><i class="bi bi-arrow-left-right"></i></div>
            <h5>Ingresos y egresos</h5>
            <p>De dónde entró y a dónde salió el dinero: ventas por método de pago, ingresos manuales, compras y egresos de caja.</p>
            <span><i class="bi bi-file-earmark-excel"></i> Excel disponible</span>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reportes.balance') }}" class="gf-rep-card">
            <div class="ic" style="background:#6B5B95;"><i class="bi bi-graph-up-arrow"></i></div>
            <h5>Balance de rentabilidad</h5>
            <p>¿La farmacia es rentable? Ventas, costo, utilidad y margen, comparado con el periodo anterior.</p>
            <span><i class="bi bi-file-earmark-excel"></i> Excel disponible</span>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reportes.ventas') }}" class="gf-rep-card">
            <div class="ic" style="background:var(--verde-oscuro);"><i class="bi bi-receipt"></i></div>
            <h5>Ventas detalladas</h5>
            <p>Una fila por venta, con filtros por estado, método de pago y cajero, y resúmenes por día y por cajero.</p>
            <span><i class="bi bi-file-earmark-excel"></i> Excel disponible</span>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reportes.kardex') }}" class="gf-rep-card">
            <div class="ic" style="background:#3F7D8C;"><i class="bi bi-journal-text"></i></div>
            <h5>Kardex de inventario</h5>
            <p>Entradas, salidas y saldo de cada producto, movimiento por movimiento. Ideal cuando el stock no cuadra.</p>
            <span><i class="bi bi-file-earmark-excel"></i> Excel disponible</span>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reportes.lotes') }}" class="gf-rep-card">
            <div class="ic" style="background:#C98A3B;"><i class="bi bi-calendar-x"></i></div>
            <h5>Lotes vencidos y por vencer</h5>
            <p>Qué ya venció y qué vence pronto, por tramos de 30, 60 y 90 días, con el dinero que está en riesgo.</p>
            <span><i class="bi bi-file-earmark-excel"></i> Excel disponible</span>
        </a>
    </div>
</div>

@endsection
