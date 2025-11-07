<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Factura;
use App\Models\Cliente;
use App\Models\OrdenCompra;
use Illuminate\Support\Facades\Auth;

class FacturaController extends Controller
{
    /**
     * Listado general de facturas
     */
    public function index()
    {
        $facturas = Factura::with('cliente', 'orden')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.facturas.index', compact('facturas'));
    }

    /**
     * Formulario de creación de nueva factura
     */
    public function create()
    {
        $clientes = Cliente::orderBy('nombres')->get();
        $ordenes  = OrdenCompra::orderBy('id', 'desc')->get();

        return view('admin.facturas.create', compact('clientes', 'ordenes'));
    }

    /**
     * Guardar nueva factura (queda en “pendiente”)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id'  => 'required|exists:clientes,id',
            'orden_id'    => 'nullable|exists:ordenes,id',
            'tipo'        => 'required|in:A,B',
            'monto'       => 'required|numeric|min:0',
            'descripcion' => 'nullable|string|max:500',
        ]);

        $factura = new Factura($validated);
        $factura->estado = 'pendiente';
        $factura->creado_por = Auth::id();
        $factura->save();

        return redirect()
            ->route('admin.facturas.index')
            ->with('success', 'Factura creada correctamente y marcada como pendiente.');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Factura $factura)
    {
        $clientes = Cliente::all();
        $ordenes  = OrdenCompra::all();

        return view('admin.facturas.edit', compact('factura', 'clientes', 'ordenes'));
    }

    /**
     * Actualizar datos de factura
     */
    public function update(Request $request, Factura $factura)
    {
        $validated = $request->validate([
            'cliente_id'  => 'required|exists:clientes,id',
            'orden_id'    => 'nullable|exists:ordenes,id',
            'tipo'        => 'required|in:A,B',
            'monto'       => 'required|numeric|min:0',
            'descripcion' => 'nullable|string|max:500',
        ]);

        $factura->update($validated);

        return redirect()
            ->route('admin.facturas.index')
            ->with('success', 'Factura actualizada correctamente.');
    }

    /**
     * Aprobar factura (Ingeniero)
     */
    public function aprobar($id)
    {
        $factura = Factura::findOrFail($id);
        $factura->estado = 'aprobada';
        $factura->aprobado_por = Auth::id();
        $factura->save();

        return redirect()->route('admin.facturas.index')
                         ->with('success', 'Factura aprobada correctamente.');
    }

    /**
     * Enviar factura aprobada a ARCA / AFIP
     */
    public function enviarAFIP($id)
    {
        $factura = Factura::findOrFail($id);

        if ($factura->estado !== 'aprobada') {
            return redirect()->route('admin.facturas.index')
                             ->with('error', 'Solo las facturas aprobadas pueden enviarse a AFIP.');
        }

        // Aquí iría la integración real con ARCA / AFIP
        $factura->estado = 'enviada_afip';
        $factura->save();

        return redirect()->route('admin.facturas.index')
                         ->with('success', 'Factura enviada a AFIP correctamente.');
    }
}
