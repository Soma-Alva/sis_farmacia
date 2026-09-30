@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <h3 class="mb-3">Lotes</h3>

    {{-- Tarjetas resumen --}}
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Total de lotes</div>
                    <div class="fs-3 fw-bold">{{ $resumen['total_lotes'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Con stock disponible</div>
                    <div class="fs-3 fw-bold text-success">{{ $resumen['con_stock'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Próximos a vencer (90 días)</div>
                    <div class="fs-3 fw-bold text-warning">{{ $resumen['proximos_vencer'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Vencidos con stock</div>
                    <div class="fs-3 fw-bold text-danger">{{ $resumen['vencidos'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Buscador y filtros --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body">
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
                    <button type="submit" class="btn btn-primary w-100">
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
    </div>

    {{-- Tabla de lotes --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
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
                                    ? now()->diffInDays($lote->fecha_vencimiento, false)
                                    : null;

                                $claseFila = '';
                                if ($lote->cantidad_actual > 0 && $diasParaVencer !== null) {
                                    if ($diasParaVencer < 0) {
                                        $claseFila = 'table-danger';
                                    } elseif ($diasParaVencer <= 30) {
                                        $claseFila = 'table-warning';
                                    }
                                }
                            @endphp
                            <tr class="{{ $claseFila }}">
                                <td>
                                    {{ $lote->producto->nombre ?? 'Producto eliminado' }}
                                    <div class="text-muted small">{{ $lote->producto->codigo_barra ?? '' }}</div>
                                </td>
                                <td>{{ $lote->numero_lote ?? '—' }}</td>
                                <td>
                                    @if($lote->fecha_vencimiento)
                                        {{ $lote->fecha_vencimiento->format('d/m/Y') }}
                                        @if($lote->cantidad_actual > 0 && $diasParaVencer !== null)
                                            <div class="small">
                                                @if($diasParaVencer < 0)
                                                    <span class="text-danger fw-bold">Vencido</span>
                                                @else
                                                    <span class="text-muted">en {{ $diasParaVencer }} días</span>
                                                @endif
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted">Sin registrar</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $lote->cantidad_actual > 0 ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $lote->cantidad_actual }} / {{ $lote->cantidad_inicial }}
                                    </span>
                                </td>
                                <td>C$ {{ number_format($lote->costo_unitario, 2) }}</td>
                                <td>
                                    <span class="badge
                                        {{ $lote->estado == 'ACTIVO' ? 'bg-success' :
                                           ($lote->estado == 'AGOTADO' ? 'bg-secondary' :
                                           ($lote->estado == 'VENCIDO' ? 'bg-danger' : 'bg-dark')) }}">
                                        {{ $lote->estado }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('lotes.show', $lote->id_lote) }}"
                                       class="btn btn-sm btn-outline-secondary">
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

            {{ $lotes->links('pagination::bootstrap-5') }}
        </div>
    </div>

</div>

@endsection
