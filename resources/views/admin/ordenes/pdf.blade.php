<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Secar</title>

    <style>
        @page { margin: 15mm; }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        /* PROHIBIR SALTOS DE PÁGINA */
        * { page-break-inside: avoid !important; }
        table { page-break-inside: avoid !important; }
        tr { page-break-inside: avoid !important; }
        td, th { page-break-inside: avoid !important; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        /* Marco exterior */
        .contenedor-total {
            border: 1.5px solid #000;
            border-radius: 12px;
            padding: 15px 20px;

            /* Agrega separación del borde del papel */
            margin: 5mm;

            min-height: 250mm;
            box-sizing: border-box;
        }


        table { width: 100%; border-collapse: collapse; }

        .info-table td {
            padding: 2px 3px;
            font-size: 9px;
        }
        .info-table .label { font-weight: bold; }

        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 3px;
            font-size: 8px;
        }

        .items-table th {
            background: #f2f2f2;
            font-weight: bold;
        }

        .totales-table {
            width: 40%;
            float: right;
            margin-top: 10px;
        }

        .totales-table td {
            padding: 3px;
            font-size: 9px;
        }

        .firma img {
            max-height: 50px;
        }
    </style>
</head>

<body>

<div class="contenedor-total">

@php
    $empresa = [
        'nombre' => 'SECAR',
        'direccion' => 'Entre Ríos 751',
        'cuit' => '30-61513606-5',
        'email' => 'secarsrl@gmail.com',
        'telefono' => '3812564909'
    ];

    $fecha = $orden->fecha ? \Carbon\Carbon::parse($orden->fecha)->format('d/m/Y') : '-';
@endphp


{{-- ENCABEZADO --}}
{{-- ENCABEZADO TOTVS --}}
<table style="width:100%; margin-bottom:6px;">
    <tr>
        {{-- LOGO IZQUIERDA --}}
        <td style="width:22%; vertical-align:top;">
            <img src="{{ public_path('assets/img/logo-ingenio.png') }}"
                 style="max-height:70px; margin-left:5px;">
        </td>

        {{-- TEXTO DEL PROVEEDOR --}}
        <td style="width:48%; vertical-align:top; font-size:10px; line-height:14px;">
            <strong style="font-size:13px;">SECAR</strong><br>
            Entre Ríos 751 - SAN MIGUEL DE TUCUMAN<br>
            TUCUMAN - ARGENTINA<br>
            CUIT: 30-61513606-5<br>
            TEL: 3812564909<br>
            secarsrl@gmail.com
        </td>

        {{-- BLOQUE ORDEN DE COMPRA --}}
        <td style="width:30%; text-align:right; vertical-align:top;">
            <div style="font-size:14px; font-weight:bold;">Orden de compra</div>
            <div style="font-size:9px; margin-top:-2px;">Purchase Order</div>

            <div style="margin-top:8px; font-size:10px;">
                <strong>N°</strong>
                <span style="font-size:14px; margin-left:5px;">
                    {{ str_pad($orden->numero_oc, 6, '0', STR_PAD_LEFT) }}
                </span>
            </div>

            <div style="font-size:10px; margin-top:5px;">
                <strong>Fecha / Date:</strong>
                {{ $fecha }}
            </div>
        </td>
    </tr>
</table>

{{-- LÍNEA HORIZONTAL --}}
<div style="width:100%; border-bottom:1px solid #000; margin-top:4px; margin-bottom:8px;"></div>


