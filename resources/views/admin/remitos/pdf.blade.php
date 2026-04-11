<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Remito {{ $remito->numero_remito }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .container {
            border: 1px solid #000;
            padding: 10px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }

        .logo {
            width: 30%;
        }

        .tipo {
            text-align: center;
            width: 10%;
            font-size: 28px;
            font-weight: bold;
        }

        .info {
            width: 60%;
            text-align: right;
        }

        .section {
            margin-top: 10px;
        }

        .row {
            display: flex;
            justify-content: space-between;
        }

        .col {
            width: 48%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th {
            background: #000;
            color: #fff;
            padding: 5px;
            font-size: 11px;
        }

        table td {
            border: 1px solid #000;
            padding: 5px;
            height: 25px;
        }

        .footer {
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }

        .small {
            font-size: 10px;
        }

    </style>
</head>
<body>

<div class="container">

    {{-- HEADER --}}
    <div class="header">

        <div class="logo">
            <strong>SECAR</strong><br>
            Ingeniería Eléctrica SRL<br>
            Entre Ríos 751 - Tel: 3815336400<br>
            Email: secarinfo@gmail.com
        </div>

        <div class="tipo">
            R
        </div>

        <div class="info">
            <strong>REMlTO</strong><br>
            N°: {{ $remito->numero_remito }}<br>
            Fecha: {{ $remito->fecha ? $remito->fecha->format('d/m/Y') : '' }}<br>
            CUIT: 30-00000000-0<br>
            Cond. IVA: Responsable Inscripto
        </div>

    </div>

    {{-- CLIENTE --}}
    <div class="section">
        <strong>Señor:</strong> {{ $remito->cliente->razon_social ?? '' }}<br>
        <strong>Domicilio:</strong> {{ $remito->cliente->direccion ?? '' }}<br>
        <strong>CUIT:</strong> {{ $remito->cliente->cuit ?? '' }}
    </div>

    {{-- DATOS EXTRA --}}
    <div class="section row">
        <div class="col">
            <strong>Cond. Venta:</strong> {{ $remito->condicion_venta }}
        </div>
        <div class="col">
            <strong>OC Asociada:</strong> {{ $remito->ordenesCompra->pluck('numero_oc')->implode(', ') ?: '-' }}
        </div>
    </div>

    <div class="section">
        <strong>Cond. IVA:</strong> {{ $remito->cliente->iva ?? '-' }}
    </div>

    {{-- ITEMS --}}
    <table>
        <thead>
            <tr>
                <th>ARTÍCULO</th>
                <th>CANTIDAD</th>
                <th>DESCRIPCIÓN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($remito->items as $item)
                <tr>
                    <td>{{ $item->articulo }}</td>
                    <td>{{ $item->cantidad }}</td>
                    <td>{{ $item->descripcion }}</td>
                </tr>
            @endforeach

            {{-- filas vacías para mantener formato --}}
            @for($i = count($remito->items); $i < 8; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>

    {{-- TRANSPORTE --}}
    <div class="section">
        <strong>Transportista:</strong> {{ $remito->transportista }}<br>
        <strong>Domicilio:</strong> {{ $remito->domicilio_transportista }}<br>
        <strong>I.V.A:</strong> {{ $remito->iva_transportista }}<br>
        <strong>C.U.I.T:</strong> {{ $remito->cuit_transportista }}
    </div>

    {{-- OBSERVACIONES --}}
    <div class="section">
        <strong>Observaciones:</strong><br>
        {{ $remito->observacion }}
    </div>

    {{-- FOOTER --}}
    <div class="footer row">

        <div class="col small">
            <strong>CAI:</strong> {{ $remito->cai }}<br>
            <strong>Vencimiento:</strong> {{ $remito->cai_vto ? $remito->cai_vto->format('d/m/Y') : '' }}
        </div>

        <div class="col small" style="text-align:right;">
            Recibí Conforme
        </div>

    </div>

</div>

</body>
</html>