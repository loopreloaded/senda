<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Cliente;
use App\Models\PedidoCotizacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CotizacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Cotizacion::with(['cliente','pedidoCotizacion'])
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

    public function create()
    {
        $clientes = Cliente::all();
        return view('admin.cotizaciones.create', compact('clientes'));
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

            'nro_pedido_asoc' => 'nullable|string|max:50',

            'items' => 'required|array|min:1',

            'items.*.producto' => 'required|string|max:255',
            'items.*.cantidad' => 'required|numeric|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.iva' => 'nullable|numeric|min:0'

        ]);


        DB::beginTransaction();

        try {

            // Crear cotización
            $cotizacion = Cotizacion::create([

                'fecha_cot' => $request->fecha_cot,
                'id_cliente' => $request->id_cliente,

                'quien_cotiza' => $request->quien_cotiza,

                'nro_pedido_asoc' => $request->nro_pedido_asoc,

                'moneda' => $request->moneda,
                'forma_pago' => $request->forma_pago,

                'lugar_entrega' => $request->lugar_entrega,
                'plazo_entrega' => $request->plazo_entrega,

                'vigencia_oferta' => $request->vigencia_oferta,

                'motivo' => $request->motivo,

                'especificaciones_tecnicas' => $request->especificaciones_tecnicas,

                'observaciones' => $request->observaciones,

                'importe_total' => $request->importe_total ?? 0

            ]);


            // Guardar items
            foreach ($request->items as $item) {

                $cotizacion->items()->create([

                    'producto' => $item['producto'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'iva' => $item['iva'] ?? 0

                ]);
            }


            // actualizar estado del pedido si corresponde
            if ($request->filled('nro_pedido_asoc')) {

                $pedido = PedidoCotizacion::with('cotizaciones.items')->where(
                    'id_ped_cot',
                    $request->nro_pedido_asoc
                )->first();

                if ($pedido) {

                    // Calcular cantidad total cotizada hasta ahora (incluyendo la actual)
                    // Como ya guardamos la actual y el commit no se ha hecho, el $pedido->cotizaciones ya debería incluirla si refrescamos o si sumamos manualmente.
                    // Para ser seguros, sumamos de la base (refrescando la relación)

                    $pedido->load('cotizaciones.items');

                    $totalCotizado = 0;
                    foreach ($pedido->cotizaciones as $cot) {
                        $totalCotizado += $cot->items->sum('cantidad');
                    }

                    $nuevoEstado = 'p'; // Default Pendiente

                    if ($totalCotizado > 0) {
                        if ($totalCotizado >= $pedido->cantidad) {
                            $nuevoEstado = 'c'; // Cotizado (completo)
                        } else {
                            $nuevoEstado = 's'; // Parcial
                        }
                    }

                    $pedido->update([
                        'estado_pc' => $nuevoEstado
                    ]);
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
        $cotizacion->update($request->all());

        return redirect()->route('cotizaciones.index')
            ->with('success', 'Cotización actualizada');
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
}

