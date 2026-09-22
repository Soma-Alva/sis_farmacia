@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">
            <h4>Nueva Compra</h4>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="card-body">

         <form action="{{ route('compras.store') }}" method="POST">

                @csrf

                <div class="row">

                    <div class="col-md-4">

                        <label class="form-label">
                            Número Factura
                        </label>

                        <input
                            type="text"
                            name="numero_factura"
                            class="form-control"
                            required>

                    </div>

                    <div class="col-md-4">

                        <label class="form-label">
                            Proveedor
                        </label>

                        <select
                            name="id_proveedor"
                            class="form-control"
                            required>

                            <option value="">
                                Seleccione
                            </option>

                            @foreach($proveedores as $p)

                                <option value="{{ $p->id_proveedor }}">
                                    {{ $p->nombre }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-4">

                        <label class="form-label">
                            Fecha
                        </label>

                        <input
                            type="date"
                            name="fecha_compra"
                            value="{{ date('Y-m-d') }}"
                            class="form-control"
                            required>

                    </div>

                </div>

                <hr>

                <h5>Detalle de Compra</h5>

                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>

                            <td>

                                <select
                                    name="id_producto"
                                    class="form-control">

                                    @foreach($productos as $prod)

                                        <option
                                            value="{{ $prod->id_producto }}">

                                            {{ $prod->nombre }}

                                        </option>

                                    @endforeach

                                </select>

                            </td>

                            <td>

                                <input
                                    type="number"
                                    name="cantidad"
                                    class="form-control"
                                    value="1"
                                    min="1">

                            </td>

                            <td>

                                <input
                                    type="number"
                                    name="precio"
                                    step="0.01"
                                    class="form-control"
                                    value="0">

                            </td>

                        </tr>

                    </tbody>

                </table>

                <button
                    type="submit"
                    class="btn btn-success">

                    Guardar Compra

                </button>

                <button
                    type="reset"
                    class="btn btn-secondary">

                    Limpiar
                </button>
                
                <button
                    type="button"
                    class="btn btn-danger"
                    onclick="window.location='{{ route('compras.index') }}'">

                    Cancelar
                
                </button>
            </form>

        </div>

    </div>

</div>

@endsection