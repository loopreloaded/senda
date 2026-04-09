<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Controladores
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\UserController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\OrdenCompraController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\NotaDebitoController;
use App\Http\Controllers\ReciboController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\PedidoCotizacionController;
use App\Http\Controllers\RemitoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Página inicial → login
Route::get('/', function () {
    return redirect('/login');
});

Auth::routes();

/* ============================================================
| SECCIÓN: SECRETARÍA / ADMIN / INGENIERO
| - Gestión operativa del sistema
============================================================ */
Route::middleware(['auth', 'role:secretaria|admin|ingeniero'])->group(function () {

    /* =======================
     | CLIENTES
     ======================= */

    Route::get('clientes/{cliente}/edit', [ClienteController::class, 'edit'])
        ->name('clientes.edit');

    Route::get('clientes/buscar', [ClienteController::class, 'buscar'])
    ->name('clientes.buscar');

    Route::post('clientes/import-excel', [ClienteController::class, 'importExcel'])
    ->name('clientes.import.excel');

    Route::resource('clientes', ClienteController::class);



    /* =======================
     | FACTURAS
     ======================= */
    Route::resource('facturas', FacturaController::class);

    Route::get('facturas/pendientes', [FacturaController::class, 'pendientes'])
        ->name('facturas.pendientes');

    Route::post('facturas/{factura}/afip', [FacturaController::class, 'enviarAfip'])
        ->name('facturas.afip');

    Route::post('facturas/{id}/enviar-afip', [FacturaController::class, 'enviarAfip'])
        ->name('facturas.enviarAfip');

    Route::put('facturas/{id}/observacion', [FacturaController::class, 'guardarObservacion'])
        ->name('facturas.observacion');

    Route::get('facturas/{id}/pdf', [FacturaController::class, 'generar_pdf_factura'])
        ->name('facturas.pdf');


   /* =======================
   | cotizacion
   ======================= */

    Route::get('cotizaciones/buscar', [CotizacionController::class, 'buscar'])
        ->name('cotizaciones.buscar');

    Route::resource('cotizaciones', CotizacionController::class)
        ->parameters([
            'cotizaciones' => 'cotizacion'
        ]);

    Route::get('cotizaciones/{cotizacion}/pdf',
        [CotizacionController::class, 'pdf']
    )->name('cotizaciones.pdf');

    // Marcar como rechazada
    Route::post('cotizaciones/{cotizacion}/rechazar',
        [CotizacionController::class, 'rechazar']
    )->name('cotizaciones.rechazar');



    /* =======================
     | pedido cotizacion
     ======================= */
    // Route::resource('pedidos-cotizacion', PedidoCotizacionController::class);

    Route::get('pedidos-cotizacion/buscar', [PedidoCotizacionController::class, 'buscar'])
        ->name('pedidos-cotizacion.buscar');

    Route::resource('pedidos-cotizacion', PedidoCotizacionController::class)
    ->parameters([
        'pedidos-cotizacion' => 'pedido_cotizacion'
    ]);

    Route::post('/pedidos-cotizacion/comentarios',
        [PedidoCotizacionController::class, 'storeComentario']
    )->name('pedidos-cotizacion.comentarios.store');


    Route::patch('/pedidos-cotizacion/{id}/no-cotizo',
        [PedidoCotizacionController::class,'noCotizo'])
    ->name('pedidos-cotizacion.no-cotizo');

    /* =======================
     | NOTAS DE DÉBITO
     ======================= */
    Route::resource('notasdebito', NotaDebitoController::class);

    Route::post('notasdebito/{id}/afip', [NotaDebitoController::class, 'enviar_nd'])
        ->name('notasdebito.afip')
        ->middleware('can:enviar nota de debito afip');

    /* =======================
     | ÓRDENES DE COMPRA
     ======================= */
    Route::resource('ordenes', OrdenCompraController::class);

    Route::get('ordenes/{orden}/pdf', [OrdenCompraController::class, 'orden_pdf'])
        ->name('ordenes.pdf');

    Route::post('ordenes/observaciones/update', [OrdenCompraController::class, 'updateObservaciones'])
        ->name('ordenes.observaciones.update')
        ->middleware('role:admin|ingeniero');

    /* =======================
     | RECIBOS
     ======================= */
    Route::resource('recibos', ReciboController::class);

    Route::post('recibos/{id}/aprobar', [ReciboController::class, 'aprobar'])
        ->name('recibos.aprobar');

    Route::get('recibos/{recibo}/pdf', [ReciboController::class, 'generar_pdf_recibo'])
        ->name('recibos.pdf');

    /* =======================
    | REMITOS
    ======================= */

    Route::resource('remitos', RemitoController::class);

    Route::get('remitos/{remito}/pdf',
        [RemitoController::class, 'pdf']
    )->name('remitos.pdf');

    Route::post('remitos/{remito}/confirmar',
        [RemitoController::class, 'confirmar']
    )->name('remitos.confirmar');


});


/* ============================================================
| SECCIÓN: DESARROLLADOR
| - Recuperación de datos y herramientas avanzadas
| ============================================================ */
Route::middleware(['auth', 'role:desarrollador'])->group(function () {
    Route::post('clientes/{id}/restore', [ClienteController::class, 'restore'])
        ->name('clientes.restore');
});


/* ============================================================
| SECCIÓN: INGENIEROS
| - Aprobaciones
============================================================ */
Route::middleware(['auth', 'role:ingeniero'])->group(function () {

    Route::post('facturas/{factura}/aprobar', [FacturaController::class, 'aprobar'])
        ->name('facturas.aprobar');

    Route::post('ordenes/{orden}/aprobar', [OrdenCompraController::class, 'aprobar'])
        ->name('ordenes.aprobar');
});


/* ============================================================
| SECCIÓN: ADMINISTRADOR
| - Configuración y usuarios
============================================================ */
Route::middleware(['auth', 'role:admin'])->group(function () {

    // Route::resource('usuarios', UserController::class);

    Route::get('configuracion/empresa', [ConfiguracionController::class, 'empresa'])
        ->name('configuracion.empresa');
});


/* ============================================================
| ACCESO GENERAL
============================================================ */
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.index');
    })->name('dashboard');
});
