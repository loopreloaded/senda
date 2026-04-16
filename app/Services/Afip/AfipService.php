<?php

namespace App\Services\Afip;
use Illuminate\Support\Facades\Log;

class AfipService
{
    protected $cuit;
    protected $cert;
    protected $key;
    protected $taPath;
    protected $wsaaWsdl;
    protected $wsfeWsdl;

    public function __construct()
    {
        $this->cuit = env('AFIP_CUIT');
        $this->cert = base_path(env('AFIP_CERT_PATH'));
        $this->key = base_path(env('AFIP_KEY_PATH'));
        $this->taPath = storage_path('afip/produccion/TA.xml'); // TA para producción
        $this->wsaaWsdl = env('AFIP_WSDL_WSAA'); // WSAA producción
        $this->wsfeWsdl = env('AFIP_WSDL_WSFE'); // WSFE producción
    }

    /**
     * Genera el Token de Acceso (TA) para producción.
     */
    public function obtenerToken()
    {
        Log::info("➡ Generando TRA para AFIP");

        // === 1) GENERACIÓN DEL TRA CON HORARIOS CORREGIDOS ===
        $generationTime = (new \DateTime('-1 minute', new \DateTimeZone('America/Argentina/Buenos_Aires')))
            ->format('Y-m-d\TH:i:sP');

        $expirationTime = (new \DateTime('+24 hours', new \DateTimeZone('America/Argentina/Buenos_Aires')))
            ->format('Y-m-d\TH:i:sP');

        $uniqueId = rand(1, 99999999);

        $tra = <<<XML
    <?xml version="1.0" encoding="UTF-8"?>
    <loginTicketRequest version="1.0">
    <header>
        <uniqueId>{$uniqueId}</uniqueId>
        <generationTime>{$generationTime}</generationTime>
        <expirationTime>{$expirationTime}</expirationTime>
    </header>
    <service>wsfe</service>
    </loginTicketRequest>
    XML;

        $traFile = storage_path('afip/produccion/TRA.xml');
        file_put_contents($traFile, $tra);


        // === 2) FIRMAR TRA ===
        $signedFile = storage_path('afip/produccion/TRA_signed.tmp');

        exec("openssl smime -sign -signer {$this->cert} -inkey {$this->key} -outform DER -nodetach -in {$traFile} -out {$signedFile} 2>&1", $output, $result);

        if ($result !== 0) {
            $err = implode("\n", $output);
            Log::error("❌ Error al firmar TRA: {$err}");
            throw new \Exception("Error al firmar TRA: {$err}");
        }

        $cms = base64_encode(file_get_contents($signedFile));


        // === 3) LLAMADA AL WSAA ===
        $client = new \SoapClient($this->wsaaWsdl, [
            'soap_version' => SOAP_1_2,
            'trace' => 1,
            'exceptions' => true
        ]);

        $loginCmsResponse = $client->loginCms(['in0' => $cms]);

        $taXml = $loginCmsResponse->loginCmsReturn;

        // === 4) GUARDAR TA ===
        file_put_contents($this->taPath, $taXml);
        Log::info("✅ TA generado correctamente en {$this->taPath}");

        return simplexml_load_string($taXml);
    }


    public function generarTRA()
    {
        // Horarios exactos que AFIP exige en PRODUCCIÓN
        $generationTime = (new \DateTime('-1 minute', new \DateTimeZone('America/Argentina/Buenos_Aires')))
            ->format('Y-m-d\TH:i:sP');

        $expirationTime = (new \DateTime('+24 hours', new \DateTimeZone('America/Argentina/Buenos_Aires')))
            ->format('Y-m-d\TH:i:sP');

        $uniqueId = rand(1, 99999999);

        return <<<XML
    <?xml version="1.0" encoding="UTF-8"?>
    <loginTicketRequest version="1.0">
        <header>
            <uniqueId>{$uniqueId}</uniqueId>
            <generationTime>{$generationTime}</generationTime>
            <expirationTime>{$expirationTime}</expirationTime>
        </header>
        <service>wsfe</service>
    </loginTicketRequest>
    XML;
    }


