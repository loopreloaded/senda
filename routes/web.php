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
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\RemitoController;
use App\Http\Controllers\OrdenPagoController;

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

    Route::post('/facturas/{id}/afip', [FacturaController::class, 'enviarAfip'])->name('facturas.afip');
    Route::get('/api/remitos/cliente/{cliente_id}', [RemitoController::class, 'getByCliente'])->name('api.remitos.cliente');

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

    Route::get('cotizaciones/{cotizacion}/json-items', [CotizacionController::class, 'jsonItems'])
        ->name('cotizaciones.jsonItems');

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
     | pedidos
     ======================= */

    Route::get('pedidos/buscar', [PedidoController::class, 'buscar'])
        ->name('pedidos.buscar');

    Route::resource('pedidos', PedidoController::class)
    ->parameters([
        'pedidos' => 'pedido'
    ]);

    Route::post('/pedidos/comentarios',
        [PedidoController::class, 'storeComentario']
    )->name('pedidos.comentarios.store');


    Route::patch('/pedidos/{id}/no-cotizo',
        [PedidoController::class,'noCotizo'])
    ->name('pedidos.no-cotizo');

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

    Route::get('ordenes/{orden}/json-items', [OrdenCompraController::class, 'jsonItems'])
        ->name('ordenes.jsonItems');

    Route::get('ordenes/cliente/{clienteId}', [OrdenCompraController::class, 'getByCliente'])
        ->name('ordenes.byCliente');

    Route::post('ordenes/observaciones/update', [OrdenCompraController::class, 'updateObservaciones'])
        ->name('ordenes.observaciones.update')
        ->middleware('role:admin|ingeniero');

    /* =======================
     | RECIBOS
     ======================= */
    Route::resource('recibos', ReciboController::class);

    Route::post('recibos/{recibo}/aprobar', [ReciboController::class, 'aprobar'])
        ->name('recibos.aprobar');

    Route::get('recibos/{recibo}/pdf', [ReciboController::class, 'generar_pdf_recibo'])
        ->name('recibos.pdf');
    Route::get('recibos/ordenes-pago/{cliente_id}', [ReciboController::class, 'getOrdenesPago'])
        ->name('recibos.ops');

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

    /* =======================
     | ÓRDENES DE PAGO
     ======================= */
    Route::resource('ordenes-pago', OrdenPagoController::class)
        ->parameters([
            'ordenes-pago' => 'ordenPago'
        ]);
    Route::post('ordenes-pago/{ordenPago}/anular', [OrdenPagoController::class, 'anular'])->name('ordenes-pago.anular');
    Route::get('ordenes-pago/facturas/{cliente_id}', [OrdenPagoController::class, 'getFacturas'])->name('ordenes-pago.facturas');


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
