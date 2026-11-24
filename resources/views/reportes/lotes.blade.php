@extends('layouts.app')

@section('content')

<style>
    .gf-rep-barra{ display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
    .gf-btn-excel{ display:inline-flex; align-items:center; gap:8px; background:#1E7B45; color:#fff; border-radius:12px; padding:9px 18px; font-size:13.5px; font-weight:600; text-decoration:none; }
    .gf-btn-excel:hover{ background:#176437; color:#fff; }
    .gf-rep-kpi{ background:#fff; border-radius:18px; padding:18px 20px; box-shadow:0 2px 10px rgba(47,79,62,0.06); height:100%; }
    .gf-rep-kpi .et{ font-size:12.5px; color:rgba(36,36,32,0.55); }
    .gf-rep-kpi .val{ font-size:22px; font-weight:700; color:var(--carbon); }
    .gf-rep-kpi .sub{ font-size:12px; color:rgba(36,36,32,0.55); margin-top:2px; }
    .gf-tot-fila td{ background:var(--verde-pastel); font-weight:700; color:var(--verde-oscuro); }
</style>

@php
    $colores = ['Vencido' => '#B4452F', '0-30 días' => '#C98A3B', '31-60 días' => '#D9B04A', '61-90 días' => '#4B7A61', 'Más de 90 días' => '#2F4F3E'];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="gf-page-title mb-0">Lotes vencidos y por vencer</h4>
        <p class="gf-page-subtitle mb-0">Lotes con stock · fecha de corte {{ now()->format('d/m/Y') }}</p>
    </div>
    <a href="{{ route('reportes.index') }}" class="gf-btn-soft gf-btn-apagado"><i class="bi bi-arrow-left"></i> Reportes</a>
</div>

<div class="gf-rep-barra">
    <div class="gf-filtros mb-0" style="flex:1 1 auto;">
        <form method="GET" action="{{ route('reportes.lotes') }}" class="row g-2">
            <div class="col-md-5">
                <select name="ventana" class="form-select">
                    @foreach(\App\Services\ReporteService::VENTANAS as $k => $t)
                        <option value="{{ $k }}" @selected($ventana === (string) $k)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select name="categoria" class="form-select">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $c)
                        <option value="{{ $c->id_categoria }}" @selected((string) $categoria === (string) $c->id_categoria)>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><button class="gf-btn-soft gf-btn-neutro w-100" style="padding:9px 0;">Filtrar</button></div>
            <div class="col-md-1"><a href="{{ route('reportes.lotes') }}" class="gf-btn-soft gf-btn-apagado d-block text-center" style="padding:9px 0;"><i class="bi bi-x-lg"></i></a></div>
        </form>
    </div>
    <a href="{{ route('reportes.lotes.excel', request()->query()) }}" class="gf-btn-excel"><i class="bi bi-file-earmark-excel"></i> Exportar Excel</a>
</div>

<div class="row g-3 mb-3">
    @foreach($tramos as $t)
        <div class="col-6 col-lg">
            <div class="gf-rep-kpi" style="border-left:5px solid {{ $colores[$t->tramo] }};">
                <div class="et">{{ $t->tramo === 'Vencido' ? 'Vencidos' : 'Vencen en ' . $t->tramo }}</div>
                <div class="val">{{ $t->lotes }} <small style="font-size:13px;font-weight:500;">lote(s)</small></div>
                <div class="sub">{{ $t->unidades }} uds. · C$ {{ number_format($t->costo, 2) }}</div>
            </div>
        </div>
    @endforeach
    <div class="col-6 col-lg">
        <div class="gf-rep-kpi" style="background:var(--verde-oscuro);">
            <div class="et" style="color:rgba(255,255,255,0.7);">Valor total en riesgo</div>
            <div class="val" style="color:#fff;">C$ {{ number_format($totalCosto, 2) }}</div>
            <div class="sub" style="color:rgba(255,255,255,0.7);">a precio de venta: C$ {{ number_format($totalVenta, 2) }}</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-9">
        <div class="gf-card">
            <div class="gf-card-header">Detalle de lotes <span class="gf-badge gf-badge-neutro ms-1">{{ $lotes->total() }}</span></div>
            <div class="p-3">
                @if($lotes->total() > 0)
                    <div class="table-responsive">
                        <table class="gf-table table-hover">
                            <thead>
                                <tr><th>Producto</th><th>Lote</th><th>Vence</th><th>Estado</th><th class="text-end">Cant.</th><th class="text-end">Valor a costo</th><th>Proveedor</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach($lotes as $f)
                                    <tr class="{{ $f->dias < 0 ? 'gf-row-danger' : ($f->dias <= 30 ? 'gf-row-warning' : '') }}">
                                        <td class="fw-semibold">{{ $f->producto }}<div class="gf-page-subtitle">{{ $f->categoria ?: 'Sin categoría' }}</div></td>
                                        <td>{{ $f->numero_lote ?: '—' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($f->fecha_vencimiento)->format('d/m/Y') }}</td>
                                        <td>
                                            @if($f->dias < 0)
                                                <span class="gf-badge gf-badge-peligro">Vencido hace {{ abs($f->dias) }} d</span>
                                            @elseif($f->dias === 0)
                                                <span class="gf-badge gf-badge-peligro">Vence hoy</span>
                                            @else
                                                <span class="gf-badge gf-badge-alerta">en {{ $f->dias }} días</span>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ $f->cantidad_actual }}</td>
                                        <td class="text-end">C$ {{ number_format($f->valor_costo, 2) }}</td>
                                        <td>{{ $f->proveedor ?: '—' }}</td>
                                        <td><a href="{{ route('lotes.show', $f->id_lote) }}" class="gf-btn-soft gf-btn-neutro">Ver</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-2 pt-2">{{ $lotes->links('pagination::bootstrap-5') }}</div>
                @else
                    <p class="gf-page-subtitle mb-0"><i class="bi bi-check-circle"></i> Todo en orden: no hay lotes con stock en este filtro.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="gf-card">
            <div class="gf-card-header">Por categoría</div>
            <div class="p-3">
                @forelse($porCategoria as $c)
                    <div class="d-flex justify-content-between py-1">
                        <span>{{ $c->nombre }} <small class="gf-page-subtitle">({{ $c->lotes }})</small></span>
                        <strong>C$ {{ number_format($c->costo, 2) }}</strong>
                    </div>
                @empty
                    <p class="gf-page-subtitle mb-0">Sin datos</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<p class="gf-page-subtitle mt-3">
    Solo se incluyen lotes con stock disponible y fecha de vencimiento. El valor a costo es cantidad × costo unitario del lote: es lo que perderías si el lote se desecha.
    Para darlos de baja usa la pantalla de Lotes.
</p>

@endsection
