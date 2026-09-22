@extends('layouts.app')

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<h3>Ventas</h3>

    <div class="mb-3">

        <a href="{{ route('ventas.historial') }}"
        class="btn btn-secondary">

            📋 Historial de Ventas

        </a>

    </div>

    <div class="row">

    <!-- PRODUCTOS -->
    <div class="col-md-8">

    <input type="text" id="buscar" class="form-control mb-3" placeholder="Buscar por nombre o código de barras...">

        <div class="card">
            <div class="card-header">Productos</div>

            <div class="card-body">

                <div class="row">
                    @foreach($productos as $p)
                    <div class="col-md-4 mb-3 producto-item"
                        data-id="{{ $p->id_producto }}"
                        data-nombre="{{ strtolower($p->nombre) }}"
                        data-codigo="{{ $p->codigo_barra }}"
                        data-precio="{{ $p->precio_venta }}"
                        data-stock="{{ $p->stock_actual }}">    
                    
    <!---
                        <div class="card p-2">
                            <h6>{{ $p->nombre }}</h6>
                            <p>S/ {{ $p->precio_venta }}</p>

                            <button class="btn btn-primary btn-sm"
                                onclick="add({{ $p->id_producto }}, '{{ $p->nombre }}', {{ $p->precio_venta }}, {{ $p->stock_actual }})">
                                Agregar
                            </button>
                        </div>
    -->


        <div class="card p-2">

            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <h6 class="mb-1">{{ $p->nombre }}</h6>

                        <small class="text-muted">
                            Código: {{ $p->codigo_barra }}
                        </small>

                        <br>

                        <small class="text-success">
                            C$ {{ number_format($p->precio_venta,2) }}
                        </small>
                </div>

                <!-- ✅ STOCK (ZONA VERDE QUE MARCASTE) -->
                <div class="text-end">
                    <span class="badge
                        {{ $p->stock_actual <= 10 ? 'bg-danger' : ($p->stock_actual <= 20 ? 'bg-warning text-dark' : 'bg-success') }}">

                        Stock: {{ $p->stock_actual }}
                    </span>
                </div>

            </div>

            <button class="btn btn-primary btn-sm mt-2 w-100"
                onclick="add({{ $p->id_producto }}, '{{ $p->nombre }}', {{ $p->precio_venta }}, {{ $p->stock_actual }})">
                Agregar
            </button>

        </div>


                </div>
                @endforeach
            </div>

        </div>
    </div>

</div>

<!-- CARRITO -->
<div class="col-md-4">

    <div class="card">
        <div class="card shadow-lg border-0">Carrito</div>

        <div class="card-body">

            <ul id="carrito" class="list-group mb-3"></ul>

            <h3 class="text-success fw-bold">
                Total: C$ <span id="total">0</span>
            </h3>

            <div
                id="totalDolares"
                class="alert alert-info py-2 mt-2"
                style="display:none;">

                <strong>Equivale a:</strong>
                <span id="usdTotal">US$ 0.00</span>

            </div>

           <form action="{{ route('ventas.procesar') }}" method="POST">
                @csrf

                    <div class="mb-2">
                        <label>Cliente</label>
                        <input type="text" name="cliente_nombre" class="form-control">
                    </div>

                    <div class="mb-2">
                        <label>DNI</label>
                        <input type="text" name="cliente_dni" class="form-control">
                    </div>

                    <div class="mb-2">
                        <label>Método de Pago</label>
                        <select name="metodo_pago" id="metodo_pago" class="form-control">
                            <option value="EFECTIVO">Efectivo</option>
                            <option value="TARJETA">Tarjeta</option>
                            <option value="TRANSFERENCIA">Transferencia</option>
                            <option value="DOLARES">Dólares</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label>Observaciones</label>
                        <textarea name="observaciones" class="form-control"></textarea>
                    </div>

                    <div class="mb-2" id="divPago">
                        <label>Paga con</label>
                        <input type="number"
                            name="monto_pagado"
                            id="pago"
                            class="form-control">
                    </div>

                    <div class="mb-2" id="divCambio">
                        <label>Cambio</label>
                        <input type="text"
                            name="cambio"
                            id="cambio"
                            class="form-control"
                            readonly>
</div>

                <input type="hidden" name="productos" id="productosInput">
                <input type="hidden" name="total" id="totalInput">

                <button
                    type="submit"
                    class="btn btn-success w-100"
                    onclick="return validarVenta()">

                    Finalizar Venta

                </button>
            </form>

        </div>
    </div>

</div>

</div>

<script>

function actualizarMetodoPago(){

    let metodo = document.getElementById("metodo_pago").value;

    if(metodo == "TARJETA" || metodo == "TRANSFERENCIA"){

        document.getElementById("divPago").style.display = "none";
        document.getElementById("divCambio").style.display = "none";

    }else{

        document.getElementById("divPago").style.display = "block";
        document.getElementById("divCambio").style.display = "block";

    }

    render();
}

