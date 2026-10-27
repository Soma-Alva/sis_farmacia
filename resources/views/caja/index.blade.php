@extends('layouts.app')

@section('content')

<style>
    /* ---------- Encabezado de caja ---------- */
    .gf-caja-top{
        background:#fff;
        border-radius:18px;
        box-shadow:0 2px 10px rgba(47,79,62,0.06);
        padding:16px 20px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        flex-wrap:wrap;
        gap:14px;
        margin-bottom:18px;
    }
    .gf-caja-top-left{
        display:flex;
        align-items:center;
        gap:14px;
    }
    .gf-caja-top-icon{
        width:48px;
        height:48px;
        border-radius:14px;
        background:var(--verde-oscuro);
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:19px;
        flex:0 0 auto;
    }
    .gf-caja-top-title{
        font-family:'Fraunces',serif;
        font-weight:600;
        color:var(--carbon);
        margin:0;
    }
    .gf-caja-top-sub{
        font-size:12.5px;
        color:rgba(36,36,32,0.55);
        margin-top:2px;
    }
    .gf-caja-top-right{
        display:flex;
        align-items:center;
        gap:14px;
        flex-wrap:wrap;
    }

    .gf-caja-tasa{
        display:flex;
        align-items:center;
        gap:10px;
        background:var(--verde-pastel);
        border-radius:12px;
        padding:8px 12px;
        color:var(--verde-oscuro);
    }
    .gf-caja-tasa > i{ font-size:16px; }
    .gf-caja-tasa-label{
        font-size:10px;
        font-weight:600;
        letter-spacing:.04em;
        text-transform:uppercase;
        color:rgba(47,79,62,0.6);
    }
    .gf-caja-tasa-valor{
        font-size:13.5px;
        font-weight:700;
    }
    .gf-caja-tasa-editar{
        background:none;
        border:none;
        color:var(--verde-oscuro);
        opacity:.6;
        padding:4px;
        cursor:pointer;
    }
    .gf-caja-tasa-editar:hover{ opacity:1; }
    .gf-caja-tasa-form input{
        width:100px;
        border:1px solid rgba(47,79,62,0.25);
        border-radius:8px;
        padding:5px 8px;
        font-size:13px;
        background:#fff;
    }
    .gf-caja-tasa-guardar, .gf-caja-tasa-cancelar{
        border:none;
        border-radius:8px;
        width:28px;
        height:28px;
        display:flex;
        align-items:center;
        justify-content:center;
        cursor:pointer;
    }
    .gf-caja-tasa-guardar{ background:var(--verde-oscuro); color:#fff; }
    .gf-caja-tasa-cancelar{ background:#EDEDEA; color:#6B6B66; }

    .gf-caja-reloj{ text-align:right; }
    .gf-caja-reloj-hora{
        font-size:15px;
        font-weight:700;
        color:var(--carbon);
    }
    .gf-caja-reloj-fecha{
        font-size:11.5px;
        color:rgba(36,36,32,0.5);
    }


    /* ---------- Tarjetas de resumen ---------- */
    .gf-caja-stats{
        display:grid;
        grid-template-columns:repeat(4, 1fr);
        gap:16px;
        margin-bottom:18px;
    }
    @media (max-width: 992px){
        .gf-caja-stats{ grid-template-columns:repeat(2, 1fr); }
    }
    @media (max-width: 576px){
        .gf-caja-stats{ grid-template-columns:1fr; }
    }
    .gf-caja-stat{
        background:#fff;
        border-radius:18px;
        box-shadow:0 2px 10px rgba(47,79,62,0.06);
        padding:18px;
    }
    .gf-caja-stat-dark{
        background:var(--verde-oscuro);
        color:#fff;
    }
    .gf-caja-stat-top{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:10px;
    }
    .gf-caja-stat-label{
        font-size:11px;
        font-weight:600;
        letter-spacing:.04em;
        text-transform:uppercase;
        color:rgba(36,36,32,0.5);
    }
    .gf-caja-stat-dark .gf-caja-stat-label{ color:rgba(255,255,255,0.7); }
    .gf-caja-stat-valor{
        font-size:22px;
        font-weight:700;
        color:var(--carbon);
        margin-top:2px;
    }
    .gf-caja-stat-dark .gf-caja-stat-valor{ color:#fff; }
    .gf-caja-stat-icono{
        width:36px;
        height:36px;
        border-radius:10px;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:15px;
        flex:0 0 auto;
    }
    .gf-caja-stat-sub{
        font-size:11.5px;
        color:rgba(36,36,32,0.5);
        margin-top:8px;
    }
    .gf-caja-stat-dark .gf-caja-stat-sub{ color:rgba(255,255,255,0.75); }
    .gf-caja-stat-divider{
        border-top:1px dashed rgba(47,79,62,0.15);
        margin:12px 0 10px;
    }
    .gf-caja-stat-dark .gf-caja-stat-divider{ border-top:1px dashed rgba(255,255,255,0.2); }
    .gf-caja-stat-foot{
        display:flex;
        align-items:center;
        justify-content:space-between;
        font-size:12px;
        color:rgba(36,36,32,0.6);
    }
    .gf-caja-stat-dark .gf-caja-stat-foot{ color:rgba(255,255,255,0.85); }
    .gf-caja-stat-foot strong{ color:var(--carbon); }
    .gf-caja-stat-dark .gf-caja-stat-foot strong{ color:#fff; }

    /* ---------- Barra de acciones ---------- */
    .gf-caja-acciones-bar{
        background:#fff;
        border-radius:18px;
        box-shadow:0 2px 10px rgba(47,79,62,0.06);
        padding:16px 20px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        flex-wrap:wrap;
        gap:12px;
        margin-bottom:18px;
    }
    .gf-caja-acciones-label{
        font-weight:600;
        font-size:13.5px;
        color:var(--carbon);
        display:flex;
        align-items:center;
        gap:8px;
    }
    .gf-caja-acciones-label i{ color:#C98A3B; }
    .gf-caja-acciones-botones{
        display:flex;
        gap:10px;
        flex-wrap:wrap;
    }
    .gf-pill{
        display:inline-flex;
        align-items:center;
        gap:7px;
        padding:9px 16px;
        border-radius:999px;
        font-size:13px;
        font-weight:600;
        text-decoration:none;
        border:1.5px solid transparent;
        cursor:pointer;
    }
    .gf-pill-verde{ background:#E3F1E7; color:#2F6B45; border-color:#C7E3D0; }
    .gf-pill-verde:hover{ background:#D4EBDA; color:#2F6B45; }
    .gf-pill-rojo{ background:#FBE9E6; color:#8C3A2A; border-color:#F4D3CC; }
    .gf-pill-rojo:hover{ background:#F6D9D2; color:#8C3A2A; }
    .gf-pill-oscuro{ background:var(--verde-oscuro); color:#fff; }
    .gf-pill-oscuro:hover{ background:var(--verde-oscuro-2); color:#fff; }
    .gf-pill-ambar{ background:#FBF3DE; color:#8A6A1F; border-color:#F2E2B4; }
    .gf-pill-ambar:hover{ background:#F6E9C4; color:#8A6A1F; }

    /* ---------- Bitácora del día ---------- */
    .gf-caja-bitacora-header{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        flex-wrap:wrap;
        gap:12px;
        padding:18px 20px 12px;
    }
    .gf-caja-bitacora-buscador{
        display:flex;
        align-items:center;
        gap:8px;
    }
    .gf-caja-refrescar{
        width:34px;
        height:34px;
        border-radius:10px;
        background:var(--verde-pastel);
        color:var(--verde-oscuro);
        border:none;
        display:flex;
        align-items:center;
        justify-content:center;
        cursor:pointer;
    }
    .gf-caja-refrescar:hover{ opacity:.8; }
</style>

<div class="gf-caja-top">

    <div class="gf-caja-top-left">
        <div class="gf-caja-top-icon"><i class="fas fa-cash-register"></i></div>
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="gf-caja-top-title">Caja Principal POS</h5>
                <span class="gf-badge {{ $abierta ? 'gf-badge-exito' : 'gf-badge-apagado' }}">
                    <i class="bi bi-circle-fill" style="font-size:7px;"></i>
                    {{ $abierta ? 'ABIERTA' : 'CERRADA' }}
                </span>
            </div>
            <div class="gf-caja-top-sub">
                <i class="bi bi-person"></i>
                Cajero: {{ $cajero ?? optional(auth()->user())->nombre_completo }}
            </div>
        </div>
    </div>

    <div class="gf-caja-top-right">

        @if($abierta)
            <div class="gf-caja-tasa" id="gf-tasa-view">
                <i class="bi bi-currency-dollar"></i>
                <div>
                    <div class="gf-caja-tasa-label">Tasa de cambio</div>
                    <div class="gf-caja-tasa-valor">1 USD = {{ number_format($caja->tipo_cambio, 2) }} C$</div>
                </div>
                <button type="button" class="gf-caja-tasa-editar" onclick="gfEditarTasa(true)" title="Corregir tasa">
                    <i class="bi bi-pencil-fill"></i>
                </button>
            </div>

            <form id="gf-tasa-form" action="{{ route('caja.tipoCambio.actualizar') }}" method="POST"
                  class="gf-caja-tasa gf-caja-tasa-form" style="display:none;">
                @csrf
                @method('PUT')
                <input type="number" step="0.0001" min="0.0001" name="tipo_cambio"
                       value="{{ $caja->tipo_cambio }}" required>
                <button type="submit" class="gf-caja-tasa-guardar" title="Guardar"><i class="bi bi-check-lg"></i></button>
                <button type="button" class="gf-caja-tasa-cancelar" onclick="gfEditarTasa(false)" title="Cancelar"><i class="bi bi-x-lg"></i></button>
            </form>
        @endif

        <div class="gf-caja-reloj">
            <div class="gf-caja-reloj-hora" id="gf-reloj-hora">--:--:-- --</div>
            <div class="gf-caja-reloj-fecha" id="gf-reloj-fecha">—</div>
        </div>

    </div>

</div>

@if($caja && $abierta)

    <div class="gf-caja-stats">

        <div class="gf-caja-stat">
            <div class="gf-caja-stat-top">
                <div>
                    <div class="gf-caja-stat-label">Monto apertura</div>
                    <div class="gf-caja-stat-valor">C$ {{ number_format($caja->saldo_inicial, 2) }}</div>
                </div>
                <div class="gf-caja-stat-icono" style="background:var(--verde-pastel); color:var(--verde-oscuro);">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
            <div class="gf-caja-stat-sub">Fondo asignado inicial</div>
            <div class="gf-caja-stat-divider"></div>
            <div class="gf-caja-stat-foot">
                <span>{{ $caja->observaciones ?: 'Turno actual' }}</span>
                <strong>{{ \Carbon\Carbon::parse($caja->hora_apertura)->format('h:i A') }}</strong>
            </div>
        </div>

        <div class="gf-caja-stat">
            <div class="gf-caja-stat-top">
                <div>
                    <div class="gf-caja-stat-label">Total ingresos</div>
                    <div class="gf-caja-stat-valor" style="color:#2F6B45;">
                        C$ {{ number_format($ventasHoy + $ingresos, 2) }}
                    </div>
                </div>
                <div class="gf-caja-stat-icono" style="background:#E3F1E7; color:#2F6B45;">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
            </div>
            <div class="gf-caja-stat-sub" style="color:#2F6B45;">
                <i class="bi bi-arrow-up-right"></i> Ventas &amp; entradas
            </div>
            <div class="gf-caja-stat-divider"></div>
            <div class="gf-caja-stat-foot">
                <span>Ventas POS: C$ {{ number_format($ventasHoy, 2) }}</span>
                <strong>Otros: C$ {{ number_format($ingresos, 2) }}</strong>
            </div>
        </div>

        <div class="gf-caja-stat">
            <div class="gf-caja-stat-top">
                <div>
                    <div class="gf-caja-stat-label">Total egresos</div>
                    <div class="gf-caja-stat-valor" style="color:#8C3A2A;">
                        C$ {{ number_format($egresos, 2) }}
                    </div>
                </div>
                <div class="gf-caja-stat-icono" style="background:#FBE9E6; color:#8C3A2A;">
                    <i class="bi bi-graph-down-arrow"></i>
                </div>
            </div>
            <div class="gf-caja-stat-sub" style="color:#8C3A2A;">
                <i class="bi bi-arrow-down-right"></i> Retiros &amp; gastos
            </div>
            <div class="gf-caja-stat-divider"></div>
            <div class="gf-caja-stat-foot">
                <span>{{ $egresosCount }} {{ $egresosCount == 1 ? 'salida registrada' : 'salidas registradas' }}</span>
                <strong style="color:#8C3A2A;">Control activo</strong>
            </div>
        </div>

        <div class="gf-caja-stat gf-caja-stat-dark">
            <div class="gf-caja-stat-top">
                <div>
                    <div class="gf-caja-stat-label">Efectivo teórico en caja</div>
                    <div class="gf-caja-stat-valor">C$ {{ number_format($efectivoTeorico, 2) }}</div>
                </div>
                <div class="gf-caja-stat-icono" style="background:rgba(255,255,255,0.18); color:#fff;">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
            <div class="gf-caja-stat-sub">Solo efectivo: lo que debería haber en la gaveta</div>
            <div class="gf-caja-stat-divider"></div>
            <div class="gf-caja-stat-foot">
                <span>Equivalente USD:</span>
                <strong>
                    $ {{ number_format($caja->tipo_cambio > 0 ? $efectivoTeorico / $caja->tipo_cambio : 0, 2) }}
                </strong>
            </div>
        </div>

    </div>

    <div class="gf-caja-acciones-bar">
        <div class="gf-caja-acciones-label">
            <i class="bi bi-lightning-charge-fill"></i> Acciones de caja
        </div>
        <div class="gf-caja-acciones-botones">
            <a href="{{ route('caja.ingreso') }}" class="gf-pill gf-pill-verde">
                <i class="bi bi-plus-circle"></i> Ingreso Extra
            </a>
            <a href="{{ route('caja.egreso') }}" class="gf-pill gf-pill-rojo">
                <i class="bi bi-dash-circle"></i> Salida / Egreso
            </a>
            <a href="{{ route('caja.arqueo') }}" class="gf-pill gf-pill-oscuro">
                <i class="bi bi-calculator"></i> Arqueo / Conteo C$
            </a>
            <a href="{{ route('caja.arqueo') }}" class="gf-pill gf-pill-ambar">
                <i class="bi bi-lock"></i> Cerrar Turno
            </a>
        </div>
    </div>

    <div class="gf-card">
        <div class="gf-caja-bitacora-header">
            <div>
                <div class="gf-card-header" style="padding:0;">Bitácora de Movimientos del Día</div>
                <p class="gf-page-subtitle mb-0">Registro cronológico de entradas, salidas y transacciones POS</p>
            </div>
            <div class="gf-caja-bitacora-buscador">
                <div class="gf-search" style="max-width:240px;">
                    <i class="bi bi-search"></i>
                    <input type="text" id="gf-bitacora-buscar" placeholder="Buscar concepto o tipo..." autocomplete="off">
                </div>
                <button type="button" class="gf-caja-refrescar" onclick="window.location.reload()" title="Actualizar">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </div>

        <div class="p-3 pt-0">
            <div class="table-responsive">
                <table class="gf-table table-hover" id="gf-bitacora-tabla">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Tipo</th>
                            <th>Concepto / Descripción</th>
                            <th>Método</th>
                            <th class="text-end">Monto (C$)</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($caja->hora_apertura)->format('h:i A') }}</td>
                            <td><span class="gf-badge gf-badge-neutro">Apertura</span></td>
                            <td>Fondo inicial de caja</td>
                            <td>Efectivo</td>
                            <td class="text-end fw-semibold" style="color:#2F6B45;">
                                +C$ {{ number_format($caja->saldo_inicial, 2) }}
                            </td>
                            <td><span class="gf-badge gf-badge-exito">Completado</span></td>
                        </tr>

                        @foreach($movimientosHoy as $m)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($m->creado_en)->format('h:i A') }}</td>
                            <td>
                                @if($m->id_venta)
                                    <span class="gf-badge gf-badge-exito">Venta POS</span>
                                @elseif($m->tipo_movimiento == 'INGRESO')
                                    <span class="gf-badge gf-badge-exito">Ingreso</span>
                                @else
                                    <span class="gf-badge gf-badge-peligro">Egreso</span>
                                @endif
                            </td>
                            <td>
                                {{ $m->id_venta ? 'Factura #'.$m->numero_ticket : ($m->descripcion ?: $m->motivo) }}
                            </td>
                            <td>{{ $m->forma_pago ? ucfirst(strtolower($m->forma_pago)) : '—' }}</td>
                            <td class="text-end fw-semibold"
                                style="color:{{ $m->tipo_movimiento == 'INGRESO' ? '#2F6B45' : '#8C3A2A' }};">
                                {{ $m->tipo_movimiento == 'INGRESO' ? '+' : '−' }}C$ {{ number_format($m->monto, 2) }}
                            </td>
                            <td><span class="gf-badge gf-badge-exito">Completado</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@else

    <div class="gf-card" id="abrirCaja">
        @include('caja._form_abrir')
    </div>

@endif

<script>
    (function () {
        var horaEl = document.getElementById('gf-reloj-hora');
        var fechaEl = document.getElementById('gf-reloj-fecha');
        if (!horaEl || !fechaEl) return;

        var dias = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
        var meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];

        function actualizarReloj() {
            var ahora = new Date();
            horaEl.textContent = ahora.toLocaleTimeString('es-NI', {
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
            });
            fechaEl.textContent = dias[ahora.getDay()] + ', ' + ahora.getDate() + ' ' +
                meses[ahora.getMonth()] + ' ' + ahora.getFullYear();
        }

        actualizarReloj();
        setInterval(actualizarReloj, 1000);
    })();

    function gfEditarTasa(mostrar) {
        var vista = document.getElementById('gf-tasa-view');
        var form = document.getElementById('gf-tasa-form');
        if (!vista || !form) return;
        vista.style.display = mostrar ? 'none' : 'flex';
        form.style.display = mostrar ? 'flex' : 'none';
    }

    (function () {
        var buscador = document.getElementById('gf-bitacora-buscar');
        var tabla = document.getElementById('gf-bitacora-tabla');
        if (!buscador || !tabla) return;

        buscador.addEventListener('input', function () {
            var termino = buscador.value.trim().toLowerCase();
            var filas = tabla.querySelectorAll('tbody tr');
            filas.forEach(function (fila) {
                var texto = fila.textContent.toLowerCase();
                fila.style.display = (termino === '' || texto.indexOf(termino) !== -1) ? '' : 'none';
            });
        });
    })();
</script>

@endsection
