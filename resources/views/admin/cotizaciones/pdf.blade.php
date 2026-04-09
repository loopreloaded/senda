<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cotización</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .container {
            border: 2px solid #000;
            padding: 15px;
        }

        .header {
            width: 100%;
        }

        .logo {
            width: 80px;
        }

        .empresa {
            font-size: 12px;
        }

        .titulo {
            text-align: center;
            font-weight: bold;
            letter-spacing: 5px;
            font-size: 18px;
        }

        .right {
            text-align: right;
        }

        .box {
            border: 1px solid #000;
            padding: 5px;
            margin-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 11px;
        }

        th {
            background: #f0f0f0;
        }

        .no-border {
            border: none !important;
        }

        .total-box {
            width: 200px;
            float: right;
            margin-top: 15px;
        }

    </style>
</head>
<body>

<div class="container">

    {{-- HEADER --}}
    <table class="header">
        <tr>
            <td width="20%">
                {{-- Logo --}}
                <img src="{{ public_path('img/logo.png') }}" class="logo">
            </td>

            <td width="50%" class="empresa">
                <strong>SECAR INGENIERIA ELECTRICA SRL</strong><br>
                Entre Ríos 751 - San Miguel de Tucumán<br>
                CUIT: 30-61513606-5<br>
                TEL: 3812564909<br>
                secarsrl@gmail.com
            </td>

            <td width="30%" class="right">
                <div class="titulo">COTIZACIÓN</div>
                <br>
                <strong>N°:</strong> {{ $cotizacion->nro_cotizacion ?? $cotizacion->id_cotizacion }}<br>
                <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($cotizacion->fecha_cot)->format('d/m/Y') }}
            </td>
        </tr>
    </table>

    <hr>

    {{-- CLIENTE --}}
    <div class="box">
        <strong>Cliente:</strong> {{ $cotizacion->cliente->razon_social ?? '' }} <br>
        <strong>CUIT:</strong> {{ $cotizacion->cliente->cuit ?? '' }}
    </div>

    {{-- CONDICIONES --}}
    <br>
    <strong>Condiciones Comerciales</strong>

    <table>
        <tr>
            <td width="30%">Moneda</td>
            <td>{{ $cotizacion->moneda }}</td>
        </tr>
        <tr>
            <td>Forma de pago</td>
            <td>{{ $cotizacion->forma_pago }}</td>
        </tr>
        <tr>
            <td>Lugar de entrega</td>
            <td>{{ $cotizacion->lugar_entrega }}</td>
        </tr>
        <tr>
            <td>Plazo de entrega</td>
            <td>{{ $cotizacion->plazo_entrega }}</td>
        </tr>
        <tr>
            <td>Vigencia de la oferta</td>
            <td>{{ $cotizacion->vigencia_oferta }}</td>
        </tr>
    </table>

    {{-- OFERTA --}}
    <br>
    <strong>Oferta</strong>

    <table>
        <thead>
            <tr>
                <th width="5%">Ítem</th>
                <th width="45%">Producto</th>
                <th width="10%">Cantidad</th>
                <th width="10%">IVA %</th>
                <th width="15%">Precio Unit.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cotizacion->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->producto }}</td>
                    <td>{{ $item->cantidad }}</td>
                    <td>{{ $item->iva }}</td>
                    <td>${{ number_format($item->precio_unitario, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ESPECIFICACIONES --}}
    <br>
    <strong>Especificaciones técnicas</strong>
    <div class="box" style="min-height: 50px;">
        {{ $cotizacion->especificaciones_tecnicas }}
    </div>

    {{-- OBSERVACIONES --}}
    <br>
    <strong>Observaciones</strong>
    <div class="box" style="min-height: 50px;">
        {{ $cotizacion->observaciones }}
    </div>

    {{-- TOTAL --}}
    <div class="total-box">
        <table>
            <tr>
                <th>Importe Total</th>
                <td>
                    ${{ number_format($cotizacion->importe_total, 2, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>

</div>

</body>
</html>
