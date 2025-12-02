<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Factura;
use App\Models\Cliente;
use App\Models\FacturaItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SystemLog;
use App\Services\Afip\AfipService;

class FacturaController extends Controller
{
    /**
     * Listado general de facturas
     */
    public function index(Request $request)
    {
        $query = Factura::with('cliente');

        // FILTRO POR CLIENTE (razón social o nombre)
        if ($request->filled('cliente')) {
            $query->whereHas('cliente', function($q) use ($request){
                $q->where('razon_social', 'like', '%'.$request->cliente.'%');
            });
        }

        // FILTRO POR TIPO
        if ($request->filled('tipo')) {
            $query->where('tipo_comprobante', $request->tipo);
        }

        // FILTRO POR ESTADO
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // FILTRO POR FECHAS (rango)
        if ($request->filled('desde')) {
            $query->whereDate('fecha_emision', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha_emision', '<=', $request->hasta);
        }

        // ORDEN + PAGINACIÓN CONSERVANDO FILTROS
        $facturas = $query->orderBy('created_at', 'desc')
                        ->paginate(10)
                        ->appends($request->query());

        return view('admin.facturas.index', compact('facturas'));
    }


    public function show($id)
    {
        $factura = Factura::with(['cliente', 'items'])->findOrFail($id);
        return view('admin.facturas.show', compact('factura'));
    }

    /**
     * Formulario de creación de nueva factura
     */
    public function create()
    {
        // Ya no se listan clientes, solo se muestra el formulario vacío
        return view('admin.facturas.create');
    }

    /**
     * Guardar nueva factura (queda en “pendiente”)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Datos del cliente
            'razon_social'   => 'required|string|max:255',
            'cuit'           => 'required|digits:11',
            'condicion_iva'  => 'required|string|max:50',
            'direccion'      => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',

            // Datos de la factura
            'tipo_comprobante' => 'required|in:A,B',
            'fecha_emision'    => 'required|date',
            'concepto'         => 'required|in:1,2,3',
            'condicion_venta'  => 'required|string|max:100',
            'moneda' => 'required|in:ARS,USD',
            'valor_dolar' => 'required_if:moneda,USD|numeric|min:0',


            // Campos adicionales SOLO para Servicios
            'fecha_desde'      => 'required_if:concepto,2|nullable|date',
            'fecha_hasta'      => 'required_if:concepto,2|nullable|date',
            'vencimiento_pago' => 'required_if:concepto,2|nullable|date',

            // Ítems
            'items' => 'required|array|min:1',
            'items.*.descripcion' => 'required|string|max:255',
            'items.*.cantidad'    => 'required|numeric|min:1',
            'items.*.precio'      => 'required|numeric|min:0',
            'items.*.iva'         => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Buscar o crear cliente
            $cliente = Cliente::firstOrCreate(
                ['cuit' => $validated['cuit']],
                [
                    'razon_social'  => $validated['razon_social'],
                    'condicion_iva' => $validated['condicion_iva'],
                    'direccion'     => $validated['direccion'],
                    'email'         => $validated['email'] ?? null,
                ]
            );

            // Crear factura
            $factura = new Factura();
            $factura->cliente_id       = $cliente->id;
            $factura->tipo_comprobante = $validated['tipo_comprobante'];

            // Punto de venta fijo
            $factura->punto_venta      = 4;
            $factura->valor_dolar      = $validated['valor_dolar'];

            $factura->fecha_emision    = $validated['fecha_emision'];
            $factura->concepto         = $validated['concepto'];
            $factura->condicion_venta  = $validated['condicion_venta'];
            $factura->moneda = $validated['moneda'];
            $factura->estado           = 'pendiente';
            $factura->creado_por       = Auth::id();

            // NUEVOS CAMPOS DE SERVICIOS
            $factura->fecha_desde      = $validated['fecha_desde']      ?? null;
            $factura->fecha_hasta      = $validated['fecha_hasta']      ?? null;
            $factura->vencimiento_pago = $validated['vencimiento_pago'] ?? null;

            $factura->save();

            // Guardar ítems
            $total = 0;
            foreach ($validated['items'] as $item) {
                $subtotal = $item['cantidad'] * $item['precio'] * (1 + ($item['iva'] ?? 0) / 100);

                FacturaItem::create([
                    'factura_id'      => $factura->id,
                    'descripcion'     => $item['descripcion'],
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'iva'             => $item['iva'] ?? 0,
                    'subtotal'        => $subtotal,
                ]);

                $total += $subtotal;
            }

            $factura->importe_total = $total;
            $factura->save();

            DB::commit();

            return redirect()
                ->route('facturas.index')
                ->with('success', 'Factura creada correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al guardar la factura: ' . $e->getMessage());
        }
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

    public function enviarAfip($id)
    {
        Log::info("[".now()->format('Y-m-d H:i:s')."] ➡ Iniciando envío a AFIP para factura ID: {$id}");

        $factura = Factura::findOrFail($id);
        $afip = new AfipService(true); // true = modo homologación

        try {

            // Obtener TA y enviar factura
            $ta = $afip->obtenerToken();
            $res = $afip->enviarFactura($factura);

            // Extraer respuesta AFIP del objeto resultado
            $afipResponse = $res->FECAESolicitarResult ?? null;

            $mensajeUsuario = "Factura enviada correctamente a AFIP.";
            $mensajeLog = [];
            $estado = "success";

            if ($afipResponse) {

                // --- 1) ANALIZAR ERRORES ---
                if (!empty($afipResponse->Errors) && isset($afipResponse->Errors->Err)) {

                    $error = $afipResponse->Errors->Err;
                    $code = $error->Code ?? null;
                    $msg  = $error->Msg ?? 'Error desconocido';

                    $mensajeUsuario = "AFIP rechazó la factura. Código {$code}: {$msg}";
                    $estado = "error";

                    Log::error(" AFIP rechazó la factura (Error {$code}): {$msg}", [
                        'factura_id' => $factura->id,
                        'afip_response' => $afipResponse
                    ]);

                    SystemLog::create([
                        'context' => 'AFIP',
                        'action' => 'EnvioFactura',
                        'related_id' => $factura->id,
                        'related_type' => Factura::class,
                        'level' => 'error',
                        'message' => "Error {$code}: {$msg}",
                        'data' => $afipResponse,
                        'user_id' => Auth::id(),
                    ]);

                    return back()->with('error', $mensajeUsuario);
                }

                // --- 2) ANALIZAR OBSERVACIONES (NO SON ERROR PERO ADVERTENCIAS IMPORTANTES) ---
                if (!empty($afipResponse->FeDetResp->FECAEDetResponse->Observaciones)) {

                    $obs = $afipResponse->FeDetResp->FECAEDetResponse->Observaciones->Obs;
                    $code = $obs->Code ?? null;
                    $msg  = $obs->Msg ?? 'Observación desconocida';

                    $mensajeUsuario .= " (Advertencia AFIP {$code}: {$msg})";

                    Log::warning(" Observación AFIP ({$code}): {$msg}", [
                        'factura_id' => $factura->id,
                        'afip_response' => $afipResponse
                    ]);

                    $mensajeLog[] = "Observación {$code}: {$msg}";
                }

                // --- 3) SI TIENE CAE OK ---
                $respuestaDetalle = $afipResponse->FeDetResp->FECAEDetResponse ?? null;

                if ($respuestaDetalle && $respuestaDetalle->Resultado === "A") {

                    $cae = $respuestaDetalle->CAE ?? null;
                    $vto = $respuestaDetalle->CAEFchVto ?? null;

                    // 🔥 ACTUALIZAR FACTURA EN BD
                    $factura->estado = "aprobada";
                    $factura->cae = $cae;
                    $factura->vto_cae = $vto;
                    $factura->aprobado_por = Auth::id();
                    $factura->save();

                    $mensajeUsuario = "Factura autorizada por AFIP. CAE: {$cae} (Venc: {$vto})";

                    Log::info("✅ Factura autorizada por AFIP - CAE {$cae}", [
                        'factura_id' => $factura->id,
                        'afip_response' => $afipResponse
                    ]);

                    SystemLog::create([
                        'context' => 'AFIP',
                        'action' => 'EnvioFactura',
                        'related_id' => $factura->id,
                        'related_type' => Factura::class,
                        'level' => 'info',
                        'message' => "Factura autorizada. CAE {$cae}",
                        'data' => $afipResponse,
                        'user_id' => Auth::id(),
                    ]);

                    return back()->with('success', $mensajeUsuario);
                }

                // --- 4) SI NO FUE AUTORIZADA ---
                if ($respuestaDetalle && $respuestaDetalle->Resultado === "R") {

                    $obs = $respuestaDetalle->Observaciones->Obs ?? null;

                    if ($obs) {
                        $code = $obs->Code ?? 'Sin código';
                        $msg  = $obs->Msg ?? 'AFIP rechazó la factura sin detalles.';

                        $mensajeUsuario = "AFIP rechazó la factura. Código {$code}: {$msg}";
                    } else {
                        $mensajeUsuario = "AFIP rechazó la factura sin detalle adicional.";
                    }

                    Log::error("❌ AFIP rechazó la factura: {$mensajeUsuario}", [
                        'factura_id' => $factura->id,
                        'afip_response' => $afipResponse
                    ]);

                    SystemLog::create([
                        'context' => 'AFIP',
                        'action' => 'EnvioFactura',
                        'related_id' => $factura->id,
                        'related_type' => Factura::class,
                        'level' => 'error',
                        'message' => $mensajeUsuario,
                        'data' => $afipResponse,
                        'user_id' => Auth::id(),
                    ]);

                    return back()->with('error', $mensajeUsuario);
                }

            }

            // --- SI LLEGA ACÁ: RESPUESTA ENVIADA PERO SIN DETALLE ---
            Log::info(" AFIP devolvió una respuesta sin detalle claro", ['factura_id' => $factura->id, 'response' => $res]);

            return back()->with('success', $mensajeUsuario);

        } catch (\Exception $e) {

            Log::error("❌ Error al enviar factura a AFIP: ".$e->getMessage(), ['factura_id' => $factura->id]);

            SystemLog::create([
                'context' => 'AFIP',
                'action' => 'EnvioFactura',
                'related_id' => $factura->id,
                'related_type' => Factura::class,
                'level' => 'error',
                'message' => $e->getMessage(),
                'data' => [],
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Error al enviar a AFIP: '.$e->getMessage());
        }
    }

    public function generar_pdf_factura($id)
    {
        $factura = Factura::with('cliente', 'items')->findOrFail($id);

        // ====== CAMPOS SEGUROS (reemplazo con "-") ======
        $cuitEmisor        = $factura->cuit_emisor ?? "-";
        $puntoVenta        = $factura->punto_venta ?? "-";
        $tipoCmpCodigo     = $factura->tipo_comprobante_codigo ?? "-";
        $numComprobante    = $factura->numero_comprobante ?? "-";
        $importeTotal      = $factura->importe_total ?? "-";
        $fechaEmision      = $factura->fecha_emision ?? "-";
        $cae               = $factura->cae ?? "-";
        $cuitReceptor      = $factura->cliente->cuit ?? "-";

        // ====== QR AFIP (con valores seguros) ======
        $qrData = [
            "ver"        => 1,
            "fecha"      => $fechaEmision,
            "cuit"       => $cuitEmisor,
            "ptoVta"     => $puntoVenta,
            "tipoCmp"    => $tipoCmpCodigo,
            "nroCmp"     => $numComprobante,
            "importe"    => $importeTotal,
            "moneda"     => "PES",
            "ctz"        => 1,
            "tipoDocRec" => 80,
            "nroDocRec"  => $cuitReceptor,
            "tipoCodAut" => "E",
            "codAut"     => $cae
        ];

        $qrBase64 = base64_encode(json_encode($qrData));
        $urlQr = "https://www.afip.gob.ar/fe/qr/?p=" . $qrBase64;

        // PDF
        $pdf = \PDF::loadView('admin.facturas.pdf', compact('factura', 'urlQr'));

        // 👉 Ya no se valida el estado
        return $pdf->stream("Factura-{$factura->id}.pdf");
    }


    private function firmarXML($xmlPath)
    {
        $certPath = base_path(env('AFIP_CERT_PATH'));
        $keyPath  = base_path(env('AFIP_KEY_PATH'));

        $xml = file_get_contents($xmlPath);

        openssl_pkcs7_sign(
            $xmlPath,
            $xmlPath . '.tmp',
            "file://$certPath",
            ["file://$keyPath", ""],
            [],
            PKCS7_BINARY | PKCS7_DETACHED
        );

        $signed = file_get_contents($xmlPath . '.tmp');
        unlink($xmlPath . '.tmp');

        return $signed;
    }

    public function guardarObservacion(Request $request, $id)
    {
        $factura = Factura::findOrFail($id);
        $factura->observaciones = $request->observaciones;
        $factura->save();

        return back()->with('success','Observación guardada correctamente.');
    }


}
