@extends('layouts.app')

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@php
    // Etiquetas de IVA calculadas aparte (sin paréntesis dentro de
    // {{ }}) para que no haya expresiones PHP largas mezcladas con
    // HTML, más fácil de leer y de depurar si algo falla.
    $ivaPorcentajeTexto = rtrim(rtrim(number_format($caja->iva_porcentaje, 2), '0'), '.');
    $ivaEtiquetaHeader = $caja->aplica_iva
        ? 'IVA aplicado: ' . $ivaPorcentajeTexto . '%'
        : 'Caja sin IVA, medicamentos exentos';
    $ivaEtiquetaResumen = $caja->aplica_iva
        ? 'IVA ' . $ivaPorcentajeTexto . '%'
        : 'IVA exento';
@endphp

<div class="gf-ventas-header">
    <div>
        <h1 class="gf-page-title">Área de Ventas</h1>
        <p class="gf-page-subtitle">
            {{ $ivaEtiquetaHeader }}
            · Tipo de cambio: C$ {{ number_format($caja->tipo_cambio, 2) }}
        </p>
    </div>

    <a href="{{ route('ventas.historial') }}" class="gf-btn-soft gf-btn-neutro">
        <i class="bi bi-clock-history"></i> Historial de ventas
    </a>
</div>

<div class="gf-pos">

    <!-- ÁREA DE VENTAS: BUSCADOR + CARRITO -->
    <div class="gf-pos-main">

        <div class="gf-card gf-pos-buscador-card">
            <div class="gf-search gf-search-pos">
                <i class="bi bi-upc-scan"></i>
                <input type="text" id="buscarProducto" autocomplete="off"
                       placeholder="Escanea el código de barras o escribe el nombre del medicamento...">
            </div>
            <div id="sugerencias" class="gf-pos-sugerencias"></div>
        </div>

        <div class="gf-card">
            <div class="gf-card-header">Carrito de compra actual</div>

            <div class="p-3 pt-2">
                <div class="table-responsive">
                    <table class="gf-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Lote</th>
                                <th>Caducidad</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio unit.</th>
                                <th class="text-end">Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="carrito">
                            <tr id="filaVacia">
                                <td colspan="8" class="text-center text-muted py-4">
                                    El carrito está vacío. Escanea o busca un producto para comenzar.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="gf-pos-resumen-items">
                    <span>Total de ítems: <strong id="totalItems">0</strong></span>
                    <span>Total de piezas: <strong id="totalPiezas">0</strong></span>
                </div>
            </div>
        </div>

    </div>

    <!-- RESUMEN DE VENTA Y COBRO -->
    <div class="gf-pos-side">

        <form action="{{ route('ventas.procesar') }}" method="POST" id="formVenta">
            @csrf

            <div class="gf-card">
                <div class="gf-card-header">Resumen de venta y cobro</div>

                <div class="p-3 pt-2">

                <div class="gf-pos-totales">
                    <div class="gf-pos-linea">
                        <span>Subtotal</span>
                        <span id="lblSubtotal">C$ 0.00</span>
                    </div>
                    <div class="gf-pos-linea">
                        <span>{{ $ivaEtiquetaResumen }}</span>
                        <span id="lblIva">C$ 0.00</span>
                    </div>
                    <div class="gf-pos-linea gf-pos-total">
                        <span>Total a pagar</span>
                        <span id="lblTotal">C$ 0.00</span>
                    </div>
                </div>

                <div id="totalDolares" class="gf-banner warning" style="display:none;">
                    Equivale a <strong id="usdTotal">US$ 0.00</strong>
                </div>

                <div class="gf-pos-cliente">
                    <div>
                        <label>Cliente</label>
                        <input type="text" name="cliente_nombre" class="form-control" placeholder="Opcional">
                    </div>
                    <div>
                        <label>DNI / Cédula</label>
                        <input type="text" name="cliente_dni" class="form-control" placeholder="Opcional">
                    </div>
                </div>

                <label class="gf-pos-label">Métodos de pago</label>
                <div class="gf-pos-metodos">
                    <button type="button" class="gf-pos-metodo activo" data-valor="EFECTIVO">
                        <i class="bi bi-cash-coin"></i> Efectivo
                    </button>
                    <button type="button" class="gf-pos-metodo" data-valor="TARJETA">
                        <i class="bi bi-credit-card"></i> Tarjeta
                    </button>
                    <button type="button" class="gf-pos-metodo" data-valor="TRANSFERENCIA">
                        <i class="bi bi-bank"></i> Transferencia
                    </button>
                    <button type="button" class="gf-pos-metodo" data-valor="DOLARES">
                        <i class="bi bi-currency-dollar"></i> Dólares
                    </button>
                </div>
                <input type="hidden" name="metodo_pago" id="metodo_pago" value="EFECTIVO">

                <div id="divPago" class="gf-pos-pago">
                    <div>
                        <label>Monto recibido</label>
                        <input type="number" step="0.01" name="monto_pagado" id="pago" class="form-control">
                    </div>
                    <div id="divCambio">
                        <label>Cambio</label>
                        <input type="text" name="cambio" id="cambio" class="form-control" readonly>
                    </div>
                </div>

                <label class="gf-pos-label">Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="2"></textarea>

                <input type="hidden" name="productos" id="productosInput">
                <input type="hidden" name="total" id="totalInput">

                <button type="submit" class="gf-btn-primario gf-pos-finalizar" onclick="return validarVenta()">
                    Finalizar venta
                </button>

                <button type="button" class="gf-btn-soft gf-btn-peligro gf-pos-cancelar" onclick="cancelarVenta()">
                    Cancelar venta
                </button>

                </div>
            </div>

        </form>

    </div>

