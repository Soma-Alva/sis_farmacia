<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\SessionTimeout;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CategoriaController;

Route::resource('productos', ProductoController::class)
    ->middleware(['auth']);


Route::middleware(['auth', SessionTimeout::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::get('/', [AuthController::class, 'showLogin'])->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


    Route::middleware(['auth', 'role:ADMINISTRADOR'])->group(function () {
    Route::get('/admin', function () {
        return "Panel Admin";
    });
});

});

Route::resource('clientes', ClienteController::class);

Route::resource('usuarios', UsuarioController::class);
    Route::get(
    '/usuarios/{id}/password',
    [UsuarioController::class, 'passwordForm']
    )->name('usuarios.password');

    Route::put(
        '/usuarios/{id}/password',
        [UsuarioController::class, 'updatePassword']
    )->name('usuarios.password.update');

Route::resource('productos', ProductoController::class);

Route::get('/ventas', [VentaController::class, 'index'])
    ->name('ventas.index')
    ->middleware('auth');

Route::post('/ventas', [VentaController::class, 'procesar'])
    ->name('ventas.procesar')
    ->middleware('auth');

Route::get('/ventas/historial', [VentaController::class, 'historial'])
    ->name('ventas.historial')
    ->middleware('auth');

Route::get('/ventas/{id}', [VentaController::class, 'show'])
    ->name('ventas.show')
    ->middleware('auth');

Route::put('/ventas/{id}/anular', [VentaController::class, 'anular'])
    ->name('ventas.anular')
    ->middleware('auth');

Route::middleware('auth')->group(function () {

    Route::get('/caja', [CajaController::class,'index'])
        ->name('caja.index');

    Route::post('/caja/abrir', [CajaController::class,'abrir'])
        ->name('caja.abrir');

});

//Route::get('/compras', [CompraController::class, 'index']);
Route::resource('compras', CompraController::class);
Route::post('/compras/{id}/aprobar', [CompraController::class, 'aprobar'])->name('compras.aprobar');

Route::resource('proveedores', ProveedorController::class)
    ->parameters(['proveedores' => 'proveedor'])
    ->except('show')
    ->middleware('auth');

Route::resource('categorias', CategoriaController::class)
    ->parameters(['categorias' => 'categoria'])
    ->except('show')
    ->middleware('auth');

//Caja
    Route::get('/caja/ingreso', [CajaController::class,'ingreso'])
        ->name('caja.ingreso');

        Route::post('/caja/ingreso', [CajaController::class,'guardarIngreso'])
            ->name('caja.ingreso.guardar');

    Route::get('/caja/egreso', [CajaController::class, 'egreso'])
        ->name('caja.egreso');

        Route::post('/caja/egreso', [CajaController::class, 'guardarEgreso'])
            ->name('caja.egreso.guardar');

    Route::get('/caja/movimientos', [CajaController::class, 'movimientos'])
        ->name('caja.movimientos');
        Route::get('/caja/arqueo', [CajaController::class,'arqueo'])
    ->name('caja.arqueo');

    Route::post('/caja/arqueo', [CajaController::class,'guardarArqueo'])
        ->name('caja.arqueo.guardar');

    Route::post('/caja/cerrar', [CajaController::class, 'cerrarCaja'])
        ->name('caja.cerrar');

    Route::middleware('auth')->group(function () {
        Route::get('/backups', [BackupController::class, 'index'])
            ->name('backups.index');

        Route::post('/backups/create', [BackupController::class, 'create'])
            ->name('backups.create');

        Route::post('/backups/restore', [BackupController::class, 'restore'])
            ->name('backups.restore');

        Route::get('/backups/download/{file}', [BackupController::class, 'download'])
            ->name('backups.download');
    });
