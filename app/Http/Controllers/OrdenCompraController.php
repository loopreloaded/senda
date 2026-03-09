<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use App\Models\OrdenItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class OrdenCompraController extends Controller
{
    public function index(Request $request)
    {
        $query = OrdenCompra::with('cliente');

        if ($request->filled('proveedor')) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('razon_social', 'LIKE', '%' . $request->proveedor . '%');
            });
        }

        if ($request->filled('motivo')) {
            $query->where('motivo', $request->motivo);
        }

        // FILTRO ESTADO
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $ordenes = $query->orderBy('numero_oc', 'asc')
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
            'id_cliente'        => 'required|exists:clientes,id',
            'cuit'              => 'required|digits:11',
            'direccion'         => 'nullable|string|max:191',
            'telefono'          => 'nullable|string|max:50',
            'email'             => 'nullable|email|max:191',
            'moneda'            => 'required|in:ARS,USD_BILLETE,USD_DIVISA',
            'fecha_entrega'     => 'nullable|date',
            'condicion_compra'  => 'required|string|max:191',
            'solicitud_compra'  => 'nullable|string|max:191',
            'motivo'            => 'required|in:cotizacion,stock',
            'observaciones'     => 'nullable|string',

            // ARCHIVO
            'archivo'           => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:5120',

            'items'                     => 'required|array|min:1',
            'items.*.codigo'            => 'nullable|string|max:191',
            'items.*.descripcion'       => 'required|string|max:500',
            'items.*.cantidad'          => 'required|numeric|min:0',
            'items.*.unidad'            => 'nullable|string|max:50',
            'items.*.precio_unitario'   => 'required|numeric|min:0',
            'items.*.iva'               => 'nullable|numeric|min:0|max:100',
            'items.*.fecha_entrega'     => 'nullable|date',
            'items.*.descuento'         => 'nullable|numeric|min:0|max:100',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Subtotales
        |--------------------------------------------------------------------------
        */

        $subtotalConIVA = 0;
        $totalFinal     = 0;

        foreach ($validated['items'] as $item) {

            $cantidad  = floatval($item['cantidad']);
            $precio    = floatval($item['precio_unitario']);
            $iva       = floatval($item['iva'] ?? 0);
            $descuento = floatval($item['descuento'] ?? 0);

            $totalBase   = $cantidad * $precio;
            $totalConIVA = $totalBase + ($totalBase * ($iva / 100));
            $totalItem   = $totalConIVA - ($totalConIVA * ($descuento / 100));

            $subtotalConIVA += $totalConIVA;
            $totalFinal     += $totalItem;
        }

        $validated['subtotal'] = $subtotalConIVA;
        $validated['total']    = $totalFinal;
        $validated['estado']   = 'pendiente';

        /*
        |--------------------------------------------------------------------------
        | Subir archivo
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('archivo')) {

            $file = $request->file('archivo');

            $nombre = 'oc_' . $validated['numero_oc'] . '_' . time() . '.' . $file->getClientOriginalExtension();

            $ruta = $file->storeAs('ordenes_compra', $nombre, 'public');

            $validated['archivo'] = $ruta;
        }

        /*
        |--------------------------------------------------------------------------
        | Crear orden
        |--------------------------------------------------------------------------
        */

        $orden = OrdenCompra::create($validated);

        /*
        |--------------------------------------------------------------------------
        | Guardar ítems
        |--------------------------------------------------------------------------
        */

        foreach ($validated['items'] as $item) {

            $cantidad  = floatval($item['cantidad']);
            $precio    = floatval($item['precio_unitario']);
            $iva       = floatval($item['iva'] ?? 0);
            $descuento = floatval($item['descuento'] ?? 0);

            $totalBase   = $cantidad * $precio;
            $totalConIVA = $totalBase + ($totalBase * ($iva / 100));
            $totalItem   = $totalConIVA - ($totalConIVA * ($descuento / 100));

            OrdenItem::create([
                'orden_compra_id' => $orden->id,
                'codigo'          => $item['codigo'] ?? null,
                'descripcion'     => $item['descripcion'],
                'cantidad'        => $cantidad,
                'unidad'          => $item['unidad'] ?? null,
                'precio_unitario' => $precio,
                'iva'             => $iva,
                'descuento'       => $descuento,
                'total'           => $totalItem,
                'fecha_entrega'   => $item['fecha_entrega'] ?? null,
            ]);
        }

        return redirect()
            ->route('ordenes.index')
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
            'id_cliente'        => 'required|exists:clientes,id',
            'cuit'              => 'required|digits:11',
            'direccion'         => 'nullable|string|max:191',
            'telefono'          => 'nullable|string|max:50',
            'email'             => 'nullable|email|max:191',
            'moneda'            => 'required|in:ARS,USD_BILLETE,USD_DIVISA',
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
            'items.*.iva'                   => 'nullable|numeric|min:0|max:100',
            'items.*.fecha_entrega'         => 'nullable|date',
            'items.*.descuento'             => 'nullable|numeric|min:0|max:100',
        ]);

        $subtotalConIVA = 0;
        $totalFinal     = 0;

        foreach ($validated['items'] as $item) {

            $cantidad  = floatval($item['cantidad']);
            $precio    = floatval($item['precio_unitario']);
            $iva       = floatval($item['iva'] ?? 0);
            $descuento = floatval($item['descuento'] ?? 0);

            $totalBase   = $cantidad * $precio;
            $totalConIVA = $totalBase + ($totalBase * ($iva / 100));
            $totalItem   = $totalConIVA - ($totalConIVA * ($descuento / 100));

            $subtotalConIVA += $totalConIVA;
            $totalFinal     += $totalItem;
        }

        $validated['subtotal'] = $subtotalConIVA;
        $validated['total']    = $totalFinal;

        DB::transaction(function () use ($validated, $orden) {

            $orden->update($validated);

            // eliminar items anteriores
            OrdenItem::where('orden_compra_id', $orden->id)->delete();

            // recrear items
            foreach ($validated['items'] as $item) {

                $cantidad  = floatval($item['cantidad']);
                $precio    = floatval($item['precio_unitario']);
                $iva       = floatval($item['iva'] ?? 0);
                $descuento = floatval($item['descuento'] ?? 0);

                $totalBase   = $cantidad * $precio;
                $totalConIVA = $totalBase + ($totalBase * ($iva / 100));
                $totalItem   = $totalConIVA - ($totalConIVA * ($descuento / 100));

                OrdenItem::create([
                    'orden_compra_id' => $orden->id,
                    'codigo'          => $item['codigo'] ?? null,
                    'descripcion'     => $item['descripcion'],
                    'cantidad'        => $cantidad,
                    'unidad'          => $item['unidad'] ?? null,
                    'precio_unitario' => $precio,
                    'iva'             => $iva,
                    'descuento'       => $descuento,
                    'total'           => $totalItem,
                    'fecha_entrega'   => $item['fecha_entrega'] ?? null,
                ]);
            }
        });

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
