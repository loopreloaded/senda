<?php

namespace App\Services\Afip;

use SoapClient;
use App\Models\Factura;
use App\Models\SystemLog;

class WsfeClient
{
    private string $wsdl;
    private string $cuit = '30615136065';

    public function __construct(bool $homologacion = true)
    {
        $this->wsdl = $homologacion
            ? 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL'
            : 'https://wsfe.afip.gov.ar/wsfev1/service.asmx?WSDL';
    }

    public function enviarFactura(Factura $factura, $token, $sign)
    {
        $client = new SoapClient($this->wsdl, ['trace' => 1]);

        $params = [
            'Auth' => [
                'Token' => $token,
                'Sign'  => $sign,
                'Cuit'  => $this->cuit,
            ],
            'FeCAEReq' => [
                'FeCabReq' => [
                    'CantReg' => 1,
                    'PtoVta' => $factura->punto_venta,
                    'CbteTipo' => $factura->tipo_comprobante == 'A' ? 1 : 6,
                ],
                'FeDetReq' => [
                    'FECAEDetRequest' => [
                        'Concepto' => 1,
                        'DocTipo' => 80,
                        'DocNro' => 20111111112,
                        'CbteDesde' => $factura->numero,
                        'CbteHasta' => $factura->numero,
                        'CbteFch' => date('Ymd', strtotime($factura->fecha_emision)),
                        'ImpTotal' => $factura->importe_total,
                        'ImpNeto' => $factura->importe_total,
                        'ImpIVA' => 0,
                        'ImpTrib' => 0,
                        'MonId' => 'PES',
                        'MonCotiz' => 1,
                    ],
                ],
            ],
        ];

        $response = $client->FECAESolicitar($params);
        SystemLog::create([
            'type' => 'afip',
            'context' => 'wsfe',
            'message' => json_encode($params),
            'response' => json_encode($response),
        ]);

        $cae = $response->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAE ?? null;
        $vto = $response->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAEFchVto ?? null;

        if ($cae) {
            $factura->update([
                'cae' => $cae,
                'vto_cae' => date('Y-m-d', strtotime($vto)),
                'estado' => 'emitida',
            ]);
        } else {
            $factura->update(['estado' => 'error']);
        }

        return $response;
    }
}
