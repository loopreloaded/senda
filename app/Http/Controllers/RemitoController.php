<?php

namespace App\Http\Controllers;

use App\Models\Remito;
use App\Models\RemitoItem;
use App\Models\Cliente;
use App\Models\OrdenCompra;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RemitoController extends Controller
{
    /**
     * Listado
     */
    public function index()
    {
        $remitos = Remito::with(['cliente', 'ordenesCompra'])
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
        $ordenes = collect(); 
        $facturas = Factura::all();

        // Obtener el próximo ID autoincremental de la tabla remitos
        $statement = DB::select("SHOW TABLE STATUS LIKE 'remitos'");
        $nextId = $statement[0]->Auto_increment ?? 1;

        return view('admin.remitos.create', compact('clientes', 'ordenes', 'facturas', 'nextId'));
    }

    /**
     * Guardar
     */
    public function store(Request $request)
    {
        $request->validate([
            'numero_remito' => 'required|unique:remitos,numero_remito',
            'fecha' => 'required|date',
            'id_cliente' => 'required|exists:clientes,id',
            'motivo' => 'required|in:pedido,particular',

            // items
            'items' => 'required|array|min:1',
            'items.*.articulo' => 'required',
            'items.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $remito = Remito::create([
                'numero_remito' => $request->numero_remito,
                'fecha' => $request->fecha,
                'id_cliente' => $request->id_cliente,
                'motivo' => $request->motivo,
                'id_cot' => $request->id_cot,
                'estado' => 'Emitido',

                'condicion_venta' => $request->condicion_venta,
                'transportista' => $request->transportista,
                'domicilio_transportista' => $request->domicilio_transportista,
                'iva_transportista' => $request->iva_transportista,
                'cuit_transportista' => $request->cuit_transportista,
                'observacion' => $request->observacion,
                // CAI/VTO omitidos de acciones automáticas por pedido del usuario
                'cai' => $request->cai,
                'cai_vto' => $request->cai_vto,
            ]);

            // 🔥 Guardar items y manejar vínculos N:N con OC
            $oc_vincular = []; // Para agrupar vínculos a enviar a la tabla intermedia

            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    RemitoItem::create([
                        'remito_id' => $remito->id,
                        'codigo' => $item['codigo'] ?? null,
                        'articulo' => $item['articulo'],
                        'cantidad' => $item['cantidad'],
                        'descripcion' => $item['descripcion'] ?? null,
                        'id_orden_item' => $item['id_orden_item'] ?? null,
                    ]);

                    // Si viene de una OC, recolectamos para la tabla oc_remito
                    if (isset($item['id_orden_compra']) && $item['id_orden_compra']) {
                        $oc_id = $item['id_orden_compra'];
                        if (!isset($oc_vincular[$oc_id])) {
                            $oc_vincular[$oc_id] = [
                                'articulo' => $item['articulo'],
                                'cantidad' => 0
                            ];
                        }
                        $oc_vincular[$oc_id]['cantidad'] += $item['cantidad'];
                    }
                }
            }

            // Guardar vínculos en la tabla intermedia
            foreach ($oc_vincular as $oc_id => $data) {
                $remito->ordenesCompra()->attach($oc_id, [
                    'articulo' => $data['articulo'],
                    'cantidad' => $data['cantidad']
                ]);
            }

            DB::commit();

            return redirect()
                ->route('remitos.index')
                ->with('success', 'Remito creado correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear el remito: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Ver detalle
     */
    public function show(Remito $remito)
    {
        $remito->load(['items', 'cliente', 'ordenesCompra']);
        return view('admin.remitos.show', compact('remito'));
    }

    /**
     * Editar
     */
    public function edit(Remito $remito)
    {
        $clientes = Cliente::all();
        $ordenes = OrdenCompra::where('id_cliente', $remito->id_cliente)->get();
        $remito->load(['items', 'ordenesCompra']);

        return view('admin.remitos.edit', compact('remito', 'clientes', 'ordenes'));
    }

    /**
     * Actualizar
     */
    public function update(Request $request, Remito $remito)
    {
        $request->validate([
            'numero_remito' => 'required|unique:remitos,numero_remito,' . $remito->id,
            'fecha' => 'required|date',
            'id_cliente' => 'required|exists:clientes,id',
            'motivo' => 'required|in:pedido,particular',

            // items
            'items' => 'required|array|min:1',
            'items.*.articulo' => 'required',
            'items.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $remito->update([
                'numero_remito' => $request->numero_remito,
                'fecha' => $request->fecha,
                'id_cliente' => $request->id_cliente,
                'motivo' => $request->motivo,
                'id_cot' => $request->id_cot,

                'condicion_venta' => $request->condicion_venta,
                'transportista' => $request->transportista,
                'domicilio_transportista' => $request->domicilio_transportista,
                'iva_transportista' => $request->iva_transportista,
                'cuit_transportista' => $request->cuit_transportista,
                'observacion' => $request->observacion,
                'cai' => $request->cai,
                'cai_vto' => $request->cai_vto,
            ]);

            // Sync items (borrar y recrear)
            $remito->items()->delete();
            $remito->ordenesCompra()->detach();

            $oc_vincular = [];

            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    RemitoItem::create([
                        'remito_id' => $remito->id,
                        'codigo' => $item['codigo'] ?? null,
                        'articulo' => $item['articulo'],
                        'cantidad' => $item['cantidad'],
                        'descripcion' => $item['descripcion'] ?? null,
                        'id_orden_item' => $item['id_orden_item'] ?? null,
                    ]);

                    if (isset($item['id_orden_compra']) && $item['id_orden_compra']) {
                        $oc_id = $item['id_orden_compra'];
                        if (!isset($oc_vincular[$oc_id])) {
                            $oc_vincular[$oc_id] = [
                                'articulo' => $item['articulo'],
                                'cantidad' => 0
                            ];
                        }
                        $oc_vincular[$oc_id]['cantidad'] += $item['cantidad'];
                    }
                }
            }

            foreach ($oc_vincular as $oc_id => $data) {
                $remito->ordenesCompra()->attach($oc_id, [
                    'articulo' => $data['articulo'],
                    'cantidad' => $data['cantidad']
                ]);
            }

            DB::commit();

            return redirect()
                ->route('remitos.index')
                ->with('success', 'Remito actualizado correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar: ' . $e->getMessage())->withInput();
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

        $pdf = Pdf::loadView('admin.remitos.pdf', compact('remito'));

        return $pdf->stream('remito_'.$remito->numero_remito.'.pdf');
    }
}
