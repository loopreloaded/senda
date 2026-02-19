@php
    $copias = ['ORIGINAL', 'DUPLICADO', 'TRIPLICADO'];
@endphp


<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Factura {{ $factura->tipo_comprobante }}</title>

<style>* {
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

@page {
    size: A4;
    margin: 10mm;
}

body {
    font-size: 13px;
    margin: 0;
    padding: 0;
}

/* CONTENEDOR GENERAL */
.factura {
    max-width: 190mm; /* 210mm - márgenes */
    margin: 0 auto;
}

/* WRAPPER */
.wrapper {
    border: 1.5px solid #333;
    padding: 5px;
    width: 100%;
}

/* TEXTO */
.text-left { text-align: left; }
.text-center { text-align: center; }
.text-right { text-align: right; }
.bold { font-weight: bold; }
.italic { font-style: italic; }

.flex {
    display: flex;
    flex-wrap: wrap;
}

.inline-block {
    display: inline-block;
}

.relative {
    position: relative;
}

/* ENCABEZADO */
.header {
    width: 100%;
}

/* COLUMNAS */
.w50 {
    width: 50%;
}

/* LETRA FACTURA (A/B/C) */
.floating-mid {
    position: absolute;
    top: 0px;                     /* ya correcto */
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 40px;                  /* ⬅️ MÁS BAJO */
    background: #fff;
    border: 1.5px solid #333;
    z-index: 5;

    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
}



/* TABLAS */
table {
    border-collapse: collapse;
    width: 100%;
}

th {
    border: 1px solid #000;
    background: #ccc;
    padding: 5px;
}

td {
    padding: 5px;
    font-size: 11px;
}

.text-20 {
    font-size: 20px;
}

.footer-fixed {
    position: fixed;
    bottom: 10px;      /* pegado al borde inferior */
    left: 20px;
    right: 20px;
}


</style>
</head>

<body>

@foreach($copias as $index => $copia)

<div class="factura">

    {{-- ================= TIPO DE COPIA ================= --}}
    <div class="wrapper text-center bold text-20"
         style="width:100%; border-bottom:0;">
        {{ $copia }}
    </div>

{{-- ================= ENCABEZADO ================= --}}
<div class="relative">

    <table style="width:102%; border-collapse:collapse;">
        <tr>

            <td class="wrapper" style="width:50%; vertical-align:top; border-right:0;">

                {{-- LOGO EMPRESA CENTRADO --}}
                <div style="text-align:center; margin-bottom:5px;">
                    <img
                        src="{{ public_path('img/logo.png') }}"
                        style="width:90px; max-height:70px;"
                        alt="Logo Empresa"
                    >
                </div>

                <h3 class="text-center" style="font-size:24px;margin-bottom:3px;">
                    {{ $empresa->razon_social }}
                </h3>

                <p style="font-size:13px;line-height:1.4;margin:0;">
                    <b>Razón Social:</b> {{ $empresa->razon_social }}<br>
                    <b>Domicilio Comercial:</b> {{ $empresa->direccion }}<br>
                    <b>Condición frente al IVA:</b> {{ $empresa->condicion_iva }}
                </p>
            </td>

            {{-- COLUMNA DERECHA --}}
            <td class="wrapper" style="width:50%; vertical-align:top;">
                <h3 class="text-center" style="font-size:24px;margin-bottom:3px;">
                    FACTURA
                </h3>
                <p style="font-size:13px;line-height:1.4;margin:0;">
                    <b>Punto de Venta:</b> {{ str_pad($factura->punto_venta,5,'0',STR_PAD_LEFT) }}
                    <b>Comp. Nro:</b> {{ str_pad($factura->numero_comprobante,8,'0',STR_PAD_LEFT) }}<br>
                    <b>Fecha de Emisión:</b>
                    {{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y') }}<br>
                    <b>CUIT:</b> {{ $empresa->cuit }}<br>
                    <b>Ingresos Brutos:</b> {{ $empresa->iibb }}<br>
                    <b>Fecha de Inicio de Actividades:</b> {{ $empresa->inicio_actividades }}
                </p>
            </td>

        </tr>
    </table>

    {{-- LETRA FACTURA (A / B / C) --}}
    <div class="wrapper floating-mid">
        <h3 class="no-margin text-center" style="font-size:32px; margin-top:0px;">
            {{ $factura->tipo_comprobante }}
        </h3>
        <h5 class="no-margin text-center" style="margin-top:-40px;">
            COD. {{ $factura->tipo_comprobante_codigo ?? '01' }}
        </h5>
    </div>


</div>

{{-- ================= SERVICIOS ================= --}}
@if($factura->concepto == 2 || $factura->concepto == 3)
<div class="wrapper flex space-around" style="margin-top:1px;">
    <span><b>Período Facturado Desde:</b>
        {{ \Carbon\Carbon::parse($factura->fecha_desde)->format('d/m/Y') }}
    </span>
    <span><b>Hasta:</b>
        {{ \Carbon\Carbon::parse($factura->fecha_hasta)->format('d/m/Y') }}
    </span>
    <span><b>Fecha de Vto. para el pago:</b>
        {{ \Carbon\Carbon::parse($factura->vencimiento_pago)->format('d/m/Y') }}
    </span>
</div>
@endif

{{-- ================= CLIENTE ================= --}}
<div class="wrapper" style="margin-top:2px;font-size:12px;">
    <div class="flex" style="margin-bottom:15px;">
        <span style="width:30%">
            <b>CUIT:</b> {{ $factura->cliente->cuit }}
        </span>
        <span>
            <b>Apellido y Nombre / Razón Social:</b> {{ $factura->cliente->razon_social }}
        </span>
    </div>

    <div class="flex" style="flex-wrap:nowrap;margin-bottom:5px;">
        <span style="width:70%">
            <b>Condición frente al IVA:</b> {{ $factura->cliente->condicion_iva }}
        </span>
        <span>
            <b>Domicilio:</b> {{ $factura->cliente->direccion }}
        </span>
    </div>

    <div class="flex">
        <span><b>Condición de venta:</b> {{ ucfirst($factura->condicion_venta) }}</span>
    </div>
</div>

{{-- ================= ITEMS ================= --}}
<table style="margin-top:5px;">
<thead>
<tr>
    <th class="text-left">Código</th>
    <th class="text-left">Producto / Servicio</th>
    <th>Cantidad</th>
    <th>U. Medida</th>
    <th>Precio Unit.</th>
    <th>% Bonif</th>
    <th>Subtotal</th>
    <th>Alicuota IVA</th>
    <th>Subtotal c/IVA</th>
</tr>
</thead>
<tbody>
@foreach($factura->items as $item)
@php
    $subtotal = $item->cantidad * $item->precio_unitario;
    $iva = $subtotal * ($item->iva / 100);
@endphp
<tr>
    <td class="text-left">{{ $item->codigo }}</td>
    <td class="text-left">{{ $item->descripcion }}</td>
    <td class="text-right">{{ number_format($item->cantidad,2,',','.') }}</td>
    <td class="text-center">{{ $item->unidad_texto }}</td>
    <td class="text-right">{{ number_format($item->precio_unitario,2,',','.') }}</td>
    <td class="text-center">0,00</td>
    <td class="text-right">{{ number_format($subtotal,2,',','.') }}</td>
    <td class="text-right">{{ $item->iva }}%</td>
    <td class="text-right">{{ number_format($subtotal + $iva,2,',','.') }}</td>
</tr>
@endforeach
</tbody>
</table>

{{-- ===================== BLOQUE INFERIOR FIJO ===================== --}}
<div class="footer-fixed" style="border:1px solid #000; padding:8px;">

    {{-- ===================== OTROS TRIBUTOS ===================== --}}
    <div>

        <table style="width:60%; border-collapse:collapse; font-size:11px;" border="1">
            <thead>
                <tr style="background:#e6e6e6;">
                    <th style="padding:4px; text-align:left;">Descripción</th>
                    <th style="padding:4px; text-align:left;">Detalle</th>
                    <th style="padding:4px; text-align:right;">Alic. %</th>
                    <th style="padding:4px; text-align:right;">Importe</th>
                </tr>
            </thead>
            <tbody>
                @if(($factura->percepcion_iibb_importe ?? 0) > 0)
                    <tr>
                        <td style="padding:4px;">Percepción de Ingresos Brutos</td>
                        <td style="padding:4px;">
                            {{ $factura->percepcion_iibb_detalle ?? 'Percepción IIBB CM' }}
                        </td>
                        <td style="padding:4px; text-align:right;">
                            {{ number_format($factura->percepcion_iibb_alicuota ?? 0, 2, ',', '.') }}
                        </td>
                        <td style="padding:4px; text-align:right;">
                            {{ number_format($factura->percepcion_iibb_importe ?? 0, 2, ',', '.') }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

    </div>

    {{-- ===================== TOTALES ===================== --}}
    <div style="margin-top:6px;">

        <table style="width:40%; margin-left:auto; border-collapse:collapse; font-size:12px;">

            <tr>
                <td style="text-align:right;"><b>Importe Neto Gravado: {{ $factura->moneda }}</b></td>
                <td style="text-align:right;">
                    {{ number_format($factura->subtotal,2,',','.') }}
                </td>
            </tr>

            <tr>
                <td style="text-align:right;"><b>IVA 27%: {{ $factura->moneda }}</b></td>
                <td style="text-align:right;">0,00</td>
            </tr>

            <tr>
                <td style="text-align:right;"><b>IVA 21%: {{ $factura->moneda }}</b></td>
                <td style="text-align:right;">
                    {{ number_format($factura->total_iva,2,',','.') }}
                </td>
            </tr>

            <tr>
                <td style="text-align:right;"><b>IVA 10.5%: {{ $factura->moneda }}</b></td>
                <td style="text-align:right;">0,00</td>
            </tr>

            <tr>
                <td style="text-align:right;"><b>IVA 5%: {{ $factura->moneda }}</b></td>
                <td style="text-align:right;">0,00</td>
            </tr>

            <tr>
                <td style="text-align:right;"><b>IVA 2.5%: {{ $factura->moneda }}</b></td>
                <td style="text-align:right;">0,00</td>
            </tr>

            <tr>
                <td style="text-align:right;"><b>IVA 0%: {{ $factura->moneda }}</b></td>
                <td style="text-align:right;">0,00</td>
            </tr>

            <tr>
                <td style="text-align:right;"><b>Importe Otros Tributos: {{ $factura->moneda }}</b></td>
                <td style="text-align:right;">
                    {{ number_format($factura->importe_total_otros_tributos ?? 0,2,',','.') }}
                </td>
            </tr>

            <tr>
                <td style="text-align:right; padding-top:6px;"><b>Importe Total: {{ $factura->moneda }}</b></td>
                <td style="text-align:right; padding-top:6px;">
                    <b>{{ number_format($factura->importe_total,2,',','.') }}</b>
                </td>
            </tr>

        </table>

    </div>

    {{-- ================= QR + CAE ================= --}}
    <div style="margin-top:6px;">

        <table style="width:100%; border-collapse:collapse; font-size:11px;">
            <tr>

                {{-- QR --}}
                <td style="width:15%; vertical-align:top;">
                    @if(!empty($factura->cae) && !empty($qrImage))
                        <img
                            src="data:image/png;base64,{{ $qrImage }}"
                            style="width:90px;"
                            alt="QR AFIP"
                        >
                    @endif
                </td>

                {{-- ARCA --}}
                <td style="width:45%; vertical-align:top; padding-left:10px;">
                    <div style="font-weight:bold; font-size:14px;">ARCA</div>
                    <div style="font-size:9px;">
                        AGENCIA DE RECAUDACIÓN<br>
                        Y CONTROL ADUANERO
                    </div>

                    <div style="margin-top:6px; font-style:italic; font-weight:bold;">
                        Comprobante Autorizado
                    </div>

                    <div style="font-size:9px; font-style:italic;">
                        Esta Agencia no se responsabiliza por los datos ingresados en el detalle de la operación
                    </div>
                </td>

                {{-- PAGINA --}}
                <td style="width:15%; vertical-align:top; text-align:center;">
                    <div style="font-weight:bold;">Pág. 1/1</div>
                </td>

                {{-- CAE --}}
                <td style="width:25%; vertical-align:top;">
                    <table style="width:100%; font-size:11px;">
                        <tr>
                            <td style="width:55%; text-align:right;"><b>CAE N°:</b></td>
                            <td style="padding-left:6px;">
                                {{ $factura->cae ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align:right;"><b>Fecha de Vto. de CAE:</b></td>
                            <td style="padding-left:6px;">
                                {{ $factura->vto_cae
                                    ? \Carbon\Carbon::parse($factura->vto_cae)->format('d/m/Y')
                                    : '-' }}
                            </td>
                        </tr>
                    </table>
                </td>

            </tr>
        </table>

    </div>

</div>



{{-- SALTO DE PÁGINA EXCEPTO EN LA ÚLTIMA --}}
@if(!$loop->last)
    <div style="page-break-after: always;"></div>
@endif

@endforeach

</div>
</body>
</html>
