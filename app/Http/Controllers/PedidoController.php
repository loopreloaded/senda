<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Cotizacion;
use Illuminate\Http\Request;
use App\Models\Cliente;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $query = Pedido::with(['cliente', 'cotizaciones.items'])
            ->where('estado_pc', '!=', 'b'); // 🔥 Excluir bajas lógicas

        // Filtro cliente
        if ($request->filled('cliente')) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('razon_social', 'like', '%' . $request->cliente . '%');
            });
        }

        // Filtro estado
        if ($request->filled('estado')) {
            $query->where('estado_pc', $request->estado);
        }


        $pedidos = $query->orderByDesc('id_ped_cot')
            ->paginate(10);

        return view('admin.pedidos.index', compact('pedidos'));
    }

    /**
     * Buscar pedidos para autocompletado en Cotizaciones
     */
    public function buscar(Request $request)
    {
        $q = $request->query('q');
        $id_cliente = $request->query('cliente_id');
        $include_id = $request->query('include_id');

        $query = Pedido::query()
            ->where('estado_pc', '!=', 'b'); // Excluir bajas

        if ($id_cliente) {
            $query->where('id_cliente', $id_cliente);
        }

        // --- SOLUCIÓN: Excluir cotizados ('c') pero permitir el incluido (para edición) ---
        $query->where(function($sub) use ($include_id) {
            $sub->where('estado_pc', '!=', 'c');
            if ($include_id && is_numeric($include_id)) {
                $sub->orWhere('id_ped_cot', $include_id);
            }
        });

        if ($q) {
            $query->where('nro_solicitud', 'LIKE', "%$q%");
        }

        $pedidos = $query->limit(20)->get();

        return response()->json($pedidos);
    }

    public function create()
    {
        $cotizaciones = Cotizacion::all();
        return view('admin.pedidos.create', compact('cotizaciones'));
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
            'estado_pc'       => $estado, // Guardar en el campo correcto
            'items_excluidos' => $request->items_excluidos,
            'nro_solicitud'   => $request->nro_solicitud,
            'cantidad'        => $request->cantidad,
            'observaciones'   => $request->observaciones,
        ];

        // Archivo
        if ($request->hasFile('archivo')) {
            $data['archivo'] = $request->file('archivo')
                ->store('pedidos', 'public');
        }

        Pedido::create($data);

        return redirect()
            ->route('pedidos.index')
            ->with('success', 'Pedido registrado correctamente');
    }

    public function show(Pedido $pedido)
    {
        return view('admin.pedidos.show', ['pedido' => $pedido]);
    }

    public function edit(Pedido $pedido)
    {
        $clientes = Cliente::orderBy('razon_social')->get();

        return view('admin.pedidos.edit', [
            'pedido' => $pedido,
            'clientes' => $clientes
        ]);
    }

    public function update(Request $request, Pedido $pedido)
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
            if ($pedido->archivo) {
                \Storage::disk('public')->delete($pedido->archivo);
            }

            $validated['archivo'] = $request->file('archivo')
                ->store('pedidos', 'public');
        }

        // Verificar que la nueva cantidad no sea menor a lo ya cotizado
        $yaCotizado = \DB::table('pedido_cotizacion')
            ->where('id_pedido_cot', $pedido->id_ped_cot)
            ->sum('cantidad');

        if ($request->cantidad < $yaCotizado) {
            return back()->withErrors([
                'cantidad' => "No se puede reducir la cantidad a {$request->cantidad} porque ya se han cotizado {$yaCotizado} unidades para este pedido."
            ])->withInput();
        }

        $pedido->update($validated);

        return redirect()
            ->route('pedidos.index')
            ->with('success', 'Pedido actualizado correctamente');
    }

    public function destroy(Pedido $pedido)
    {
        $pedido->estado_pc = 'b'; // baja lógica
        $pedido->save();

        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido eliminado correctamente');
    }

    public function storeComentario(Request $request)
    {
        $request->validate([
            'pedido_id' => 'required',
            'comentarios' => 'required|string'
        ]);

        Pedido::where('id_ped_cot', $request->pedido_id)
            ->update([
                'comentarios' => $request->comentarios
            ]);

        return back()->with('success', 'Comentarios guardada correctamente.');
    }

    public function noCotizo($id)
    {
        $pedido = Pedido::findOrFail($id);

        $pedido->estado_pc = 'n';
        $pedido->save();

        return redirect()->back()->with('ok','Pedido marcado como NO COTIZÓ');
    }

}
