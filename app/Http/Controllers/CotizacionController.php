<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Cliente;
use Illuminate\Http\Request;

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
            'moneda' => 'required|string|max:10',
            'forma_pago' => 'required|string|max:20',
            'importe_total' => 'nullable|numeric'
        ]);

        Cotizacion::create($request->all());

        return redirect()->route('cotizaciones.index')
            ->with('success', 'Cotización creada correctamente');
    }

    public function show(Cotizacion $cotizacion)
    {
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
