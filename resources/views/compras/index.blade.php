@extends('layouts.app')

@section('content')

<div class="container-fluid">


<div class="d-flex justify-content-between align-items-center mb-3">

    <h3>Compras</h3>

    <a href="{{ route('compras.create') }}"
       class="btn btn-primary">

        <i class="fas fa-plus"></i>
        Nueva Compra

    </a>

</div>


    <div class="card shadow">

        <div class="card-body">

            <table class="table table-hover">

                <thead class="table-dark">

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

                        <td>{{ $c->numero_factura }}</td>

                        <td>
                            {{ $c->proveedor->nombre ?? 'N/A' }}
                        </td>

                        <td>{{ $c->fecha_compra }}</td>

                        <td>
                            C$ {{ number_format($c->total,2) }}
                        </td>

                        <td>{{ $c->estado }}</td>

                        <td>

                            @if($c->estado == 'PENDIENTE')

                              <form action="{{ route('compras.aprobar', $c->id_compra) }}" method="POST">

                                    @csrf

                                    <button type="submit" class="btn btn-success btn-sm">
                                        Aprobar
                                    </button>

                                </form>

                            @endif

                            </td>
                    </tr>

                    @empty

                    <tr>

                        <td colspan="5">
                            No existen compras registradas
                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection