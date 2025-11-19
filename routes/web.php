<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Controladores principales del sistema
use App\Http\Controllers\UserController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\OrdenCompraController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\NotaDebitoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Aquí se registran todas las rutas del sistema.
| Se agrupan por roles y por módulo.
|--------------------------------------------------------------------------
*/

// Página principal → redirige al login
Route::get('/', function () {
    return redirect('/login');
});

Auth::routes();

/* ============================================================
| SECCIÓN: SECRETARÍA
| - Puede crear facturas, órdenes, cargar clientes, enviar a AFIP
============================================================ */
Route::middleware(['auth', 'role:secretaria|admin'])->group(function() {

    // FACTURAS
    Route::resource('facturas', FacturaController::class);
    Route::get('facturas/pendientes', [FacturaController::class, 'pendientes'])->name('facturas.pendientes');
    // Route::post('facturas/{factura}/afip', [FacturaController::class, 'afip'])->name('facturas.afip');
    // Route::post('facturas/{factura}/afip', [FacturaController::class, 'enviarAfip'])
    // ->name('facturas.afip');
    Route::post('facturas/{factura}/afip', [FacturaController::class, 'enviarAfip'])->name('facturas.afip');


    // Route::post('facturas/afip', [FacturaController::class, 'afip'])->name('facturas.afip');
    // Route::post('facturas/{id}/afip', [FacturaController::class, 'enviar_afip'])->name('facturas.enviar_afip');

    // routes/web.php
    Route::post('/facturas/{id}/enviar-afip', [FacturaController::class, 'enviarAfip'])
        ->name('facturas.enviarAfip');

    //
    Route::resource('notasdebito', NotaDebitoController::class);

    Route::post('notasdebito/{id}/afip', [NotaDebitoController::class, 'enviar_nd'])
        ->name('notasdebito.afip')
        ->middleware('can:enviar nota de debito afip');


    // ÓRDENES DE COMPRA
    Route::resource('ordenes', OrdenCompraController::class);
    Route::get('ordenes/{orden}/pdf', [OrdenCompraController::class, 'pdf'])->name('ordenes.pdf');

    // CLIENTES
    Route::resource('clientes', ClienteController::class);

});


/* ============================================================
| SECCIÓN: INGENIEROS
| - Pueden revisar y aprobar facturas / órdenes pendientes
============================================================ */
Route::middleware(['auth', 'role:ingeniero'])->group(function() {

    // Aprobar facturas
    Route::post('facturas/{factura}/aprobar', [FacturaController::class, 'aprobar'])->name('facturas.aprobar');

    // Aprobar órdenes de compra (opcional)
    Route::post('ordenes/{orden}/aprobar', [OrdenCompraController::class, 'aprobar'])->name('ordenes.aprobar');
});


/* ============================================================
| SECCIÓN: ADMINISTRADOR
| - Puede gestionar usuarios, roles, permisos, reportes
============================================================ */
Route::middleware(['auth', 'role:admin'])->group(function() {

    // USUARIOS DEL SISTEMA
    Route::resource('usuarios', UserController::class);
    // Configuración de empresa
    Route::get('configuracion/empresa', [ConfiguracionController::class, 'empresa'])
        ->name('configuracion.empresa');

});


/* ============================================================
| ACCESO GENERAL (usuarios autenticados)
| - Todos los roles pueden ver su panel e información personal
============================================================ */
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.index');
    })->name('dashboard');
});
