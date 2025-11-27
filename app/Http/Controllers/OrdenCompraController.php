<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use App\Models\OrdenItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class OrdenCompraController extends Controller
{
    public function index(Request $request)
    {
        $query = OrdenCompra::query();

        if($request->filled('numero')){
            $query->where('numero_oc', 'LIKE', '%'.$request->numero.'%');
        }

        if($request->filled('proveedor')){
            $query->where('proveedor', 'LIKE', '%'.$request->proveedor.'%');
        }

        if($request->filled('fecha')){
            $query->whereDate('fecha', $request->fecha);
        }

        $ordenes = $query->orderBy('fecha','desc')
                        ->paginate(10)
                        ->appends($request->query());

        return view('admin.ordenes.index', compact('ordenes'));
    }


    public function create()
    {
        return view('admin.ordenes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_oc'         => 'required|string|max:191|unique:orden_compras,numero_oc',
            'fecha'             => 'required|date',
            'proveedor'         => 'required|string|max:191',
            'cuit'              => 'required|digits:11',
            'direccion'         => 'nullable|string|max:191',
            'telefono'          => 'nullable|string|max:50',
            'email'             => 'nullable|email|max:191',
            'moneda'            => 'required|string|max:10',
            'fecha_entrega'     => 'nullable|date',
            'condicion_compra'  => 'required|string|max:191',
            'solicitud_compra'  => 'nullable|string|max:191',
            'observaciones'     => 'nullable|string',

            'items'                         => 'required|array|min:1',
            'items.*.codigo'                => 'nullable|string|max:191',
            'items.*.descripcion'           => 'required|string|max:500',
            'items.*.cantidad'              => 'required|numeric|min:0',
            'items.*.unidad'                => 'nullable|string|max:50',
            'items.*.precio_unitario'       => 'required|numeric|min:0',
            'items.*.fecha_entrega'         => 'nullable|date',
            'items.*.descuento'             => 'nullable|numeric|min:0|max:100',  // 🔥 % máximo razonable
        ]);

        // 🔥 CALCULAR SUBTOTAL Y TOTAL CON % DE DESCUENTO
        $subtotal = 0;
        $total_final = 0;

        foreach ($request->items as $item) {

            $subtotal_item = $item['cantidad'] * $item['precio_unitario'];
            $descuento_item = $subtotal_item * (($item['descuento'] ?? 0) / 100);
            $total_item = $subtotal_item - $descuento_item;

            $subtotal += $subtotal_item;
            $total_final += $total_item;
        }

        $validated['subtotal'] = $subtotal;
        $validated['total'] = $total_final;

        $validated['estado'] = 'pendiente';

        // CREAR ORDEN
        $orden = OrdenCompra::create($validated);

        // DETALLE DE ITEMS
        foreach ($request->items as $item) {

            $subtotal_item = $item['cantidad'] * $item['precio_unitario'];
            $descuento_item = $subtotal_item * (($item['descuento'] ?? 0) / 100);

            $item['orden_compra_id'] = $orden->id;
            $item['total'] = $subtotal_item - $descuento_item;  // 🔥 total con % desc
            $item['fecha_entrega'] = $item['fecha_entrega'] ?? null;

            OrdenItem::create($item);
        }

        return redirect()->route('ordenes.index')
            ->with('success', 'Orden de compra creada correctamente.');
    }



    public function show($id)
    {
        $orden = OrdenCompra::with('items')->findOrFail($id);
        return view('admin.ordenes.show', compact('orden'));
    }

    public function edit($id)
    {
        $orden = OrdenCompra::with('items')->findOrFail($id);
        return view('admin.ordenes.edit', compact('orden'));
    }

    public function update(Request $request, $id)
    {
        $orden = OrdenCompra::findOrFail($id);

        $validated = $request->validate([
            'numero_oc'         => 'required|string|max:191|unique:orden_compras,numero_oc,' . $orden->id,
            'fecha'             => 'required|date',
            'proveedor'         => 'required|string|max:191',
            'cuit'              => 'required|digits:11',
            'direccion'         => 'nullable|string|max:191',
            'telefono'          => 'nullable|string|max:50',
            'email'             => 'nullable|email|max:191',
            'moneda'            => 'required|string|max:10',
            'condicion_compra'  => 'required|string|max:191',
            'solicitud_compra'  => 'nullable|string|max:191',
            'observaciones'     => 'nullable|string',

            'items'                         => 'required|array|min:1',
            'items.*.codigo'                => 'nullable|string|max:191',
            'items.*.descripcion'           => 'required|string|max:500',
            'items.*.cantidad'              => 'required|numeric|min:0',
            'items.*.unidad'                => 'nullable|string|max:50',
            'items.*.precio_unitario'       => 'required|numeric|min:0',
            'items.*.fecha_entrega' => 'nullable|date',
            'items.*.descuento'             => 'nullable|numeric|min:0',
        ]);

        // 🔥 RECALCULAR SUBTOTAL Y TOTAL
        $subtotal = 0;

        foreach ($request->items as $item) {
            $totalLinea = ($item['cantidad'] * $item['precio_unitario']) - ($item['descuento'] ?? 0);
            $subtotal += $totalLinea;
        }

        $validated['subtotal'] = $subtotal;
        $validated['total'] = $subtotal;

        // Actualizar OC principal
        $orden->update($validated);

        // 🔥 REEMPLAZAR ITEMS
        OrdenItem::where('orden_compra_id', $orden->id)->delete();

        foreach ($request->items as $item) {
            $item['orden_compra_id'] = $orden->id;
            $item['total'] = ($item['cantidad'] * $item['precio_unitario']) - ($item['descuento'] ?? 0);

            OrdenItem::create($item);
        }

        return redirect()->route('ordenes.index')
            ->with('success', 'Orden de compra actualizada correctamente.');
    }


    public function destroy($id)
    {
        $orden = OrdenCompra::findOrFail($id);

        if ($orden->adjunto_pdf) {
            Storage::disk('public')->delete($orden->adjunto_pdf);
        }

        OrdenItem::where('orden_compra_id', $id)->delete();

        $orden->delete();

        return redirect()->route('ordenes.index')
            ->with('success', 'Orden de compra eliminada correctamente.');
    }

    public function orden_pdf(OrdenCompra $orden)
    {
        $orden->load('items');

        $pdf = Pdf::loadView('admin.ordenes.pdf', compact('orden'))
                  ->setPaper('A4', 'portrait');

        return $pdf->stream("Orden_Compra_{$orden->numero_oc}.pdf");
    }

    public function updateObservaciones(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:orden_compras,id',
            'observaciones' => 'nullable|string'
        ]);

        $orden = OrdenCompra::findOrFail($request->id);
        $orden->observaciones = $request->observaciones;
        $orden->save();

        return back()->with('success', 'Observaciones actualizadas.');
    }

}