const tipoCambio = {{ $caja->tipo_cambio }};

let carrito = [];

function add(id, nombre, precio, stock){
    

    let item = carrito.find(p => p.id === id);

    if(item){
        if(item.cantidad >= stock){
            alert("No hay suficiente stock");
            return;
        }
        item.cantidad++;
    } else {
        carrito.push({id, nombre, precio, cantidad:1, stock});
    }

    render();
}

// ✅ ELIMINAR PRODUCTO
function removeItem(id){
    carrito = carrito.filter(p => p.id !== id);
    render();
}

// ✅ CAMBIAR CANTIDAD
function changeQty(id, delta){
    let item = carrito.find(p => p.id === id);

    if(!item) return;

    item.cantidad += delta;

    if(item.cantidad <= 0){
        removeItem(id);
        return;
    }

    if(item.cantidad > item.stock){
        alert("Stock insuficiente");
        item.cantidad = item.stock;
    }

    render();
}

function render(){

    let html = '';
    let total = 0;

    carrito.forEach(p => {

        let subtotal = p.precio * p.cantidad;
        total += subtotal;

        html += `
        <li class="list-group-item">

            <div class="d-flex justify-content-between">
                <strong>${p.nombre}</strong>
                <button class="btn btn-danger btn-sm" onclick="removeItem(${p.id})">X</button>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-2">

                <div>
                    <button class="btn btn-sm btn-secondary" onclick="changeQty(${p.id}, -1)">-</button>
                    ${p.cantidad}
                    <button class="btn btn-sm btn-secondary" onclick="changeQty(${p.id}, 1)">+</button>
                </div>

                <span>C$ ${subtotal.toFixed(2)}</span>

            </div>
        </li>
        `;
    });

    document.getElementById('carrito').innerHTML = html;
    document.getElementById('total').innerText = total.toFixed(2);

    document.getElementById('totalInput').value = total;
    document.getElementById('productosInput').value = JSON.stringify(carrito);

    // Mostrar equivalente en dólares
    let metodo = document.getElementById("metodo_pago").value;

    if(metodo === "DOLARES"){

        document.getElementById("totalDolares").style.display = "block";

        let totalUSD = total / tipoCambio;

        document.getElementById("usdTotal").innerText =
            "US$ " + totalUSD.toFixed(2);

    }else{

        document.getElementById("totalDolares").style.display = "none";

    }
}

// ✅ BUSCADOR
document.getElementById("buscar").addEventListener("keydown", function(e){

    if(e.key === "Enter"){

        e.preventDefault();

        let codigo = this.value.trim();

        let productos = document.querySelectorAll(".producto-item");

        productos.forEach(function(item){

            if(item.dataset.codigo === codigo){

                add(
                    parseInt(item.dataset.id),
                    item.dataset.nombre,
                    parseFloat(item.dataset.precio),
                    parseInt(item.dataset.stock)
                );

                document.getElementById("buscar").value = "";

            }

        });

    }

});

document.addEventListener("DOMContentLoaded", function(){

    console.log("JS cargado"); // prueba

    let buscador = document.getElementById('buscar');

    if(buscador){

        buscador.addEventListener('keyup', function(){

            let filtro = this.value.toLowerCase();
            let items = document.querySelectorAll('.producto-item');

            items.forEach(item => {
                let nombre = item.dataset.nombre;
                let codigo = item.dataset.codigo;

                if(
                    nombre.includes(filtro) ||
                    codigo.includes(filtro)
                ){
                    item.style.display = '';
                }   
                else{
                    item.style.display = 'none';
                    }
            });

        });

    }

});


</script>

<script>

function calcularCambio(){

    let pago = parseFloat(document.getElementById("pago").value) || 0;

    let total = parseFloat(document.getElementById("total").innerText) || 0;

    let metodo = document.getElementById("metodo_pago").value;

    let cambio = 0;

    if(metodo === "DOLARES"){

        // El cliente escribe dólares
        let pagoCordobas = pago * tipoCambio;

        cambio = pagoCordobas - total;

    }else{

        // El cliente escribe córdobas
        cambio = pago - total;

    }

    document.getElementById("cambio").value =
        cambio >= 0 ? cambio.toFixed(2) : "0.00";

}
document.getElementById("pago")
    .addEventListener("keyup", calcularCambio);

document.getElementById("metodo_pago")
.addEventListener("change", function(){

    actualizarMetodoPago();

    calcularCambio();

});
actualizarMetodoPago();
function validarVenta(){

    if(carrito.length===0){

        alert("Debe agregar productos.");

        return false;

    }

    return true;

}
</script>
``


@endsection
