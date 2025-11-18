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
    public function index()
    {
        $facturas = Factura::with('cliente')->orderBy('created_at', 'desc')->paginate(10);
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
            'punto_venta'      => 'required|numeric|min:1',
            'fecha_emision'    => 'required|date',
            'concepto'         => 'required|in:1,2,3',
            'condicion_venta'  => 'required|string|max:100',

            // Ítems
            'items' => 'required|array|min:1',
            'items.*.descripcion' => 'required|string|max:255',
            'items.*.cantidad'    => 'required|numeric|min:1',
            'items.*.precio'      => 'required|numeric|min:0',
            'items.*.iva'         => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Buscar o crear cliente (por CUIT)
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
            $factura->punto_venta      = $validated['punto_venta'];
            $factura->fecha_emision    = $validated['fecha_emision'];
            $factura->concepto         = $validated['concepto'];
            $factura->condicion_venta  = $validated['condicion_venta'];
            $factura->estado           = 'pendiente';
            $factura->creado_por       = Auth::id();
            $factura->save();

            // Guardar ítems
            $total = 0;
            foreach ($validated['items'] as $item) {
                $subtotal = $item['cantidad'] * $item['precio'] * (1 + ($item['iva'] ?? 0) / 100);

                FacturaItem::create([
                    'factura_id'   => $factura->id,
                    'descripcion'  => $item['descripcion'],
                    'cantidad'     => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'iva'          => $item['iva'] ?? 0,
                    'subtotal'     => $subtotal,
                ]);

                $total += $subtotal;
            }

            // Actualizar total en la factura
            $factura->importe_total = $total;
            $factura->save();

            DB::commit();

            return redirect()
                ->route('facturas.index')
                ->with('success', 'Factura creada correctamente y marcada como pendiente.');


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

    /**
     * Enviar factura aprobada a ARCA / AFIP
     */
    public function enviar_afip($id)
    {
        SystemLog::create([
            'context' => 'AFIP',
            'action' => 'inicio_enviar',
            'message' => 'Entrando al método enviarAfip con ID: ' . $id,
            'level' => 'debug',
            'user_id' => Auth::id(),
        ]);

        $factura = Factura::findOrFail($id);

        SystemLog::create([
            'context' => 'AFIP',
            'action' => 'factura_encontrada',
            'message' => 'Factura encontrada, estado: ' . $factura->estado,
            'level' => 'debug',
            'related_id' => $factura->id,
            'related_type' => 'App\Models\Factura',
        ]);

        try {
            // 1️⃣ Generar XML del comprobante
            $xml = view('afip.xml.factura', compact('factura'))->render();
            $xmlPath = storage_path("app/afip/wsaa.xml");
            file_put_contents($xmlPath, $xml);

            // 2️⃣ Firmar XML con OpenSSL
            $signed = $this->firmarXML($xmlPath);

            // 3️⃣ Enviar a AFIP
            $endpoint = config('app.env') === 'production'
                ? env('AFIP_URL_PROD')
                : env('AFIP_URL_HOMO');

            $soap = new \SoapClient($endpoint, [
                'trace' => 1,
                'exceptions' => true,
            ]);

            $response = $soap->__soapCall('FECAESolicitar', [
                ['FeCAEReq' => $signed]
            ]);

            SystemLog::create([
                'servicio' => 'WSFEv1',
                'accion' => 'FECAESolicitar',
                'factura_id' => $factura->id,
                'request' => $soap->__getLastRequest(),
                'response' => $soap->__getLastResponse(),
            ]);


            // 5 Actualizar factura si fue aprobada
            $resultado = $response->FeDetResp->FECAEDetResponse[0] ?? null;
            if ($resultado && $resultado->Resultado === 'A') {
                $factura->update([
                    'estado' => 'aprobada',
                    'cae' => $resultado->CAE,
                    'vto_cae' => $resultado->CAEFchVto,
                ]);
            } else {
                throw new \Exception('Rechazada por AFIP');
            }

            return back()->with('success', 'Factura enviada y aprobada.');
        } catch (\Exception $e) {
           SystemLog::create([
                'contexto' => 'AFIP',
                'accion' => 'FECAESolicitar',
                'detalle' => 'Factura enviada a AFIP',
                'referencia_id' => $factura->id,
                'datos_request' => $soap->__getLastRequest(),
                'datos_response' => $soap->__getLastResponse(),
                'nivel' => 'info',
            ]);


            Log::error('Error AFIP: '.$e->getMessage());
            return back()->with('error', 'Error al enviar la factura: '.$e->getMessage());
        }
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

}