{{-- SECCIÓN INFORMACIÓN DEL PROVEEDOR / PARA / TO --}}
<table style="width:100%; font-size:9px; margin-bottom:6px;">
    <tr>
        {{-- COLUMNA IZQUIERDA --}}
        <td style="width:55%; vertical-align:top;">

            <table style="width:100%; font-size:9px;">
                <tr>
                    <td style="font-weight:bold;">Para / To:</td>
                    <td>
                        {{-- Código proveedor + nombre (si no existe, se muestra "-") --}}
                        {{ '-' }} - {{ $orden->proveedor ?: '-' }}
                    </td>
                </tr>

                <tr>
                    <td style="font-weight:bold;">Condición de compra / Payment Terms:</td>
                    <td>{{ $orden->condicion_compra ?: '-' }}</td>
                </tr>

                <tr>
                    <td style="font-weight:bold;">Tel / Phone:</td>
                    <td>-</td>
                </tr>

                <tr>
                    <td style="font-weight:bold;">Email:</td>
                    <td>-</td>
                </tr>

                <tr>
                    <td style="font-weight:bold;">CUIT:</td>
                    <td>{{ $orden->cuit ?: '-' }}</td>
                </tr>
            </table>

        </td>

        {{-- COLUMNA DERECHA --}}
        <td style="width:45%; vertical-align:top;">

            <table style="width:100%; font-size:9px;">
                <tr>
                    <td style="font-weight:bold;">Dirección / Address:</td>
                    <td>-</td>
                </tr>

                <tr>
                    <td style="font-weight:bold;">País / Country:</td>
                    <td>ARGENTINA</td>
                </tr>

                <tr>
                    <td style="font-weight:bold;">Moneda / Currency:</td>
                    <td>
                        @if($orden->moneda)
                            {{ strtoupper($orden->moneda) }}
                        @else
                            -
                        @endif
                    </td>
                </tr>

                <tr>
                    <td style="font-weight:bold;">Solicitud de compras / Purchasing request:</td>
                    <td>{{ str_pad($orden->id, 6, '0', STR_PAD_LEFT) }}</td>
                </tr>
            </table>

        </td>
    </tr>
</table>

{{-- LÍNEA HORIZONTAL DEBAJO --}}
<div style="width:100%; border-bottom:1px solid #000; margin-top:2px; margin-bottom:8px;"></div>

{{-- ITEMS --}}
<table class="items-table" style="margin-top:10px;">
    <thead>
        <tr>
            <th width="6%">Item</th>
            <th width="12%">Código</th>
            <th width="40%">Descripción</th>
            <th width="10%">U.M.</th>
            <th width="8%">Cantidad</th>
            <th width="12%">Precio Unit.</th>
            <th width="6%">Desc.</th>
            <th width="10%">Total</th>
        </tr>
    </thead>

    <tbody>
        @foreach($orden->items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->codigo ?: '-' }}</td>
            <td>{{ $item->descripcion ?: '-' }}</td>
            <td>{{ $item->unidad ?: '-' }}</td>
            <td>{{ number_format($item->cantidad, 2, ',', '.') }}</td>
            <td>{{ number_format($item->precio_unitario, 2, ',', '.') }}</td>
            <td>{{ number_format($item->descuento, 2, ',', '.') }}</td>
            <td>{{ number_format($item->total, 2, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>


{{-- TOTALES --}}
<table class="totales-table">
    <tr>
        <td style="text-align:right; font-weight:bold;">SUBTOTAL:</td>
        <td style="border:1px solid #000; text-align:right;">
            {{ number_format($orden->subtotal, 2, ',', '.') }}
        </td>
    </tr>

    <tr>
        <td style="text-align:right; font-weight:bold;">DESCUENTO:</td>
        <td style="border:1px solid #000; text-align:right;">
            {{ number_format($orden->descuento, 2, ',', '.') }}
        </td>
    </tr>

    <tr>
        <td style="text-align:right; font-weight:bold;">TOTAL:</td>
        <td style="border:1px solid #000; text-align:right;">
            {{ number_format($orden->total, 2, ',', '.') }}
        </td>
    </tr>
</table>


{{-- FIRMA --}}
<div style="clear:both; margin-top:40px; text-align:right;">
    <img src="{{ public_path('firma.png') }}" alt="Firma">
    <div style="border-top:1px solid #000; display:inline-block; padding-top:3px; font-size:9px;">
        Firma autorizada
    </div>
</div>


</div> {{-- FIN DEL MARCO --}}

</body>
</html>
