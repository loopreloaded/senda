<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de compra</title>

    <style>
        @page { margin: 15mm; }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        * { page-break-inside: avoid !important; }
        table { page-break-inside: avoid !important; }
        tr { page-break-inside: avoid !important; }
        td, th { page-break-inside: avoid !important; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        .contenedor-total {
            border: 1.5px solid #000;
            border-radius: 12px;
            padding: 15px 20px;
            margin: 5mm;
            min-height: 250mm;
            box-sizing: border-box;

            position: relative;          /* 👈 IMPORTANTE */
            padding-bottom: 100px;        /* 👈 espacio reservado para la firma */
        }


        table { width: 100%; border-collapse: collapse; }

        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 3px;
            font-size: 8px;
        }

        .items-table th {
            background: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="contenedor-total">

@php
    $empresa = [
        'nombre' => 'SECAR SRL',
        'direccion' => 'Entre Ríos 751 - San Miguel de Tucumán',
        'cuit' => '30-61513606-5',
        'email' => 'secarsrl@gmail.com',
        'telefono' => '3812564909'
    ];

    $fecha = $orden->fecha ? \Carbon\Carbon::parse($orden->fecha)->format('d/m/Y') : '-';
    $fecha_entrega = $orden->fecha_entrega ? \Carbon\Carbon::parse($orden->fecha_entrega)->format('d/m/Y') : '-';
@endphp


{{-- ENCABEZADO --}}
<table style="width:100%; margin-bottom:6px;">
    <tr>

        <td style="width:22%; vertical-align:top;">
            <img src="{{ public_path('assets/img/logo-secar.png') }}"
                 style="max-height:70px; margin-left:5px;">
        </td>

        <td style="width:48%; vertical-align:top; font-size:10px; line-height:14px;">
            <strong style="font-size:13px;">SECAR INGENIERIA ELECTRICA SRL</strong><br>
            {{ $empresa['direccion'] }}<br>
            CUIT: {{ $empresa['cuit'] }}<br>
            TEL: {{ $empresa['telefono'] }}<br>
            {{ $empresa['email'] }}
        </td>

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
                <strong>Fecha / Date:</strong> {{ $fecha }}
            </div>

        </td>
    </tr>
</table>

<div style="width:100%; border-bottom:1px solid #000; margin-top:4px; margin-bottom:8px;"></div>


{{-- INFORMACIÓN DEL PROVEEDOR --}}
<table style="width:100%; font-size:9px; margin-bottom:6px;">
    <tr>

        {{-- IZQUIERDA --}}
        <td style="width:55%; vertical-align:top;">

            <table style="width:100%; font-size:9px;">
                <tr>
                    <td style="font-weight:bold;">Razon social / Supplier:</td>
                    <td>{{ $orden->proveedor ?: '-' }}</td>
                </tr>

                <tr>
                    <td style="font-weight:bold;">CUIT:</td>
                    <td>{{ $orden->cuit ?: '-' }}</td>
                </tr>

                <tr>
                    <td style="font-weight:bold;">Teléfono:</td>
                    <td>{{ $orden->telefono ?: '-' }}</td>
                </tr>

                <tr>
                    <td style="font-weight:bold;">Email:</td>
                    <td>{{ $orden->email ?: '-' }}</td>
                </tr>

                <tr>
                    <td style="font-weight:bold;">Condición de compra:</td>
                    <td>{{ $orden->condicion_compra ?: '-' }}</td>
                </tr>
            </table>
        </td>

        {{-- DERECHA --}}
        <td style="width:45%; vertical-align:top;">

            <table style="width:100%; font-size:9px;">
                <tr>
                    <td style="font-weight:bold;">Dirección:</td>
                    <td>{{ $orden->direccion ?: '-' }}</td>
                </tr>

                <tr>
                    <td style="font-weight:bold;">Moneda:</td>
                    <td>{{ strtoupper($orden->moneda) }}</td>
                </tr>

                <tr>
                    <td style="font-weight:bold;">Solicitud de compra:</td>
                    <td>{{ $orden->solicitud_compra ?: '-' }}</td>
                </tr>

            </table>
        </td>

    </tr>
</table>

<div style="width:100%; border-bottom:1px solid #000; margin-top:2px; margin-bottom:8px;"></div>


{{-- ITEMS --}}
<table class="items-table" style="margin-top:10px;">
    <thead>
        <tr>
            <th width="6%">Item</th>
            <th width="12%">Código</th>
            <th width="40%">Descripción</th>
            <th width="12%">Fecha Entrega</th>
            <th width="10%">U.M.</th>
            <th width="8%">Cantidad</th>
            <th width="12%">Precio Unit.</th>
            <th width="6%">IVA %</th>
            <th width="6%">Desc. %</th>
            <th width="10%">Total</th>
        </tr>
    </thead>

    <tbody>
        @foreach($orden->items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->codigo ?: '-' }}</td>
            <td>{{ $item->descripcion ?: '-' }}</td>
            <td>{{ $item->fecha_entrega ? \Carbon\Carbon::parse($item->fecha_entrega)->format('d/m/Y') : '-' }}</td>
            <td>{{ $item->unidad ?: '-' }}</td>
            <td>{{ number_format($item->cantidad, 2, ',', '.') }}</td>
            <td>{{ number_format($item->precio_unitario, 2, ',', '.') }}</td>
            <td>{{ number_format($item->iva ?? 0, 2, ',', '.') }}</td>
            <td>{{ number_format($item->descuento, 2, ',', '.') }}</td>
            <td>{{ number_format($item->total, 2, ',', '.') }}</td>

        </tr>
        @endforeach
    </tbody>
