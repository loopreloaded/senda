<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Remito</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 15px;
        }

        .datos {
            margin-bottom: 10px;
            line-height: 1.4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table, th, td {
            border: 1px solid #000;
        }

        th, td {
            padding: 6px;
            text-align: left;
        }

        .titulo-seccion {
            margin-top: 20px;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>

<body>

<h2>REMITO</h2>

<div class="datos">
    <strong>Razón Social:</strong> {{ $remito->razon_social ?? '-' }} <br>
    <strong>Domicilio:</strong> {{ $remito->domicilio ?? '-' }} <br>
    <strong>Localidad:</strong> {{ $remito->localidad ?? '-' }} <br>
    <strong>CUIT:</strong> {{ $remito->cuit ?? '-' }} <br>
    <strong>Orden de Compra:</strong> {{ $remito->orden_compra ?? '-' }} <br>
    <strong>Fecha:</strong> {{ $remito->fecha ?? '-' }} <br>
    <strong>Estado:</strong> {{ $remito->estado ?? '-' }} <br>
</div>

<h4 class="titulo-seccion">Detalle de Ítems</h4>

<table>
    <thead>
        <tr>
            <th style="width: 80px;">Artículo</th>
            <th>Descripción</th>
            <th style="width: 80px;">Cantidad</th>
        </tr>
    </thead>

    <tbody>
        @foreach($remito->items as $item)
        <tr>
            <td>{{ $item->articulo }}</td>
            <td>{{ $item->descripcion }}</td>
            <td>{{ $item->cantidad }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
