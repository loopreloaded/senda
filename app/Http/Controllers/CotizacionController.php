<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CotizacionItem;

class CotizacionController extends Controller
{
    public function index()
    {
        $cotizaciones = Cotizacion::with('cliente')->latest()->paginate(15);
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
            'forma_pago' => 'required|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.producto' => 'required|string|max:45',
            'items.*.cantidad' => 'required|numeric|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.iva' => 'nullable|numeric|min:0'
        ]);

        DB::beginTransaction();

        try {

            //
            $cotizacion = Cotizacion::create([
                'fecha_cot' => $request->fecha_cot,
                'id_cliente' => $request->id_cliente,
                'moneda' => $request->moneda,
                'forma_pago' => $request->forma_pago,
                'lugar_entrega' => $request->lugar_entrega,
                'plazo_entrega' => $request->plazo_entrega,
                'vigencia_oferta' => $request->vigencia_oferta,
                'especificaciones_tecnicas' => $request->especificaciones_tecnicas,
                'observaciones' => $request->observaciones,
                'importe_total' => $request->importe_total ?? 0,
            ]);

            //
            foreach ($request->items as $item) {

                $cotizacion->items()->create([
                    'producto' => $item['producto'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'iva' => $item['iva'] ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('cotizaciones.index')
                ->with('success', 'Cotización creada correctamente');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->withInput()
                ->with('error', 'Error al guardar la cotización');
        }
    }

    public function show($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'items'])->findOrFail($id);

        return view('admin.cotizaciones.show', compact('cotizacion'));
    }

    public function edit(Cotizacion $cotizacion)
    {
        $clientes = Cliente::all();
        return view('admin.cotizaciones.edit', compact('cotizacion', 'clientes'));
    }

    public function update(Request $request, Cotizacion $cotizacion)
    {
        $cotizacion->update($request->all());

        return redirect()->route('admin.cotizaciones.index')
            ->with('success', 'Cotización actualizada');
    }

    public function destroy(Cotizacion $cotizacion)
    {
        $cotizacion->delete();

        return redirect()->route('admin.cotizaciones.index')
            ->with('success', 'Cotización eliminada');
    }
}
