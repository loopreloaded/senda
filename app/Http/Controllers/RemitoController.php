<?php

namespace App\Http\Controllers;

use App\Models\Remito;
use App\Models\RemitoItem;
use App\Models\Cliente;
use App\Models\OrdenCompra;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

            // items
            'items.*.articulo' => 'required',
            'items.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {

            $remito = Remito::create([
                'numero_remito' => $request->numero_remito,
                'fecha' => $request->fecha,
                'id_cliente' => $request->id_cliente,
                'id_orden_compra' => $request->id_orden_compra,
                'id_factura' => $request->id_factura,
                'estado' => 'Emitido',

                // nuevos
                'condicion_venta' => $request->condicion_venta,
                'transportista' => $request->transportista,
                'domicilio_transportista' => $request->domicilio_transportista,
                'iva_transportista' => $request->iva_transportista,
                'cuit_transportista' => $request->cuit_transportista,
                'observacion' => $request->observacion,
                'cai' => $request->cai,
                'cai_vto' => $request->cai_vto,

                'comentarios' => $request->observacion,
            ]);

            // 🔥 Guardar items
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    RemitoItem::create([
                        'id_remito' => $remito->id_remito,
                        'articulo' => $item['articulo'],
                        'cantidad' => $item['cantidad'],
                        'descripcion' => $item['descripcion'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('remitos.index')
                ->with('success', 'Remito creado correctamente');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error al crear el remito: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalle
     */
    public function show(Remito $remito)
    {
        $remito->load('items');

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

        $remito->load('items');

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

            // items
            'items.*.articulo' => 'required',
            'items.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {

            $remito->update([
                'numero_remito' => $request->numero_remito,
                'fecha' => $request->fecha,
                'id_cliente' => $request->id_cliente,
                'id_orden_compra' => $request->id_orden_compra,
                'id_factura' => $request->id_factura,

                // nuevos
                'condicion_venta' => $request->condicion_venta,
                'transportista' => $request->transportista,
                'domicilio_transportista' => $request->domicilio_transportista,
                'iva_transportista' => $request->iva_transportista,
                'cuit_transportista' => $request->cuit_transportista,
                'observacion' => $request->observacion,
                'cai' => $request->cai,
                'cai_vto' => $request->cai_vto,

                'comentarios' => $request->comentarios,
            ]);

            // 🔥 Sync items (simple: borrar y recrear)
            RemitoItem::where('id_remito', $remito->id_remito)->delete();

            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    RemitoItem::create([
                        'id_remito' => $remito->id_remito,
                        'articulo' => $item['articulo'],
                        'cantidad' => $item['cantidad'],
                        'descripcion' => $item['descripcion'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('remitos.index')
                ->with('success', 'Remito actualizado correctamente');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Anular
     */
    public function destroy(Remito $remito)
    {
        $remito->update([
            'estado' => 'Anulado'
        ]);

        return redirect()
            ->route('remitos.index')
            ->with('success', 'Remito anulado correctamente');
    }

    /**
     * Confirmar
     */
    public function confirmar(Remito $remito)
    {
        $remito->update([
            'estado' => 'Confirmado'
        ]);

        return redirect()
            ->route('remitos.index')
            ->with('success', 'Remito confirmado');
    }

    /**
     * PDF
     */
    public function pdf(Remito $remito)
    {
        $remito->load(['cliente', 'items']);

        return view('admin.remitos.pdf', compact('remito'));
    }
}
