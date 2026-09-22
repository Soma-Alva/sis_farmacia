<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Farmacia</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        
</head>

<body class="bg-light">

    <div class="container mt-4">

        <h2>Dashboard Farmacia</h2>

        <div class="row mt-4">

            <div class="col-md-4">
                <div class="card bg-success text-white p-3">
                    <h5>Ventas Hoy</h5>
                    <h3>C$ {{ $ventasHoy }}</h3>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-info text-white p-3">
                    <h5>Total Productos</h5>
                    <h3>{{ $totalProductos }}</h3>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-danger text-white p-3">
                    <h5>Stock Bajo</h5>
                    <h3>{{ $stockBajo }}</h3>
                </div>
            </div>

        </div>

        </div>
        <h4 class="mt-5">Últimas Ventas</h4>

        <table class="table table-bordered mt-2">
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Total</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ultimasVentas as $v)
                <tr>
                    <td>{{ $v->numero_ticket }}</td>
                    <td>{{ $v->total }}</td>
                    <td>{{ $v->fecha_venta }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-danger mt-3">Cerrar sesión</button>
        </form>

    </div>
</body>
</html>
