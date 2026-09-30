@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">
            <h4>Nueva Compra</h4>
            @if ($errors->any())
                <div class="alert alert-danger mt-2 mb-0">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="card-body">

            <form action="{{ route('compras.store') }}" method="POST" id="formCompra">
                @csrf

                <div class="row">

                    <div class="col-md-4">
                        <label class="form-label">Número Factura</label>
                        <input type="text" name="numero_factura" class="form-control"
                               value="{{ old('numero_factura') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Proveedor</label>
                        <select name="id_proveedor" class="form-control" required>
                            <option value="">Seleccione</option>
                            @foreach($proveedores as $p)
                                <option value="{{ $p->id_proveedor }}"
                                    {{ old('id_proveedor') == $p->id_proveedor ? 'selected' : '' }}>
                                    {{ $p->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha_compra" value="{{ old('fecha_compra', date('Y-m-d')) }}"
                               class="form-control" required>
                    </div>

                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Detalle de Compra</h5>
                    <button type="button" class="btn btn-sm btn-success" id="btnAgregarFila">
                        <i class="bi bi-plus-lg"></i> Agregar producto
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="tablaDetalle">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 220px">Producto</th>
                                <th style="width: 100px">Cantidad</th>
                                <th style="width: 130px">Precio unit.</th>
                                <th style="width: 150px">N° de Lote</th>
                                <th style="width: 160px">Vencimiento</th>
                                <th style="width: 110px">Subtotal</th>
                                <th style="width: 50px"></th>
                            </tr>
                        </thead>
                        <tbody id="detalleBody">
                            {{-- las filas se generan por JS a partir de #filaTemplate --}}
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">Total:</th>
                                <th id="totalCompra">C$ 0.00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <button type="submit" class="btn btn-success">Guardar Compra</button>
                <button type="reset" class="btn btn-secondary">Limpiar</button>
                <button type="button" class="btn btn-danger"
                        onclick="window.location='{{ route('compras.index') }}'">Cancelar</button>

            </form>

        </div>

    </div>

</div>

{{-- Plantilla de una fila (no se envía, solo se clona por JS) --}}
<template id="filaTemplate">
    <tr>
        <td>
            <select name="id_producto[]" class="form-control select-producto" required>
                <option value="">Seleccione</option>
                @foreach($productos as $prod)
                    <option value="{{ $prod->id_producto }}" data-precio="{{ $prod->precio_compra }}">
                        {{ $prod->nombre }} @if($prod->presentacion) ({{ $prod->presentacion }}) @endif
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="cantidad[]" class="form-control input-cantidad" value="1" min="1" required>
        </td>
        <td>
            <input type="number" name="precio[]" step="0.01" min="0" class="form-control input-precio" value="0" required>
        </td>
        <td>
            <input type="text" name="numero_lote[]" class="form-control" placeholder="Opcional">
        </td>
        <td>
            <input type="date" name="fecha_vencimiento[]" class="form-control">
        </td>
        <td class="text-end subtotal-fila">C$ 0.00</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-fila">
                <i class="bi bi-x-lg"></i>
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const tpl = document.getElementById('filaTemplate');
    const tbody = document.getElementById('detalleBody');
    const btnAgregar = document.getElementById('btnAgregarFila');
    const totalEl = document.getElementById('totalCompra');

    function formatoMoneda(valor) {
        return 'C$ ' + Number(valor || 0).toFixed(2);
    }

    function recalcularFila(fila) {
        const cantidad = parseFloat(fila.querySelector('.input-cantidad').value) || 0;
        const precio = parseFloat(fila.querySelector('.input-precio').value) || 0;
        const subtotal = cantidad * precio;
        fila.querySelector('.subtotal-fila').textContent = formatoMoneda(subtotal);
        return subtotal;
    }

    function recalcularTotal() {
        let total = 0;
        tbody.querySelectorAll('tr').forEach(fila => {
            total += recalcularFila(fila);
        });
        totalEl.textContent = formatoMoneda(total);
    }

    function actualizarBotonesQuitar() {
        const filas = tbody.querySelectorAll('tr');
        filas.forEach(fila => {
            fila.querySelector('.btn-quitar-fila').disabled = filas.length === 1;
        });
    }

    function agregarFila() {
        const nodo = tpl.content.cloneNode(true);
        const fila = nodo.querySelector('tr');

        fila.querySelector('.input-cantidad').addEventListener('input', recalcularTotal);
        fila.querySelector('.input-precio').addEventListener('input', recalcularTotal);

        fila.querySelector('.select-producto').addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const precio = opt ? opt.getAttribute('data-precio') : null;
            if (precio && parseFloat(fila.querySelector('.input-precio').value) === 0) {
                fila.querySelector('.input-precio').value = parseFloat(precio).toFixed(2);
            }
            recalcularTotal();
        });

        fila.querySelector('.btn-quitar-fila').addEventListener('click', function () {
            fila.remove();
            actualizarBotonesQuitar();
            recalcularTotal();
        });

        tbody.appendChild(fila);
        actualizarBotonesQuitar();
        recalcularTotal();
    }

    btnAgregar.addEventListener('click', agregarFila);

    // arrancar con una fila
    agregarFila();
});
</script>

@endsection
