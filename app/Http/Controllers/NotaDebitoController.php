<?php

namespace App\Http\Controllers;

use App\Models\NotaDebito;
use App\Models\NotaDebitoItem;
use App\Models\Factura;
use App\Models\Cliente;
use App\Models\SystemLog;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\Afip\AfipService;

class NotaDebitoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('permission:ver notas de debito')->only(['index', 'show']);
        $this->middleware('permission:crear notas de debito')->only(['create', 'store']);
        $this->middleware('permission:aprobar notas de debito')->only(['update']);
        $this->middleware('permission:enviar nota de debito afip')->only(['enviarAfip']);
    }

    // ---------------------------------------------------
    // LISTADO
    // ---------------------------------------------------
    public function index()
    {
        $notas = NotaDebito::orderBy('id', 'DESC')->paginate(20);
        return view('notas_debito.index', compact('notas'));
    }

    // ---------------------------------------------------
    // CREAR
    // ---------------------------------------------------
    public function create()
    {
        $facturas = Factura::with('cliente')->get();

        return view('admin.notas_debito.create', compact('facturas'));
    }


    // ---------------------------------------------------
    // GUARDAR NUEVA ND
    // ---------------------------------------------------
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'   => 'required',
            'tipo_comprobante' => 'required',
            'punto_venta'  => 'required|numeric',
            'fecha_emision' => 'required|date',
            'items.*.descripcion' => 'required',
            'items.*.cantidad' => 'required|numeric|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        // Calcular total
        $total = 0;
        foreach ($request->items as $item) {
            $total += ($item['cantidad'] * $item['precio_unitario']);
        }

        $nota = NotaDebito::create([
            'factura_id'      => $request->factura_id,
            'cliente_id'      => $request->cliente_id,
            'tipo_comprobante'=> $request->tipo_comprobante,
            'punto_venta'     => $request->punto_venta,
            'numero'          => null, // AFIP lo devuelve
            'fecha_emision'   => $request->fecha_emision,
            'concepto'        => 1,
            'condicion_venta' => $request->condicion_venta ?? '',
            'importe_total'   => $total,
            'estado'          => 'pendiente',
            'creado_por'      => Auth::id(),
        ]);

        // Guardar items
        foreach ($request->items as $it) {
            NotaDebitoItem::create([
                'nota_id'         => $nota->id,
                'descripcion'     => $it['descripcion'],
                'cantidad'        => $it['cantidad'],
                'precio_unitario' => $it['precio_unitario'],
                'iva'             => $it['iva'] ?? 21,
                'subtotal'        => $it['cantidad'] * $it['precio_unitario'],
            ]);
        }

        return redirect()->route('notas_debito.index')->with('success', 'Nota de Débito creada correctamente.');
    }

    // ---------------------------------------------------
    // VER DETALLE
    // ---------------------------------------------------
    public function show($id)
    {
        $nota = NotaDebito::with('items', 'cliente')->findOrFail($id);
        return view('notas_debito.show', compact('nota'));
    }

    // ---------------------------------------------------
    // EDITAR
    // ---------------------------------------------------
    public function edit($id)
    {
        $nota = NotaDebito::with('items')->findOrFail($id);
        $clientes = Cliente::all();
        return view('notas_debito.edit', compact('nota', 'clientes'));
    }

    // ---------------------------------------------------
    // ACTUALIZAR (opcional)
    // ---------------------------------------------------
    public function update(Request $request, $id)
    {
        // Se puede agregar lógica si querés permitir edición antes del AFIP
    }

    // ---------------------------------------------------
    // ENVIAR A AFIP Y ACTUALIZAR CAE
    // ---------------------------------------------------
    public function enviar_nd($id)
    {
        Log::info("Enviando Nota de Débito ID: {$id} a AFIP…");

        $nota = NotaDebito::with('items', 'cliente')->findOrFail($id);
        $afip = new AfipService(); // homologación

        try {
            $ta = $afip->obtenerToken();
            $res = $afip->enviarNotaDebito($nota);

            $resp = $res->FECAESolicitarResult ?? null;
            $det = $resp->FeDetResp->FECAEDetResponse ?? null;

            if ($det && $det->Resultado === "A") {

                $nota->estado = 'aprobada';
                $nota->cae = $det->CAE;
                $nota->vto_cae = $det->CAEFchVto;
                $nota->aprobado_por = Auth::id();
                $nota->numero = $det->CbteDesde;
                $nota->save();

                SystemLog::create([
                    'context' => 'AFIP',
                    'action' => 'EnvioNotaDebito',
                    'related_id' => $nota->id,
                    'related_type' => NotaDebito::class,
                    'level' => 'info',
                    'message' => "ND autorizada. CAE {$det->CAE}",
                    'data' => $resp,
                    'user_id' => Auth::id(),
                ]);

                return back()->with('success', "ND autorizada por AFIP. CAE {$det->CAE}");
            }

            return back()->with('error', 'AFIP rechazó la Nota de Débito');

        } catch (\Exception $e) {
            Log::error($e->getMessage());

            SystemLog::create([
                'context' => 'AFIP',
                'action' => 'EnvioNotaDebito',
                'related_id' => $nota->id,
                'related_type' => NotaDebito::class,
                'level' => 'error',
                'message' => $e->getMessage(),
                'data' => [],
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
