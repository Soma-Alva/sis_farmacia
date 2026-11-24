@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="gf-page-title mb-0">Kardex de inventario</h4>
        <p class="gf-page-subtitle mb-0">Resumen por producto · {{ $desde->format('d/m/Y') }} – {{ $hasta->format('d/m/Y') }}. Haz clic en un producto para ver su kardex completo.</p>
    </div>
    <a href="{{ route('reportes.index') }}" class="gf-btn-soft gf-btn-apagado"><i class="bi bi-arrow-left"></i> Reportes</a>
</div>

@include('reportes._filtro', ['ruta' => 'reportes.kardex', 'excel' => 'reportes.kardex.excel'])

<div class="gf-filtros mb-3">
    <form method="GET" action="{{ route('reportes.kardex') }}" class="row g-2">
        <input type="hidden" name="periodo" value="rango">
        <input type="hidden" name="desde" value="{{ $desde->toDateString() }}">
        <input type="hidden" name="hasta" value="{{ $hasta->toDateString() }}">
        <div class="col-md-6"><input type="text" name="q" class="form-control" placeholder="Buscar producto o código de barras" value="{{ $q }}"></div>
        <div class="col-md-3 d-flex align-items-center">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="solo_mov" value="1" id="soloMov" @checked($soloMov)>
                <label class="form-check-label" for="soloMov">Solo con movimientos</label>
            </div>
        </div>
        <div class="col-md-2"><button class="gf-btn-soft gf-btn-neutro w-100" style="padding:9px 0;">Buscar</button></div>
        <div class="col-md-1"><a href="{{ route('reportes.kardex') }}" class="gf-btn-soft gf-btn-apagado d-block text-center" style="padding:9px 0;"><i class="bi bi-x-lg"></i></a></div>
    </form>
</div>

<div class="gf-card">
    <div class="gf-card-header">Productos <span class="gf-badge gf-badge-neutro ms-1">{{ $resumen->total() }}</span></div>
    <div class="p-3">
        @if($resumen->total() > 0)
            <div class="table-responsive">
                <table class="gf-table table-hover">
                    <thead>
                        <tr><th>Producto</th><th>Código</th><th class="text-end">Saldo inicial</th><th class="text-end">Entradas</th><th class="text-end">Salidas</th><th class="text-end">Saldo final</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($resumen as $r)
                            <tr>
                                <td class="fw-semibold">
                                    <a href="{{ route('reportes.kardex', ['producto' => $r->id_producto, 'periodo' => 'rango', 'desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString()]) }}" style="color:var(--verde-medio);text-decoration:none;">{{ $r->nombre }}</a>
                                </td>
                                <td class="gf-page-subtitle">{{ $r->codigo_barra ?: '—' }}</td>
                                <td class="text-end">{{ number_format($r->saldo_inicial) }}</td>
                                <td class="text-end" style="color:#2F7A4B;">+ {{ number_format($r->entradas) }}</td>
                                <td class="text-end" style="color:#B4452F;">− {{ number_format($r->salidas) }}</td>
                                <td class="text-end fw-semibold">{{ number_format($r->saldo_final) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('reportes.kardex', ['producto' => $r->id_producto, 'periodo' => 'rango', 'desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString()]) }}" class="gf-btn-soft gf-btn-neutro">Ver kardex</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-2 pt-2">{{ $resumen->links('pagination::bootstrap-5') }}</div>
        @else
            <p class="gf-page-subtitle mb-0">No hay productos con movimientos en este periodo. Quita "Solo con movimientos" para ver todos.</p>
        @endif
    </div>
</div>

@endsection