</table>


{{-- OBSERVACIONES (ANCHO FIJO + ALINEADO CORRECTO) --}}
@if($orden->observaciones)
<div style="margin-top:12px; width:100%; font-size:10px;">

    <div style="font-weight:bold; margin-bottom:4px;">Observaciones:</div>

    <div style="
        border:1px solid #000;
        padding:7px 10px;
        border-radius:4px;
        white-space:pre-wrap;
        width: 95%;
        margin-left: 1%;
        margin-right: 1%;
        box-sizing:border-box;
        font-size:10px;
        line-height:14px;
        display:block;
    ">
        {{ $orden->observaciones }}
    </div>
</div>
@endif




<div style="width:100%; border-bottom:1px solid #000; margin-top:6px; margin-bottom:6px;"></div>


{{-- TOTALES --}}
@php
    $subtotal_con_iva = 0;
    $total_descuentos = 0;

    foreach ($orden->items as $i) {

        $cantidad  = $i->cantidad;
        $precio    = $i->precio_unitario;
        $iva       = $i->iva ?? 0;
        $descuento = $i->descuento ?? 0;

        $totalBase   = $cantidad * $precio;
        $totalConIVA = $totalBase + ($totalBase * ($iva / 100));
        $totalFinal  = $totalConIVA - ($totalConIVA * ($descuento / 100));

        $subtotal_con_iva += $totalConIVA;
        $total_descuentos += ($totalConIVA * ($descuento / 100));
    }
@endphp


<table style="width:100%; font-size:10px; border-collapse:collapse;">
    <tr>
        <td style="width:60%; vertical-align:top;">

        </td>

        <td style="width:40%; vertical-align:top;">
            <table style="width:100%; font-size:10px; border-collapse:collapse;">

                <tr>
                    <td style="text-align:right; font-weight:bold;">SUBTOTAL C/IVA:</td>
                    <td style="border:1px solid #000; padding:3px; text-align:right;">
                        {{ number_format($subtotal_con_iva, 2, ',', '.') }}
                    </td>
                </tr>

                <tr>
                    <td style="text-align:right; font-weight:bold;">DESCUENTOS TOTALES:</td>
                    <td style="border:1px solid #000; padding:3px; text-align:right;">
                        {{ number_format($total_descuentos, 2, ',', '.') }}
                    </td>
                </tr>

                <tr>
                    <td style="text-align:right; font-weight:bold;">TOTAL FINAL:</td>
                    <td style="border:1px solid #000; padding:3px; text-align:right;">
                        {{ number_format($orden->total, 2, ',', '.') }}
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>


<div style="width:100%; border-bottom:1px solid #000; margin-top:8px;"></div>



    {{-- FIRMA --}}
    <div style="
        position:absolute;
        bottom:25px;
        right:20px;
        text-align:right;
    ">
        <img src="{{ public_path('assets/img/firma.png') }}" alt="Firma" style="max-height:50px;">
        <div style="border-top:1px solid #000; padding-top:3px; font-size:9px;">
            Firma autorizada
        </div>
    </div>


</div>
</body>
</html>
