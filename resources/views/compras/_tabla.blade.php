<p class="gf-page-subtitle mb-2">{{ $compras->total() }} compra(s) encontrada(s)</p>

<div class="gf-card">
    <div class="p-3">
        <div class="table-responsive">
            <table class="gf-table table-hover">
                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>Proveedor</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($compras as $c)
                    <tr>
                        <td class="fw-semibold">{{ $c->numero_factura }}</td>
                        <td>{{ $c->proveedor->nombre ?? 'N/A' }}</td>
                        <td>{{ $c->fecha_compra }}</td>
                        <td>C$ {{ number_format($c->total,2) }}</td>
                        <td>
                            <span class="gf-badge {{ $c->estado == 'APROBADA' ? 'gf-badge-exito' : ($c->estado == 'CANCELADA' ? 'gf-badge-peligro' : 'gf-badge-alerta') }}">
                                {{ $c->estado }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('compras.show', $c->id_compra) }}" class="gf-btn-soft gf-btn-neutro">
                                Ver
                            </a>

                            @if($c->estado == 'PENDIENTE')
                                <form action="{{ route('compras.aprobar', $c->id_compra) }}" method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('¿Aprobar esta compra? Esto creará los lotes y sumará el stock.');">
                                    @csrf
                                    <button type="submit" class="gf-btn-soft gf-btn-exito">
                                        Aprobar
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No se encontraron compras con esa búsqueda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-2 pt-2">
            {{ $compras->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
