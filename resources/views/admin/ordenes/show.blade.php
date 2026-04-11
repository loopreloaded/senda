@extends('adminlte::page')

@section('title', 'Detalle de Orden de Compra')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h2>Detalle de Orden de Compra #{{ $orden->numero_oc }}</h2>
        <div>
            <a href="{{ route('ordenes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="{{ route('ordenes.pdf', $orden->id) }}" class="btn btn-danger" target="_blank">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            @hasanyrole('admin|ingeniero')
            <a href="{{ route('ordenes.edit', $orden->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
            @endhasanyrole
        </div>
    </div>
@stop

@section('content')
<div class="row">
    {{-- Información General --}}
    <div class="col-md-4">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Información General</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th style="width: 40%">ID OC (#):</th>
                        <td><span class="badge badge-dark">OC-{{ $orden->id }}</span></td>
                    </tr>
                    <tr>
                        <th>Nro OC (Ext.):</th>
                        <td>{{ $orden->numero_oc }}</td>
                    </tr>
                    <tr>
                        <th>Fecha:</th>
                        <td>{{ \Carbon\Carbon::parse($orden->fecha)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Cliente:</th>
                        <td>{{ $orden->cliente->razon_social ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>CUIT:</th>
                        <td>{{ $orden->cuit }}</td>
                    </tr>
                    <tr>
                        <th>Motivo:</th>
                        <td>
                            @if($orden->motivo == 'pedido')
                                <span class="badge badge-info">Pedido</span>
                            @else
                                <span class="badge badge-secondary">Particular</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Estado:</th>
                        <td>
                            @if($orden->estado == 'pendiente')
                                <span class="badge badge-warning">Pendiente</span>
                            @elseif($orden->estado == 'parcial')
                                <span class="badge badge-info">Parcial</span>
                            @elseif($orden->estado == 'completa')
                                <span class="badge badge-success">Completa</span>
                            @else
                                <span class="badge badge-danger">Anulada</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Trazabilidad --}}
    <div class="col-md-8">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">Trazabilidad de Entrega</h3>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 border-right">
                        <h6>Cant. Pedida (OC)</h6>
                        <h4 class="text-primary">{{ number_format($orden->cant_art_oc, 2) }}</h4>
                    </div>
                    <div class="col-md-3 border-right">
                        <h6>Cant. Entregada</h6>
                        <h4 class="text-success">{{ number_format($orden->cant_art_rem, 2) }}</h4>
                    </div>
                    <div class="col-md-3 border-right">
                        <h6>Pendiente Recibir</h6>
                        <h4 class="text-danger">{{ number_format($orden->cant_art_oc - $orden->cant_art_rem, 2) }}</h4>
                    </div>
                    <div class="col-md-3">
                        <h6>Progreso</h6>
                        @php
                            $progreso = $orden->cant_art_oc > 0 ? ($orden->cant_art_rem / $orden->cant_art_oc) * 100 : 0;
                        @endphp
                        <div class="progress progress-sm mt-2">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progreso }}%" aria-valuenow="{{ $progreso }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small>{{ number_format($progreso, 1) }}%</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    {{-- Ítems de la OC --}}
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-dark">
                <h3 class="card-title">Ítems de la Orden</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Cotización</th>
                            <th class="text-right">Cantidad OC</th>
                            <th class="text-right">Cantidad Remitida</th>
                            <th>Unidad</th>
                            <th class="text-right">Precio Unit.</th>
                            <th class="text-right">IVA</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orden->items as $item)
                        <tr>
                            <td>{{ $item->codigo ?? '-' }}</td>
                            <td>{{ $item->descripcion }}</td>
                            <td>
                                @if($item->id_cotizacion_item && $item->cotizacionItem)
                                    <a href="{{ route('cotizaciones.show', $item->cotizacionItem->id_cotizacion) }}" class="badge badge-info">
                                        Cot #{{ $item->cotizacionItem->id_cotizacion }}
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-right font-weight-bold">{{ number_format($item->cantidad, 2) }}</td>
                            <td class="text-right text-success">
                                @php
                                    // Cálculo simplificado por descripción para el show
                                    $remitido = $orden->remitos()->where('estado','!=','Anulado')->get()->flatMap->items->where('descripcion', $item->descripcion)->sum('cantidad');
                                @endphp
                                {{ number_format($remitido, 2) }}
                            </td>
                            <td>{{ $item->unidad_texto ?? $item->unidad }}</td>
                            <td class="text-right">{{ number_format($item->precio_unitario, 2) }}</td>
                            <td class="text-right">{{ $item->iva }}%</td>
                            <td class="text-right">{{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <th colspan="8" class="text-right">TOTAL ({{ $orden->moneda }}):</th>
                            <th class="text-right"><h4>{{ number_format($orden->total, 2) }}</h4></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

@if($orden->motivo == 'pedido' && $orden->cotizaciones->count() > 0)
<div class="row mt-3">
    {{-- Cotizaciones Vinculadas --}}
    <div class="col-md-12">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">Cotizaciones Vinculadas</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>ID COT (#)</th>
                            <th>Fecha Cot.</th>
                            <th>Vínculo (Artículo)</th>
                            <th class="text-right">Cant. Vinculada</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orden->cotizaciones as $cot)
                        <tr>
                            <td>Cot #{{ $cot->id_cotizacion }}</td>
                            <td>{{ \Carbon\Carbon::parse($cot->fecha_cot)->format('d/m/Y') }}</td>
                            <td>{{ $cot->pivot->articulo }}</td>
                            <td class="text-right font-weight-bold">{{ number_format($cot->pivot->cantidad, 2) }}</td>
                            <td>
                                <a href="{{ route('cotizaciones.show', $cot->id_cotizacion) }}" class="btn btn-xs btn-info">Ver Cot</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

@if($orden->remitos->count() > 0)
<div class="row mt-3">
    {{-- Remitos Emitidos --}}
    <div class="col-md-12">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">Remitos (Entregas Realizadas)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Nro Remito</th>
                            <th>Fecha</th>
                            <th>Items Incluidos</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orden->remitos as $remito)
                        <tr>
                            <td>{{ $remito->nro_remito }}</td>
                            <td>{{ \Carbon\Carbon::parse($remito->fecha)->format('d/m/Y') }}</td>
                            <td>
                                @foreach($remito->items as $ri)
                                    <span class="badge badge-light border">{{ $ri->cantidad }} x {{ Str::limit($ri->descripcion, 20) }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if($remito->estado == 'Emitido')
                                    <span class="badge badge-info">Emitido</span>
                                @elseif($remito->estado == 'Confirmado')
                                    <span class="badge badge-success">Confirmado</span>
                                @else
                                    <span class="badge badge-danger">{{ $remito->estado }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('remitos.show', $remito->id) }}" class="btn btn-xs btn-default">Ver</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

@stop
