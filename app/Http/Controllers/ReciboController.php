<?php

namespace App\Http\Controllers;

use App\Models\Recibo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReciboController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver recibos')->only(['index', 'show']);
        $this->middleware('permission:crear recibos')->only(['create', 'store']);
        $this->middleware('permission:editar recibos')->only(['edit', 'update']);
        $this->middleware('permission:eliminar recibos')->only(['destroy']);
        $this->middleware('permission:aprobar recibos')->only(['aprobar']);
    }

    /**
     * Listado de recibos
     */
    public function index()
    {
        // Si querés ordenar por fecha descendente:
        $recibos = Recibo::orderBy('fecha', 'desc')->get();

        return view('admin.recibos.index', compact('recibos'));
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        $recibo = new Recibo();

        return view('admin.recibos.create', compact('recibo'));
    }

    /**
     * Guardar nuevo recibo
     */
    public function store(Request $request)
    {
        $request->validate([
            'nro_recibo' => 'required|string|max:20',
            'fecha'      => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            Recibo::create([
                'nro_recibo' => $request->nro_recibo,
                'fecha'      => $request->fecha,
            ]);

            DB::commit();

            return redirect()
                ->route('recibos.index')
                ->with('success', 'Recibo creado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    /**
     * Mostrar un recibo
     */
    public function show(Recibo $recibo)
    {
        return view('admin.recibos.show', compact('recibo'));
    }

    /**
     * Formulario de edición
     */
    public function edit(Recibo $recibo)
    {
        return view('admin.recibos.edit', compact('recibo'));
    }

    /**
     * Actualizar un recibo
     */
    public function update(Request $request, Recibo $recibo)
    {
        $request->validate([
            'nro_recibo' => 'required|string|max:20',
            'fecha'      => 'required|date',
        ]);

        $recibo->update([
            'nro_recibo' => $request->nro_recibo,
            'fecha'      => $request->fecha,
        ]);

        return redirect()
            ->route('recibos.index')
            ->with('success', 'Recibo actualizado correctamente.');
    }

    /**
     * Eliminar un recibo
     */
    public function destroy(Recibo $recibo)
    {
        $recibo->delete();

        return redirect()
            ->route('recibos.index')
            ->with('success', 'Recibo eliminado.');
    }

    /**
     * Aprobar un recibo
     *
     * Nota: tu tabla actual NO tiene columna 'estado'.
     * Cuando la agregues, podés descomentar la lógica.
     */
    public function aprobar(Recibo $recibo)
    {
        // Ejemplo cuando exista la columna 'estado' en la tabla recibos:
        // $recibo->estado = 'aprobado';
        // $recibo->save();

        return back()->with('info', 'Función de aprobación aún no implementada en la base de datos.');
    }

    /**
     * Generar PDF de un recibo
     */
    public function generar_pdf_recibo(Recibo $recibo)
    {
        // Si más adelante el recibo tiene relaciones, podés cargarlas acá con load()

        $pdf = \PDF::loadView('admin.recibos.pdf', compact('recibo'));

        // Usamos id_recibo porque es tu PK real
        return $pdf->stream("Recibo-{$recibo->id_recibo}.pdf");
    }
}
