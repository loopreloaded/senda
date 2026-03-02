<?php

namespace App\Http\Controllers;

use App\Models\Remito;
use App\Models\Cliente;
use App\Models\OrdenCompra;
use App\Models\Factura;
use Illuminate\Http\Request;

class RemitoController extends Controller
{
    /**
     * Listado
     */
    public function index()
    {
        $remitos = Remito::with(['cliente', 'ordenCompra', 'factura'])
            ->latest()
            ->paginate(15);

        return view('admin.remitos.index', compact('remitos'));
    }

    /**
     * Formulario creación
     */
    public function create()
    {
        $clientes = Cliente::all();
        $ordenes = OrdenCompra::all();
        $facturas = Factura::all();

        return view('admin.remitos.create', compact('clientes', 'ordenes', 'facturas'));
    }

    /**
     * Guardar
     */
    public function store(Request $request)
    {
        $request->validate([
            'numero_remito' => 'required|unique:remitos,numero_remito',
            'fecha' => 'required|date',
            'id_cliente' => 'required',
        ]);

        Remito::create([
            'numero_remito' => $request->numero_remito,
            'fecha' => $request->fecha,
            'id_cliente' => $request->id_cliente,
            'id_orden_compra' => $request->id_orden_compra,
            'id_factura' => $request->id_factura,
            'estado' => 'emitido',
            'comentarios' => $request->comentarios,
        ]);

        return redirect()
            ->route('remitos.index')
            ->with('success', 'Remito creado correctamente');
    }

    /**
     * Ver detalle / comentar
     */
    public function show(Remito $remito)
    {
        return view('admin.remitos.show', compact('remito'));
    }

    /**
     * Editar
     */
    public function edit(Remito $remito)
    {
        $clientes = Cliente::all();
        $ordenes = OrdenCompra::all();
        $facturas = Factura::all();

        return view('admin.remitos.edit', compact('remito', 'clientes', 'ordenes', 'facturas'));
    }

    /**
     * Actualizar
     */
    public function update(Request $request, Remito $remito)
    {
        $request->validate([
            'numero_remito' => 'required|unique:remitos,numero_remito,' . $remito->id_remito . ',id_remito',
            'fecha' => 'required|date',
        ]);

        $remito->update($request->all());

        return redirect()
            ->route('remitos.index')
            ->with('success', 'Remito actualizado correctamente');
    }

    /**
     * Anular (cambia estado)
     */
    public function destroy(Remito $remito)
    {
        $remito->update([
            'estado' => 'anulado'
        ]);

        return redirect()
            ->route('remitos.index')
            ->with('success', 'Remito anulado correctamente');
    }

    /**
     * Confirmar remito
     */
    public function confirmar(Remito $remito)
    {
        $remito->update([
            'estado' => 'confirmado'
        ]);

        return redirect()
            ->route('remitos.index')
            ->with('success', 'Remito confirmado');
    }

    /**
     * Generar PDF
     */
    public function pdf(Remito $remito)
    {
        return view('admin.remitos.pdf', compact('remito'));

        // Si luego usás dompdf:
        // $pdf = \PDF::loadView('admin.remitos.pdf', compact('remito'));
        // return $pdf->stream('remito_'.$remito->numero_remito.'.pdf');
    }
}