    /**
     * Envía la factura al WSFE (AFIP).
     */
    public function enviarFactura($factura)
    {
        Log::info("➡ Enviando factura ID {$factura->id} a AFIP");

        // ==============================
        // 1) TOKEN
        // ==============================
        if (!file_exists($this->taPath)) {
            $this->obtenerToken();
        }

        $ta    = simplexml_load_file($this->taPath);
        $token = (string)$ta->credentials->token;
        $sign  = (string)$ta->credentials->sign;

        // ==============================
        // 2) SOAP
        // ==============================
        $client = new \SoapClient($this->wsfeWsdl, [
            'trace'      => 1,
            'exceptions' => true,
        ]);

        $auth = [
            'Token' => $token,
            'Sign'  => $sign,
            'Cuit'  => $this->cuit,
        ];

        // ==============================
        // 3) TIPO Y NÚMERO DE COMPROBANTE
        // ==============================
        $cbteTipo = $factura->tipo_comprobante === 'A' ? 1 : 6;

        $ultimo = $client->FECompUltimoAutorizado([
        'Auth'     => $auth,
        'PtoVta'   => $factura->punto_venta,
        'CbteTipo' => $cbteTipo,
        ]);

        $cbteDesde = $ultimo->FECompUltimoAutorizadoResult->CbteNro + 1;

        // ==============================
        // 4) MONEDA
        // ==============================
        $monId    = (strpos($factura->moneda, 'USD') !== false) ? 'DOL' : 'PES';
        $monCotiz = (strpos($factura->moneda, 'USD') !== false)
            ? max(1, (float)$factura->valor_dolar)
            : 1;

        // ==============================
        // 5) DOCUMENTO CLIENTE
        // ==============================
        $cuitCliente = preg_replace('/[^0-9]/', '', $factura->cliente->cuit);

        if ($cbteTipo == 1) { // FACTURA A

            if (!$this->cuitValido($cuitCliente)) {
                throw new \Exception(
                    'Factura A rechazada: el cliente debe tener CUIT válido.'
                );
            }

            $docTipo = 80;
            $docNro  = (int)$cuitCliente;

        } else { // FACTURA B

            if ($this->cuitValido($cuitCliente)) {
                $docTipo = 80;
                $docNro  = (int)$cuitCliente;
            } else {
                $docTipo = 99;
                $docNro  = 0;
            }
        }


        // ==============================
        // 6) FECHAS DE SERVICIO
        // ==============================
        $fchServDesde = null;
        $fchServHasta = null;
        $fchVtoPago   = null;

        if (in_array($factura->concepto, [2, 3])) {
            $fchServDesde = $factura->fecha_desde ? date('Ymd', strtotime($factura->fecha_desde)) : null;
            $fchServHasta = $factura->fecha_hasta ? date('Ymd', strtotime($factura->fecha_hasta)) : null;
            $fchVtoPago   = $factura->vencimiento_pago ? date('Ymd', strtotime($factura->vencimiento_pago)) : null;
        }

        // ==============================
        // 7) RECÁLCULO DE ÍTEMS
        // ==============================
        $impNeto     = 0;
        $impIVA      = 0;
        $impOpEx     = 0;
        $impTotConc  = 0;
        $ivaAgrupado = [];

        foreach ($factura->items as $item) {

            $base = (float)$item->subtotal_sin_iva;
            $iva  = (float)$item->iva;

            if ($iva == 0) {
                $impOpEx += $base;
                continue;
            }

            $map = [
                2.5  => 9,
                5    => 8,
                10.5 => 4,
                21   => 5,
                27   => 6,
            ];

            if (!isset($map[$iva])) {
                continue;
            }

            $ivaId      = $map[$iva];
            $ivaImporte = round($base * ($iva / 100), 2);

            $impNeto += $base;
            $impIVA  += $ivaImporte;

            if (!isset($ivaAgrupado[$ivaId])) {
                $ivaAgrupado[$ivaId] = [
                    'Id'      => $ivaId,
                    'BaseImp'=> 0,
                    'Importe'=> 0,
                ];
            }

            $ivaAgrupado[$ivaId]['BaseImp'] += $base;
            $ivaAgrupado[$ivaId]['Importe'] += $ivaImporte;
        }

        // ==============================
        // 8) TRIBUTOS / PERCEPCIONES
        // ==============================
        $tributos = [];
        $impTrib  = 0;

        if ($factura->percepcion_iva_importe > 0) {
            $tributos[] = [
                'Id'      => 6,
                'Desc'    => $factura->percepcion_iva_detalle ?? 'Percepción IVA',
                'BaseImp' => round((float)$factura->percepcion_iva_base, 2),
                'Alic'    => round((float)$factura->percepcion_iva_alicuota, 2),
                'Importe' => round((float)$factura->percepcion_iva_importe, 2),
            ];
            $impTrib += $factura->percepcion_iva_importe;
        }

        if ($factura->percepcion_iibb_importe > 0) {
            $tributos[] = [
                'Id'      => 2,
                'Desc'    => $factura->percepcion_iibb_detalle ?? 'Percepción IIBB',
                'BaseImp' => round((float)$factura->percepcion_iibb_base, 2),
                'Alic'    => round((float)$factura->percepcion_iibb_alicuota, 2),
                'Importe' => round((float)$factura->percepcion_iibb_importe, 2),
            ];
            $impTrib += $factura->percepcion_iibb_importe;
        }

        if ($factura->importe_total_otros_tributos > 0) {
            $tributos[] = [
                'Id'      => 99,
                'Desc'    => 'Otros Tributos',
                'BaseImp' => round((float)$factura->importe_total_otros_tributos, 2),
                'Alic'    => 0,
                'Importe' => round((float)$factura->importe_total_otros_tributos, 2),
            ];
            $impTrib += $factura->importe_total_otros_tributos;
        }

        // ==============================
        // 9) ARMAR DETALLE AFIP
        // ==============================
        $detalle = [
            'Concepto'   => $factura->concepto,
            'DocTipo'    => $docTipo,
            'DocNro'     => $docNro,
            'CbteDesde'  => $cbteDesde,
            'CbteHasta'  => $cbteDesde,
            'CbteFch'    => date('Ymd', strtotime($factura->fecha_emision)),
            'ImpTotal'   => round($factura->importe_total + $impTrib, 2),
            'ImpTotConc' => round($impTotConc, 2),
            'ImpNeto'    => round($impNeto, 2),
            'ImpIVA'     => round($impIVA, 2),
            'ImpTrib'    => round($impTrib, 2),
            'ImpOpEx'    => round($impOpEx, 2),
            'MonId'      => $monId,
            'MonCotiz'   => $monCotiz,
        ];

        if (!empty($ivaAgrupado)) {
            $detalle['Iva'] = [
                'AlicIva' => array_values($ivaAgrupado),
            ];
        }

        if (!empty($tributos)) {
            $detalle['Tributos'] = [
                'Tributo' => $tributos,
            ];
        }

        if ($fchServDesde) {
            $detalle['FchServDesde'] = $fchServDesde;
            $detalle['FchServHasta'] = $fchServHasta;
            $detalle['FchVtoPago']   = $fchVtoPago;
        }

        // ==============================
        // 10) REMITOS / COMPROBANTES ASOCIADOS
        // ==============================
        if ($factura->remitos && $factura->remitos->count()) {

            $cbtesAsoc = [];

            foreach ($factura->remitos as $remito) {
                $cbtesAsoc[] = [
                    'Tipo'   => 91,
                    'PtoVta' => $remito->pto_venta,
                    'Nro'    => $remito->comprobante,
                ];
            }

            if (!empty($cbtesAsoc)) {
                $detalle['CbtesAsoc'] = [
                    'CbteAsoc' => $cbtesAsoc,
                ];
            }
        }

        Log::info('📤 AFIP FECAE DETALLE', $detalle);

        // ==============================
        // 11) ENVÍO A AFIP
        // ==============================
        $res = $client->FECAESolicitar([
            'Auth' => $auth,
            'FeCAEReq' => [
                'FeCabReq' => [
                    'CantReg' => 1,
                    'PtoVta'  => $factura->punto_venta,
                    'CbteTipo'=> $cbteTipo,
                ],
                'FeDetReq' => [
                    'FECAEDetRequest' => $detalle,
                ],
            ],
        ]);

        return $res;
    }




