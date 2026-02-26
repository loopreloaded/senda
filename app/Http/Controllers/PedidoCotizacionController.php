<?php

namespace App\Http\Controllers;

use App\Models\PedidoCotizacion;
use App\Models\Cotizacion;
use Illuminate\Http\Request;
use App\Models\Cliente;

class PedidoCotizacionController extends Controller
{
    public function index(Request $request)
    {
        $query = PedidoCotizacion::with('cliente');

        // Filtro cliente
        if ($request->filled('cliente')) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('razon_social', 'like', '%' . $request->cliente . '%');
            });
        }

        // Filtro estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro fecha
        if ($request->filled('fecha')) {
            $query->whereDate('fecha', $request->fecha);
        }

        $pedidos = $query->orderByDesc('id_ped_cot')
            ->paginate(10);

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
            'fecha'         => 'required|date',
            'id_cliente'    => 'required|exists:clientes,id',
            'archivo'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'observaciones' => 'nullable|string'
        ]);

        $data = [
            'fecha'         => $request->fecha,
            'id_cliente'    => $request->id_cliente,
            'estado'        => $request->estado ?? 'p', // por defecto pendiente
            'observaciones' => $request->observaciones,
        ];

        // Archivo
        if ($request->hasFile('archivo')) {
            $data['archivo'] = $request->file('archivo')
                ->store('pedidos-cotizacion', 'public');
        }

        PedidoCotizacion::create($data);

        return redirect()
            ->route('pedidos-cotizacion.index')
            ->with('success', 'Pedido registrado correctamente');
    }

    public function show(PedidoCotizacion $pedido_cotizacion)
    {
        return view('admin.pedidos-cotizacion.show', compact('pedido_cotizacion'));
    }

    public function edit(PedidoCotizacion $pedidos_cotizacion)
    {
        $clientes = Cliente::orderBy('razon_social')->get();

        return view('admin.pedidos-cotizacion.edit', [
            'pedido_cotizacion' => $pedidos_cotizacion,
            'clientes' => $clientes
        ]);
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
