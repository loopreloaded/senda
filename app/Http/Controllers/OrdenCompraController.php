<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use Illuminate\Http\Request;

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
            'numero_oc'         => 'required|string|max:191|unique:orden_compras,numero_oc',
            'fecha'             => 'required|date',
            'proveedor'         => 'required|string|max:191',
            'cuit'              => 'required|digits:11',
            'moneda'            => 'required|string|max:10',
            'condicion_compra'  => 'required|string|max:191',
            'subtotal'          => 'required|numeric|min:0',
            'descuento'         => 'nullable|numeric|min:0',
            'total'             => 'required|numeric|min:0',
        ]);

        // Estado inicial siempre "pendiente"
        $validated['estado'] = 'pendiente';

        OrdenCompra::create($validated);

        return redirect()
            ->route('ordenes.index')
            ->with('success', 'Orden de compra creada correctamente.');
    }

    public function show($id)
    {
        $orden = OrdenCompra::findOrFail($id);
        return view('admin.ordenes.show', compact('orden'));
    }

    public function edit($id)
    {
        $orden = OrdenCompra::findOrFail($id);
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
            'moneda'            => 'required|string|max:10',
            'condicion_compra'  => 'required|string|max:191',
            'subtotal'          => 'required|numeric|min:0',
            'descuento'         => 'nullable|numeric|min:0',
            'total'             => 'required|numeric|min:0',
        ]);

        $orden->update($validated);

        return redirect()
            ->route('ordenes.index')
            ->with('success', 'Orden de compra actualizada correctamente.');
    }

    public function destroy($id)
    {
        $orden = OrdenCompra::findOrFail($id);
        $orden->delete();

        return redirect()
            ->route('ordenes.index')
            ->with('success', 'Orden de compra eliminada correctamente.');
    }
}
