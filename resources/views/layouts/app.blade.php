<!DOCTYPE html>
<html lang="es">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grey Farmacia</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root{
            --verde-oscuro:#2F4F3E;
            --verde-oscuro-2:#274335;
            --verde-medio:#4B7A61;
            --verde-claro:#A9CDB4;
            --verde-pastel:#EAF2EC;
            --crema:#F7F5EF;
            --carbon:#242420;
            --blanco:#FFFFFF;
        }

        body{
            background:var(--verde-pastel);
            font-family:'Inter',sans-serif;
            color:var(--carbon);
        }

        /* ---------- Sidebar ---------- */
        .gf-sidebar{
            width:250px;
            flex:0 0 250px;
            position:fixed;
            top:0;
            left:0;
            height:100vh;
            overflow-y:auto;
            background:linear-gradient(190deg, var(--verde-oscuro) 0%, var(--verde-medio) 100%);
            padding:20px 16px;
            display:flex;
            flex-direction:column;
            z-index:20;
        }
        .gf-sidebar::-webkit-scrollbar{ width:6px; }
        .gf-sidebar::-webkit-scrollbar-thumb{ background:rgba(255,255,255,0.18); border-radius:10px; }

        .gf-main{
            margin-left:250px;
            width:calc(100% - 250px);
            min-height:100vh;
        }

        .gf-logo-badge{
            display:flex;
            align-items:center;
            justify-content:center;
            margin-bottom:15px;
        }
        .gf-logo-badge img{
            width: 65px;
            height: 65px;
        }

        .gf-nav{
            list-style:none;
            padding:0;
            margin:0 0 8px;
        }
        .gf-nav-section{
            font-size:10.5px;
            letter-spacing:.08em;
            color:rgba(255,255,255,0.45);
            text-transform:uppercase;
            margin:18px 10px 8px;
        }
        .gf-nav li{ margin-bottom:4px; }
        .gf-nav a{
            display:flex;
            align-items:center;
            gap:11px;
            padding:10px 14px;
            border-radius:12px;
            color:rgba(255,255,255,0.78);
            text-decoration:none;
            font-size:14px;
            font-weight:500;
            transition:background .15s ease, color .15s ease;
        }
        .gf-nav a i{ font-size:15px; width:18px; text-align:center; }
        .gf-nav a:hover{
            background:rgba(255,255,255,0.08);
            color:var(--blanco);
        }
        .gf-nav a.activo{
            background:rgba(255,255,255,0.16);
            color:var(--blanco);
            font-weight:600;
        }

        .gf-sidebar-footer{
            margin-top:auto;
            padding-top:14px;
        }
        .gf-btn-logout{
            width:100%;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            background:rgba(255,255,255,0.1);
            border:none;
            color:rgba(255,255,255,0.85);
            padding:10px 0;
            border-radius:12px;
            font-size:13.5px;
            font-weight:500;
            cursor:pointer;
            transition:background .15s ease;
        }
        .gf-btn-logout:hover{ background:rgba(216,96,76,0.65); color:var(--blanco); }

        /* ---------- Topbar ---------- */
        .gf-topbar{
            background:var(--blanco);
            border-radius:18px;
            padding:16px 22px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:16px;
            flex-wrap:wrap;
            box-shadow:0 2px 10px rgba(47,79,62,0.06);
            margin-bottom:20px;
        }
        .gf-topbar-saludo h6{
            margin:0;
            font-size:12px;
            color:rgba(36,36,32,0.5);
            font-weight:500;
        }
        .gf-topbar-saludo strong{
            display:block;
            font-size:17px;
            color:var(--verde-oscuro);
        }

        .gf-search{
            flex:1 1 320px;
            max-width:420px;
            position:relative;
        }
        .gf-search i{
            position:absolute;
            left:14px;
            top:50%;
            transform:translateY(-50%);
            color:rgba(36,36,32,0.35);
            font-size:14px;
        }
        .gf-search input{
            width:100%;
            background:var(--verde-pastel);
            border:none;
            border-radius:999px;
            padding:10px 14px 10px 36px;
            font-size:13.5px;
            color:var(--carbon);
            outline:none;
        }
        .gf-search input::placeholder{ color:rgba(36,36,32,0.4); }

        .gf-topbar-right{
            display:flex;
            align-items:center;
            gap:14px;
        }
        .gf-bell{
            width:38px;
            height:38px;
            border-radius:50%;
            background:var(--verde-pastel);
            display:flex;
            align-items:center;
            justify-content:center;
            color:var(--verde-oscuro);
            font-size:15px;
            position:relative;
        }
        .gf-avatar{
            width:38px;
            height:38px;
            border-radius:50%;
            background:var(--verde-oscuro);
            color:var(--blanco);
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:600;
            font-size:14px;
        }

        .gf-content{
            padding:20px 26px 32px;
        }

        /* Tarjetas genéricas reutilizables en páginas internas */
        .gf-card{
            background:var(--blanco);
            border-radius:18px;
            box-shadow:0 2px 10px rgba(47,79,62,0.06);
            border:none;
        }
        .gf-card-header{
            font-family:'Fraunces',serif;
            font-weight:600;
            font-size:16px;
            color:var(--verde-oscuro);
            padding:18px 20px 0;
        }
        .gf-page-title{
            font-family:'Fraunces',serif;
            font-weight:600;
            color:var(--verde-oscuro);
            margin-bottom:0;
        }
        .gf-page-subtitle{
            color:rgba(36,36,32,0.55);
            font-size:13.5px;
            margin-bottom:0;
        }

        /* ---------- Sistema de tabla unificado (gf-table) ---------- */
        .gf-table{
            width:100%;
            margin-bottom:0;
        }
        .gf-table thead th{
            background:var(--verde-pastel);
            color:var(--verde-oscuro);
            font-size:11.5px;
            font-weight:600;
            text-transform:uppercase;
            letter-spacing:.03em;
            border:none;
            padding:11px 14px;
            white-space:nowrap;
        }
        .gf-table thead th:first-child{ border-top-left-radius:10px; border-bottom-left-radius:10px; }
        .gf-table thead th:last-child{ border-top-right-radius:10px; border-bottom-right-radius:10px; }
        .gf-table tbody td{
            vertical-align:middle;
            font-size:13.5px;
            padding:12px 14px;
            border-bottom:1px solid rgba(47,79,62,0.08);
            color:var(--carbon);
        }
        .gf-table tbody tr:last-child td{ border-bottom:none; }
        .gf-table tbody tr{ transition:background .12s ease; }
        .gf-table tbody tr:hover{ background:rgba(169,205,180,0.12); }
        .gf-table tbody tr.gf-row-warning{ background:rgba(201,138,59,0.08); }
        .gf-table tbody tr.gf-row-danger{ background:rgba(140,58,42,0.07); }
        .gf-subtext{
            display:block;
            font-size:11.5px;
            color:rgba(36,36,32,0.45);
            margin-top:1px;
        }

        /* Insignias de estado/cantidad, un único set de colores en toda la app */
        .gf-badge{
            display:inline-block;
            padding:3px 11px;
            border-radius:999px;
            font-size:11.5px;
            font-weight:600;
            white-space:nowrap;
        }
        .gf-badge-exito{ background:#E3F1E7; color:#2F6B45; }
        .gf-badge-neutro{ background:var(--verde-pastel); color:var(--verde-oscuro); }
        .gf-badge-alerta{ background:#FBF3DE; color:#8A6A1F; }
        .gf-badge-peligro{ background:#FBE9E6; color:#8C3A2A; }
        .gf-badge-apagado{ background:#EDEDEA; color:#6B6B66; }

        /* Botones de acción pequeños, mismo estilo en toda la app */
        .gf-btn-soft{
            display:inline-block;
            border:none;
            border-radius:8px;
            padding:5px 12px;
            font-size:12.5px;
            font-weight:500;
            text-decoration:none;
            cursor:pointer;
            transition:opacity .12s ease;
        }
        .gf-btn-soft:hover{ opacity:0.8; }
        .gf-btn-neutro{ background:var(--verde-pastel); color:var(--verde-oscuro); }
        .gf-btn-exito{ background:#E3F1E7; color:#2F6B45; }
        .gf-btn-peligro{ background:#FBE9E6; color:#8C3A2A; }
        .gf-btn-apagado{ background:#EDEDEA; color:#6B6B66; }

        .gf-filtros{
            background:var(--blanco);
            border-radius:18px;
            box-shadow:0 2px 10px rgba(47,79,62,0.06);
            padding:18px 20px;
            margin-bottom:16px;
        }
        .gf-filtros .form-label{ font-size:12px; font-weight:500; color:rgba(36,36,32,0.6); }
        .gf-filtros .form-control, .gf-filtros .form-select{
            border-radius:10px;
            border:1px solid rgba(47,79,62,0.15);
            font-size:13.5px;
        }
        .gf-filtros .form-control:focus, .gf-filtros .form-select:focus{
            border-color:var(--verde-medio);
            box-shadow:0 0 0 .2rem rgba(75,122,97,0.15);
        }

        /* Tarjetas de número grande (dashboard, backups, etc.) */
        .gf-stat{
            background:var(--blanco);
            border-radius:18px;
            padding:20px;
            box-shadow:0 2px 10px rgba(47,79,62,0.06);
            height:100%;
        }
        .gf-stat .icono{
            width:38px;
            height:38px;
            border-radius:10px;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            font-size:16px;
            margin-bottom:14px;
        }
        .gf-stat .etiqueta{
            font-size:13px;
            color:rgba(36,36,32,0.55);
            margin-bottom:2px;
        }
        .gf-stat .valor{
            font-size:26px;
            font-weight:700;
            color:var(--carbon);
        }

        .gf-banner{
            border-radius:16px;
            padding:14px 18px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:12px;
            margin-bottom:16px;
            font-size:14px;
        }
        .gf-banner.danger{ background:#FBE9E6; color:#8C3A2A; }
        .gf-banner.warning{ background:#FBF3DE; color:#8A6A1F; }
        .gf-banner a{
            white-space:nowrap;
            background:rgba(255,255,255,0.6);
            border-radius:999px;
            padding:6px 14px;
            font-size:13px;
            font-weight:600;
            text-decoration:none;
            color:inherit;
        }

        .gf-btn-primario{
            background:var(--verde-oscuro);
            color:#fff;
            border:none;
            border-radius:10px;
            font-size:13.5px;
            padding:9px 16px;
            text-decoration:none;
            display:inline-flex;
            align-items:center;
            gap:6px;
        }
        .gf-btn-primario:hover{ background:var(--verde-oscuro-2); color:#fff; }
    </style>
</head>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );

    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<body>

<div class="d-flex">

    <!-- SIDEBAR -->
    <div class="gf-sidebar">

        <div class="gf-logo-badge">
            <img src="{{ asset('images/favicon.png') }}" alt="Grey Farmacia">
        </div>

        <ul class="gf-nav">
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'activo' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
        </ul>

        @if(auth()->user()->rol->nombre == 'ADMINISTRADOR')
        <div class="gf-nav-section">Inventario</div>
        <ul class="gf-nav">
            <li>
                <a href="{{ route('productos.index') }}" class="{{ request()->routeIs('productos.*') ? 'activo' : '' }}">
                    <i class="bi bi-box-seam"></i> Productos
                </a>
            </li>
            <li>
                <a href="{{ route('categorias.index') }}" class="{{ request()->routeIs('categorias.*') ? 'activo' : '' }}">
                    <i class="bi bi-tags"></i> Categorías
                </a>
            </li>
            <li>
                <a href="{{ route('compras.index') }}" class="{{ request()->routeIs('compras.*') ? 'activo' : '' }}">
                    <i class="bi bi-receipt"></i> Compras
                </a>
            </li>
            <li>
                <a href="{{ route('lotes.index') }}" class="{{ request()->routeIs('lotes.*') ? 'activo' : '' }}">
                    <i class="bi bi-upc-scan"></i> Lotes
                </a>
            </li>
            <li>
                <a href="{{ route('proveedores.index') }}" class="{{ request()->routeIs('proveedores.*') ? 'activo' : '' }}">
                    <i class="bi bi-truck"></i> Proveedores
                </a>
            </li>
        </ul>
        @endif

        <div class="gf-nav-section">Personas</div>
        <ul class="gf-nav">
            @if(auth()->user()->rol->nombre == 'ADMINISTRADOR')
            <li>
                <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'activo' : '' }}">
                    <i class="bi bi-person-plus"></i> Usuarios
                </a>
            </li>
            @endif
            <li>
                <a href="{{ route('clientes.index') }}" class="{{ request()->routeIs('clientes.*') ? 'activo' : '' }}">
                    <i class="bi bi-people"></i> Clientes
                </a>
            </li>
        </ul>

        <div class="gf-nav-section">Operación</div>
        <ul class="gf-nav">
            <li>
                <a href="{{ route('ventas.index') }}" class="{{ request()->routeIs('ventas.*') ? 'activo' : '' }}">
                    <i class="bi bi-cart-check"></i> Ventas
                </a>
            </li>
            <li>
                <a href="{{ route('caja.index') }}" class="{{ request()->routeIs('caja.*') ? 'activo' : '' }}">
                    <i class="fas fa-cash-register"></i> Caja
                </a>
            </li>
            <li>
                <a href="{{ route('backups.index') }}" class="{{ request()->routeIs('backups.*') ? 'activo' : '' }}">
                    <i class="bi bi-shield-check"></i> Backups
                </a>
            </li>
        </ul>

        <div class="gf-sidebar-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="gf-btn-logout">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </button>
            </form>
        </div>

    </div>

    <!-- CONTENIDO -->
    <div class="gf-main">

        <div class="gf-content">

            <div class="gf-topbar">

                <div class="gf-topbar-saludo">
                    <h6>Bienvenido/a</h6>
                    <strong>{{ optional(auth()->user())->nombre_completo }}</strong>
                </div>

                @php
                    $gfBuscables = [
                        'productos' => 'Buscar producto...',
                        'compras'   => 'Buscar compra...',
                        'clientes'  => 'Buscar cliente...',
                    ];
                    $gfSeccionBusqueda = collect($gfBuscables)->first(fn($placeholder, $seccion) => request()->routeIs("$seccion.*"));
                @endphp

                @if($gfSeccionBusqueda)
                    <div class="gf-search">
                        <i class="bi bi-search"></i>
                        <input type="text" id="gf-buscador-tabla" placeholder="{{ $gfSeccionBusqueda }}" autocomplete="off">
                    </div>
                @endif

                <div class="gf-topbar-right">
                    <div class="gf-bell"><i class="bi bi-bell"></i></div>
                    <div class="gf-avatar">
                        {{ strtoupper(substr(optional(auth()->user())->nombre_completo ?? 'U', 0, 1)) }}
                    </div>
                </div>

            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')

        </div>

    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    (function () {
        var buscador = document.getElementById('gf-buscador-tabla');
        if (!buscador) return;

        var tabla = document.querySelector('.gf-content table.gf-table');
        if (!tabla) return;

        var filas = tabla.querySelectorAll('tbody tr');

        buscador.addEventListener('input', function () {
            var termino = buscador.value.trim().toLowerCase();

            filas.forEach(function (fila) {
                var texto = fila.textContent.toLowerCase();
                fila.style.display = texto.includes(termino) ? '' : 'none';
            });
        });
    })();
</script>

@stack('scripts')
</body>
</html>
