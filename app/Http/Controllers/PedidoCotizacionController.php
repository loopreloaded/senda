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
        $query = PedidoCotizacion::with(['cliente', 'cotizaciones.items'])
            ->where('estado_pc', '!=', 'b'); // 🔥 Excluir bajas lógicas

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


        $pedidos = $query->orderByDesc('id_ped_cot')
            ->paginate(10);

        return view('admin.pedidos-cotizacion.index', compact('pedidos'));
    }

    /**
     * Buscar pedidos para autocompletado en Cotizaciones
     */
    public function buscar(Request $request)
    {
        $q = $request->query('q');
        $id_cliente = $request->query('cliente_id');
        $include_id = $request->query('include_id');

        $query = PedidoCotizacion::query()
            ->where('estado_pc', '!=', 'b');

        if ($id_cliente) {
            $query->where('id_cliente', $id_cliente);
        }

        // Si hay un ID específico que incluir (ej: al editar), asegurar que está en el resultado
        if ($include_id && is_numeric($include_id)) {
            $query->orWhere('id_ped_cot', $include_id);
        }

        if ($q) {
            $query->where('nro_solicitud', 'LIKE', "%$q%");
        }

        $pedidos = $query->limit(20)->get();

        return response()->json($pedidos);
    }

    public function create()
    {
        $cotizaciones = Cotizacion::all();
        return view('admin.pedidos-cotizacion.create', compact('cotizaciones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha'           => 'required|date',
            'id_cliente'      => 'required|exists:clientes,id',
            'items_excluidos' => 'nullable|string|max:255',
            'nro_solicitud'   => 'nullable|string|max:100',
            'cantidad'        => 'required|integer|min:1',
            'archivo'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'observaciones'   => 'nullable|string',
            'motivo'          => 'nullable|string|max:50'
        ]);

        // Determinar estado automáticamente
        $estado = $request->estado ?? 'p';

        if ($request->motivo === 'pedido') {
            if (empty($request->items_excluidos)) {
                $estado = 'c';
            } else {
                $estado = 'p';
            }
        }

        $data = [
            'fecha'           => $request->fecha,
            'id_cliente'      => $request->id_cliente,
            'estado'          => $estado,
            'items_excluidos' => $request->items_excluidos,
            'nro_solicitud'   => $request->nro_solicitud,
            'cantidad'        => $request->cantidad,
            'observaciones'   => $request->observaciones,
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

    public function edit(PedidoCotizacion $pedido_cotizacion)
    {
        $clientes = Cliente::orderBy('razon_social')->get();

        return view('admin.pedidos-cotizacion.edit', [
            'pedido_cotizacion' => $pedido_cotizacion,
            'clientes' => $clientes
        ]);
    }

    public function update(Request $request, PedidoCotizacion $pedido_cotizacion)
    {
        $validated = $request->validate([
            'id_cliente'    => 'required|exists:clientes,id',
            'nro_solicitud' => 'nullable|string|max:100',
            'cantidad'      => 'required|integer|min:1',
            'archivo'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'fecha'         => 'required|date',
            'observaciones' => 'nullable|string'
        ]);

        // 🚀 Si NO se sube archivo, lo quitamos del array para no sobrescribirlo
        if (!$request->hasFile('archivo')) {
            unset($validated['archivo']);
        } else {

            // Si se sube uno nuevo, eliminamos el anterior
            if ($pedido_cotizacion->archivo) {
                \Storage::disk('public')->delete($pedido_cotizacion->archivo);
            }

            $validated['archivo'] = $request->file('archivo')
                ->store('pedidos-cotizacion', 'public');
        }

        $pedido_cotizacion->update($validated);

        return redirect()
            ->route('pedidos-cotizacion.index')
            ->with('success', 'Pedido actualizado correctamente');
    }

    public function destroy(PedidoCotizacion $pedido_cotizacion)
    {
        $pedido_cotizacion->estado_pc = 'b'; // baja lógica
        $pedido_cotizacion->save();

        return redirect()->route('pedidos-cotizacion.index')
            ->with('success', 'Pedido eliminado correctamente');
    }

    public function storeComentario(Request $request)
    {
        $request->validate([
            'pedido_id' => 'required',
            'comentarios' => 'required|string'
        ]);

        PedidoCotizacion::where('id_ped_cot', $request->pedido_id)
            ->update([
                'comentarios' => $request->comentarios
            ]);

        return back()->with('success', 'Comentarios guardada correctamente.');
    }

    public function noCotizo($id)
    {
        $pedido = PedidoCotizacion::findOrFail($id);

        $pedido->estado_pc = 'n';
        $pedido->save();

        return redirect()->back()->with('ok','Pedido marcado como NO COTIZÓ');
    }

}
