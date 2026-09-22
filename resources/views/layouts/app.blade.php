<!DOCTYPE html>
<html lang="es">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmacia Grey</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo.ico') }}">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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

<body class="bg-light">

<div class="d-flex">

    <!-- SIDEBAR -->
    <div class="bg-dark text-white p-3 shadow"
         style="width:250px; min-height:100vh;">

        <h3 class="text-center mb-3">
            <i class="bi bi-capsule-pill"></i> Farmacia
        </h3>

        <div class="text-center">
            <p class="mb-0 fw-bold">
                {{ optional(auth()->user())->nombre_completo }}
            </p>

            <small class="text-secondary">
                {{ optional(optional(auth()->user())->rol)->nombre }}
            </small>
        </div>

        <hr>

        <ul class="nav flex-column">

            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('dashboard') ? 'fw-bold bg-secondary rounded' : '' }}"
                   href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>

            <!-- Productos -->
            @if(auth()->user()->rol->nombre == 'ADMINISTRADOR')
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('productos.*') ? 'fw-bold bg-secondary rounded' : '' }}"
                   href="{{ route('productos.index') }}">
                    <i class="bi bi-box-seam"></i>
                    Productos
                </a>
            </li>
            @endif

            <!-- Categorías -->
            @if(auth()->user()->rol->nombre == 'ADMINISTRADOR')
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('categorias.*') ? 'fw-bold bg-secondary rounded' : '' }}"
                   href="{{ route('categorias.index') }}">
                    <i class="bi bi-tags"></i>
                    Categorías
                </a>
            </li>
            @endif

             <!-- Compras -->
            @if(auth()->user()->rol->nombre == 'ADMINISTRADOR')
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('compras.*') ? 'fw-bold bg-secondary rounded' : '' }}"
                   href="{{ route('compras.index') }}">
                    <i class="bi bi-box-seam"></i>
                    Compras
                </a>
            </li>
            @endif

            <!-- Proveedores -->
            @if(auth()->user()->rol->nombre == 'ADMINISTRADOR')
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('proveedores.*') ? 'fw-bold bg-secondary rounded' : '' }}"
                   href="{{ route('proveedores.index') }}">
                    <i class="bi bi-truck"></i>
                    Proveedores
                </a>
            </li>
            @endif

           <hr>
           <h1></h1>



            <!-- Usuarios -->
            @if(auth()->user()->rol->nombre == 'ADMINISTRADOR')
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('usuarios.*') ? 'fw-bold bg-secondary rounded' : '' }}"
                    href="{{ route('usuarios.index') }}">
                        <i class="bi bi-person-plus"></i>
                        Usuarios
                    </a>
                </li>
            @endif

            <!-- Clientes -->
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('clientes.*') ? 'fw-bold bg-secondary rounded' : '' }}"
                   href="{{ route('clientes.index') }}">
                    <i class="bi bi-people"></i>
                    Clientes
                </a>
            </li>

            <hr>
            <h1></h1>

            <!-- Venta -->
            <li class="nav-item">
                <a href="{{ route('ventas.index') }}"
                class="nav-link text-white {{ request()->routeIs('ventas.*') ? 'bg-secondary rounded' : '' }}">

                    <i class="bi bi-cart-check"></i>
                    Ventas
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('caja.index') }}"
                class="nav-link text-white {{ request()->routeIs('caja.*') ? 'bg-secondary rounded' : '' }}">

                     <i class="fas fa-cash-register"></i>
                    Caja
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('backups.index') }}"
                class="nav-link text-white {{ request()->routeIs('backups.*') ? 'bg-secondary rounded' : '' }}">
                    <i class="bi bi-shield-check"></i>
                    Backups
                </a>
            </li>

        </ul>

        <hr>

        <form action="{{ route('logout') }}" method="POST">
            @csrf

            <button class="btn btn-danger w-100">
                <i class="bi bi-box-arrow-right"></i>
                Cerrar sesión
            </button>
        </form>

    </div>

    <!-- CONTENIDO -->
    <div class="p-4 w-100">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')

    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
