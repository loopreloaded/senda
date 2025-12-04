<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recibo</title>

    <style>
        @page { margin: 15px; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .hoja {
            padding: 10px;
            box-sizing: border-box;
        }

        /* ─────────── ENCABEZADO ─────────── */
        .header {
            width: 100%;
            border: 2px solid #000;
            padding: 8px;
            box-sizing: border-box;
            position: relative;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            width: 100%;
        }

        .logo {
            width: 110px;
        }

        .titulo-remito {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 3px;
            text-align: right;
        }

        .recuadro-remito {
            border: 2px solid #000;
            padding: 4px 6px;
            display: inline-block;
            font-size: 13px;
            font-weight: bold;
            margin-top: 5px;
        }

        /* ─────────── BLOQUES ─────────── */
        .bloque {
            width: 100%;
            border: 1px solid #000;
            border-top: none;
            padding: 5px 8px;
            box-sizing: border-box;
            line-height: 1.3;
        }

        .bloque label {
            font-weight: bold;
        }

        /* ─────────── TABLA ─────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 11px;
            word-wrap: break-word;
        }

        th {
            background: #eee;
        }

        .art { width: 70px; }
        .cant { width: 70px; }
        .desc { width: auto; }

        /* PIE */
        .footer {
            width: 100%;
            margin-top: 20px;
            font-size: 11px;
        }

        .fila-footer {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            width: 100%;
        }

        .observaciones {
            flex: 1;
            height: 90px;
            border: 1px solid #000;
            padding: 6px;
            box-sizing: border-box;
        }

        .recibi {
            width: 30%;
            height: 90px;
            border: 1px solid #000;
            padding: 6px;
            box-sizing: border-box;
            text-align: center;
        }

        .marca-agua {
            position: absolute;
            top: 58%;
            left: 50%;
            width: 260px;
            opacity: 0.07;
            transform: translate(-50%, -50%);
            z-index: -1;
        }
    </style>
</head>

<body>

<div class="hoja">

    <!-- Marca de agua -->
    <img src="{{ public_path('assets/img/logo-secar.png') }}" class="marca-agua">

    <!-- ENCABEZADO -->
    <!-- ENCABEZADO -->
<table style="width:100%; border:2px solid #000; border-collapse:collapse; padding:0; margin:0;">
    <tr>
        <!-- COLUMNA IZQUIERDA -->
        <td style="width:60%; padding:8px; vertical-align:top;">

            <img src="{{ public_path('assets/img/logo-secar.png') }}" style="width:110px;">

            <div style="font-size:16px; font-weight:bold; margin-top:4px;">
                INGENIERÍA ELÉCTRICA SRL
            </div>

            <div style="font-size:11px; margin-top:3px; line-height:1.3;">
                Entre Ríos 751 — Tel: 381 2564900 / 381 3363240<br>
                Email: secarsrl@gmail.com<br>
                IVA RESPONSABLE INSCRIPTO
            </div>

        </td>

        <!-- COLUMNA DERECHA -->
        <td style="width:40%; padding:8px; vertical-align:top; text-align:right;">

            <div style="font-size:22px; font-weight:bold; letter-spacing:3px;">
                R E M I T O
            </div>

            <div style="font-size:11px;">
                DOCUMENTO NO VÁLIDO COMO FACTURA
            </div>

            <div style="border:2px solid #000; padding:4px 6px;
                        font-size:13px; font-weight:bold;
                        display:inline-block; margin-top:4px;">
                Nº {{ $remito->punto_venta ?? '0003' }} - {{ str_pad($remito->numero ?? 0, 8, '0', STR_PAD_LEFT) }}
            </div>

            <div style="font-size:12px; margin-top:4px;">
                Fecha: {{ $remito->fecha ?? '-' }}
            </div>

            <div style="font-size:11px; margin-top:4px; line-height:1.3;">
                CUIT: 30-95153066-5<br>
                Inicio de Act.: 11/88
            </div>

        </td>
    </tr>
</table>


    <!-- BLOQUE: SEÑORES -->
    <table style="width:100%; border-collapse:collapse; border:1px solid #000; border-top:none; margin:0; padding:0;">
        <tr>
            <td style="padding:6px; font-size:12px;">
                <strong>SEÑORES:</strong> {{ $remito->razon_social }} &nbsp;&nbsp;
                <strong>COD. CLIENTE:</strong> - &nbsp;&nbsp;
                <strong>ORDEN DE COMPRA N°:</strong> {{ $remito->orden_compra }}
            </td>
        </tr>
    </table>

    <!-- BLOQUE: DOMICILIO -->
    <table style="width:100%; border-collapse:collapse; border:1px solid #000; border-top:none; margin:0; padding:0;">
        <tr>
            <td style="padding:6px; font-size:12px; line-height:1.3;">
                <strong>DOMICILIO:</strong> {{ $remito->domicilio }} <br>
                <strong>LOCALIDAD:</strong> {{ $remito->localidad }} <br>
                <strong>I.V.A.:</strong> - &nbsp;&nbsp;
                <strong>C.U.I.T.:</strong> {{ $remito->cuit }} &nbsp;&nbsp;
                <strong>PROVEEDOR N°:</strong> -
            </td>
        </tr>
    </table>

    <!-- BLOQUE: CONDICIONES -->
    <table style="width:100%; border-collapse:collapse; border:1px solid #000; border-top:none; margin:0; padding:0;">
        <tr>
            <td style="padding:6px; font-size:12px;">
                <strong>CONDICIONES DE VENTA:</strong> Otra
            </td>
        </tr>
    </table>


    <!-- TABLA -->
    <table>
        <thead>
        <tr>
            <th class="art">ARTÍCULO</th>
            <th class="cant">CANTIDAD</th>
            <th class="desc">DESCRIPCIÓN</th>
        </tr>
        </thead>

        <tbody>
        @foreach($remito->items as $item)
            <tr>
                <td style="text-align:center;">{{ $item->articulo }}</td>
                <td style="text-align:center;">{{ $item->cantidad }}</td>
                <td>{{ $item->descripcion }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- PIE -->
    <div class="footer">
        <div class="fila-footer">

            <div class="observaciones">
                <strong>OBSERVACIONES:</strong><br>
                {!! nl2br(e($remito->observaciones ?? '')) !!}
            </div>

            <div class="recibi">
                <div style="margin-top:55px;">Recibí Conforme</div>
            </div>

        </div>
    </div>

</div>

</body>
</html>
