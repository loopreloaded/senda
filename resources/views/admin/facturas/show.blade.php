@extends('adminlte::page')

@section('title', 'Detalle de Factura')

@section('content_header')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <h2>Factura {{ $factura->tipo_comprobante }} — N° {{ str_pad($factura->punto_venta, 4, '0', STR_PAD_LEFT) }}-{{ str_pad($factura->id, 8, '0', STR_PAD_LEFT) }}</h2>
        </div>
    </div>
@stop

@php
    $unidadTexto = [
        1  => 'kilogramos',
        2  => 'metros',
        3  => 'metros cuadrados',
        4  => 'metros cúbicos',
        5  => 'litros',
        6  => '1000 kWh',
        7  => 'unidades',
        8  => 'pares',
        9  => 'docenas',
        10 => 'quilates',
        11 => 'millares',
        14 => 'gramos',
        15 => 'milímetros',
        16 => 'mm cúbicos',
        17 => 'kilómetros',
        18 => 'hectolitros',
        20 => 'centímetros',
        25 => 'jgo. pqt. mazo naipes',
        27 => 'cm cúbicos',
        29 => 'toneladas',
        30 => 'dam cúbicos',
        31 => 'hm cúbicos',
        32 => 'km cúbicos',
        33 => 'microgramos',
        34 => 'nanogramos',
        35 => 'picogramos',
        41 => 'miligramos',
        47 => 'mililitros',
        48 => 'curie',
        49 => 'milicurie',
        50 => 'microcurie',
        51 => 'uiacthor',
        52 => 'muiacthor',
        53 => 'kg base',
        54 => 'gruesa',
        61 => 'kg bruto',
        62 => 'uiactant',
        63 => 'muiactant',
        64 => 'uiactig',
        65 => 'muiactig',
        66 => 'kg activo',
        67 => 'gramo activo',
        68 => 'gramo base',
        96 => 'packs',
        98 => 'otras unidades',
    ];
@endphp


