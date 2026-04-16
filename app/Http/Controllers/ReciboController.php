<?php

namespace App\Http\Controllers;

use App\Models\Recibo;
use App\Models\Cliente;
use App\Models\OrdenPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;

class ReciboController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver recibos')->only(['index', 'show']);
        $this->middleware('permission:crear recibos')->only(['create', 'store']);
        $this->middleware('permission:editar recibos')->only(['edit', 'update']);
        $this->middleware('permission:eliminar recibos')->only(['destroy']);
        $this->middleware('permission:aprobar recibos')->only(['aprobar']);
    }

    /**
     * Listado de recibos
     */
    public function index()
    {
        $recibos = Recibo::with(['cliente'])
            ->orderBy('fecha', 'desc')
            ->orderBy('id_recibo', 'desc')
            ->get();

        return view('admin.recibos.index', compact('recibos'));
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        $recibo = new Recibo();
        $clientes = Cliente::orderBy('razon_social')->get();
        
        // Formato para el ID correlativo (informativo)
        $latest = Recibo::latest('id_recibo')->first();
        $nextId = $latest ? $latest->id_recibo + 1 : 1;
        $formattedId = '#' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return view('admin.recibos.create', compact('recibo', 'clientes', 'formattedId'));
    }

    /**
     * Guardar nuevo recibo
     */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'    => 'required|exists:clientes,id',
            'nro_recibo'    => 'required|string|max:50',
            'fecha'         => 'required|date',
            'motivo'        => 'required|in:pedido,particular',
            'detalles_pago' => 'nullable|string|max:255',
            
            // Importes y Retenciones
            'iva'           => 'nullable|numeric|min:0',
            'ganancia'      => 'nullable|numeric|min:0',
            'iibb'          => 'nullable|numeric|min:0',
            'percepcion_ib' => 'nullable|numeric|min:0',
            
            // Vinculacion
            'ops' => 'required_if:motivo,pedido|array',
            'ops.*.id' => 'exists:ordenes_pago,id',
            'ops.*.saldado' => 'nullable|numeric|min:0',

            // Si es particular, el importe saldado es manual
            'importe_saldado' => 'required_if:motivo,particular|nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->only([
                'cliente_id', 'nro_recibo', 'fecha', 'motivo', 'detalles_pago',
                'iva', 'ganancia', 'iibb', 'percepcion_ib'
            ]);

            // Cálculo de retenciones
            $total_retenciones = (float)($data['iva'] ?? 0) + 
                                (float)($data['ganancia'] ?? 0) + 
                                (float)($data['iibb'] ?? 0) + 
                                (float)($data['percepcion_ib'] ?? 0);
            
            $data['total_retenciones'] = $total_retenciones;

            if ($request->motivo === 'pedido') {
                $importe_saldado = 0;
                foreach ($request->ops as $op_data) {
                    if ($op_data['saldado'] > 0) {
                        $op = OrdenPago::find($op_data['id']);
                        
                        // Validación: no puede saldar más de lo pagado en la OP
                        if ($op_data['saldado'] > $op->importe_pagado) {
                            throw new \Exception("El importe saldado en la OP {$op->id} no puede ser mayor al pagado ({$op->importe_pagado}).");
                        }
                        
                        $importe_saldado += (float)$op_data['saldado'];
                    }
                }
                $data['importe_saldado'] = $importe_saldado;
            } else {
                $data['importe_saldado'] = $request->importe_saldado ?? 0;
            }

            $data['importe_total'] = $data['importe_saldado'] + $total_retenciones;
            $data['estado'] = Recibo::ESTADO_EMITIDA;

            $recibo = Recibo::create($data);

            // Relacionar OPs
            if ($request->motivo === 'pedido') {
                foreach ($request->ops as $op_data) {
                    if ($op_data['saldado'] > 0) {
                        $recibo->ordenesPago()->attach($op_data['id'], ['saldado' => $op_data['saldado']]);
                        OrdenPago::find($op_data['id'])->syncSaldado();
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('recibos.index')
                ->with('success', 'Recibo creado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear el recibo: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Mostrar un recibo
     */
    public function show(Recibo $recibo)
    {
        $recibo->load(['cliente', 'ordenesPago', 'creador']);
        return view('admin.recibos.show', compact('recibo'));
    }

    /**
     * Formulario de edición
     */
    public function edit(Recibo $recibo)
    {
        if ($recibo->estado === Recibo::ESTADO_CERRADA) {
            return redirect()->route('recibos.index')->with('error', 'No se puede editar un recibo cerrado.');
        }

        $clientes = Cliente::orderBy('razon_social')->get();
        $recibo->load('ordenesPago');

        return view('admin.recibos.edit', compact('recibo', 'clientes'));
    }

    /**
     * Actualizar un recibo
     */
    public function update(Request $request, Recibo $recibo)
    {
        if ($recibo->estado === Recibo::ESTADO_CERRADA) {
            return back()->with('error', 'No se puede editar un recibo cerrado.');
        }

        $request->validate([
            'cliente_id'    => 'required|exists:clientes,id',
            'nro_recibo'    => 'required|string|max:50',
            'fecha'         => 'required|date',
            'motivo'        => 'required|in:pedido,particular',
            'detalles_pago' => 'nullable|string|max:255',
            'iva'           => 'nullable|numeric|min:0',
            'ganancia'      => 'nullable|numeric|min:0',
            'iibb'          => 'nullable|numeric|min:0',
            'percepcion_ib' => 'nullable|numeric|min:0',
            'ops'           => 'required_if:motivo,pedido|array',
            'ops.*.id'      => 'nullable|exists:ordenes_pago,id',
            'ops.*.saldado' => 'nullable|numeric|min:0',
            'importe_saldado' => 'required_if:motivo,particular|nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->only([
                'cliente_id', 'nro_recibo', 'fecha', 'motivo', 'detalles_pago',
                'iva', 'ganancia', 'iibb', 'percepcion_ib'
            ]);

            $total_retenciones = (float)($data['iva'] ?? 0) + 
                                (float)($data['ganancia'] ?? 0) + 
                                (float)($data['iibb'] ?? 0) + 
                                (float)($data['percepcion_ib'] ?? 0);
            
            $data['total_retenciones'] = $total_retenciones;

            if ($request->motivo === 'pedido') {
                $oldOpIds = $recibo->ordenesPago()->pluck('ordenes_pago.id')->toArray();
                $importe_saldado = 0;
                $syncData = [];
                foreach ($request->ops as $op_data) {
                    if ($op_data['saldado'] > 0) {
                        $op = OrdenPago::find($op_data['id']);
                        if ($op_data['saldado'] > $op->importe_pagado) {
                            throw new \Exception("El importe saldado en la OP {$op->id} no puede ser mayor al pagado ({$op->importe_pagado}).");
                        }
                        $importe_saldado += (float)$op_data['saldado'];
                        $syncData[$op_data['id']] = ['saldado' => $op_data['saldado']];
                    }
                }
                $data['importe_saldado'] = $importe_saldado;
                $recibo->ordenesPago()->sync($syncData);
                
                $newOpIds = array_keys($syncData);
                $affectedIds = array_unique(array_merge($oldOpIds, $newOpIds));
                foreach ($affectedIds as $id) {
                    OrdenPago::find($id)->syncSaldado();
                }
            } else {
                $affectedIds = $recibo->ordenesPago()->pluck('ordenes_pago.id')->toArray();
                $data['importe_saldado'] = $request->importe_saldado ?? 0;
                $recibo->ordenesPago()->detach();
                foreach ($affectedIds as $id) {
                    OrdenPago::find($id)->syncSaldado();
                }
            }

            $data['importe_total'] = $data['importe_saldado'] + $total_retenciones;

            $recibo->update($data);

            DB::commit();

            return redirect()
                ->route('recibos.index')
                ->with('success', 'Recibo actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar el recibo: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Eliminar un recibo
     */
    public function destroy(Recibo $recibo)
    {
        if ($recibo->estado === Recibo::ESTADO_CERRADA) {
            return back()->with('error', 'No se puede eliminar un recibo cerrado.');
        }

        $affectedIds = $recibo->ordenesPago()->pluck('ordenes_pago.id')->toArray();
        $recibo->ordenesPago()->detach();
        $recibo->delete();

        foreach ($affectedIds as $id) {
            $op = OrdenPago::find($id);
            if ($op) $op->syncSaldado();
        }

        return redirect()
            ->route('recibos.index')
            ->with('success', 'Recibo eliminado.');
    }

    /**
     * Cerrar (Aprobar) un recibo
     */
    public function aprobar(Recibo $recibo)
    {
        $recibo->estado = Recibo::ESTADO_CERRADA;
        $recibo->save();

        return back()->with('success', 'Recibo cerrado correctamente.');
    }

    /**
     * AJAX: Obtener Ordenes de Pago por cliente
     */
    public function getOrdenesPago($cliente_id)
    {
        // Traemos OPs que no estén anuladas, no estén ya pagadas (saldadas) y pertenezcan al cliente
        $ops = OrdenPago::where('cliente_id', $cliente_id)
            ->whereNotIn('estado', [OrdenPago::ESTADO_ANULADA, OrdenPago::ESTADO_PAGADA])
            ->select('id', 'fecha', 'nro_op', 'importe_pagado', 'importe_saldado')
            ->get();

        return response()->json($ops);
    }

    public function generar_pdf_recibo(Recibo $recibo)
    {
        $recibo->load(['cliente', 'ordenesPago']);
        
        $pdf = new TCPDF();
        $pdf::SetTitle('Recibo ' . $recibo->nro_recibo);
        $pdf::SetMargins(10, 10, 10);
        $pdf::SetAutoPageBreak(true, 10);
        $pdf::AddPage();

        // Estilo básico mejorado
        $pdf::SetFont('helvetica', 'B', 16);
        $pdf::Cell(0, 10, 'RECIBO OFICIAL', 0, 1, 'C');
        $pdf::SetFont('helvetica', '', 12);
        $pdf::Cell(0, 10, 'Nro: ' . $recibo->nro_recibo, 0, 1, 'R');
        $pdf::Cell(0, 10, 'Fecha: ' . ($recibo->fecha ? $recibo->fecha->format('d/m/Y') : 'N/A'), 0, 1, 'R');
        
        $pdf::Ln(10);
        
        $pdf::SetFont('helvetica', 'B', 12);
        $pdf::Cell(0, 10, 'CLIENTE:', 0, 1);
        $pdf::SetFont('helvetica', '', 12);
        $pdf::Cell(0, 8, 'Razón Social: ' . $recibo->cliente->razon_social, 0, 1);
        $pdf::Cell(0, 8, 'CUIT: ' . $recibo->cliente->cuit, 0, 1);
        $pdf::Cell(0, 8, 'Dirección: ' . $recibo->cliente->direccion, 0, 1);
        
        $pdf::Ln(10);

        // Tabla de OPs o Detalle
        if ($recibo->motivo === 'pedido' && $recibo->ordenesPago->count() > 0) {
            $pdf::SetFont('helvetica', 'B', 11);
            $pdf::Cell(40, 7, 'ID OP', 1);
            $pdf::Cell(60, 7, 'Nro OP', 1);
            $pdf::Cell(50, 7, 'Fecha', 1);
            $pdf::Cell(40, 7, 'Saldado', 1, 1, 'R');
            
            $pdf::SetFont('helvetica', '', 11);
            foreach ($recibo->ordenesPago as $op) {
                $pdf::Cell(40, 7, 'OP-' . str_pad($op->id, 4, '0', STR_PAD_LEFT), 1);
                $pdf::Cell(60, 7, $op->nro_op, 1);
                $pdf::Cell(50, 7, $op->fecha ? $op->fecha->format('d/m/Y') : 'N/A', 1);
                $pdf::Cell(40, 7, '$ ' . number_format($op->pivot->saldado, 2), 1, 1, 'R');
            }
        } else {
            $pdf::SetFont('helvetica', 'B', 11);
            $pdf::Cell(0, 7, 'MOTIVO: Particular', 0, 1);
        }

        $pdf::Ln(5);
        $pdf::Cell(150, 7, 'Importe Saldado Total:', 0, 0, 'R');
        $pdf::Cell(40, 7, '$ ' . number_format($recibo->importe_saldado, 2), 0, 1, 'R');

        $pdf::Ln(10);
        $pdf::SetFont('helvetica', 'B', 11);
        $pdf::Cell(0, 7, 'RETENCIONES:', 0, 1);
        $pdf::SetFont('helvetica', '', 11);
        $pdf::Cell(150, 6, 'IVA:', 0, 0, 'R'); $pdf::Cell(40, 6, '$ ' . number_format($recibo->iva, 2), 0, 1, 'R');
        $pdf::Cell(150, 6, 'Ganancia:', 0, 0, 'R'); $pdf::Cell(40, 6, '$ ' . number_format($recibo->ganancia, 2), 0, 1, 'R');
        $pdf::Cell(150, 6, 'IIBB:', 0, 0, 'R'); $pdf::Cell(40, 6, '$ ' . number_format($recibo->iibb, 2), 0, 1, 'R');
        $pdf::Cell(150, 6, 'Percepción I.B.:', 0, 0, 'R'); $pdf::Cell(40, 6, '$ ' . number_format($recibo->percepcion_ib, 2), 0, 1, 'R');
        
        $pdf::SetFont('helvetica', 'B', 11);
        $pdf::Cell(150, 7, 'TOTAL RETENCIONES:', 0, 0, 'R'); $pdf::Cell(40, 7, '$ ' . number_format($recibo->total_retenciones, 2), 0, 1, 'R');
        
        $pdf::Ln(5);
        $pdf::SetFont('helvetica', 'B', 14);
        $pdf::Cell(150, 10, 'IMPORTE TOTAL:', 0, 0, 'R');
        $pdf::Cell(40, 10, '$ ' . number_format($recibo->importe_total, 2), 0, 1, 'R');

        $pdf::Ln(10);
        $pdf::SetFont('helvetica', 'I', 10);
        $pdf::Cell(0, 7, 'Detalles de Pago: ' . ($recibo->detalles_pago ?? 'N/A'), 0, 1);
        $pdf::Cell(0, 7, 'Estado: ' . $recibo->estado, 0, 1);

        return response($pdf::Output('recibo.pdf', 'S'))
                ->header('Content-Type', 'application/pdf');
    }
}
