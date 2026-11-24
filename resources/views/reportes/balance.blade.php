@extends('layouts.app')

@section('content')

@php
    $a = $actual;
    $p = $anterior;
    $delta = function ($act, $ant) {
        if ($ant != 0) { $pct = (($act - $ant) / abs($ant)) * 100; } else { $pct = $act != 0 ? 100 : 0; }
        $clase = abs($pct) < 0.05 ? 'igual' : ($pct > 0 ? 'sube' : 'baja');
        $flecha = $clase === 'igual' ? '■' : ($pct > 0 ? '▲' : '▼');
        return ['txt' => $flecha . ' ' . number_format(abs($pct), 1) . '%', 'clase' => $clase];
    };
    $rentable = $a['utilidad'] > 0;
    $enPerdida = $a['utilidad'] < 0;
    $colorVeredicto = $rentable ? '#2F7A4B' : ($enPerdida ? '#B4452F' : '#8A6A1F');
    $fondoVeredicto = $rentable ? '#E3F1E8' : ($enPerdida ? '#FBE9E6' : '#FBF3DE');
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="gf-page-title mb-0">Balance de rentabilidad</h4>
        <p class="gf-page-subtitle mb-0">{{ $desde->format('d/m/Y') }} – {{ $hasta->format('d/m/Y') }}</p>
    </div>
    <a href="{{ route('reportes.index') }}" class="gf-btn-soft gf-btn-apagado"><i class="bi bi-arrow-left"></i> Reportes</a>
</div>

@include('reportes._filtro', ['ruta' => 'reportes.balance', 'excel' => 'reportes.balance.excel'])

