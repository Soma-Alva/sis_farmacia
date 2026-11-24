@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="gf-page-title mb-0">Ventas detalladas</h4>
        <p class="gf-page-subtitle mb-0">{{ $desde->format('d/m/Y') }} – {{ $hasta->format('d/m/Y') }}</p>
    </div>
    <a href="{{ route('reportes.index') }}" class="gf-btn-soft gf-btn-apagado"><i class="bi bi-arrow-left"></i> Reportes</a>
</div>

@include('reportes._filtro', ['ruta' => 'reportes.ventas', 'excel' => 'reportes.ventas.excel'])

<div class="gf-filtros mb-3">
    <form method="GET" action="{{ route('reportes.ventas') }}" class="row g-2">
        <input type="hidden" name="periodo" value="rango">
        <input type="hidden" name="desde" value="{{ $desde->toDateString() }}">
        <input type="hidden" name="hasta" value="{{ $hasta->toDateString() }}">
        <div class="col-md-3">
            <select name="estado" class="form-select">
                <option value="COMPLETADA" @selected($estado === 'COMPLETADA')>Completadas</option>
                <option value="ANULADA" @selected($estado === 'ANULADA')>Anuladas</option>
                <option value="PENDIENTE" @selected($estado === 'PENDIENTE')>Pendientes</option>
                <option value="TODAS" @selected($estado === 'TODAS')>Todas</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="metodo" class="form-select">
                <option value="">Todos los métodos</option>
                @foreach(\App\Services\ReporteService::METODOS as $m)
                    <option value="{{ $m }}" @selected($metodo === $m)>{{ \App\Services\ReporteService::etiquetaMetodo($m) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="cajero" class="form-select">
                <option value="">Todos los cajeros</option>
                @foreach($cajeros as $c)
                    <option value="{{ $c->id_usuario }}" @selected((string) $cajero === (string) $c->id_usuario)>{{ $c->nombre_completo }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="gf-btn-soft gf-btn-neutro w-100" style="padding:9px 0;">Filtrar</button>
        </div>
        <div class="col-md-1">
            <a href="{{ route('reportes.ventas') }}" class="gf-btn-soft gf-btn-apagado d-block text-center" style="padding:9px 0;"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3"><div class="gf-rep-kpi"><div class="et">Ventas</div><div class="val">{{ $tot->tickets }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="gf-rep-kpi"><div class="et">Subtotal (sin IVA)</div><div class="val">C$ {{ number_format($tot->subtotal, 2) }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="gf-rep-kpi"><div class="et">IVA</div><div class="val">C$ {{ number_format($tot->igv, 2) }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="gf-rep-kpi"><div class="et">Total</div><div class="val pos">C$ {{ number_format($tot->total, 2) }}</div></div></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="gf-card h-100">
            <div class="gf-card-header">Por método de pago</div>
            <div class="p-3">
                @forelse($porMetodo as $m)
                    <div class="d-flex justify-content-between py-1"><span>{{ \App\Services\ReporteService::etiquetaMetodo($m->nombre) }} <small class="gf-page-subtitle">({{ $m->tickets }})</small></span><strong>C$ {{ number_format($m->total, 2) }}</strong></div>
                @empty
                    <p class="gf-page-subtitle mb-0">Sin ventas con estos filtros</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="gf-card h-100">
            <div class="gf-card-header">Por cajero</div>
            <div class="p-3">
                @forelse($porCajero as $c)
                    <div class="d-flex justify-content-between py-1"><span>{{ $c->nombre }} <small class="gf-page-subtitle">({{ $c->tickets }})</small></span><strong>C$ {{ number_format($c->total, 2) }}</strong></div>
                @empty
                    <p class="gf-page-subtitle mb-0">Sin ventas con estos filtros</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="gf-card">
    <div class="gf-card-header">Detalle de ventas</div>
    <div class="p-3">
        @if($ventas->total() > 0)
            <div class="table-responsive">
                <table class="gf-table table-hover">
                    <thead>
                        <tr><th>Ticket</th><th>Fecha</th><th>Cliente</th><th>Cajero</th><th>Pago</th><th class="text-end">Total</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        @foreach($ventas as $v)
                            <tr>
                                <td class="fw-semibold"><a href="{{ route('ventas.show', $v->id_venta) }}" style="color:var(--verde-medio);text-decoration:none;">{{ $v->numero_ticket }}</a></td>
                                <td class="gf-page-subtitle">{{ \Carbon\Carbon::parse($v->fecha_venta)->format('d/m/Y H:i') }}</td>
                                <td>{{ $v->cliente_nombre ?: 'Cliente General' }}</td>
                                <td>{{ $v->cajero ?: '—' }}</td>
                                <td><span class="gf-badge gf-badge-neutro">{{ $v->metodo_pago }}</span></td>
                                <td class="text-end">C$ {{ number_format($v->total, 2) }}</td>
                                <td>
                                    @if($v->estado === 'ANULADA') <span class="gf-badge gf-badge-peligro">Anulada</span>
                                    @elseif($v->estado === 'PENDIENTE') <span class="gf-badge gf-badge-alerta">Pendiente</span>
                                    @else <span class="gf-badge gf-badge-neutro">Completada</span> @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-2 pt-2">{{ $ventas->links('pagination::bootstrap-5') }}</div>
        @else
            <p class="gf-page-subtitle mb-0">No hay ventas con estos filtros</p>
        @endif
    </div>
</div>

@endsection
