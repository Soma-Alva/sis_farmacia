@extends('layouts.app')

@section('content')

<style>
    .gf-stat-clickable{
        cursor:pointer;
        transition:transform .12s ease, box-shadow .12s ease;
    }
    .gf-stat-clickable:hover,
    .gf-stat-clickable:focus-visible{
        transform:translateY(-3px);
        box-shadow:0 8px 18px rgba(47,79,62,0.14);
        outline:none;
    }

    .gf-modal-stock{
        border:none;
        border-radius:22px;
        overflow:hidden;
        box-shadow:0 24px 60px rgba(47,79,62,0.3);
    }
    .gf-modal-stock-header{
        background:linear-gradient(135deg, #8A6A1F 0%, #C98A3B 100%);
        padding:24px 54px 22px 24px;
        display:flex;
        align-items:flex-start;
        gap:14px;
        position:relative;
    }
    .gf-modal-stock-icono{
        width:46px;
        height:46px;
        border-radius:14px;
        background:rgba(255,255,255,0.2);
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:20px;
        color:#fff;
        flex:0 0 auto;
    }
    .gf-modal-stock-titulo{
        font-family:'Fraunces',serif;
        font-weight:600;
        font-size:19px;
        color:#fff;
        margin:0;
    }
    .gf-modal-stock-sub{
        font-size:12.5px;
        color:rgba(255,255,255,0.85);
        margin:3px 0 0;
    }
    .gf-modal-stock-cerrar{
        position:absolute;
        top:16px;
        right:16px;
        width:32px;
        height:32px;
        border-radius:50%;
        border:none;
        background:rgba(255,255,255,0.2);
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        cursor:pointer;
    }
    .gf-modal-stock-cerrar:hover{ background:rgba(255,255,255,0.35); }
    .gf-modal-stock-resumen{
        display:flex;
        gap:10px;
        flex-wrap:wrap;
        padding:16px 24px 4px;
        background:#fff;
    }
    .gf-modal-stock-lista{
        background:#fff;
        padding:8px 24px 22px;
        max-height:55vh;
        overflow-y:auto;
    }
    .gf-modal-stock-fila{
        display:flex;
        align-items:center;
        gap:14px;
        padding:12px 0;
        border-bottom:1px solid rgba(47,79,62,0.08);
    }
    .gf-modal-stock-fila:last-child{ border-bottom:none; }
    .gf-modal-stock-info{ flex:1 1 auto; min-width:0; }
    .gf-modal-stock-nombre{
        font-weight:600;
        font-size:13.5px;
        color:var(--carbon);
    }
    .gf-modal-stock-meta{
        font-size:11.5px;
        color:rgba(36,36,32,0.5);
    }
    .gf-modal-stock-cifras{
        text-align:right;
        white-space:nowrap;
    }
    .gf-modal-stock-cifras small{
        display:block;
        font-size:11px;
        color:rgba(36,36,32,0.5);
        margin-top:3px;
    }
    .gf-modal-stock-pie{
        background:#fff;
        padding:0 24px 22px;
        text-align:right;
    }

    /* ---------- Dashboard completo ---------- */
    .gf-dash-top{ display:flex; justify-content:space-between; align-items:flex-end; gap:14px; flex-wrap:wrap; margin-bottom:16px; }
    .gf-filtro-periodo{ display:flex; align-items:center; gap:6px; flex-wrap:wrap; background:#fff; border-radius:14px; padding:6px; box-shadow:0 2px 10px rgba(47,79,62,0.06); }
    .gf-filtro-periodo a{ padding:7px 14px; border-radius:10px; font-size:13px; font-weight:600; color:var(--verde-oscuro); text-decoration:none; }
    .gf-filtro-periodo a:hover{ background:var(--verde-pastel); }
    .gf-filtro-periodo a.activo{ background:var(--verde-oscuro); color:#fff; }
    .gf-filtro-periodo form{ display:flex; align-items:center; gap:6px; margin:0; }
    .gf-filtro-periodo input[type=date]{ border:1px solid rgba(47,79,62,0.15); border-radius:9px; padding:5px 8px; font-size:12.5px; color:var(--carbon); }
    .gf-filtro-periodo button{ border:none; background:var(--verde-pastel); color:var(--verde-oscuro); border-radius:9px; padding:6px 12px; font-size:12.5px; font-weight:600; }

    .gf-kpi-sub{ font-size:12px; margin-top:4px; color:rgba(36,36,32,0.55); }
    .gf-delta{ font-weight:700; }
    .gf-delta.sube{ color:#2F7A4B; }
    .gf-delta.baja{ color:#B4452F; }
    .gf-delta.igual{ color:rgba(36,36,32,0.5); }
    .gf-stat .valor{ font-size:24px; }
    a.gf-stat-enlace{ color:inherit; text-decoration:none; display:block; height:100%; }

    .gf-panel-titulo{ display:flex; justify-content:space-between; align-items:center; gap:8px; }
    .gf-panel-titulo small{ font-weight:500; color:rgba(36,36,32,0.55); font-size:12px; }
    .gf-resumen-periodo{ display:flex; gap:22px; flex-wrap:wrap; padding:14px 18px 0; }
    .gf-resumen-periodo div span{ display:block; font-size:11.5px; color:rgba(36,36,32,0.55); }
    .gf-resumen-periodo div strong{ font-size:17px; color:var(--carbon); }
    .gf-sin-datos{ height:100%; display:flex; align-items:center; justify-content:center; color:rgba(36,36,32,0.45); font-size:13px; text-align:center; }

    .gf-accesos{ display:flex; gap:10px; flex-wrap:wrap; }
    .gf-accesos a{
        display:inline-flex; align-items:center; gap:8px; padding:9px 16px; border-radius:12px;
        background:#fff; color:var(--verde-oscuro); font-size:13px; font-weight:600; text-decoration:none;
        box-shadow:0 2px 10px rgba(47,79,62,0.06); transition:transform .12s ease, background .12s ease;
    }
    .gf-accesos a:hover{ background:var(--verde-pastel); transform:translateY(-2px); }
    .gf-link-ver{ font-size:12.5px; font-weight:600; color:var(--verde-medio); text-decoration:none; }
    .gf-link-ver:hover{ text-decoration:underline; }

    .gf-actividad{ display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid rgba(47,79,62,0.08); }
    .gf-actividad:last-child{ border-bottom:none; }
    .gf-actividad-icono{ width:34px; height:34px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex:0 0 auto; }
    .gf-actividad-icono.ing{ background:#E3F1E8; color:#2F7A4B; }
    .gf-actividad-icono.egr{ background:#FBE9E6; color:#B4452F; }
    .gf-actividad-info{ flex:1 1 auto; min-width:0; }
    .gf-actividad-txt{ font-size:13.5px; font-weight:600; color:var(--carbon); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .gf-actividad-meta{ font-size:11.5px; color:rgba(36,36,32,0.5); }
    .gf-actividad-monto{ font-size:13px; font-weight:700; white-space:nowrap; color:var(--carbon); }
    .gf-actividad-monto.ing{ color:#2F7A4B; }
    .gf-actividad-monto.egr{ color:#B4452F; }
</style>

<div class="gf-dash-top">
    <div>
        <h4 class="gf-page-title mb-0">Dashboard</h4>
        <p class="gf-page-subtitle mb-0">Hola, {{ \Illuminate\Support\Str::of(auth()->user()->nombre_completo)->before(' ') }} · {{ now()->locale('es')->translatedFormat('l d \d\e F, Y') }}</p>
    </div>

    <div class="gf-filtro-periodo">
        <a href="{{ route('dashboard', ['periodo' => 'hoy']) }}" class="{{ $periodo === 'hoy' ? 'activo' : '' }}">Hoy</a>
        <a href="{{ route('dashboard', ['periodo' => '7']) }}" class="{{ $periodo === '7' ? 'activo' : '' }}">7 días</a>
        <a href="{{ route('dashboard', ['periodo' => '30']) }}" class="{{ $periodo === '30' ? 'activo' : '' }}">30 días</a>
        <a href="{{ route('dashboard', ['periodo' => 'mes']) }}" class="{{ $periodo === 'mes' ? 'activo' : '' }}">Este mes</a>
        <form method="GET" action="{{ route('dashboard') }}">
            <input type="hidden" name="periodo" value="rango">
            <input type="date" name="desde" value="{{ $desde->toDateString() }}" max="{{ now()->toDateString() }}" required>
            <span>–</span>
            <input type="date" name="hasta" value="{{ $hasta->toDateString() }}" max="{{ now()->toDateString() }}" required>
            <button type="submit">Aplicar</button>
        </form>
    </div>
</div>

@php
    // Variación porcentual entre el valor actual y el anterior.
    $delta = function ($actual, $anterior) {
        if ($anterior > 0) {
            $pct = (($actual - $anterior) / $anterior) * 100;
        } else {
            $pct = $actual > 0 ? 100 : 0;
        }
        $clase = abs($pct) < 0.05 ? 'igual' : ($pct > 0 ? 'sube' : 'baja');
        $flecha = $clase === 'igual' ? '■' : ($pct > 0 ? '▲' : '▼');
        return ['txt' => $flecha . ' ' . number_format(abs($pct), 1) . '%', 'clase' => $clase];
    };
    $dHoy = $delta($ventasHoy, $ventasAyer);
    $dMes = $delta($ventasMes, $ventasMesAnt);
    $dGan = $esAdmin ? $delta($gananciaMes, $gananciaMesAnt) : null;
    $dPer = $delta($totalPeriodo, $totalPeriodoAnt);
@endphp

{{-- Accesos rápidos --}}
<div class="gf-accesos mb-3">
    <a href="{{ route('ventas.index') }}"><i class="bi bi-cart-plus"></i> Nueva venta</a>
    <a href="{{ route('caja.index') }}"><i class="bi bi-cash-coin"></i> {{ $caja ? 'Ir a caja' : 'Abrir caja' }}</a>
    <a href="{{ route('productos.create') }}"><i class="bi bi-plus-square"></i> Nuevo producto</a>
    <a href="{{ route('compras.create') }}"><i class="bi bi-bag-plus"></i> Nueva compra</a>
    <a href="{{ route('ventas.historial') }}"><i class="bi bi-clock-history"></i> Historial de ventas</a>
</div>

{{-- Alertas de vencimiento --}}
@if($lotesVencidos->count() > 0)
    <div class="gf-banner danger">
        <div>
            <i class="bi bi-exclamation-triangle-fill"></i>
            Tienes <strong>{{ $lotesVencidos->count() }}</strong> lote(s) <strong>vencido(s)</strong>
            con stock disponible. No deberían seguir vendiéndose.
        </div>
        <a href="{{ route('lotes.index', ['estado' => 'ACTIVO']) }}">Revisar</a>
    </div>
@endif

@if($lotesPorVencer->count() > 0)
    <div class="gf-banner warning">
        <div>
            <i class="bi bi-clock-history"></i>
            <strong>{{ $lotesPorVencer->count() }}</strong> lote(s) vencen en los próximos 90 días
            @if($porVencer30 > 0) (<strong>{{ $porVencer30 }}</strong> en los próximos 30) @endif.
        </div>
        <a href="{{ route('lotes.index', ['proximos_vencer' => 1]) }}">Ver en Lotes</a>
    </div>
@endif

{{-- ===== Tarjetas principales ===== --}}
<div class="row g-3 mb-3">

    <div class="col-6 col-xl-3">
        <div class="gf-stat">
            <div class="icono" style="background:var(--verde-oscuro);"><i class="bi bi-cart-check"></i></div>
            <div class="etiqueta">Ventas de hoy</div>
            <div class="valor">C$ {{ number_format($ventasHoy, 2) }}</div>
            <div class="gf-kpi-sub">{{ $ticketsHoy }} ticket(s) · <span class="gf-delta {{ $dHoy['clase'] }}">{{ $dHoy['txt'] }}</span> vs ayer</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="gf-stat">
            <div class="icono" style="background:var(--verde-medio);"><i class="bi bi-calendar-month"></i></div>
            <div class="etiqueta">Ventas del mes</div>
            <div class="valor">C$ {{ number_format($ventasMes, 2) }}</div>
            <div class="gf-kpi-sub"><span class="gf-delta {{ $dMes['clase'] }}">{{ $dMes['txt'] }}</span> vs mes anterior</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="gf-stat">
            <div class="icono" style="background:#3F7D8C;"><i class="bi bi-receipt"></i></div>
            <div class="etiqueta">Ticket promedio (mes)</div>
            <div class="valor">C$ {{ number_format($ticketPromedio, 2) }}</div>
            <div class="gf-kpi-sub">{{ $ticketsMes }} venta(s) en el mes</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <a href="{{ route('caja.index') }}" class="gf-stat-enlace">
            <div class="gf-stat gf-stat-clickable">
                <div class="icono" style="background:{{ $caja ? '#2F7A4B' : '#8C3A2A' }};"><i class="bi bi-cash-coin"></i></div>
                <div class="etiqueta">Caja</div>
                @if($caja)
                    <div class="valor">C$ {{ number_format($efectivoEnCaja, 2) }}</div>
                    <div class="gf-kpi-sub"><span class="gf-delta sube">● Abierta</span> · efectivo esperado</div>
                @else
                    <div class="valor">Cerrada</div>
                    <div class="gf-kpi-sub">Haz clic para abrir la caja</div>
                @endif
            </div>
        </a>
    </div>

    @if($esAdmin)
    <div class="col-6 col-xl-3">
        <div class="gf-stat">
            <div class="icono" style="background:#6B5B95;"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="etiqueta">Ganancia estimada (mes)</div>
            <div class="valor">C$ {{ number_format($gananciaMes, 2) }}</div>
            <div class="gf-kpi-sub"><span class="gf-delta {{ $dGan['clase'] }}">{{ $dGan['txt'] }}</span> vs mes anterior</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="gf-stat">
            <div class="icono" style="background:#4B7A61;"><i class="bi bi-boxes"></i></div>
            <div class="etiqueta">Valor del inventario</div>
            <div class="valor">C$ {{ number_format($valorInventarioCosto, 2) }}</div>
            <div class="gf-kpi-sub">A precio de venta: C$ {{ number_format($valorInventarioVenta, 2) }}</div>
        </div>
    </div>
    @endif

    <div class="col-6 col-xl-3">
        <div class="gf-stat {{ $stockBajo > 0 ? 'gf-stat-clickable' : '' }}"
             @if($stockBajo > 0)
                 data-bs-toggle="modal" data-bs-target="#gfModalStockBajo"
                 role="button" tabindex="0" title="Ver productos con stock bajo"
             @endif
        >
            <div class="icono" style="background:#C98A3B;"><i class="bi bi-exclamation-circle"></i></div>
            <div class="etiqueta">Stock bajo</div>
            <div class="valor">{{ $stockBajo }}</div>
            <div class="gf-kpi-sub">de {{ $totalProductos }} productos activos</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <a href="{{ route('lotes.index', ['proximos_vencer' => 1]) }}" class="gf-stat-enlace">
            <div class="gf-stat gf-stat-clickable">
                <div class="icono" style="background:{{ $lotesVencidos->count() > 0 ? '#C0503D' : '#C98A3B' }};"><i class="bi bi-calendar-x"></i></div>
                <div class="etiqueta">Vencidos / por vencer (90 d)</div>
                <div class="valor">{{ $lotesVencidos->count() }} / {{ $lotesPorVencer->count() }}</div>
                <div class="gf-kpi-sub">@if($esAdmin) Valor en riesgo: C$ {{ number_format($valorEnRiesgo, 2) }} @else Revisa los lotes @endif</div>
            </div>
        </a>
    </div>

</div>

{{-- ===== Ventas del periodo ===== --}}
<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="gf-card h-100">
            <div class="gf-card-header gf-panel-titulo">
                <span>Ventas por día</span>
                <small>{{ $desde->format('d/m/Y') }} – {{ $hasta->format('d/m/Y') }}</small>
            </div>
            <div class="gf-resumen-periodo">
                <div><span>Total del periodo</span><strong>C$ {{ number_format($totalPeriodo, 2) }}</strong></div>
                <div><span>Tickets</span><strong>{{ $ticketsPeriodo }}</strong></div>
                <div><span>vs periodo anterior</span><strong class="gf-delta {{ $dPer['clase'] }}">{{ $dPer['txt'] }}</strong></div>
            </div>
            <div class="p-3" style="height:300px;"><canvas id="ventasDiaChart"></canvas></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="gf-card h-100">
            <div class="gf-card-header">Ventas por método de pago</div>
            <div class="p-3" style="height:360px;">
                @if($porMetodo->count() > 0)
                    <canvas id="metodoChart"></canvas>
                @else
                    <div class="gf-sin-datos">Sin ventas en este periodo</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="gf-card h-100">
            <div class="gf-card-header">Top 5 productos más vendidos</div>
            <div class="p-3" style="height:290px;">
                @if($topProductos->count() > 0)
                    <canvas id="topChart"></canvas>
                @else
                    <div class="gf-sin-datos">Sin ventas en este periodo</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="gf-card h-100">
            <div class="gf-card-header">Ventas por hora del día <small class="ms-1">(tickets)</small></div>
            <div class="p-3" style="height:290px;">
                @if($ticketsPeriodo > 0)
                    <canvas id="horaChart"></canvas>
                @else
                    <div class="gf-sin-datos">Sin ventas en este periodo</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="gf-card h-100">
            <div class="gf-card-header">Ventas por categoría</div>
            <div class="p-3" style="height:290px;">
                @if($ventasPorCategoria->count() > 0)
                    <canvas id="categoriaChart"></canvas>
                @else
                    <div class="gf-sin-datos">Sin ventas en este periodo</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="gf-card h-100">
            <div class="gf-card-header">Ventas por cajero</div>
            <div class="p-3" style="height:290px;">
                @if($ventasPorCajero->count() > 0)
                    <canvas id="cajeroChart"></canvas>
                @else
                    <div class="gf-sin-datos">Sin ventas en este periodo</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ===== Listas operativas ===== --}}
<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="gf-card h-100">
            <div class="gf-card-header gf-panel-titulo">
                <span><i class="bi bi-receipt"></i> Últimas ventas</span>
                <a href="{{ route('ventas.historial') }}" class="gf-link-ver">Ver todas</a>
            </div>
            <div class="p-3">
                @if($ultimasVentas->count() > 0)
                    <div class="table-responsive">
                        <table class="gf-table table-hover">
                            <thead>
                                <tr><th>Ticket</th><th>Fecha</th><th>Cliente</th><th>Pago</th><th class="text-end">Total</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach($ultimasVentas as $v)
                                    <tr>
                                        <td class="fw-semibold">
                                            <a href="{{ route('ventas.show', $v->id_venta) }}" class="gf-link-ver">{{ $v->numero_ticket }}</a>
                                            @if($v->estado === 'ANULADA') <span class="gf-badge gf-badge-peligro">Anulada</span> @endif
                                        </td>
                                        <td class="gf-page-subtitle">{{ \Carbon\Carbon::parse($v->fecha_venta)->format('d/m H:i') }}</td>
                                        <td>{{ $v->cliente_nombre ?: 'Cliente General' }}</td>
                                        <td><span class="gf-badge gf-badge-neutro">{{ $v->metodo_pago }}</span></td>
                                        <td class="text-end">C$ {{ number_format($v->total, 2) }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('ventas.ticket', $v->id_venta) }}" target="_blank" class="gf-btn-soft gf-btn-neutro" title="Imprimir ticket">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="gf-sin-datos" style="height:120px;">Aún no hay ventas registradas</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="gf-card h-100">
            <div class="gf-card-header gf-panel-titulo">
                <span><i class="bi bi-activity"></i> Actividad reciente de caja</span>
                <a href="{{ route('caja.movimientos') }}" class="gf-link-ver">Ver todo</a>
            </div>
            <div class="p-3">
                @forelse($movimientosCaja as $m)
                    <div class="gf-actividad">
                        <div class="gf-actividad-icono {{ $m->tipo_movimiento === 'INGRESO' ? 'ing' : 'egr' }}">
                            <i class="bi {{ $m->tipo_movimiento === 'INGRESO' ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }}"></i>
                        </div>
                        <div class="gf-actividad-info">
                            <div class="gf-actividad-txt">{{ $m->descripcion ?: ucfirst(strtolower($m->tipo_movimiento)) }}</div>
                            <div class="gf-actividad-meta">
                                {{ \Carbon\Carbon::parse($m->creado_en)->format('d/m H:i') }}
                                @if($m->usuario) · {{ $m->usuario }} @endif
                                @if($m->forma_pago) · {{ ucfirst(strtolower($m->forma_pago)) }} @endif
                            </div>
                        </div>
                        <div class="gf-actividad-monto {{ $m->tipo_movimiento === 'INGRESO' ? 'ing' : 'egr' }}">
                            {{ $m->tipo_movimiento === 'INGRESO' ? '+' : '−' }} C$ {{ number_format($m->monto, 2) }}
                        </div>
                    </div>
                @empty
                    <div class="gf-sin-datos" style="height:120px;">Sin movimientos de caja</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="gf-card h-100">
            <div class="gf-card-header gf-panel-titulo">
                <span><i class="bi bi-calendar-x"></i> Lotes vencidos o próximos a vencer</span>
                <a href="{{ route('lotes.index', ['proximos_vencer' => 1]) }}" class="gf-link-ver">Ver todos</a>
            </div>
            <div class="p-3">
                @php $lotesAlerta = $lotesVencidos->concat($lotesPorVencer)->take(8); @endphp
                @if($lotesAlerta->count() > 0)
                    <div class="table-responsive">
                        <table class="gf-table table-hover">
                            <thead>
                                <tr><th>Producto</th><th>Lote</th><th>Vence</th><th>Estado</th><th>Stock</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach($lotesAlerta as $lote)
                                    @php $diasLote = (int) now()->startOfDay()->diffInDays($lote->fecha_vencimiento->copy()->startOfDay(), false); @endphp
                                    <tr class="{{ $diasLote < 0 ? 'gf-row-danger' : ($diasLote <= 30 ? 'gf-row-warning' : '') }}">
                                        <td class="fw-semibold">{{ $lote->producto->nombre ?? 'Producto eliminado' }}</td>
                                        <td>{{ $lote->numero_lote ?? '—' }}</td>
                                        <td>{{ $lote->fecha_vencimiento->format('d/m/Y') }}</td>
                                        <td>
                                            @if($diasLote < 0)
                                                <span class="gf-badge gf-badge-peligro">Vencido</span>
                                            @else
                                                <span class="gf-badge gf-badge-alerta">en {{ $diasLote }} días</span>
                                            @endif
                                        </td>
                                        <td>{{ $lote->cantidad_actual }}</td>
                                        <td><a href="{{ route('lotes.show', $lote->id_lote) }}" class="gf-btn-soft gf-btn-neutro">Ver</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="gf-sin-datos" style="height:120px;"><span><i class="bi bi-check-circle"></i> Todo en orden: ningún lote vence en los próximos 90 días</span></div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="gf-card h-100">
            <div class="gf-card-header gf-panel-titulo">
                <span><i class="bi bi-hourglass-split"></i> Sin movimiento (60 días)</span>
                @if($esAdmin && $sinMovimientoTotal > 0)
                    <small>C$ {{ number_format($sinMovimientoTotal, 2) }} parados</small>
                @endif
            </div>
            <div class="p-3">
                @forelse($sinMovimiento as $p)
                    <div class="gf-actividad">
                        <div class="gf-actividad-info">
                            <div class="gf-actividad-txt">{{ $p->nombre }}</div>
                            <div class="gf-actividad-meta">{{ $p->stock_actual }} unidades en stock</div>
                        </div>
                        @if($esAdmin)
                            <div class="gf-actividad-monto">C$ {{ number_format($p->valor, 2) }}</div>
                        @endif
                    </div>
                @empty
                    <div class="gf-sin-datos" style="height:120px;"><span><i class="bi bi-check-circle"></i> Todos los productos con stock se han vendido en los últimos 60 días</span></div>
                @endforelse
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>

document.querySelectorAll('.gf-stat-clickable').forEach(function (tarjeta) {
    tarjeta.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); tarjeta.click(); }
    });
});

const VERDE_OSCURO = '#2F4F3E';
const VERDE_MEDIO  = '#4B7A61';
const PALETA = ['#2F4F3E', '#4B7A61', '#C98A3B', '#3F7D8C', '#6B5B95', '#8C3A2A'];
const fmt = new Intl.NumberFormat('es-NI', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const cordoba = (v) => 'C$ ' + fmt.format(v);

Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = 'rgba(36,36,32,0.65)';

function crear(id, config) {
    const el = document.getElementById(id);
    if (el) { new Chart(el, config); }
}

crear('ventasDiaChart', {
    type: 'line',
    data: {
        labels: @json($diasLabels),
        datasets: [{
            label: 'Ventas',
            data: @json($diasValores),
            fill: true,
            backgroundColor: 'rgba(75,122,97,0.14)',
            borderColor: VERDE_OSCURO,
            tension: 0.3,
            pointRadius: {{ count($diasLabels) > 31 ? 0 : 3 }},
            pointBackgroundColor: VERDE_OSCURO
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => cordoba(c.parsed.y) } } },
        scales: { y: { beginAtZero: true, ticks: { callback: (v) => 'C$ ' + v } } }
    }
});

crear('metodoChart', {
    type: 'doughnut',
    data: {
        labels: @json($porMetodo->pluck('metodo_pago')),
        datasets: [{ data: @json($porMetodo->pluck('total')->map(fn ($v) => round((float) $v, 2))), backgroundColor: PALETA, borderWidth: 2 }]
    },
    options: {
        maintainAspectRatio: false,
        cutout: '62%',
        plugins: {
            legend: { position: 'bottom' },
            tooltip: { callbacks: { label: (c) => ' ' + c.label + ': ' + cordoba(c.parsed) } }
        }
    }
});

crear('topChart', {
    type: 'bar',
    data: {
        labels: @json($topProductos->pluck('nombre')),
        datasets: [{ label: 'Unidades', data: @json($topProductos->pluck('unidades')), backgroundColor: VERDE_MEDIO, borderRadius: 6 }]
    },
    options: {
        indexAxis: 'y',
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});

crear('horaChart', {
    type: 'bar',
    data: {
        labels: @json($horasLabels),
        datasets: [{ label: 'Tickets', data: @json($horasValores), backgroundColor: '#C98A3B', borderRadius: 5 }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});

crear('categoriaChart', {
    type: 'doughnut',
    data: {
        labels: @json($ventasPorCategoria->pluck('nombre')),
        datasets: [{ data: @json($ventasPorCategoria->pluck('total')->map(fn ($v) => round((float) $v, 2))), backgroundColor: PALETA, borderWidth: 2 }]
    },
    options: {
        maintainAspectRatio: false,
        cutout: '55%',
        plugins: {
            legend: { position: 'right' },
            tooltip: { callbacks: { label: (c) => ' ' + c.label + ': ' + cordoba(c.parsed) } }
        }
    }
});

crear('cajeroChart', {
    type: 'bar',
    data: {
        labels: @json($ventasPorCajero->pluck('nombre')),
        datasets: [{ label: 'Ventas', data: @json($ventasPorCajero->pluck('total')->map(fn ($v) => round((float) $v, 2))), backgroundColor: VERDE_OSCURO, borderRadius: 6 }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => cordoba(c.parsed.y) } } },
        scales: { y: { beginAtZero: true, ticks: { callback: (v) => 'C$ ' + v } } }
    }
});

</script>
@endpush

{{-- Detalle de los productos con stock bajo (se abre desde la tarjeta "Stock Bajo") --}}
@if($stockBajo > 0)
    <div class="modal fade" id="gfModalStockBajo" tabindex="-1" aria-labelledby="gfModalStockBajoTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content gf-modal-stock">

                <div class="gf-modal-stock-header">
                    <div class="gf-modal-stock-icono"><i class="bi bi-exclamation-triangle"></i></div>
                    <div>
                        <h5 class="gf-modal-stock-titulo" id="gfModalStockBajoTitulo">Productos con stock bajo</h5>
                        <p class="gf-modal-stock-sub">En o por debajo de su stock mínimo · los más críticos primero</p>
                    </div>
                    <button type="button" class="gf-modal-stock-cerrar" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="gf-modal-stock-resumen">
                    <span class="gf-badge gf-badge-peligro">{{ $productosStockBajo->where('stock_actual', '<=', 0)->count() }} agotado(s)</span>
                    <span class="gf-badge gf-badge-alerta">{{ $productosStockBajo->where('stock_actual', '>', 0)->count() }} por debajo del mínimo</span>
                </div>

                <div class="gf-modal-stock-lista">
                    @foreach($productosStockBajo as $producto)
                        <div class="gf-modal-stock-fila">
                            <div class="gf-modal-stock-info">
                                <div class="gf-modal-stock-nombre">{{ $producto->nombre }}</div>
                                <div class="gf-modal-stock-meta">
                                    {{ $producto->categoria ?: 'Sin categoría' }}
                                    @if($producto->presentacion) · {{ $producto->presentacion }} @endif
                                    @if($producto->concentracion) · {{ $producto->concentracion }} @endif
                                </div>
                            </div>
                            <div class="gf-modal-stock-cifras">
                                @if($producto->stock_actual <= 0)
                                    <span class="gf-badge gf-badge-peligro">Agotado</span>
                                @else
                                    <span class="gf-badge gf-badge-alerta">{{ $producto->stock_actual }} en stock</span>
                                @endif
                                <small>Mínimo: {{ $producto->stock_minimo }} · Faltan {{ max($producto->stock_minimo - $producto->stock_actual, 0) }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="gf-modal-stock-pie">
                    <a href="{{ route('productos.index') }}" class="gf-btn-soft gf-btn-neutro">
                        <i class="bi bi-box-seam"></i> Ir a Productos
                    </a>
                </div>

            </div>
        </div>
    </div>
@endif

@endsection
