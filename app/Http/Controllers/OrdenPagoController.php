<?php

namespace App\Http\Controllers;

use App\Models\OrdenPago;
use App\Models\Cliente;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrdenPagoController extends Controller
{
    /**
     * Listado de Órdenes de Pago
     */
    public function index(Request $request)
    {
        $query = OrdenPago::with(['cliente']);

        // Filtrar por estados Recibida o Parcial por defecto
        if (!$request->filled('estado')) {
            $query->whereIn('estado', [OrdenPago::ESTADO_RECIBIDA, OrdenPago::ESTADO_PARCIAL]);
        } else {
            $query->where('estado', $request->estado);
        }

        // Filtro por cliente
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        $ordenes = $query->latest()->paginate(15);
        $clientes = Cliente::orderBy('razon_social')->get();

        return view('admin.ordenes-pago.index', compact('ordenes', 'clientes'));
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        $clientes = Cliente::orderBy('razon_social')->get();
        
        // Formato para el ID correlativo
        $latest = OrdenPago::withTrashed()->latest('id')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        $formattedId = 'OP-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return view('admin.ordenes-pago.create', compact('clientes', 'formattedId'));
    }

    /**
     * Guardar Orden de Pago
     */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'fecha' => 'required|date',
            'nro_op' => 'required|string|max:100',
            'motivo' => 'required|in:pedido,particular',
            'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'observaciones' => 'nullable|string',
            'importe_pagado' => 'required_if:motivo,particular|numeric|min:0',
            
            // Facturas asociadas si es pedido
            'facturas' => 'required_if:motivo,pedido|array',
            'facturas.*.id' => 'exists:facturas,id',
            'facturas.*.pagado' => 'numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->only([
                'cliente_id', 'fecha', 'nro_op', 'motivo', 'observaciones'
            ]);

            // Manejo de archivo
            if ($request->hasFile('archivo')) {
                $file = $request->file('archivo');
                $filename = 'op_' . time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('ordenes_pago', $filename, 'public');
                $data['archivo'] = $path;
            }

            // Importe total dependiente del motivo
            if ($request->motivo === 'particular') {
                $data['importe_pagado'] = $request->importe_pagado ?? 0;
            } else {
                $total = 0;
                foreach ($request->facturas as $f) {
                    $total += (float)($f['pagado'] ?? 0);
                }
                $data['importe_pagado'] = $total;
            }

            $data['estado'] = OrdenPago::ESTADO_RECIBIDA;

            $op = OrdenPago::create($data);

            // Relación con facturas
            if ($request->motivo === 'pedido') {
                foreach ($request->facturas as $f) {
                    if (isset($f['pagado']) && $f['pagado'] > 0) {
                        $op->facturas()->attach($f['id'], ['pagado' => $f['pagado']]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('ordenes-pago.index')
                ->with('success', 'Orden de Pago registrada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar la OP: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Ver detalle
     */
    public function show(OrdenPago $ordenPago)
    {
        $ordenPago->load(['cliente', 'facturas', 'recibos']);
        return view('admin.ordenes-pago.show', compact('ordenPago'));
    }

    /**
     * Formulario de edición
     */
    public function edit(OrdenPago $ordenPago)
    {
        if ($ordenPago->estado !== OrdenPago::ESTADO_RECIBIDA) {
            return redirect()->route('ordenes-pago.index')->with('error', 'Solo se pueden editar OPs en estado Recibida.');
        }

        $clientes = Cliente::orderBy('razon_social')->get();
        $ordenPago->load('facturas');

        return view('admin.ordenes-pago.edit', compact('ordenPago', 'clientes'));
    }

    /**
     * Actualizar Orden de Pago
     */
    public function update(Request $request, OrdenPago $ordenPago)
    {
        if ($ordenPago->estado !== OrdenPago::ESTADO_RECIBIDA) {
            return back()->with('error', 'No se puede editar una OP que ya no está en estado Recibida.');
        }

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'fecha' => 'required|date',
            'nro_op' => 'required|string|max:100',
            'motivo' => 'required|in:pedido,particular',
            'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'observaciones' => 'nullable|string',
            'importe_pagado' => 'required_if:motivo,particular|numeric|min:0',
            'facturas' => 'required_if:motivo,pedido|array',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->only(['cliente_id', 'fecha', 'nro_op', 'motivo', 'observaciones']);

            if ($request->hasFile('archivo')) {
                // Eliminar anterior si existe
                if ($ordenPago->archivo) {
                    Storage::disk('public')->delete($ordenPago->archivo);
                }
                $file = $request->file('archivo');
                $filename = 'op_' . time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('ordenes_pago', $filename, 'public');
                $data['archivo'] = $path;
            }

            if ($request->motivo === 'particular') {
                $data['importe_pagado'] = $request->importe_pagado ?? 0;
                $ordenPago->facturas()->detach();
            } else {
                $total = 0;
                $syncData = [];
                foreach ($request->facturas as $f) {
                    if (isset($f['pagado']) && $f['pagado'] > 0) {
                        $syncData[$f['id']] = ['pagado' => $f['pagado']];
                        $total += (float)$f['pagado'];
                    }
                }
                $data['importe_pagado'] = $total;
                $ordenPago->facturas()->sync($syncData);
            }

            $ordenPago->update($data);

            DB::commit();

            return redirect()
                ->route('ordenes-pago.index')
                ->with('success', 'Orden de Pago actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar la OP: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Anular OP
     */
    public function anular(OrdenPago $ordenPago)
    {
        $ordenPago->update(['estado' => OrdenPago::ESTADO_ANULADA]);

        return back()->with('success', 'Orden de Pago anulada.');
    }

    /**
     * Eliminar OP
     */
    public function destroy(OrdenPago $ordenPago)
    {
        if ($ordenPago->archivo) {
            Storage::disk('public')->delete($ordenPago->archivo);
        }
        $ordenPago->delete();

        return redirect()->route('ordenes-pago.index')->with('success', 'Orden de Pago eliminada.');
    }

    /**
     * AJAX: Obtener facturas pendientes por cliente
     */
    public function getFacturas($cliente_id)
    {
        $facturas = Factura::where('cliente_id', $cliente_id)
            ->whereIn('estado', ['emitida', 'parcial'])
            ->get();

        return response()->json($facturas);
    }
}