</div>

<style>
    .gf-ventas-header{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:16px;
        flex-wrap:wrap;
        margin-bottom:18px;
    }

    .gf-pos{
        display:grid;
        grid-template-columns:1fr 360px;
        gap:20px;
        align-items:start;
    }
    @media (max-width: 992px){
        .gf-pos{ grid-template-columns:1fr; }
    }

    .gf-pos-main{ display:flex; flex-direction:column; gap:16px; min-width:0; }

    .gf-pos-buscador-card{ padding:14px 18px; position:relative; }
    .gf-search-pos{ max-width:none; }
    .gf-search-pos input{ padding:12px 16px 12px 40px; font-size:14.5px; }
    .gf-search-pos i{ font-size:16px; }

    .gf-pos-sugerencias{
        position:absolute;
        left:18px;
        right:18px;
        top:64px;
        background:var(--blanco);
        border-radius:14px;
        box-shadow:0 10px 28px rgba(47,79,62,0.16);
        z-index:30;
        max-height:320px;
        overflow-y:auto;
        display:none;
    }
    .gf-pos-sugerencias.activo{ display:block; }
    .gf-pos-sugerencia{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        padding:10px 16px;
        cursor:pointer;
        border-bottom:1px solid rgba(36,36,32,0.06);
    }
    .gf-pos-sugerencia:last-child{ border-bottom:none; }
    .gf-pos-sugerencia:hover{ background:var(--verde-pastel); }
    .gf-pos-sugerencia .nombre{ font-weight:600; color:var(--verde-oscuro); font-size:13.5px; }
    .gf-pos-sugerencia .meta{ font-size:12px; color:rgba(36,36,32,0.55); }
    .gf-pos-sugerencia .precio{ font-size:13px; font-weight:600; white-space:nowrap; }
    .gf-pos-sugerencia.sin-stock{ opacity:0.5; cursor:not-allowed; }

    .gf-pos-resumen-items{
        display:flex;
        justify-content:flex-end;
        gap:24px;
        padding:12px 4px 2px;
        font-size:13px;
        color:rgba(36,36,32,0.6);
    }

    .gf-pos-side{ position:sticky; top:20px; }

    .gf-pos-totales{ padding:4px 0 10px; }
    .gf-pos-linea{
        display:flex;
        justify-content:space-between;
        font-size:13.5px;
        color:rgba(36,36,32,0.65);
        padding:6px 0;
    }
    .gf-pos-total{
        border-top:1px solid rgba(36,36,32,0.08);
        margin-top:4px;
        padding-top:10px;
        font-size:17px;
        font-weight:700;
        color:var(--verde-oscuro);
    }

    .gf-pos-cliente{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:10px;
        margin-bottom:14px;
    }
    .gf-pos-cliente label, .gf-pos-label{
        display:block;
        font-size:12px;
        font-weight:500;
        color:rgba(36,36,32,0.55);
        margin:0 0 4px;
    }
    .gf-pos-label{ margin-top:14px; }

    .gf-pos-metodos{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:8px;
        margin-bottom:6px;
    }
    .gf-pos-metodo{
        display:flex;
        align-items:center;
        justify-content:center;
        gap:6px;
        padding:10px 8px;
        border:1px solid rgba(36,36,32,0.1);
        background:var(--blanco);
        border-radius:12px;
        font-size:13px;
        font-weight:500;
        color:var(--carbon);
        cursor:pointer;
        transition:all .15s ease;
    }
    .gf-pos-metodo:hover{ border-color:var(--verde-medio); }
    .gf-pos-metodo.activo{
        background:var(--verde-oscuro);
        border-color:var(--verde-oscuro);
        color:var(--blanco);
    }

    .gf-pos-pago{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:10px;
        margin:14px 0 4px;
    }

    .gf-pos-finalizar{
        width:100%;
        margin-top:16px;
        padding:13px;
        font-size:15px;
        justify-content:center;
    }
    .gf-pos-cancelar{
        width:100%;
        margin-top:8px;
        text-align:center;
    }

    .gf-cant-stepper{
        display:flex;
        align-items:center;
        justify-content:center;
        gap:6px;
    }
    .gf-cant-stepper button{
        width:24px;
        height:24px;
        border:none;
        border-radius:7px;
        background:var(--verde-pastel);
        color:var(--verde-oscuro);
        font-weight:700;
        line-height:1;
        cursor:pointer;
    }
    .gf-cant-stepper button:hover{ background:var(--verde-claro); }
    .gf-cant-stepper span{ min-width:22px; text-align:center; font-weight:600; }
