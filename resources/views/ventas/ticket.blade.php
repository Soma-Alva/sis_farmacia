<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ticket {{ $venta->numero_ticket }}</title>
<style>
    /*
        Vista independiente (no usa layouts.app): esto es lo que se manda
        a imprimir en la impresora térmica AON PR-250. El ancho del papel
        se controla desde config/farmacia.php (ancho_ticket), por si el
        rollo instalado es de 58mm en vez de 80mm.
    */
    @page {
        size: {{ config('farmacia.ancho_ticket', '80mm') }} auto;
        margin: 0;
    }

    * {
        box-sizing: border-box;
    }

    html, body {
        margin: 0;
        padding: 0;
        background: #fff;
    }

    body {
        width: {{ config('farmacia.ancho_ticket', '80mm') }};
        font-family: 'Courier New', Courier, monospace;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.4;
        color: #000;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        padding: 6px 8px 14px 8px;
    }

    .gf-ticket-logo {
        display: block;
        margin: 0 auto 4px auto;
        max-width: 60%;
        max-height: 75px;
    }

    .gf-ticket-centro {
        text-align: center;
    }

    .gf-ticket-nombre {
        font-size: 17px;
        font-weight: bold;
        color: #000;
        text-transform: uppercase;
    }

    .gf-ticket-dato {
        font-size: 13px;
    }

    .gf-ticket-linea {
        border-top: 1px dashed #000;
        margin: 6px 0;
    }

    .gf-ticket-info table {
        width: 100%;
        border-collapse: collapse;
    }

    .gf-ticket-info td {
        padding: 0;
        vertical-align: top;
        font-size: 13px;
    }

    .gf-ticket-info td.gf-ticket-label {
        white-space: nowrap;
        padding-right: 4px;
    }

    table.gf-ticket-detalle {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
    }

    table.gf-ticket-detalle th {
        text-align: left;
        font-size: 12px;
        font-weight: bold;
        border-bottom: 1px dashed #000;
        padding: 0 0 2px 0;
    }

    table.gf-ticket-detalle td {
        font-size: 12.5px;
        padding: 3px 0;
        vertical-align: top;
    }

    table.gf-ticket-detalle th.gf-ticket-num,
    table.gf-ticket-detalle td.gf-ticket-num {
        text-align: right;
        white-space: nowrap;
    }

    .gf-ticket-producto-codigo {
        font-size: 11px;
        font-weight: 500;
        color: #000;
    }

    .gf-ticket-totales table {
        width: 100%;
        border-collapse: collapse;
    }

    .gf-ticket-totales td {
        font-size: 13px;
        padding: 2px 0;
    }

    .gf-ticket-totales td.gf-ticket-num {
        text-align: right;
    }

    .gf-ticket-total-final td {
        font-size: 16px;
        font-weight: bold;
        border-top: 1px dashed #000;
        padding-top: 4px;
    }

    .gf-ticket-footer {
        margin-top: 10px;
        text-align: center;
        font-size: 12px;
        font-weight: 600;
    }

    /* Botón de reimpresión: solo se ve en pantalla, nunca en el papel */
    .gf-ticket-no-imprimir {
        text-align: center;
        margin-top: 12px;
    }

    @media print {
        .gf-ticket-no-imprimir {
            display: none;
        }
    }
</style>
</head>
<body>

    <img src="{{ asset('images/logo_ticket.png') }}" alt="Logo" class="gf-ticket-logo">

    <div class="gf-ticket-centro">
        <div class="gf-ticket-nombre">{{ config('farmacia.nombre') }}</div>
        <div class="gf-ticket-dato">Tel: {{ config('farmacia.telefono') }}</div>
        <div class="gf-ticket-dato">RUC: {{ config('farmacia.ruc') }}</div>
        <div class="gf-ticket-dato">{{ config('farmacia.direccion') }}</div>
    </div>

    <div class="gf-ticket-linea"></div>

    <div class="gf-ticket-info">
        <table>
            <tr>
                <td class="gf-ticket-label">Ticket:</td>
                <td>{{ $venta->numero_ticket }}</td>
            </tr>
            <tr>
                <td class="gf-ticket-label">Fecha:</td>
                <td>{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td class="gf-ticket-label">Cliente:</td>
                <td>{{ $venta->cliente_nombre ?: 'Cliente General' }}</td>
            </tr>
            @if(!empty($venta->cajero))
            <tr>
                <td class="gf-ticket-label">Cajero:</td>
                <td>{{ $venta->cajero }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="gf-ticket-linea"></div>

    <table class="gf-ticket-detalle">
        <thead>
            <tr>
                <th>Producto</th>
                <th class="gf-ticket-num">Cant.</th>
                <th class="gf-ticket-num">P/U</th>
                <th class="gf-ticket-num">Subt.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalle as $item)
            <tr>
                <td>
                    {{ $item->nombre }}
                </td>
                <td class="gf-ticket-num">{{ $item->cantidad }}</td>
                <td class="gf-ticket-num">{{ number_format($item->precio_unitario, 2) }}</td>
                <td class="gf-ticket-num">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="gf-ticket-linea"></div>

    <div class="gf-ticket-totales">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td class="gf-ticket-num">C$ {{ number_format($venta->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>
                    @if((float) $venta->igv == 0)
                        IVA:
                    @else
                        IVA:
                    @endif
                </td>
                <td class="gf-ticket-num">C$ {{ number_format($venta->igv, 2) }}</td>
            </tr>
            <tr class="gf-ticket-total-final">
                <td>TOTAL:</td>
                <td class="gf-ticket-num">C$ {{ number_format($venta->total, 2) }}</td>
            </tr>
        </table>

        <div class="gf-ticket-linea"></div>

        <table>
            <tr>
                <td>Forma de pago:</td>
                <td class="gf-ticket-num">{{ $venta->metodo_pago }}</td>
            </tr>
            @if(!is_null($venta->monto_pagado))
            <tr>
                <td>Pagó con:</td>
                <td class="gf-ticket-num">C$ {{ number_format($venta->monto_pagado, 2) }}</td>
            </tr>
            <tr>
                <td>Cambio:</td>
                <td class="gf-ticket-num">C$ {{ number_format($venta->cambio, 2) }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="gf-ticket-footer">
        ¡Gracias por su compra!<br>
        Conserve este ticket para cualquier reclamo.
    </div>

    <div class="gf-ticket-no-imprimir">
        <button onclick="window.print()">Imprimir ticket</button>
    </div>

    <script>
        // Se manda a imprimir automáticamente apenas carga la página: la
        // persona solo tiene que elegir/confirmar la impresora térmica
        // (AON PR-250) en el diálogo nativo del navegador. El botón de
        // arriba queda como respaldo por si el navegador bloquea el
        // auto-print o si se necesita reimprimir sin recargar.
        window.onload = function () {
            window.print();
        };
    </script>

</body>
</html>
