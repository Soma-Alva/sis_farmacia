@if($filas->count() > 0)
    <div class="table-responsive" style="max-height:320px; overflow-y:auto;">
        <table class="gf-table table-hover">
            <thead><tr><th>Fecha</th><th>Detalle</th><th>Pago</th><th class="text-end">Monto</th></tr></thead>
            <tbody>
                @foreach($filas as $f)
                    <tr>
                        <td class="gf-page-subtitle">{{ \Carbon\Carbon::parse($f->fecha)->format('d/m H:i') }}</td>
                        <td>
                            {{ $f->descripcion ?: ($f->motivo ?: '—') }}
                            @if($f->descripcion && $f->motivo)<div class="gf-page-subtitle">{{ $f->motivo }}</div>@endif
                        </td>
                        <td>{{ $f->forma_pago ? \App\Services\ReporteService::etiquetaMetodo($f->forma_pago) : '—' }}</td>
                        <td class="text-end">C$ {{ number_format($f->monto, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="gf-tot-fila"><td colspan="3">Total</td><td class="text-end">C$ {{ number_format($total, 2) }}</td></tr>
            </tbody>
        </table>
    </div>
@else
    <p class="gf-page-subtitle mb-0">{{ $vacio }}</p>
@endif
