@extends('layouts.app')

@section('content')

<div class="mb-3">
    <h4 class="gf-page-title">Lotes</h4>
    <p class="gf-page-subtitle">Inventario por lote, con trazabilidad de vencimiento</p>
</div>

{{-- Tarjetas resumen --}}
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="gf-card p-3 text-center">
            <div class="gf-page-subtitle mb-1">Total de lotes</div>
            <div class="fs-3 fw-bold" style="color:var(--verde-oscuro);">{{ $resumen['total_lotes'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="gf-card p-3 text-center">
            <div class="gf-page-subtitle mb-1">Con stock disponible</div>
            <div class="fs-3 fw-bold" style="color:#2F6B45;">{{ $resumen['con_stock'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="gf-card p-3 text-center">
            <div class="gf-page-subtitle mb-1">Próximos a vencer (90 días)</div>
            <div class="fs-3 fw-bold" style="color:#8A6A1F;">{{ $resumen['proximos_vencer'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="gf-card p-3 text-center">
            <div class="gf-page-subtitle mb-1">Vencidos con stock</div>
            <div class="fs-3 fw-bold" style="color:#8C3A2A;">{{ $resumen['vencidos'] }}</div>
        </div>
    </div>
</div>

{{-- Buscador y filtros --}}
<div class="gf-filtros">
    <form method="GET" action="{{ route('lotes.index') }}" class="row g-2 align-items-end">

        <div class="col-md-4">
            <label class="form-label">Buscar</label>
            <input type="text" name="buscar" class="form-control"
                   placeholder="Producto, código de barras o N° de lote..."
                   value="{{ request('buscar') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
                <option value="">Todos</option>
                @foreach(['ACTIVO', 'AGOTADO', 'VENCIDO', 'BLOQUEADO'] as $estado)
                    <option value="{{ $estado }}" {{ request('estado') == $estado ? 'selected' : '' }}>
                        {{ $estado }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 form-check mt-4">
            <input type="checkbox" class="form-check-input" id="proximos_vencer"
                   name="proximos_vencer" value="1" {{ request('proximos_vencer') ? 'checked' : '' }}>
            <label class="form-check-label" for="proximos_vencer">
                Solo próximos a vencer
            </label>
        </div>

        <div class="col-md-2">
            <button type="submit" class="gf-btn-primario w-100 justify-content-center">
                <i class="bi bi-search"></i> Buscar
            </button>
        </div>

    </form>

    @if(request()->anyFilled(['buscar', 'estado', 'proximos_vencer']))
        <a href="{{ route('lotes.index') }}" class="d-inline-block mt-2 small">
            Limpiar filtros
        </a>
    @endif
</div>

{{-- Tabla de lotes --}}
<div class="gf-card">
    <div class="p-3">
        <div class="table-responsive">
            <table class="gf-table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>N° de Lote</th>
                        <th>Vencimiento</th>
                        <th>Stock actual</th>
                        <th>Costo unit.</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lotes as $lote)
                        @php
                            $diasParaVencer = $lote->fecha_vencimiento
                                ? (int) now()->diffInDays($lote->fecha_vencimiento, false)
                                : null;

                            $claseFila = '';
                            if ($lote->cantidad_actual > 0 && $diasParaVencer !== null) {
                                if ($diasParaVencer < 0) {
                                    $claseFila = 'gf-row-danger';
                                } elseif ($diasParaVencer <= 30) {
                                    $claseFila = 'gf-row-warning';
                                }
                            }
                        @endphp
                        <tr class="{{ $claseFila }}">
                            <td>
                                {{ $lote->producto->nombre ?? 'Producto eliminado' }}
                                <span class="gf-subtext">{{ $lote->producto->codigo_barra ?? '' }}</span>
                            </td>
                            <td>{{ $lote->numero_lote ?? '—' }}</td>
                            <td>
                                @if($lote->fecha_vencimiento)
                                    {{ $lote->fecha_vencimiento->format('d/m/Y') }}
                                    @if($lote->cantidad_actual > 0 && $diasParaVencer !== null)
                                        @if($diasParaVencer < 0)
                                            <span class="gf-subtext" style="color:#8C3A2A;font-weight:600;">Vencido</span>
                                        @else
                                            <span class="gf-subtext">en {{ $diasParaVencer }} días</span>
                                        @endif
                                    @endif
                                @else
                                    <span class="gf-subtext">Sin registrar</span>
                                @endif
                            </td>
                            <td>
                                <span class="gf-badge {{ $lote->cantidad_actual > 0 ? 'gf-badge-exito' : 'gf-badge-apagado' }}">
                                    {{ $lote->cantidad_actual }} / {{ $lote->cantidad_inicial }}
                                </span>
                            </td>
                            <td>C$ {{ number_format($lote->costo_unitario, 2) }}</td>
                            <td>
                                <span class="gf-badge
                                    {{ $lote->estado == 'ACTIVO' ? 'gf-badge-exito' :
                                       ($lote->estado == 'AGOTADO' ? 'gf-badge-apagado' :
                                       ($lote->estado == 'VENCIDO' ? 'gf-badge-peligro' : 'gf-badge-neutro')) }}">
                                    {{ $lote->estado }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('lotes.show', $lote->id_lote) }}" class="gf-btn-soft gf-btn-neutro">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No se encontraron lotes con esos filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-2 pt-2">
            {{ $lotes->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection
