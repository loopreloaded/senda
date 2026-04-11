@extends('adminlte::page')

@section('title', 'Detalle de Remito')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Remito #{{ $remito->numero_remito }}</h1>
        <div>
            <a href="{{ route('remitos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="{{ route('remitos.pdf', $remito) }}" class="btn btn-info" target="_blank">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            @if(in_array($remito->estado, ['Emitido', 'Parcial']))
                <a href="{{ route('remitos.edit', $remito) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar
                </a>
            @endif
        </div>
    </div>
@stop

@section('content')
<div class="row">
    {{-- INFORMACIÓN GENERAL --}}
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title text-bold">Información General</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="150">Cliente:</th>
                        <td>{{ $remito->cliente->razon_social ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Fecha Emisión:</th>
                        <td>{{ $remito->fecha ? $remito->fecha->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Motivo:</th>
                        <td>
                            <span class="badge {{ $remito->motivo == 'pedido' ? 'badge-info' : 'badge-secondary' }}">
                                {{ ucfirst($remito->motivo == 'pedido' ? 'Vinculado' : 'Particular') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Estado:</th>
                        <td>
                             @if($remito->estado === 'Emitido')
                                <span class="badge badge-warning">Emitido</span>
                            @elseif($remito->estado === 'Facturado')
                                <span class="badge badge-success">Facturado</span>
                            @elseif($remito->estado === 'Parcial')
                                <span class="badge badge-primary">Parcial</span>
                            @elseif($remito->estado === 'Anulado')
                                <span class="badge badge-danger">Anulado</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Condición Venta:</th>
                        <td>{{ $remito->condicion_venta ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- FLETE --}}
    <div class="col-md-6">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title text-bold">Flete</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="150">Transportista:</th>
                        <td>{{ $remito->transportista ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Domicilio:</th>
                        <td>{{ $remito->domicilio_transportista ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>CUIT:</th>
                        <td>{{ $remito->cuit_transportista ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>IVA:</th>
                        <td>{{ $remito->iva_transportista ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- ITEMS --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h3 class="card-title text-bold">Detalle de Artículos</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover m-0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Artículo</th>
                            <th>Descripción</th>
                            <th class="text-right">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($remito->items as $item)
                            <tr>
                                <td>{{ $item->codigo ?? '-' }}</td>
                                <td>
                                    {{ $item->articulo }}
                                    @if($item->id_orden_item)
                                        <br><small class="text-info">Vinculado a OC Ítem #{{ $item->id_orden_item }}</small>
                                    @endif
                                </td>
                                <td>{{ $item->descripcion ?? '-' }}</td>
                                <td class="text-right text-bold">{{ $item->cantidad }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-light">
                            <th colspan="3" class="text-right">TOTAL ÍTEMS:</th>
                            <th class="text-right">{{ $remito->items->sum('cantidad') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    @if($remito->observacion)
        <div class="col-12">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Observaciones</h3>
                </div>
                <div class="card-body">
                    {{ $remito->observacion }}
                </div>
            </div>
        </div>
    @endif
</div>
@stop