</style>

@php
    // Catálogo completo (solo productos activos) para búsqueda y
    // autocompletado por nombre, código de barras o descripción.
    // Se arma aparte, en un bloque @php, para no meter un closure
    // multilínea dentro de los paréntesis de @json (algunos editores
    // y linters de PHP se confunden al leer un archivo .blade.php
    // mezclado con HTML/JS y marcan falsos errores de paréntesis).
    $catalogoVentas = $productos->map(function ($p) {
        return [
            'id'       => $p->id_producto,
            'nombre'   => $p->nombre,
            'codigo'   => $p->codigo_barra,
            'precio'   => (float) $p->precio_venta,
            'stock'    => (int) $p->stock_actual,
            'lote'     => $p->proximo_lote,
            'vence'    => $p->proximo_vencimiento,
        ];
    })->values();
@endphp

<script>
    const catalogo = @json($catalogoVentas);

    const tipoCambio = {{ $caja->tipo_cambio }};
    const aplicaIva = {{ $caja->aplica_iva ? 'true' : 'false' }};
    const ivaPorcentaje = {{ (float) $caja->iva_porcentaje }};

    let carrito = [];

    function formatear(valor){
        return Number(valor || 0).toFixed(2);
    }

    function buscarPorCodigoExacto(codigo){
        return catalogo.find(p => p.codigo && p.codigo === codigo);
    }

    function agregarProducto(producto){
        if (producto.stock <= 0){
            alert('"' + producto.nombre + '" no tiene stock disponible.');
            return;
        }

        let item = carrito.find(p => p.id === producto.id);

        if (item){
            if (item.cantidad >= producto.stock){
                alert('No hay suficiente stock de "' + producto.nombre + '".');
                return;
            }
            item.cantidad++;
        } else {
            carrito.push({
                id: producto.id,
                nombre: producto.nombre,
                codigo: producto.codigo,
                precio: producto.precio,
                stock: producto.stock,
                lote: producto.lote,
                vence: producto.vence,
                cantidad: 1,
            });
        }

        render();
    }

    function removerItem(id){
        carrito = carrito.filter(p => p.id !== id);
        render();
    }

    function cambiarCantidad(id, delta){
        let item = carrito.find(p => p.id === id);
        if (!item) return;

        item.cantidad += delta;

        if (item.cantidad <= 0){
            removerItem(id);
            return;
        }

        if (item.cantidad > item.stock){
            alert('Stock insuficiente.');
            item.cantidad = item.stock;
        }

        render();
    }

    function cancelarVenta(){
        if (carrito.length === 0) return;
        if (!confirm('¿Deseas vaciar el carrito y cancelar esta venta?')) return;
        carrito = [];
        render();
    }

    function render(){
        const tbody = document.getElementById('carrito');
        let totalPiezas = 0;
        let total = 0;

        if (carrito.length === 0){
            tbody.innerHTML = `
                <tr id="filaVacia">
                    <td colspan="8" class="text-center text-muted py-4">
                        El carrito está vacío. Escanea o busca un producto para comenzar.
                    </td>
                </tr>`;
        } else {
            tbody.innerHTML = carrito.map(p => {
                const subtotal = p.precio * p.cantidad;
                total += subtotal;
                totalPiezas += p.cantidad;

                return `
                    <tr>
                        <td>${p.codigo ?? '—'}</td>
                        <td>${p.nombre}</td>
                        <td>${p.lote ?? '—'}</td>
                        <td>${p.vence ?? '—'}</td>
                        <td class="text-center">
                            <div class="gf-cant-stepper">
                                <button type="button" onclick="cambiarCantidad(${p.id}, -1)">-</button>
                                <span>${p.cantidad}</span>
                                <button type="button" onclick="cambiarCantidad(${p.id}, 1)">+</button>
                            </div>
                        </td>
                        <td class="text-end">C$ ${formatear(p.precio)}</td>
                        <td class="text-end">C$ ${formatear(subtotal)}</td>
                        <td class="text-center">
                            <button type="button" class="gf-btn-soft gf-btn-peligro" onclick="removerItem(${p.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>`;
            }).join('');
        }

        document.getElementById('totalItems').textContent = carrito.length;
        document.getElementById('totalPiezas').textContent = totalPiezas;

        document.getElementById('productosInput').value = JSON.stringify(
            carrito.map(p => ({ id: p.id, cantidad: p.cantidad, precio: p.precio }))
        );
        document.getElementById('totalInput').value = total;

        let subtotalSinIva = total;
        let iva = 0;

        if (aplicaIva && ivaPorcentaje > 0){
            const factor = 1 + (ivaPorcentaje / 100);
            subtotalSinIva = total / factor;
            iva = total - subtotalSinIva;
        }

        document.getElementById('lblSubtotal').textContent = 'C$ ' + formatear(subtotalSinIva);
        document.getElementById('lblIva').textContent = 'C$ ' + formatear(iva);
        document.getElementById('lblTotal').textContent = 'C$ ' + formatear(total);

        const metodo = document.getElementById('metodo_pago').value;
        if (metodo === 'DOLARES'){
            document.getElementById('totalDolares').style.display = 'block';
            document.getElementById('usdTotal').textContent = 'US$ ' + formatear(total / tipoCambio);
        } else {
            document.getElementById('totalDolares').style.display = 'none';
        }

        calcularCambio();
    }

    // ---------- BUSCADOR / ESCÁNER DE CÓDIGO DE BARRAS ----------
    const inputBuscar = document.getElementById('buscarProducto');
    const cajaSugerencias = document.getElementById('sugerencias');

    function renderSugerencias(lista){
        if (lista.length === 0){
            cajaSugerencias.classList.remove('activo');
            cajaSugerencias.innerHTML = '';
            return;
        }

        cajaSugerencias.innerHTML = lista.slice(0, 8).map(p => `
            <div class="gf-pos-sugerencia ${p.stock <= 0 ? 'sin-stock' : ''}" data-id="${p.id}">
                <div>
                    <div class="nombre">${p.nombre}</div>
                    <div class="meta">${p.codigo ? 'Código: ' + p.codigo : 'Sin código'} · Stock: ${p.stock}</div>
                </div>
                <div class="precio">C$ ${formatear(p.precio)}</div>
            </div>
        `).join('');

        cajaSugerencias.classList.add('activo');
    }

    inputBuscar.addEventListener('input', function(){
        const termino = this.value.trim().toLowerCase();

        if (termino === ''){
            renderSugerencias([]);
            return;
        }

        // Si lo escrito/leído coincide EXACTO con un código de barras,
        // lo agregamos de inmediato: así funciona con una pistola lectora
        // que no siempre manda "Enter" al terminar de leer.
        const porCodigo = buscarPorCodigoExacto(this.value.trim());
        if (porCodigo){
            agregarProducto(porCodigo);
            this.value = '';
            renderSugerencias([]);
            return;
        }

        const coincidencias = catalogo.filter(p =>
            p.nombre.toLowerCase().includes(termino) ||
            (p.codigo && p.codigo.toLowerCase().includes(termino))
        );

        renderSugerencias(coincidencias);
    });

    inputBuscar.addEventListener('keydown', function(e){
        if (e.key !== 'Enter') return;
        e.preventDefault();

        const codigo = this.value.trim();
        const porCodigo = buscarPorCodigoExacto(codigo);

        if (porCodigo){
            agregarProducto(porCodigo);
        } else {
            // Si no hay coincidencia exacta de código, agrega la
            // primera sugerencia visible (búsqueda por nombre).
            const primera = cajaSugerencias.querySelector('.gf-pos-sugerencia:not(.sin-stock)');
            if (primera){
                const producto = catalogo.find(p => p.id === parseInt(primera.dataset.id));
                if (producto) agregarProducto(producto);
            }
        }

        this.value = '';
        renderSugerencias([]);
    });

    cajaSugerencias.addEventListener('click', function(e){
        const fila = e.target.closest('.gf-pos-sugerencia');
        if (!fila || fila.classList.contains('sin-stock')) return;

        const producto = catalogo.find(p => p.id === parseInt(fila.dataset.id));
        if (producto) agregarProducto(producto);

        inputBuscar.value = '';
        renderSugerencias([]);
        inputBuscar.focus();
    });

    document.addEventListener('click', function(e){
        if (!e.target.closest('.gf-pos-buscador-card')){
            renderSugerencias([]);
        }
    });

    // Mantener el foco en el buscador para que la pistola lectora
    // siempre tenga dónde "escribir", incluso si el cajero hizo clic
    // en otra parte de la pantalla.
    document.addEventListener('click', function(e){
        if (!e.target.closest('.gf-pos-side') && !e.target.closest('.gf-pos-sugerencias')){
            inputBuscar.focus();
        }
    });

    // ---------- MÉTODO DE PAGO ----------
    document.querySelectorAll('.gf-pos-metodo').forEach(btn => {
        btn.addEventListener('click', function(){
            document.querySelectorAll('.gf-pos-metodo').forEach(b => b.classList.remove('activo'));
            this.classList.add('activo');
            document.getElementById('metodo_pago').value = this.dataset.valor;
            actualizarVisibilidadPago();
            render();
        });
    });

    function actualizarVisibilidadPago(){
        const metodo = document.getElementById('metodo_pago').value;
        const mostrar = (metodo === 'EFECTIVO' || metodo === 'DOLARES');
        document.getElementById('divPago').style.display = mostrar ? 'grid' : 'none';
    }

    function calcularCambio(){
        const metodo = document.getElementById('metodo_pago').value;
        const pago = parseFloat(document.getElementById('pago').value) || 0;
        const total = parseFloat(document.getElementById('totalInput').value) || 0;

        let cambio = 0;

        if (metodo === 'DOLARES'){
            cambio = (pago * tipoCambio) - total;
        } else {
            cambio = pago - total;
        }

        document.getElementById('cambio').value = cambio >= 0 ? cambio.toFixed(2) : '0.00';
    }

    document.getElementById('pago').addEventListener('input', calcularCambio);

    function validarVenta(){
        if (carrito.length === 0){
            alert('Debe agregar al menos un producto al carrito.');
            return false;
        }

        const metodo = document.getElementById('metodo_pago').value;

        if ((metodo === 'EFECTIVO' || metodo === 'DOLARES')){
            const pago = parseFloat(document.getElementById('pago').value) || 0;
            if (pago <= 0){
                alert('Debe ingresar el monto recibido.');
                return false;
            }
        }

        return true;
    }

    actualizarVisibilidadPago();
    render();
    inputBuscar.focus();
</script>

@endsection