@section('content')

    {{-- Mensajes de éxito --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>✔ Éxito:</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Mensajes de error --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>❌ Error:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif


    <div class="card">
        <div class="card-body">

            {{-- ENCABEZADO --}}
            <h5 class="mb-3"><strong>Datos del Comprobante</strong></h5>
            <div class="row">
                <div class="col-md-6">
                    <strong>Emisor:</strong> Secar <br>
                    <strong>CUIT:</strong> 30-61513606-5<br>
                    <strong>Domicilio:</strong> Entre Rios 751<br>
                    <strong>Pto. Venta:</strong> {{ str_pad($factura->punto_venta, 4, '0', STR_PAD_LEFT) }}
                </div>
                <div class="col-md-6">
                    <strong>Receptor:</strong> {{ $factura->cliente->razon_social ?? 'Sin cliente' }}<br>
                    <strong>CUIT:</strong> {{ $factura->cliente->cuit ?? '-' }}<br>
                    <strong>Domicilio:</strong> {{ $factura->cliente->direccion ?? '-' }}<br>
                    <strong>Email:</strong> {{ $factura->cliente->email ?? '-' }}<br>
                    <strong>Condición IVA:</strong> {{ $factura->cliente->condicion_iva_texto ?? '-' }}<br>
                    <strong>Condición IIBB:</strong> {{ $factura->cliente->condicion_iibb_texto ?? '-' }}
                </div>
            </div>

            <hr>

            <div class="row mb-2">
                <div class="col-md-3">
                    <strong>ID FAC (#):</strong> <span class="badge badge-dark">FAC-{{ $factura->id }}</span>
                </div>
                <div class="col-md-3">
                    <strong>Tipo:</strong> {{ $factura->tipo_comprobante }}
                </div>
                <div class="col-md-3">
                    <strong>Moneda:</strong> {{ $factura->moneda }}
                </div>
                <div class="col-md-3">
                    <strong>Nro. Comprobante AFIP:</strong> {{ str_pad($factura->punto_venta, 4, '0', STR_PAD_LEFT) }}-{{ str_pad($factura->numero_comprobante_afip ?? $factura->id, 8, '0', STR_PAD_LEFT) }}
                </div>
                <div class="col-md-4">
                    <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y') }}
                </div>
            </div>

            {{-- ITEMS --}}
            <h5 class="mt-4 mb-2"><strong>Ítems</strong></h5>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Descripción</th>
                        <th>Unidad</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Unitario</th>
                        <th class="text-end">% Bonif</th>
                        <th class="text-end">Imp. Bonif</th>
                        <th class="text-end">IVA (%)</th>
                        <th class="text-end">Subtotal s/IVA</th>
                        <th class="text-end">Subtotal c/IVA</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($factura->items as $item)
                        <tr>
                            <td>{{ $item->codigo }}</td>
                            <td>{{ $item->descripcion }}</td>
                            <td>{{ $unidadTexto[$item->unidad] ?? $item->unidad }}</td>

                            <td class="text-end">{{ number_format($item->cantidad, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($item->precio_unitario, 2, ',', '.') }}</td>

                            {{-- % Bonificación --}}
                            <td class="text-end">
                                {{ number_format($item->bonificacion_porcentaje ?? 0, 2, ',', '.') }}%
                            </td>

                            {{-- Importe bonificación --}}
                            <td class="text-end">
                                {{ number_format($item->bonificacion_importe ?? 0, 2, ',', '.') }}
                            </td>

                            {{-- IVA --}}
                            <td class="text-end">{{ number_format($item->iva, 2) }}%</td>

                            {{-- Subtotal sin IVA --}}
                            <td class="text-end">
                                {{ number_format($item->subtotal_sin_iva ?? 0, 2, ',', '.') }}
                            </td>

                            {{-- Subtotal con IVA --}}
                            <td class="text-end">
                                {{ number_format($item->subtotal_con_iva ?? 0, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>


            {{-- RESUMEN --}}
            @php
                $subtotal_sin_iva = $factura->items->sum('subtotal_sin_iva');

                $subtotal_con_iva = $factura->items->sum('subtotal_con_iva');

                $subtotal_otros_tributos = $factura->importe_total_otros_tributos ?? 0;

                $importe_total_final = $subtotal_con_iva + $subtotal_otros_tributos;
            @endphp


            <div class="row mt-4">
                <div class="col-md-6 text-end">
                    @php
                        $total_final = ($factura->importe_total ?? 0)
                                    + ($factura->importe_total_otros_tributos ?? 0);
                    @endphp

                    <h4>
                        <strong>Total:</strong>
                        $ {{ number_format($factura->importe_total, 2, ',', '.') }}
                    </h4>

                </div>
            </div>

            {{-- PERCEPCIONES --}}
            @if(
                ($factura->percepcion_iva_importe ?? 0) > 0 ||
                ($factura->percepcion_iibb_importe ?? 0) > 0
            )
                <hr>

                <h5 class="mt-3"><strong>Otros Tributos / Percepciones</strong></h5>

                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Detalle</th>
                            <th class="text-end">Base Imponible</th>
                            <th class="text-end">Alícuota %</th>
                            <th class="text-end">Importe</th>
                        </tr>
                    </thead>
                    <tbody>

                        {{-- Percepción IVA --}}
                        @if(($factura->percepcion_iva_importe ?? 0) > 0)
                            <tr>
                                <td>{{ $factura->percepcion_iva_detalle }}</td>
                                <td class="text-end">
                                    {{ number_format($factura->percepcion_iva_base ?? 0, 2, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    {{ number_format($factura->percepcion_iva_alicuota ?? 0, 2, ',', '.') }}%
                                </td>
                                <td class="text-end">
                                    {{ number_format($factura->percepcion_iva_importe ?? 0, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endif

                        {{-- Percepción Ingresos Brutos --}}
                        @if(($factura->percepcion_iibb_importe ?? 0) > 0)
                            <tr>
                                <td>{{ $factura->percepcion_iibb_detalle }}</td>
                                <td class="text-end">
                                    {{ number_format($factura->percepcion_iibb_base ?? 0, 2, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    {{ number_format($factura->percepcion_iibb_alicuota ?? 0, 2, ',', '.') }}%
                                </td>
                                <td class="text-end">
                                    {{ number_format($factura->percepcion_iibb_importe ?? 0, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endif

                    </tbody>
                </table>
            @endif

            @if(($factura->importe_total_otros_tributos ?? 0) > 0)
                <div class="row mt-2">
                    <div class="col-md-6"></div>
                    <div class="col-md-6 text-end">
                        <p>
                            <h4>
                                <strong>Importe Total:</strong>
                                $ {{ number_format($importe_total_final, 3, ',', '.') }}
                            </h4>

                        </p>
                    </div>
                </div>
            @endif

            <div class="col-md-6">
                <p>
                    <strong>Subtotal s/IVA:</strong>
                    $ {{ number_format($subtotal_sin_iva, 3, ',', '.') }}
                </p>

                <p>
                    <strong>Subtotal c/IVA:</strong>
                    $ {{ number_format($subtotal_con_iva, 3, ',', '.') }}
                </p>

                <p>
                    <strong>Subtotal Tributos:</strong>
                    $ {{ number_format($subtotal_otros_tributos, 3, ',', '.') }}
                </p>
            </div>


            <hr>

            {{-- CAE --}}
            @if($factura->cae)
                <p><strong>CAE:</strong> {{ $factura->cae }} &nbsp;&nbsp; — &nbsp;&nbsp; <strong>Vto CAE:</strong> {{ \Carbon\Carbon::parse($factura->vto_cae)->format('d/m/Y') }}</p>
            @else
                <p><strong>CAE:</strong> No emitido</p>
            @endif

            {{-- ESTADO --}}
            <p><strong>Estado:</strong>
                <span class="badge
                    @if($factura->estado == \App\Models\Factura::ESTADO_BORRADOR) bg-secondary
                    @elseif($factura->estado == \App\Models\Factura::ESTADO_EMITIDA) bg-success
                    @elseif($factura->estado == \App\Models\Factura::ESTADO_PARCIAL) bg-warning
                    @elseif($factura->estado == \App\Models\Factura::ESTADO_PAGADA) bg-primary
                    @elseif($factura->estado == \App\Models\Factura::ESTADO_RECHAZADA) bg-danger
                    @else bg-light text-dark @endif">
                    {{ ucfirst($factura->estado) }}
                </span>
            </p>

            {{-- OBSERVACIONES --}}
            <h5><strong>Observaciones</strong></h5>
            <div style="
                border: 1px solid #ccc;
                padding:10px;
                border-radius:6px;
                background:#fafafa;
                white-space:pre-wrap;
                font-size:14px;
            ">
                {{ $factura->observaciones }}
            </div>


            {{-- BOTONES --}}
            <div class="mt-4 d-flex justify-content-between align-items-center">

                {{-- Botón Volver --}}
                <a href="{{ route('facturas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>

                <div class="d-flex gap-2">

                    {{-- BOTÓN: Enviar a AFIP (Admin/Ingeniero, solo si está en Borrador) --}}
                    @if($factura->estado === \App\Models\Factura::ESTADO_BORRADOR)
                        @hasrole('admin|ingeniero')
                            <form action="{{ route('facturas.afip', $factura->id) }}"
                                method="POST"
                                onsubmit="return confirm('¿Enviar factura a AFIP?')">
                                @csrf
                                <button class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Enviar a AFIP
                                </button>
                            </form>
                        @endhasrole
                    @endif

                    {{-- BOTÓN: Editar factura --}}
                    @if($factura->estado === \App\Models\Factura::ESTADO_BORRADOR)
                        <a href="{{ route('facturas.edit', $factura->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    @endif
                </div>
            </div>


        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
<script>
    document.getElementById('btnAfip')?.addEventListener('click', function() {
        if (confirm('¿Desea enviar esta factura a AFIP?')) {
            document.getElementById('afipForm').submit();
        }
    });
</script>
@stop
