<p class="gf-page-subtitle mb-2">{{ $clientes->total() }} cliente(s) encontrado(s)</p>

<div class="gf-card">
    <div class="p-3">
        <div class="table-responsive">
            <table class="gf-table table-hover">
                <thead>
                    <tr>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Cédula</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($clientes as $cliente)
                        <tr>
                            <td class="fw-semibold">{{ $cliente->nombres }}</td>
                            <td>{{ $cliente->apellidos }}</td>
                            <td>{{ $cliente->cedula }}</td>
                            <td>{{ $cliente->telefono }}</td>
                            <td>{{ $cliente->email }}</td>

                            <td>
                                <span class="gf-badge {{ $cliente->estado ? 'gf-badge-exito' : 'gf-badge-apagado' }}">
                                    {{ $cliente->estado ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('clientes.edit', $cliente->id_cliente) }}" class="gf-btn-soft gf-btn-neutro">
                                    Editar
                                </a>

                                <form action="{{ route('clientes.destroy', $cliente->id_cliente) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="gf-btn-soft gf-btn-peligro" onclick="return confirm('¿Eliminar cliente?')">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No se encontraron clientes con esa búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-2 pt-2">
            {{ $clientes->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
