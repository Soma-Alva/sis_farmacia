@extends('layouts.app')

@section('content')

@php $editando = isset($compra); @endphp

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">
            <h4>{{ $editando ? 'Editar Compra ' . $compra->numero_factura : 'Nueva Compra' }}</h4>
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

            <form action="{{ $editando ? route('compras.update', $compra->id_compra) : route('compras.store') }}" method="POST" id="formCompra">
                @csrf
                @if($editando) @method('PUT') @endif

                <div class="row">

                    <div class="col-md-4">
                        <label class="form-label">Número Factura</label>
                        <input type="text" name="numero_factura" class="form-control"
                               value="{{ old('numero_factura', $editando ? $compra->numero_factura : '') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Proveedor</label>
                        <select name="id_proveedor" class="form-control" required>
                            <option value="">Seleccione</option>
                            @foreach($proveedores as $p)
                                <option value="{{ $p->id_proveedor }}"
                                    {{ old('id_proveedor', $editando ? $compra->id_proveedor : null) == $p->id_proveedor ? 'selected' : '' }}>
                                    {{ $p->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha_compra" value="{{ old('fecha_compra', $editando ? \Illuminate\Support\Carbon::parse($compra->fecha_compra)->format('Y-m-d') : date('Y-m-d')) }}"
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

                <button type="submit" class="btn btn-success">{{ $editando ? 'Guardar cambios' : 'Guardar Compra' }}</button>
                @unless($editando)
                    <button type="reset" class="btn btn-secondary">Limpiar</button>
                @endunless
                <button type="button" class="btn btn-danger"
                        onclick="window.location='{{ $editando ? route('compras.show', $compra->id_compra) : route('compras.index') }}'">Cancelar</button>

            </form>

        </div>

    </div>

</div>

@php
    // Filas con las que arranca el formulario: lo que se escribió (si
    // hubo un error de validación), o las líneas de la compra que se
    // edita; vacío = una fila en blanco.
    $filasIniciales = [];
    if (old('id_producto')) {
        foreach (old('id_producto') as $i => $idp) {
            $filasIniciales[] = [
                'id_producto' => $idp,
                'cantidad' => old('cantidad.' . $i),
                'precio' => old('precio.' . $i),
                'numero_lote' => old('numero_lote.' . $i),
                'fecha_vencimiento' => old('fecha_vencimiento.' . $i),
            ];
        }
    } elseif ($editando) {
        foreach ($compra->detalles as $d) {
            $filasIniciales[] = [
                'id_producto' => $d->id_producto,
                'cantidad' => $d->cantidad,
                'precio' => \App\Support\Dinero::plano($d->precio_unitario),
                'numero_lote' => $d->numero_lote,
                'fecha_vencimiento' => $d->fecha_vencimiento
                    ? \Illuminate\Support\Carbon::parse($d->fecha_vencimiento)->format('Y-m-d')
                    : null,
            ];
        }
    }
@endphp

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
            <input type="number" name="precio[]" step="0.0001" min="0" class="form-control input-precio" value="0" required>
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

    // Hasta 4 decimales de precisión, sin ceros de más y mínimo 2:
    // 7 -> 7.00, 2.6 -> 2.60, 2.6087 -> 2.6087.
    function redondear4(valor) {
        return Math.round((Number(valor) || 0) * 10000) / 10000;
    }

    function textoNumero(valor) {
        const s = redondear4(valor).toFixed(4).replace(/0+$/, '');
        const [ent, dec = ''] = s.split('.');
        const entero = ent.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return entero + '.' + dec.padEnd(2, '0');
    }

    function formatoMoneda(valor) {
        return 'C$ ' + textoNumero(valor);
    }

    function recalcularFila(fila) {
        const cantidad = parseFloat(fila.querySelector('.input-cantidad').value) || 0;
        const precio = parseFloat(fila.querySelector('.input-precio').value) || 0;
        const subtotal = redondear4(cantidad * precio);
        fila.querySelector('.subtotal-fila').textContent = formatoMoneda(subtotal);
        return subtotal;
    }

    function recalcularTotal() {
        let total = 0;
        tbody.querySelectorAll('tr').forEach(fila => {
            total += recalcularFila(fila);
        });
        totalEl.textContent = formatoMoneda(redondear4(total));
    }

    function actualizarBotonesQuitar() {
        const filas = tbody.querySelectorAll('tr');
        filas.forEach(fila => {
            fila.querySelector('.btn-quitar-fila').disabled = filas.length === 1;
        });
    }

    function agregarFila(datos) {
        const nodo = tpl.content.cloneNode(true);
        const fila = nodo.querySelector('tr');

        fila.querySelector('.input-cantidad').addEventListener('input', recalcularTotal);
        fila.querySelector('.input-precio').addEventListener('input', recalcularTotal);

        fila.querySelector('.select-producto').addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const precio = opt ? opt.getAttribute('data-precio') : null;
            if (precio && parseFloat(fila.querySelector('.input-precio').value) === 0) {
                fila.querySelector('.input-precio').value = textoNumero(parseFloat(precio)).replace(/,/g, '');
            }
            recalcularTotal();
        });

        fila.querySelector('.btn-quitar-fila').addEventListener('click', function () {
            fila.remove();
            actualizarBotonesQuitar();
            recalcularTotal();
        });

        tbody.appendChild(fila);

        if (datos) {
            fila.querySelector('.select-producto').value = datos.id_producto ?? '';
            fila.querySelector('.input-cantidad').value = datos.cantidad ?? 1;
            fila.querySelector('.input-precio').value = datos.precio ?? 0;
            fila.querySelector('[name="numero_lote[]"]').value = datos.numero_lote ?? '';
            fila.querySelector('[name="fecha_vencimiento[]"]').value = datos.fecha_vencimiento ?? '';
        }

        actualizarBotonesQuitar();
        recalcularTotal();
    }

    btnAgregar.addEventListener('click', function () { agregarFila(); });

    // arrancar con las filas guardadas (o una en blanco)
    const filasIniciales = @json($filasIniciales);
    if (filasIniciales.length) {
        filasIniciales.forEach(function (f) { agregarFila(f); });
    } else {
        agregarFila();
    }
});
</script>

@endsection
