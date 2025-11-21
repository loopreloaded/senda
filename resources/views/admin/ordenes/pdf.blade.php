<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Compra</title>

    <style>
        @page { margin: 15mm 10mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }

        .header-box {
            border: 1px solid #000;
            border-radius: 8px;
            padding: 6px 8px;
            margin-bottom: 8px;
        }

        table { border-collapse: collapse; width: 100%; }
        .info-table td { padding: 2px 3px; font-size: 9px; }
        .info-table .label { font-weight: bold; }

        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 3px;
            font-size: 8px;
        }
        .items-table th { background: #f2f2f2; }

        .totales-table { width: 40%; float: right; margin-top: 10px; }
        .totales-table td { padding: 3px; font-size: 9px; }

        .firma img { max-height: 50px; }
    </style>
</head>

<body>

@php
    // Empresa emisora fija
    $empresa = [
        'nombre' => 'SECAR',
        'direccion' => 'Entre Ríos 751',
        'cuit' => '30-61513606-5',
        'email' => 'secarsrl@gmail.com',
        'telefono' => '3812564909'
    ];

    // Formato seguro de fecha (string -> Carbon)
    $fecha = $orden->fecha ? \Carbon\Carbon::parse($orden->fecha)->format('d/m/Y') : '-';

@endphp


{{-- ENCABEZADO --}}
<div class="header-box">

    <table>
        <tr>
            <td width="40%">
                <img src="{{ public_path('assets/img/logo.png') }}" style="max-height:45px;">
            </td>
            <td width="60%" style="text-align:right;">
                <div style="font-size:15px; font-weight:bold;">Orden de compra</div>
                <div style="font-size:12px;">N° {{ $orden->numero_oc }}</div>
            </td>
        </tr>
    </table>

    <table class="info-table" style="margin-top:5px;">
        <tr>
            <td class="label">Fecha:</td>
            <td>{{ $fecha }}</td>

            <td class="label">CUIT Empresa:</td>
            <td>{{ $empresa['cuit'] }}</td>

            <td class="label">Condición compra:</td>
            <td>{{ $orden->condicion_compra ?? '-' }}</td>
        </tr>

        <tr>
            <td class="label">Proveedor:</td>
            <td colspan="3">{{ $orden->proveedor ?: '-' }}</td>

            <td class="label">Moneda:</td>
            <td>{{ $orden->moneda ? strtoupper($orden->moneda) : '-' }}</td>
        </tr>

        <tr>
            <td class="label">CUIT Proveedor:</td>
            <td>{{ $orden->cuit ?: '-' }}</td>

            <td class="label">País:</td>
            <td>ARGENTINA</td>

            <td class="label">Código Prov.:</td>
            <td>-</td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td>
                <strong>{{ $empresa['nombre'] }}</strong><br>
                {{ $empresa['direccion'] }}<br>
                {{ $empresa['email'] }}<br>
                Tel: {{ $empresa['telefono'] }}
            </td>
        </tr>
    </table>
</div>


{{-- TABLA DE ITEMS --}}
<table class="items-table">
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


<div style="clear:both; margin-top:30px;"></div>

{{-- FIRMA --}}
<div style="text-align:right; margin-top:20px;">
    <img src="{{ public_path('firma.png') }}" alt="Firma">
    <div style="border-top:1px solid #000; display:inline-block; padding-top:3px; font-size:9px;">
        Firma autorizada
    </div>
</div>

</body>
</html>