{{-- Veredicto --}}
<div class="gf-card mb-3" style="background:{{ $fondoVeredicto }}; border:none;">
    <div class="p-4 d-flex align-items-center gap-3 flex-wrap">
        <div style="width:56px;height:56px;border-radius:16px;background:{{ $colorVeredicto }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:26px;">
            <i class="bi {{ $rentable ? 'bi-emoji-smile' : ($enPerdida ? 'bi-emoji-frown' : 'bi-emoji-neutral') }}"></i>
        </div>
        <div class="flex-grow-1">
            <div style="font-size:13px;color:rgba(36,36,32,0.6);">En este periodo la farmacia está</div>
            <div style="font-family:'Fraunces',serif;font-size:26px;font-weight:700;color:{{ $colorVeredicto }};">{{ $veredicto }}</div>
            <div style="font-size:13.5px;color:rgba(36,36,32,0.75);">
                @if($a['tickets'] === 0)
                    No hay ventas completadas en el periodo seleccionado.
                @elseif($rentable)
                    Por cada C$ 100 vendidos quedan C$ {{ number_format($a['margen'] * 100, 2) }} de utilidad.
                @elseif($enPerdida)
                    El costo de lo vendido es mayor que las ventas. Revisa los precios de venta.
                @else
                    Las ventas solo alcanzan para cubrir el costo.
                @endif
            </div>
        </div>
        <div class="text-end">
            <div style="font-size:12.5px;color:rgba(36,36,32,0.6);">Utilidad del periodo</div>
            <div style="font-size:28px;font-weight:700;color:{{ $colorVeredicto }};">C$ {{ number_format($a['utilidad'], 2) }}</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="gf-card h-100">
            <div class="gf-card-header">Estado de resultados</div>
            <div class="p-3">
                <table class="gf-table">
                    <thead>
                        <tr><th>Concepto</th><th class="text-end">Periodo actual</th><th class="text-end">Periodo anterior</th><th class="text-end">Variación</th></tr>
                    </thead>
                    <tbody>
                        @php
                            $filas = [
                                ['Ventas netas (sin IVA)', 'neto'],
                                ['(−) Costo de lo vendido', 'costo'],
                            ];
                        @endphp
                        @foreach($filas as [$et, $k])
                            @php $d = $delta($a[$k], $p[$k]); @endphp
                            <tr>
                                <td>{{ $et }}</td>
                                <td class="text-end">C$ {{ number_format($a[$k], 2) }}</td>
                                <td class="text-end">C$ {{ number_format($p[$k], 2) }}</td>
                                <td class="text-end"><span class="gf-delta {{ $k === 'costo' ? 'igual' : $d['clase'] }}">{{ $d['txt'] }}</span></td>
                            </tr>
                        @endforeach
                        @php $dU = $delta($a['utilidad'], $p['utilidad']); @endphp
                        <tr class="gf-tot-fila">
                            <td>Utilidad del periodo</td>
                            <td class="text-end">C$ {{ number_format($a['utilidad'], 2) }}</td>
                            <td class="text-end">C$ {{ number_format($p['utilidad'], 2) }}</td>
                            <td class="text-end"><span class="gf-delta {{ $dU['clase'] }}">{{ $dU['txt'] }}</span></td>
                        </tr>
                        <tr>
                            <td>Margen de utilidad</td>
                            <td class="text-end">{{ number_format($a['margen'] * 100, 1) }}%</td>
                            <td class="text-end">{{ number_format($p['margen'] * 100, 1) }}%</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
                <p class="gf-page-subtitle mt-2 mb-0">
                    Periodo anterior: {{ $antDesde->format('d/m/Y') }} – {{ $antHasta->format('d/m/Y') }}.
                    El costo es el del lote vendido (o el precio de compra del producto). Las compras de mercadería no se restan: se vuelven costo cuando el producto se vende.
                </p>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="gf-card h-100">
            <div class="gf-card-header">Situación actual (hoy)</div>
            <div class="p-3">
                <table class="gf-table">
                    <tbody>
                        <tr><td>Inventario a costo</td><td class="text-end">C$ {{ number_format($inventarioCosto, 2) }}</td></tr>
                        <tr><td>Inventario a precio de venta</td><td class="text-end">C$ {{ number_format($inventarioVenta, 2) }}</td></tr>
                        <tr><td>Utilidad potencial del inventario</td><td class="text-end">C$ {{ number_format($inventarioVenta - $inventarioCosto, 2) }}</td></tr>
                        <tr>
                            <td>Efectivo esperado en caja</td>
                            <td class="text-end">
                                @if($efectivoCaja === null) <span class="gf-badge gf-badge-neutro">Caja cerrada</span>
                                @else C$ {{ number_format($efectivoCaja, 2) }} @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Pérdida por lotes vencidos (a costo)</td>
                            <td class="text-end" style="color:{{ $perdidaVencidos > 0 ? '#B4452F' : 'inherit' }};">C$ {{ number_format($perdidaVencidos, 2) }}</td>
                        </tr>
                        <tr><td>Compras de mercadería del periodo</td><td class="text-end">C$ {{ number_format($comprasPeriodo, 2) }}</td></tr>
                        <tr><td>IVA cobrado (no es ingreso propio)</td><td class="text-end">C$ {{ number_format($a['iva'], 2) }}</td></tr>
                        <tr>
                            <td>Ventas anuladas</td>
                            <td class="text-end">{{ $anuladas->n }} · C$ {{ number_format($anuladas->total, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="gf-card h-100">
            <div class="gf-card-header">Productos que más utilidad dejan (top 15)</div>
            <div class="p-3">
                @if($porProducto->count() > 0)
                    <div class="table-responsive">
                        <table class="gf-table table-hover">
                            <thead><tr><th>Producto</th><th class="text-end">Uds.</th><th class="text-end">Venta neta</th><th class="text-end">Utilidad</th><th class="text-end">Margen</th></tr></thead>
                            <tbody>
                                @foreach($porProducto as $r)
                                    <tr>
                                        <td class="fw-semibold">{{ $r->nombre }}</td>
                                        <td class="text-end">{{ $r->unidades }}</td>
                                        <td class="text-end">C$ {{ number_format($r->venta, 2) }}</td>
                                        <td class="text-end">C$ {{ number_format($r->utilidad, 2) }}</td>
                                        <td class="text-end"><span class="gf-badge {{ $r->margen < 0 ? 'gf-badge-peligro' : 'gf-badge-neutro' }}">{{ number_format($r->margen * 100, 1) }}%</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="gf-page-subtitle mb-0">Sin ventas en este periodo</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="gf-card h-100">
            <div class="gf-card-header">Rentabilidad por categoría</div>
            <div class="p-3">
                @if($porCategoria->count() > 0)
                    <table class="gf-table">
                        <thead><tr><th>Categoría</th><th class="text-end">Utilidad</th><th class="text-end">Margen</th></tr></thead>
                        <tbody>
                            @foreach($porCategoria as $r)
                                <tr>
                                    <td>{{ $r->nombre }}</td>
                                    <td class="text-end">C$ {{ number_format($r->utilidad, 2) }}</td>
                                    <td class="text-end">{{ number_format($r->margen * 100, 1) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="gf-page-subtitle mb-0">Sin ventas en este periodo</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
