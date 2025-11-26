@extends('adminlte::page')

@section('title', 'Detalle de Factura')

@section('content_header')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <h2>Factura {{ $factura->tipo_comprobante }} — N° {{ str_pad($factura->punto_venta, 4, '0', STR_PAD_LEFT) }}-{{ str_pad($factura->numero, 8, '0', STR_PAD_LEFT) }}</h2>
        </div>
    </div>
@stop

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
                    <strong>Receptor:</strong> {{ $factura->cliente->nombre ?? 'Sin cliente' }}<br>
                    <strong>CUIT:</strong> {{ $factura->cliente->cuit ?? '-' }}<br>
                    <strong>Domicilio:</strong> {{ $factura->cliente->direccion ?? '-' }}<br>
                    <strong>Condición IVA:</strong> {{ $factura->cliente->condicion_iva ?? '-' }}
                </div>
            </div>

            <hr>

            <div class="row mb-2">
                <div class="col-md-4">
                    <strong>Tipo:</strong> {{ $factura->tipo_comprobante }}
                </div>
                <div class="col-md-4">
                    <strong>Número:</strong> {{ str_pad($factura->punto_venta, 4, '0', STR_PAD_LEFT) }}-{{ str_pad($factura->numero, 8, '0', STR_PAD_LEFT) }}
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
                        <th>Descripción</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Unitario</th>
                        <th class="text-end">IVA (%)</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $base_iva = 0;
                        $iva_total = 0;
                    @endphp
                    @foreach($factura->items as $item)
                        @php
                            $base_iva += $item->subtotal;
                            $iva_total += $item->subtotal * ($item->iva / 100);
                        @endphp
                        <tr>
                            <td>{{ $item->descripcion }}</td>
                            <td class="text-end">{{ number_format($item->cantidad, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($item->precio_unitario, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($item->iva, 0) }}%</td>
                            <td class="text-end">{{ number_format($item->subtotal, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- RESUMEN --}}
            <div class="row mt-4">
                <div class="col-md-6">
                    <p><strong>Base IVA 21%:</strong> {{ number_format($base_iva, 2, ',', '.') }}</p>
                    <p><strong>IVA 21%:</strong> {{ number_format($iva_total, 2, ',', '.') }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <p><strong>Subtotal:</strong> {{ number_format($base_iva, 2, ',', '.') }}</p>
                    <p><strong>IVA total:</strong> {{ number_format($iva_total, 2, ',', '.') }}</p>
                    <h4><strong>Total:</strong> ${{ number_format($factura->importe_total, 2, ',', '.') }}</h4>
                </div>
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
                    @if($factura->estado == 'pendiente') bg-warning
                    @elseif($factura->estado == 'aprobada') bg-success
                    @else bg-secondary @endif">
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

                    {{-- BOTÓN: Enviar a AFIP (solo Admin o Ingeniero y si está pendiente) --}}
                    @if($factura->estado === 'pendiente' && auth()->user()->hasAnyRole(['admin','ingeniero']))
                        <form action="{{ route('facturas.afip', $factura->id) }}"
                            method="POST"
                            onsubmit="return confirm('¿Enviar factura a AFIP?')">
                            @csrf
                            <button class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Enviar a AFIP
                            </button>
                        </form>
                    @endif

                    {{-- BOTÓN: Editar factura --}}
                    @if($factura->estado != 'aprobada')
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