    // =======================
    //
    // =======================
    public function enviarNotaDebito($nota)
    {
        // =======================
        // 1) Conectar a WSFE
        // =======================
        $ta = $this->obtenerToken();
        $wsfe = new \SoapClient($this->wsfeWsdl, [
            'soap_version' => SOAP_1_2,
            'exceptions'   => true,
            'trace'        => 1,
        ]);

        $auth = [
            'Token'  => $ta['token'],
            'Sign'   => $ta['sign'],
            'Cuit'   => $this->cuit,
        ];

        // =======================
        // 2) Determinar tipo de ND
        // =======================
        // A -> 2, B -> 3
        if (stripos($nota->tipo_comprobante, 'A') !== false) {
            $cbteTipo = 2;  // Nota de Débito A
        } else {
            $cbteTipo = 3;  // Nota de Débito B
        }

        // =======================
        // 3) Traer último autorizado
        // =======================
        $ultimo = $wsfe->FECompUltimoAutorizado([
            'Auth'    => $auth,
            'PtoVta'  => 4,         // punto de venta fijo
            'CbteTipo'=> $cbteTipo
        ]);

        $cbteDesde = $ultimo->FECompUltimoAutorizadoResult->CbteNro + 1;

        // =======================
        // 4) Datos del cliente
        // =======================
        $cliente = $nota->cliente;

        $docTipo = 80; // CUIT
        $docNro  = intval(preg_replace('/[^\d]/', '', $cliente->cuit));

        // =======================
        // 5) Comprobante asociado
        // =======================
        $factura = $nota->factura;

        // Mapear tipo de factura AFIP (1 = A, 6 = B)
        if (stripos($factura->tipo_comprobante, 'A') !== false) {
            $cbteTipoFactura = 1; // Factura A
        } else {
            $cbteTipoFactura = 6; // Factura B
        }

        $cbteAsoc = [
            [
                'Tipo'   => $cbteTipoFactura,
                'PtoVta' => intval($factura->punto_venta),
                'Nro'    => intval($factura->numero),
            ]
        ];

        // =======================
        // 6) Importes
        // =======================
        $importeTotal = floatval($nota->importe_total);
        $importeNeto  = $importeTotal; // si es todo gravado
        $importeIva   = 0;             // si querés sumar IVA explícito, se ajusta luego

        // =======================
        // 7) Armar datos AFIP (simple)
        // =======================
        $fechaCbte = date('Ymd', strtotime($nota->fecha_emision));

        $data = [
            'Auth' => $auth,
            'FeCAEReq' => [
                'FeCabReq' => [
                    'CantReg' => 1,
                    'PtoVta'  => 4,
                    'CbteTipo'=> $cbteTipo,
                ],
                'FeDetReq' => [
                    'FECAEDetRequest' => [
                        'Concepto'   => $nota->concepto ?? 1,
                        'DocTipo'    => $docTipo,
                        'DocNro'     => $docNro,
                        'CbteDesde'  => $cbteDesde,
                        'CbteHasta'  => $cbteDesde,
                        'CbteFch'    => $fechaCbte,
                        'ImpTotal'   => $importeTotal,
                        'ImpTotConc' => 0,
                        'ImpNeto'    => $importeNeto,
                        'ImpOpEx'    => 0,
                        'ImpIVA'     => $importeIva,
                        'ImpTrib'    => 0,
                        'MonId'      => 'PES',
                        'MonCotiz'   => 1,
                        'CbtesAsoc'  => $cbteAsoc,
                    ]
                ]
            ]
        ];

        // =======================
        // 8) Solicitud AFIP
        // =======================
        return $wsfe->FECAESolicitar($data);
    }

    private function cuitValido($cuit)
    {
        $cuit = preg_replace('/[^0-9]/', '', $cuit);

        if (strlen($cuit) != 11) {
            return false;
        }

        $multiplicadores = [5,4,3,2,7,6,5,4,3,2];
        $suma = 0;

        for ($i = 0; $i < 10; $i++) {
            $suma += $cuit[$i] * $multiplicadores[$i];
        }

        $resto = $suma % 11;
        $digito = 11 - $resto;

        if ($digito == 11) $digito = 0;
        if ($digito == 10) $digito = 9;

        return $digito == $cuit[10];
    }



}
