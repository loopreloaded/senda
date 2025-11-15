<?php

namespace App\Services\Afip;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

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
     * Envia la factura al WSFE producción.
     */
    public function enviarFactura($factura)
    {
        Log::info("➡ Enviando factura ID {$factura->id} a AFIP (producción)");

        // === 1) CARGAR TA ===
        if (!file_exists($this->taPath)) {
            $this->obtenerToken();
        }

        $ta = simplexml_load_file($this->taPath);
        $token = (string)$ta->credentials->token;
        $sign = (string)$ta->credentials->sign;

        // === 2) CLIENTE AFIP ===
        $client = new \SoapClient($this->wsfeWsdl, ['trace' => 1, 'exceptions' => 1]);

        $auth = [
            'Token' => $token,
            'Sign'  => $sign,
            'Cuit'  => $this->cuit,
        ];

        // === 3) OBTENER ÚLTIMO NÚMERO DESDE AFIP ===
        $ultimo = $client->FECompUltimoAutorizado([
            'Auth' => $auth,
            'PtoVta' => $factura->punto_venta,
            'CbteTipo' => $factura->tipo_comprobante === 'A' ? 1 : 6,
        ]);

        $cbteDesde = $ultimo->FECompUltimoAutorizadoResult->CbteNro + 1;
        $cbteHasta = $cbteDesde;

        // === 4) REDONDEO CORRECTO PRODUCCIÓN ===
        $impNeto = round($factura->importe_total / 1.21, 2);
        $impIVA  = round($factura->importe_total - $impNeto, 2);

        // === 5) TIPOS DOC ===
        $docTipo = $factura->cliente->cuit ? 80 : 99;
        $docNro  = $factura->cliente->cuit ?? 0;

        // === 6) ARMADO DE LA FACTURA ===
        $data = [
            'FeCAEReq' => [
                'FeCabReq' => [
                    'CantReg' => 1,
                    'PtoVta' => $factura->punto_venta,
                    'CbteTipo' => $factura->tipo_comprobante === 'A' ? 1 : 6,
                ],
                'FeDetReq' => [
                    'FECAEDetRequest' => [
                        'Concepto' => 1,
                        'DocTipo' => $docTipo,
                        'DocNro'  => $docNro,
                        'CbteDesde' => $cbteDesde,
                        'CbteHasta' => $cbteHasta,
                        'CbteFch' => date('Ymd', strtotime($factura->fecha_emision)),
                        'ImpTotal' => $factura->importe_total,
                        'ImpTotConc' => 0,
                        'ImpNeto' => $impNeto,
                        'ImpIVA'  => $impIVA,
                        'ImpTrib' => 0,
                        'ImpOpEx' => 0,
                        'MonId' => 'PES',
                        'MonCotiz' => 1,
                        'Iva' => [
                            'AlicIva' => [
                                'Id' => 5,
                                'BaseImp' => $impNeto,
                                'Importe' => $impIVA,
                            ]
                        ]
                    ]
                ]
            ]
        ];

        try {
            $res = $client->FECAESolicitar(['Auth' => $auth] + $data);

            Log::info("✅ Factura enviada correctamente", [
                'request'  => $client->__getLastRequest(),
                'response' => $client->__getLastResponse()
            ]);

            return $res;

        } catch (\Exception $e) {

            Log::error("❌ Error al enviar factura a AFIP: {$e->getMessage()}", [
                'request'  => $client->__getLastRequest(),
                'response' => $client->__getLastResponse()
            ]);

            throw $e;
        }
    }

}
