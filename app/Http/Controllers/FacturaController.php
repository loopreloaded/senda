<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\SystemLog;
use App\Models\Factura;
use App\Models\Cliente;
use App\Models\FacturaItem;
use App\Models\FacturaRemito;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\Afip\AfipService;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use SimpleSoftwareIO\QrCode\Generator;


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
        $latest = \App\Models\Factura::latest('id')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        
        return view('admin.facturas.create', compact('nextId'));
    }

    public function edit($id)
    {
        $factura = \App\Models\Factura::with(['cliente', 'items', 'remitos'])->findOrFail($id);
        return view('admin.facturas.edit', compact('factura'));
    }

    /**
     * Guardar nueva factura
     */
    public function store(Request $request)
    {
        // =============================
        // VALIDACIÓN (logueada)
        // =============================
        try {

            $validated = $request->validate([

                // CLIENTE
                'razon_social'  => 'required|string|max:255',
                'cuit'          => 'required|regex:/^\d{11}$/',
                'condicion_iva' => 'required|string|max:50',
                'direccion'     => 'required|string|max:255',
                'email'         => 'nullable|email|max:255',

                // FACTURA
                'tipo_comprobante' => 'required|in:A,B',
                'fecha_emision'    => 'required|date',
                'concepto'         => 'required|in:1,2,3',
                'condicion_venta'  => 'required|string|max:100',
                'moneda'           => 'required|string',
                'valor_dolar'      => 'nullable|numeric|min:0',
                'motivo'           => 'required|in:pedido,particular',

                // SERVICIOS
                'fecha_desde'      => 'nullable|date',
                'fecha_hasta'      => 'nullable|date',
                'vencimiento_pago' => 'nullable|date',

                // ITEMS
                'items' => 'required|array|min:1',
                'items.*.codigo'                  => 'nullable|string',
                'items.*.descripcion'             => 'required|string|max:255',
                'items.*.cantidad'                => 'required|numeric|min:0.01',
                'items.*.unidad'                  => 'required|numeric|min:1',
                'items.*.precio'                  => 'required|numeric|min:0',
                'items.*.iva'                     => 'required|numeric|min:0',
                'items.*.bonificacion_porcentaje' => 'nullable|numeric|min:0|max:100',
                'items.*.bonificacion_importe'    => 'required|numeric|min:0',
                'items.*.subtotal_sin_iva'         => 'required|numeric|min:0',
                'items.*.subtotal_con_iva'         => 'required|numeric|min:0',
                'items.*.remito_id'               => 'nullable|integer',

                // REMITOS (IDs)
                'remitos' => 'nullable|array',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            Log::warning('Validación fallida al crear factura', [
                'errors'  => $e->errors(),
                'request' => $request->all(),
            ]);

            throw $e; // 👈 deja que Laravel redirija y muestre errores
        }

        // =============================
        // TRANSACCIÓN
        // =============================
        DB::beginTransaction();

        try {

            // 1) CLIENTE
            $cliente = Cliente::firstOrCreate(
                ['cuit' => $validated['cuit']],
                [
                    'razon_social'      => $validated['razon_social'],
                    'condicion_iva_id'  => \App\Models\CondicionIva::where('codigo', $validated['condicion_iva'])->value('id'),
                    'condicion_iibb_id' => \App\Models\CondicionIibb::where('codigo', $request->condicion_iibb)->value('id'),
                    'indice'            => $request->percepcion_iibb_alicuota ?? 0,
                    'direccion'         => $validated['direccion'],
                    'email'             => $validated['email'] ?? null,
                ]
            );

            // 2) FACTURA
            $factura = new Factura();
            $factura->cliente_id       = $cliente->id;
            $factura->tipo_comprobante = $validated['tipo_comprobante'];
            $factura->punto_venta      = 4;
            $factura->fecha_emision    = $validated['fecha_emision'];
            $factura->concepto         = $validated['concepto'];
            $factura->condicion_venta  = $validated['condicion_venta'];
            $factura->moneda           = $validated['moneda'];
            $factura->valor_dolar      = $validated['valor_dolar'] ?? 1;
            $factura->motivo           = $validated['motivo'];
            $factura->estado           = Factura::ESTADO_BORRADOR;
            $factura->creado_por       = Auth::id();

            $factura->fecha_desde      = $validated['fecha_desde'] ?? null;
            $factura->fecha_hasta      = $validated['fecha_hasta'] ?? null;
            $factura->vencimiento_pago = $validated['vencimiento_pago'] ?? null;

            // PERCEPCIONES
            $factura->percepcion_iva_detalle   = $request->percepcion_iva_detalle ?? null;
            $factura->percepcion_iva_base      = $request->percepcion_iva_base ?? 0;
            $factura->percepcion_iva_alicuota  = $request->percepcion_iva_alicuota ?? 0;
            $factura->percepcion_iva_importe   = $request->percepcion_iva_importe ?? 0;

            $factura->percepcion_iibb_detalle  = $request->percepcion_iibb_detalle ?? null;
            $factura->percepcion_iibb_base     = $request->percepcion_iibb_base ?? 0;
            $factura->percepcion_iibb_alicuota = $request->percepcion_iibb_alicuota ?? 0;
            $factura->percepcion_iibb_importe  = $request->percepcion_iibb_importe ?? 0;

            $factura->importe_total_otros_tributos = $request->importe_total_otros_tributos ?? 0;

            $factura->save();

            // 3) ITEMS & REMITOS (N:N Pivot)
            $subtotal_general  = 0;
            $total_iva_general = 0;
            $cant_art_fac      = 0;
            $nombres_articulos = [];
            $vinculos_remitos  = [];

            foreach ($validated['items'] as $item) {

                $iva_importe = $item['subtotal_sin_iva'] * ($item['iva'] / 100);

                FacturaItem::create([
                    'factura_id'              => $factura->id,
                    'codigo'                  => $item['codigo'] ?? null,
                    'unidad'                  => $item['unidad'],
                    'descripcion'             => $item['descripcion'],
                    'cantidad'                => $item['cantidad'],
                    'precio_unitario'         => $item['precio'],
                    'iva'                     => $item['iva'],
                    'bonificacion_porcentaje' => $item['bonificacion_porcentaje'] ?? 0,
                    'bonificacion_importe'    => $item['bonificacion_importe'],
                    'subtotal_sin_iva'        => $item['subtotal_sin_iva'],
                    'subtotal_con_iva'        => $item['subtotal_con_iva'],
                    'subtotal'                => $item['subtotal_con_iva'],
                ]);

                $subtotal_general  += $item['subtotal_sin_iva'];
                $total_iva_general += $iva_importe;
                $cant_art_fac      += $item['cantidad'];
                $nombres_articulos[] = $item['descripcion'];

                // Si viene de un remito, guardamos para el pivot
                if (isset($item['remito_id']) && $item['remito_id']) {
                    $rid = $item['remito_id'];
                    if (!isset($vinculos_remitos[$rid])) {
                        $vinculos_remitos[$rid] = [
                            'articulo' => $item['descripcion'],
                            'cantidad' => 0
                        ];
                    }
                    $vinculos_remitos[$rid]['cantidad'] += $item['cantidad'];
                }
            }

            // 4) TOTALIZAR Y RESUMEN
            $factura->subtotal      = $subtotal_general;
            $factura->total_iva     = $total_iva_general;
            $factura->importe_total = $subtotal_general + $total_iva_general;
            $factura->cant_art_fac  = $cant_art_fac;
            $factura->art_fac       = implode(', ', array_unique($nombres_articulos));
            $factura->save();

            // 5) ATTACH REMITOS (Table remito_factura)
            if ($validated['motivo'] === 'pedido' && !empty($vinculos_remitos)) {
                foreach ($vinculos_remitos as $remito_id => $data) {
                    
                    // VALIDACIÓN DE CANTIDADES
                    $remito = \App\Models\Remito::with('items')->find($remito_id);
                    if ($remito) {
                        $cant_en_remito = $remito->items()->sum('cantidad');
                        $cant_ya_facturada = DB::table('remito_factura')
                            ->where('id_rem', $remito_id)
                            ->sum('cantidad');
                        
                        $disponible = $cant_en_remito - $cant_ya_facturada;
                        
                        if ($data['cantidad'] > $disponible) {
                            throw new \Exception("La cantidad a facturar ({$data['cantidad']}) supera lo disponible en el Remito #{$remito->numero_remito} (Disponible: {$disponible})");
                        }

                        $factura->remitos()->attach($remito_id, [
                            'articulo' => $data['articulo'],
                            'cantidad' => $data['cantidad']
                        ]);
                        
                        // Actualizar estado del remito
                        $remito->actualizarEstado();
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('facturas.index')
                ->with('success', 'Factura creada correctamente');

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Error al guardar factura', [
                'mensaje' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return back()
                ->with('error', 'Error al guardar la factura: ' . $e->getMessage())
                ->withInput();
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
     * Enviar factura a AFIP
     */
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

                    // 🔥 ESTE ES EL DATO QUE TE FALTABA
                    $numeroAfip = $respuestaDetalle->CbteDesde ?? null;

                    if (!$numeroAfip) {
                        throw new \Exception('AFIP no devolvió el número de comprobante (CbteDesde)');
                    }

                    // ✅ ACTUALIZAR FACTURA EN BD (COMPLETO)
                    $factura->estado = Factura::ESTADO_EMITIDA;
                    $factura->cae = $cae;
                    $factura->vto_cae = \Carbon\Carbon::createFromFormat('Ymd', $vto);
                    $factura->numero_comprobante_afip = $numeroAfip; // 👈 CLAVE
                    $factura->aprobado_por = Auth::id();
                    $factura->save();

                    $mensajeUsuario = "Factura autorizada por AFIP. N° {$numeroAfip} - CAE: {$cae}";

                    Log::info("✅ Factura autorizada por AFIP", [
                        'factura_id' => $factura->id,
                        'numero_afip' => $numeroAfip,
                        'cae' => $cae,
                    ]);

                    SystemLog::create([
                        'context' => 'AFIP',
                        'action' => 'EnvioFactura',
                        'related_id' => $factura->id,
                        'related_type' => Factura::class,
                        'level' => 'info',
                        'message' => "Factura autorizada AFIP N° {$numeroAfip} - CAE {$cae}",
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

        $empresa = (object)[
            'razon_social'       => 'SECAR INGENIERIA ELECTRICA SRL',
            'cuit'               => '30615136065',
            'direccion'          => 'Mitre 751 - San Miguel de Tucumán',
            'condicion_iva'      => 'Responsable Inscripto',
            'iibb'               => '30615136065',
            'inicio_actividades' => '01/01/2010',
        ];

        // Verificación de datos AFIP obligatorios
        if (empty($factura->numero_comprobante_afip) || empty($factura->cae)) {
            abort(400, 'La factura aún no fue autorizada por AFIP');
        }

        $cliente = $factura->cliente;

        $qrData = [
            "ver"     => 1,
            "fecha"   => \Carbon\Carbon::parse($factura->fecha_emision)->format('Y-m-d'),

            // CUIT emisor
            "cuit" => (int) preg_replace('/\D/', '', $empresa->cuit),

            "ptoVta"  => (int) $factura->punto_venta,

            // Tipo de comprobante AFIP
            "tipoCmp" => $factura->tipo_comprobante === 'A' ? 1 : 6,

            // ESTE ES EL DATO CLAVE CORRECTO
            "nroCmp"  => (int) $factura->numero_comprobante_afip,

            "importe" => round((float) $factura->importe_total, 2),

            "moneda" => $factura->moneda === 'USD' ? 'DOL' : 'ARS',

            "ctz"     => 1,

            "tipoDocRec" => $cliente ? 80 : 99,

            "nroDocRec" => (int) preg_replace('/\D/', '', $cliente->cuit),
            "codAut"    => (int) $factura->cae,


            "tipoCodAut" => "E",

            // CAE real
            // "codAut" => (string) $factura->cae,
        ];

        // Quitar nulos por seguridad
        $qrData = array_filter($qrData, fn($v) => $v !== null);

        $qrBase64 = base64_encode(json_encode($qrData, JSON_UNESCAPED_UNICODE));

        // ESTE ES EL LINK CORRECTO Y DEFINITIVO
        // $afipUrl = "https://servicioscf.afip.gob.ar/publico/comprobantes/cae.aspx?p={$qrBase64}";
        $afipUrl = "https://www.afip.gob.ar/fe/qr/?p={$qrBase64}";


        // Generación remota del QR
        $qrRemoteUrl = "https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=" . urlencode($afipUrl);

        $qrImage = null;
        $qrContent = @file_get_contents($qrRemoteUrl);

        if ($qrContent !== false) {
            $qrImage = base64_encode($qrContent);
        }

        $pdf = \PDF::loadView(
            'admin.facturas.pdf',
            compact('factura', 'empresa', 'qrImage')
        );

        return $pdf->stream("Factura-{$factura->numero_comprobante_afip}.pdf");
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
