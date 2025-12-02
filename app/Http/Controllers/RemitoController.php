<?php

namespace App\Http\Controllers;

use App\Models\Remito;
use App\Models\RemitoItem;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RemitoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver remitos')->only(['index', 'show']);
        $this->middleware('permission:crear remitos')->only(['create', 'store']);
        $this->middleware('permission:editar remitos')->only(['edit', 'update']);
        $this->middleware('permission:eliminar remitos')->only(['destroy']);
        $this->middleware('permission:aprobar remitos')->only(['aprobar']);
    }

    public function index()
    {
        $remitos = Remito::with('items')->get();

        return view('admin.remitos.index', compact('remitos'));
    }

    public function create()
    {
        $clientes = Cliente::all();
        return view('admin.remitos.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha'         => 'required|date',
            'razon_social'  => 'required|string|max:255',
            'domicilio'     => 'required|string|max:255',
            'localidad'     => 'required|string|max:150',
            'orden_compra'  => 'nullable|string|max:100',
            'cuit'          => 'required|digits:11',

            'items' => 'required|array|min:1',
            'items.*.articulo'    => 'required|string|max:20',
            'items.*.cantidad'    => 'required|numeric|min:1',
            'items.*.descripcion' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {

            $remito = Remito::create([
                'fecha'         => $request->fecha,
                'razon_social'  => $request->razon_social,
                'domicilio'     => $request->domicilio,
                'localidad'     => $request->localidad,
                'orden_compra'  => $request->orden_compra,
                'cuit'          => $request->cuit,
                'estado'        => 'pendiente',
                'creado_por'    => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                RemitoItem::create([
                    'remito_id'   => $remito->id,
                    'articulo'    => $item['articulo'],
                    'cantidad'    => $item['cantidad'],
                    'descripcion' => $item['descripcion'],
                ]);
            }

            DB::commit();
            return redirect()->route('remitos.index')->with('success', 'Remito creado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => $e->getMessage()]);
        }
    }



    public function show($id)
    {
        $remito = Remito::with('cliente', 'items')->findOrFail($id);
        return view('admin.remitos.show', compact('remito'));
    }

    public function edit($id)
    {
        $remito = Remito::with('items')->findOrFail($id);
        $clientes = Cliente::all();
        return view('admin.remitos.edit', compact('remito', 'clientes'));
    }

    public function update(Request $request, $id)
    {
        $remito = Remito::findOrFail($id);

        $request->validate([
            'cliente_id' => 'required',
            'fecha'      => 'required|date',
        ]);

        $remito->update([
            'cliente_id' => $request->cliente_id,
            'fecha'      => $request->fecha,
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('remitos.index')->with('success', 'Remito actualizado correctamente.');
    }

    public function destroy($id)
    {
        Remito::destroy($id);
        return redirect()->route('remitos.index')->with('success', 'Remito eliminado.');
    }

    public function aprobar($id)
    {
        $remito = Remito::findOrFail($id);
        $remito->estado = 'aprobado';
        $remito->save();

        return back()->with('success', 'Remito aprobado.');
    }

   public function generar_pdf_remito($id)
    {
        // Cargar remito con sus items (NO existe 'cliente')
        $remito = Remito::with('items')->findOrFail($id);

        // Generar PDF
        $pdf = \PDF::loadView('admin.remitos.pdf', compact('remito'));

        return $pdf->stream("Remito-{$remito->id}.pdf");
    }


}
