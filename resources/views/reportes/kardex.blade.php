@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="gf-page-title mb-0">Kardex · {{ $producto->nombre }}</h4>
        <p class="gf-page-subtitle mb-0">
            {{ $producto->categoria ?: 'Sin categoría' }}
            @if($producto->presentacion) · {{ $producto->presentacion }} @endif
            @if($producto->concentracion) · {{ $producto->concentracion }} @endif
            @if($producto->codigo_barra) · Código {{ $producto->codigo_barra }} @endif
            · {{ $desde->format('d/m/Y') }} – {{ $hasta->format('d/m/Y') }}
        </p>
    </div>
    <a href="{{ route('reportes.kardex', ['periodo' => $periodo, 'desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString()]) }}" class="gf-btn-soft gf-btn-apagado"><i class="bi bi-arrow-left"></i> Todos los productos</a>
</div>

@include('reportes._filtro', ['ruta' => 'reportes.kardex', 'excel' => 'reportes.kardex.excel'])

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3"><div class="gf-rep-kpi"><div class="et">Saldo inicial</div><div class="val">{{ number_format($saldoInicial) }}</div><div class="sub">al {{ $desde->format('d/m/Y') }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="gf-rep-kpi"><div class="et">Entradas</div><div class="val pos">+ {{ number_format($entradas) }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="gf-rep-kpi"><div class="et">Salidas</div><div class="val neg">− {{ number_format($salidas) }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="gf-rep-kpi" style="background:var(--verde-oscuro);"><div class="et" style="color:rgba(255,255,255,0.7);">Saldo final</div><div class="val" style="color:#fff;">{{ number_format($saldoFinal) }}</div><div class="sub" style="color:rgba(255,255,255,0.7);">Stock actual: {{ number_format($producto->stock_actual) }}</div></div></div>
</div>

<div class="gf-card">
    <div class="gf-card-header">Movimientos <span class="gf-badge gf-badge-neutro ms-1">{{ $filas->count() }}</span></div>
    <div class="p-3">
        @if($filas->count() > 0)
            <div class="table-responsive">
                <table class="gf-table table-hover">
                    <thead>
                        <tr><th>Fecha</th><th>Tipo</th><th>Detalle</th><th>Lote</th><th>Usuario</th><th class="text-end">Entrada</th><th class="text-end">Salida</th><th class="text-end">Saldo</th></tr>
                    </thead>
                    <tbody>
                        <tr class="gf-tot-fila"><td colspan="7">Saldo inicial</td><td class="text-end">{{ number_format($saldoInicial) }}</td></tr>
                        @foreach($filas as $f)
                            <tr>
                                <td class="gf-page-subtitle">{{ \Carbon\Carbon::parse($f->creado_en)->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($f->tipo_movimiento === 'ENTRADA') <span class="gf-badge gf-badge-neutro">Entrada</span>
                                    @elseif($f->tipo_movimiento === 'SALIDA') <span class="gf-badge gf-badge-alerta">Salida</span>
                                    @else <span class="gf-badge gf-badge-peligro">Ajuste</span> @endif
                                </td>
                                <td>{{ $f->descripcion ?: ($f->motivo ?: '—') }}@if($f->descripcion && $f->motivo)<div class="gf-page-subtitle">{{ $f->motivo }}</div>@endif</td>
                                <td>{{ $f->numero_lote ?: '—' }}</td>
                                <td>{{ $f->usuario ?: '—' }}</td>
                                <td class="text-end" style="color:#2F7A4B;">{{ $f->delta > 0 ? '+ ' . number_format($f->delta) : '' }}</td>
                                <td class="text-end" style="color:#B4452F;">{{ $f->delta < 0 ? '− ' . number_format(abs($f->delta)) : '' }}</td>
                                <td class="text-end fw-semibold">{{ number_format($f->saldo) }}</td>
                            </tr>
                        @endforeach
                        <tr class="gf-tot-fila"><td colspan="5">Totales / saldo final</td><td class="text-end">+ {{ number_format($entradas) }}</td><td class="text-end">− {{ number_format($salidas) }}</td><td class="text-end">{{ number_format($saldoFinal) }}</td></tr>
                    </tbody>
                </table>
            </div>
        @else
            <p class="gf-page-subtitle mb-0">Este producto no tuvo movimientos en el periodo. Saldo: {{ number_format($saldoFinal) }}.</p>
        @endif
    </div>
</div>

<p class="gf-page-subtitle mt-3">
    El saldo se calcula hacia atrás desde el stock real actual del producto. Si el producto tenía stock antes de registrarse su primer movimiento, ese stock aparece dentro del saldo inicial.
</p>

@endsection
