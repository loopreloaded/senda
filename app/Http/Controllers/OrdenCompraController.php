<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use App\Models\OrdenItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrdenCompraController extends Controller
{
    public function index(Request $request)
    {
        $query = OrdenCompra::with(['cliente', 'cotizaciones']);

        // Filtro Cliente (antes proveedor)
        if ($request->filled('cliente')) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('razon_social', 'LIKE', '%' . $request->cliente . '%');
            });
        }

        // Filtro Motivo
        if ($request->filled('motivo')) {
            $query->where('motivo', $request->motivo);
        }

        // Filtro Estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $ordenes = $query->orderBy('id', 'desc')
                    ->paginate(10)
                    ->appends($request->query());

        return view('admin.ordenes.index', compact('ordenes'));
    }

    public function create()
    {
        $orden = new OrdenCompra();
        $latest = OrdenCompra::latest('id')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        return view('admin.ordenes.create', compact('orden', 'nextId'));
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
            'motivo'            => 'required|in:pedido,particular',
            'observaciones'     => 'nullable|string',
            'archivo'           => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:5120',

            // ítems de la orden (tabla orden_compras_items)
            'items'                     => 'required|array|min:1',
            'items.*.codigo'            => 'nullable|string|max:191',
            'items.*.descripcion'       => 'required|string|max:500',
            'items.*.cantidad'          => 'required|numeric|min:0',
            'items.*.unidad'            => 'nullable|string|max:50',
            'items.*.precio_unitario'   => 'required|numeric|min:0',
            'items.*.iva'               => 'nullable|numeric|min:0|max:100',
            'items.*.fecha_entrega'     => 'nullable|date',
            'items.*.descuento'         => 'nullable|numeric|min:0|max:100',
            'items.*.id_cotizacion_item'=> 'nullable|exists:cotizacion_items,id_cot_item',
            'items.*.id_cotizacion'     => 'nullable|exists:cotizaciones,id_cotizacion',

            // vínculos con cotizaciones (tabla cotizacion_oc)
            'vinculos'          => 'nullable|array',
            'vinculos.*.id_cot' => 'required|exists:cotizaciones,id_cotizacion',
            'vinculos.*.articulo' => 'required|string',
            'vinculos.*.cantidad' => 'required|numeric|min:0.01',
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
        $validated['estado']   = OrdenCompra::ESTADO_PENDIENTE;

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

        DB::beginTransaction();
        try {
            /*
            |--------------------------------------------------------------------------
            | Crear orden
            |--------------------------------------------------------------------------
            */
            $orden = OrdenCompra::create($validated);

            /*
            |--------------------------------------------------------------------------
            | Guardar ítems principales
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
                    'id_cotizacion_item' => $item['id_cotizacion_item'] ?? null,
                    'id_cotizacion'   => $item['id_cotizacion'] ?? null,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Guardar vínculos N:N (Pedido) - Automático por ítems
            |--------------------------------------------------------------------------
            */
            if ($validated['motivo'] === 'pedido') {
                $vinculosAuto = [];
                foreach ($validated['items'] as $item) {
                    $idCot = $item['id_cotizacion'] ?? null;
                    if ($idCot) {
                        if (!isset($vinculosAuto[$idCot])) {
                            $vinculosAuto[$idCot] = [
                                'id_cot'   => $idCot,
                                'articulo' => Str::limit($item['descripcion'], 100),
                                'cantidad' => 0
                            ];
                        }
                        $vinculosAuto[$idCot]['cantidad'] += (float)($item['cantidad'] ?? 0);
                    }
                }

                foreach ($vinculosAuto as $v) {
                    $orden->cotizaciones()->attach($v['id_cot'], [
                        'articulo' => $v['articulo'],
                        'cantidad' => $v['cantidad']
                    ]);

                    // Actualizar estado de la cotización vinculada
                    $cotizacion = \App\Models\Cotizacion::find($v['id_cot']);
                    if ($cotizacion) {
                        $totalPedida = $cotizacion->ordenesCompra()->sum('cotizacion_oc.cantidad');
                        $totalCotizada = $cotizacion->items()->sum('cantidad');

                        if ($totalPedida >= $totalCotizada) {
                            $cotizacion->update(['estado_cotizacion' => 'a']); // Aceptada
                        } else {
                            $cotizacion->update(['estado_cotizacion' => 'p']); // Parcial
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('ordenes.index')->with('success', 'Orden de compra creada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $orden = OrdenCompra::with(['items.cotizacionItem', 'items.cotizacion', 'cotizaciones', 'cliente', 'remitos.items'])->findOrFail($id);
        return view('admin.ordenes.show', compact('orden'));
    }

    public function edit($id)
    {
        $orden = OrdenCompra::with(['cliente','items.cotizacionItem', 'items.cotizacion', 'cotizaciones'])->findOrFail($id);
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
            'motivo'            => 'required|in:pedido,particular',
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
            'items.*.id_cotizacion_item'    => 'nullable|exists:cotizacion_items,id_cot_item',
            'items.*.id_cotizacion'         => 'nullable|exists:cotizaciones,id_cotizacion',

            'vinculos'          => 'nullable|array',
            'vinculos.*.id_cot' => 'required|exists:cotizaciones,id_cotizacion',
            'vinculos.*.articulo' => 'required|string',
            'vinculos.*.cantidad' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
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

            $orden->update($validated);

            // items
            $orden->items()->delete();
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
                    'id_cotizacion_item' => $item['id_cotizacion_item'] ?? null,
                    'id_cotizacion'      => $item['id_cotizacion'] ?? null,
                ]);
            }

            // vinculos
            if ($validated['motivo'] === 'pedido') {
                $orden->cotizaciones()->detach();
                
                $vinculosAuto = [];
                foreach ($validated['items'] as $item) {
                    $idCot = $item['id_cotizacion'] ?? null;
                    if ($idCot) {
                        if (!isset($vinculosAuto[$idCot])) {
                            $vinculosAuto[$idCot] = [
                                'id_cot'   => $idCot,
                                'articulo' => Str::limit($item['descripcion'], 100),
                                'cantidad' => 0
                            ];
                        }
                        $vinculosAuto[$idCot]['cantidad'] += (float)($item['cantidad'] ?? 0);
                    }
                }

                foreach ($vinculosAuto as $v) {
                    $orden->cotizaciones()->attach($v['id_cot'], [
                        'articulo' => $v['articulo'],
                        'cantidad' => $v['cantidad']
                    ]);
                    
                    // Actualizar estado de la cotización
                    $cotizacion = \App\Models\Cotizacion::find($v['id_cot']);
                    if ($cotizacion) {
                        $totalOC = $cotizacion->ordenesCompra()->sum('cotizacion_oc.cantidad');
                        $totalCot = $cotizacion->items()->sum('cantidad');
                        
                        if ($totalOC >= $totalCot) {
                            $cotizacion->update(['estado_cotizacion' => 'a']);
                        } else {
                            $cotizacion->update(['estado_cotizacion' => 'p']);
                        }
                    }
                }
            } else {
                $orden->cotizaciones()->detach();
            }

            $orden->actualizarEstado();

            DB::commit();
            return redirect()->route('ordenes.index')->with('success', 'Orden de compra actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $orden = OrdenCompra::findOrFail($id);

        // No permitir eliminar si tiene remitos que no estén anulados
        if ($orden->remitos()->where('estado', '!=', 'Anulado')->exists()) {
            return back()->with('error', 'No se puede eliminar la Orden de Compra porque tiene Remitos asociados.');
        }

        if ($orden->archivo) {
            Storage::disk('public')->delete($orden->archivo);
        }

        $orden->items()->delete();
        $orden->cotizaciones()->detach();
        $orden->delete();

        return redirect()->route('ordenes.index')->with('success', 'Orden de compra eliminada correctamente.');
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
    public function jsonItems(OrdenCompra $orden)
    {
        $orden->load('items');
        return response()->json($orden->items);
    }
}
