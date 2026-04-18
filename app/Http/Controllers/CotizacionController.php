<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Cliente;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CotizacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Cotizacion::with(['cliente','pedidos'])
                    ->orderBy('fecha_cot','desc');

        // Cliente (búsqueda parcial)
        if ($request->filled('cliente')) {

            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('razon_social','like','%'.$request->cliente.'%');
            });
        }

        // Motivo
        if ($request->filled('motivo')) {
            $query->where('motivo',$request->motivo);
        }

        // Estado
        if ($request->filled('estado')) {

            if ($request->estado === 'VIGENTE') {

                $query->where(function ($q) {

                    $q->whereNull('vigencia_oferta')
                      ->orWhere('vigencia_oferta','>=',now());

                });

            }

            if ($request->estado === 'VENCIDA') {

                $query->whereNotNull('vigencia_oferta')
                      ->where('vigencia_oferta','<',now());

            }
        }

        $cotizaciones = $query->paginate(15)->withQueryString();

        return view('admin.cotizaciones.index', compact('cotizaciones'));
    }

    /**
     * Buscar cotizaciones para vincular en Orden de Compra
     */
    public function buscar(Request $request)
    {
        $cliente_id = $request->query('cliente_id');
        
        $query = Cotizacion::query()
            ->whereIn('estado_cotizacion', ['v', 'p']) // Vigente o Parcial
            ->whereNotNull('id_cliente');
        if ($cliente_id) {
            $query->where("id_cliente", $cliente_id);
        }

        $cotizaciones = $query->with("cliente")->limit(20)->get();

        return response()->json($cotizaciones);
    }


    /**
     * Obtener ítems de la cotización en formato JSON
     */
    public function jsonItems($id)
    {
        $cotizacion = Cotizacion::with('items')->findOrFail($id);
        return response()->json($cotizacion->items);
    }

    public function create()
    {
        $clientes = Cliente::all();
        
        // Obtener el próximo ID autoincremental de la tabla cotizaciones
        $statement = DB::select("SHOW TABLE STATUS LIKE 'cotizaciones'");
        $nextId = $statement[0]->Auto_increment ?? 1;

        return view('admin.cotizaciones.create', compact('clientes', 'nextId'));
    }

    public function store(Request $request)
    {

        $request->validate([

            'fecha_cot' => 'required|date',
            'id_cliente' => 'required|exists:clientes,id',

            'quien_cotiza' => 'nullable|string|max:150',

            'moneda' => 'required|in:ARS,USD_BILLETE,USD_DIVISA',

            'forma_pago' => 'required|string|max:20',

            'motivo' => 'required|in:pedido,particular',



            'items' => 'required|array|min:1',

            'items.*.producto' => 'required|string|max:255',
            'items.*.cantidad' => 'required|numeric|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.iva' => 'nullable|numeric|min:0',
            'items.*.id_pedido_cot' => 'required_if:motivo,pedido'

        ]);

        $this->validarCantidades($request);

        DB::beginTransaction();

        try {

            // Crear cotización
            $cotizacion = Cotizacion::create([
                'nro_cotizacion' => $request->nro_cotizacion,
                'fecha_cot' => $request->fecha_cot,
                'id_cliente' => $request->id_cliente,

                'quien_cotiza' => $request->quien_cotiza,

                'moneda' => $request->moneda,
                'forma_pago' => $request->forma_pago,

                'lugar_entrega' => $request->lugar_entrega,
                'plazo_entrega' => $request->plazo_entrega,

                'vigencia_oferta' => $request->vigencia_oferta,

                'motivo' => $request->motivo,

                'especificaciones_tecnicas' => $request->especificaciones_tecnicas,

                'observaciones' => $request->observaciones,

                'importe_total' => $request->importe_total ?? 0,

                'estado_cotizacion' => 'v' // Vigente por defecto

            ]);

            // Guardar items y relaciones
            foreach ($request->items as $item) {

                $cotItem = $cotizacion->items()->create([
                    'id_pedido_cot' => $item['id_pedido_cot'] ?? null,
                    'producto' => $item['producto'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'iva' => $item['iva'] ?? 0
                ]);

                // Si está vinculado a un pedido, guardar en tabla intermedia
                if (!empty($item['id_pedido_cot'])) {
                    $cotizacion->pedidos()->attach($item['id_pedido_cot'], [
                        'producto' => $item['producto'],
                        'cantidad' => $item['cantidad']
                    ]);
                }
            }


            // actualizar estado del/los pedido(s) si corresponde
            // (Esta lógica se puede refinar para iterar sobre todos los pedidos vinculados)
            if ($cotizacion->pedidos()->count() > 0) {
                foreach ($cotizacion->pedidos as $pedido) {
                    $this->actualizarEstadoPedido($pedido);
                }
            }



            DB::commit();


            return redirect()
                ->route('cotizaciones.index')
                ->with('success','Cotización creada correctamente');


        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error','Error al guardar la cotización');

        }
    }

    public function show($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'items'])->findOrFail($id);

        return view('admin.cotizaciones.show', compact('cotizacion'));
    }

    public function edit(Cotizacion $cotizacion)
    {
        return view('admin.cotizaciones.edit', compact('cotizacion'));
    }

    public function update(Request $request, Cotizacion $cotizacion)
    {
        $request->validate([
            'fecha_cot' => 'required|date',
            'id_cliente' => 'required|exists:clientes,id',
            'moneda' => 'required|in:ARS,USD_BILLETE,USD_DIVISA',
            'forma_pago' => 'required|string|max:20',
            'motivo' => 'required|in:pedido,particular',
            'items' => 'required|array|min:1',
            'items.*.producto' => 'required|string|max:255',
            'items.*.cantidad' => 'required|numeric|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.iva' => 'nullable|numeric|min:0',
            'items.*.id_pedido_cot' => 'required_if:motivo,pedido'
        ]);

        $this->validarCantidades($request, $cotizacion->id_cotizacion);

        DB::beginTransaction();
        try {
            $cotizacion->update($request->except('items'));
            
            // Capturar IDs de pedidos vinculados ANTES de desvincular
            $pedidoIdsPrevios = $cotizacion->pedidos->pluck('id_ped_cot')->toArray();

            // Eliminar items y relaciones previas para re-insertar
            $cotizacion->items()->delete();
            $cotizacion->pedidos()->detach();

            foreach ($request->items as $item) {
                $cotizacion->items()->create([
                    'id_pedido_cot' => $item['id_pedido_cot'] ?? null,
                    'producto' => $item['producto'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'iva' => $item['iva'] ?? 0
                ]);

                if (!empty($item['id_pedido_cot'])) {
                    $cotizacion->pedidos()->attach($item['id_pedido_cot'], [
                        'producto' => $item['producto'],
                        'cantidad' => $item['cantidad']
                    ]);
                }
            }

            // Recargar relación para tener los pedidos actuales
            $cotizacion->load('pedidos');
            $pedidoIdsActuales = $cotizacion->pedidos->pluck('id_ped_cot')->toArray();

            // Unir IDs previos y actuales para actualizar todos los afectados
            $todosLosAfectados = array_unique(array_merge($pedidoIdsPrevios, $pedidoIdsActuales));

            foreach ($todosLosAfectados as $pedidoId) {
                $pedido = Pedido::find($pedidoId);
                if ($pedido) {
                    $this->actualizarEstadoPedido($pedido);
                }
            }

            DB::commit();
            return redirect()->route('cotizaciones.index')
                ->with('success', 'Cotización actualizada correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar la cotización')->withInput();
        }
    }


    public function destroy(Cotizacion $cotizacion)
    {
        $cotizacion->delete();

        return redirect()->route('admin.cotizaciones.index')
            ->with('success', 'Cotización eliminada');
    }

    public function pdf($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'items'])
                        ->findOrFail($id);

        $pdf = Pdf::loadView(
            'admin.cotizaciones.pdf',
            compact('cotizacion')
        );

        return $pdf->stream('cotizacion_'.$cotizacion->id.'.pdf');
    }

    public function rechazar(Cotizacion $cotizacion)
    {
        try {

            // Si está vencida, no permitir rechazar (opcional)
            if ($cotizacion->vigencia_oferta && now()->gt($cotizacion->vigencia_oferta)) {
                return redirect()
                    ->route('cotizaciones.index')
                    ->with('error', 'No se puede rechazar una cotización vencida');
            }

            // Actualizar estado
            $cotizacion->update([
                'estado_cotizacion' => 'r'
            ]);

            return redirect()
                ->route('cotizaciones.index')
                ->with('success', 'Cotización marcada como rechazada');

        } catch (\Exception $e) {

            return redirect()
                ->route('cotizaciones.index')
                ->with('error', 'Error al rechazar la cotización');

        }
    }

    /**
     * Lógica para actualizar el estado del pedido basado en lo cotizado
     */
    protected function actualizarEstadoPedido(Pedido $pedido)
    {
        // Calcular lo cotizado para este pedido a través de la tabla intermedia
        $totalCotizado = DB::table('pedido_cotizacion')
            ->where('id_pedido_cot', $pedido->id_ped_cot)
            ->sum('cantidad');

        $nuevoEstado = 'p'; // Pendiente

        if ($totalCotizado > 0) {
            if ($totalCotizado >= $pedido->cantidad) {
                $nuevoEstado = 'c'; // Cotizado
            } else {
                $nuevoEstado = 's'; // Parcial
            }
        }

        $pedido->update(['estado_pc' => $nuevoEstado]);
    }

    /**
     * Valida que lo solicitado no supere la cantidad restante del pedido
     */
    protected function validarCantidades(Request $request, $idCotizacionOmitir = null)
    {
        $itemsByPedido = [];
        foreach ($request->items as $item) {
            if (!empty($item['id_pedido_cot'])) {
                $id = $item['id_pedido_cot'];
                $itemsByPedido[$id] = ($itemsByPedido[$id] ?? 0) + $item['cantidad'];
            }
        }

        foreach ($itemsByPedido as $idPedido => $cantidadNueva) {
            $pedido = \App\Models\Pedido::findOrFail($idPedido);

            // Sumar lo ya cotizado en otros registros
            $yaCotizado = DB::table('pedido_cotizacion')
                ->where('id_pedido_cot', $idPedido)
                ->when($idCotizacionOmitir, function ($query) use ($idCotizacionOmitir) {
                    return $query->where('id_cotizacion', '!=', $idCotizacionOmitir);
                })
                ->sum('cantidad');

            if (($yaCotizado + $cantidadNueva) > $pedido->cantidad) {
                $msg = "No se puede asignar {$cantidadNueva} unidades al pedido {$pedido->nro_solicitud} porque el máximo permitido es {$pedido->cantidad}.";
                
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'items' => [$msg]
                ]);
            }
        }
    }
}


