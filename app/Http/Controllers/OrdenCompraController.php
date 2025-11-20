<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use App\Models\OrdenItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class OrdenCompraController extends Controller
{
    public function index()
    {
        $ordenes = OrdenCompra::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.ordenes.index', compact('ordenes'));
    }

    public function create()
    {
        return view('admin.ordenes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Datos generales
            'numero_oc'         => 'required|string|max:191|unique:orden_compras,numero_oc',
            'fecha'             => 'required|date',
            'proveedor'         => 'required|string|max:191',
            'cuit'              => 'required|digits:11',
            'direccion'         => 'nullable|string|max:191',
            'telefono'          => 'nullable|string|max:50',
            'email'             => 'nullable|email|max:191',
            'moneda'            => 'required|string|max:10',
            'condicion_compra'  => 'required|string|max:191',
            'solicitud_compra'  => 'nullable|string|max:191',

            // Totales
            'total'             => 'required|numeric|min:0',

            // Adjuntos
            'adjunto_pdf'       => 'nullable|file|mimes:pdf|max:2048',

            // Ítems
            'items'                         => 'required|array|min:1',
            'items.*.codigo'                => 'nullable|string|max:191',
            'items.*.descripcion'           => 'required|string|max:500',
            'items.*.cantidad'              => 'required|numeric|min:0',
            'items.*.unidad'                => 'nullable|string|max:50',
            'items.*.precio_unitario'       => 'required|numeric|min:0',
            'items.*.descuento'             => 'nullable|numeric|min:0',
            'items.*.total'                 => 'required|numeric|min:0',
        ]);

        // Guardar adjunto si existe
        if ($request->hasFile('adjunto_pdf')) {
            $validated['adjunto_pdf'] = $request->file('adjunto_pdf')->store('ordenes_adjuntos', 'public');
        }

        // Estado inicial
        $validated['estado'] = 'pendiente';

        // Crear OC
        $orden = OrdenCompra::create($validated);

        // Guardar ítems
        foreach ($request->items as $itemData) {
            $itemData['orden_compra_id'] = $orden->id;
            OrdenItem::create($itemData);
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
            'total'             => 'required|numeric|min:0',
            'observaciones'     => 'nullable|string',

            'adjunto_pdf'       => 'nullable|file|mimes:pdf|max:2048',

            'items'                         => 'required|array|min:1',
            'items.*.codigo'                => 'nullable|string|max:191',
            'items.*.descripcion'           => 'required|string|max:500',
            'items.*.cantidad'              => 'required|numeric|min:0',
            'items.*.unidad'                => 'nullable|string|max:50',
            'items.*.precio_unitario'       => 'required|numeric|min:0',
            'items.*.descuento'             => 'nullable|numeric|min:0',
            'items.*.total'                 => 'required|numeric|min:0',
        ]);

        // Reemplazar adjunto si se sube uno nuevo
        if ($request->hasFile('adjunto_pdf')) {
            if ($orden->adjunto_pdf) {
                Storage::disk('public')->delete($orden->adjunto_pdf);
            }
            $validated['adjunto_pdf'] = $request->file('adjunto_pdf')->store('ordenes_adjuntos', 'public');
        }

        $orden->update($validated);

        // Borrar ítems anteriores
        OrdenItem::where('orden_compra_id', $orden->id)->delete();

        // Crear los nuevos ítems
        foreach ($request->items as $itemData) {
            $itemData['orden_compra_id'] = $orden->id;
            OrdenItem::create($itemData);
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
}
