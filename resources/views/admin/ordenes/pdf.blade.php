<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orden de Compra Nº {{ $orden->numero_oc }}</title>

    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }

        .header { text-align: center; margin-bottom: 10px; }
        .title { font-size: 20px; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; }
        th { background-color: #f0f0f0; }

        .section-title { margin-top: 20px; font-weight: bold; }

        .signature-container {
            width: 100%;
            margin-top: 50px;
            text-align: right;
        }

        .signature-image {
            width: 180px; /* Ajustá según tu firma */
        }

        .signature-text {
            margin-top: 5px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="header">
    <div class="title">ORDEN DE COMPRA - SECAR</div>
    <div>Nº {{ $orden->numero_oc }}</div>
</div>

<h4>Datos del Proveedor</h4>

<table>
    <tr>
        <th>Proveedor</th>
        <td>{{ $orden->proveedor }}</td>
        <th>CUIT</th>
        <td>{{ $orden->cuit }}</td>
    </tr>
    <tr>
        <th>Dirección</th>
        <td>{{ $orden->direccion }}</td>
        <th>Teléfono</th>
        <td>{{ $orden->telefono }}</td>
    </tr>
    <tr>
        <th>Email</th>
        <td>{{ $orden->email }}</td>
        <th>Fecha</th>
        <td>{{ $orden->fecha }}</td>
    </tr>
    <tr>
        <th>Condición Compra</th>
        <td>{{ $orden->condicion_compra }}</td>
        <th>Solicitud Compra</th>
        <td>{{ $orden->solicitud_compra }}</td>
    </tr>
</table>

<h4 class="section-title">Ítems</h4>

<table>
    <thead>
        <tr>
            <th>Código</th>
            <th>Descripción</th>
            <th>Cant.</th>
            <th>Unidad</th>
            <th>P. Unitario</th>
            <th>Desc.</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($orden->items as $item)
        <tr>
            <td>{{ $item->codigo }}</td>
            <td>{{ $item->descripcion }}</td>
            <td>{{ number_format($item->cantidad, 2) }}</td>
            <td>{{ $item->unidad }}</td>
            <td>${{ number_format($item->precio_unitario, 2) }}</td>
            <td>${{ number_format($item->descuento, 2) }}</td>
            <td>${{ number_format($item->total, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h3 class="section-title">Total General</h3>
<p><strong>Total: </strong> ${{ number_format($orden->total, 2) }}</p>

@if($orden->observaciones)
<h4 class="section-title">Observaciones</h4>
<p>{{ $orden->observaciones }}</p>
@endif

{{-- FIRMA ABAJO A LA DERECHA --}}
<div class="signature-container">
    <img src="{{ public_path('assets/img/firma.png') }}" class="signature-image">
    <div class="signature-text">Firma autorizada</div>
</div>

</body>
</html>
