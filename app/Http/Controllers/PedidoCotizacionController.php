<?php

namespace App\Http\Controllers;

use App\Models\PedidoCotizacion;
use App\Models\Cotizacion;
use Illuminate\Http\Request;

class PedidoCotizacionController extends Controller
{
    public function index()
    {
        $pedidos = PedidoCotizacion::with('cotizacion')->latest()->paginate(15);
        return view('admin.pedidos-cotizacion.index', compact('pedidos'));
    }

    public function create()
    {
        $cotizaciones = Cotizacion::all();
        return view('admin.pedidos-cotizacion.create', compact('cotizaciones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'observaciones' => 'nullable|string'
        ]);

        $data = $request->all();

        if ($request->hasFile('archivo')) {
            $data['archivo'] = $request->file('archivo')
                ->store('pedidos-cotizacion', 'public');
        }

        PedidoCotizacion::create($data);

        return redirect()->route('pedidos-cotizacion.index')
            ->with('success', 'Pedido registrado correctamente');
    }

    public function show(PedidoCotizacion $pedido_cotizacion)
    {
        return view('admin.pedidos-cotizacion.show', compact('pedido_cotizacion'));
    }

    public function edit(PedidoCotizacion $pedido_cotizacion)
    {
        return view('admin.pedidos-cotizacion.edit', compact('pedido_cotizacion'));
    }

    public function update(Request $request, PedidoCotizacion $pedido_cotizacion)
    {
        $data = $request->all();

        if ($request->hasFile('archivo')) {
            $data['archivo'] = $request->file('archivo')
                ->store('pedidos-cotizacion', 'public');
        }

        $pedido_cotizacion->update($data);

        return redirect()->route('pedidos-cotizacion.index')
            ->with('success', 'Pedido actualizado');
    }

    public function destroy(PedidoCotizacion $pedido_cotizacion)
    {
        $pedido_cotizacion->delete();

        return redirect()->route('admin.pedidos-cotizacion.index')
            ->with('success', 'Pedido eliminado');
    }
}
