@extends('adminlte::page')

@section('title', 'Detalle de Recibo')

@section('content_header')
    <h1>Detalle de Recibo {{ $recibo->nro_recibo }}</h1>
@stop

@section('content')

    <div class="row">
        <div class="col-md-4">
            {{-- Info General --}}
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <h3 class="profile-username text-center">{{ $recibo->nro_recibo }}</h3>
                    <p class="text-muted text-center">{{ $recibo->fecha ? $recibo->fecha->format('d/m/Y') : 'N/A' }}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Estado</b> 
                            <span class="float-right badge {{ $recibo->estado === 'Cerrada' ? 'badge-success' : 'badge-primary' }}">
                                {{ $recibo->estado }}
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Motivo</b> <span class="float-right text-capitalize">{{ $recibo->motivo }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Creado por</b> <span class="float-right">{{ $recibo->creador->name ?? 'Sistema' }}</span>
                        </li>
                    </ul>

                    <a href="{{ route('recibos.pdf', $recibo) }}" class="btn btn-secondary btn-block" target="_blank">
                        <i class="fas fa-file-pdf"></i> Ver PDF
                    </a>
                </div>
            </div>

            {{-- Info Cliente --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Datos del Cliente</h3>
                </div>
                <div class="card-body">
                    <strong><i class="fas fa-user mr-1"></i> Razón Social</strong>
                    <p class="text-muted">{{ $recibo->cliente->razon_social }}</p>
                    <hr>
                    <strong><i class="fas fa-id-card mr-1"></i> CUIT</strong>
                    <p class="text-muted">{{ $recibo->cliente->cuit }}</p>
                    <hr>
                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Dirección</strong>
                    <p class="text-muted">{{ $recibo->cliente->direccion }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#detalle" data-toggle="tab">Detalle Financiero</a></li>
                        @if($recibo->motivo === 'pedido')
                            <li class="nav-item"><a class="nav-link" href="#ops" data-toggle="tab">OPs Vinculadas</a></li>
                        @endif
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        {{-- Tab Detalle Financiero --}}
                        <div class="active tab-pane" id="detalle">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h6>Importes</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Importe Saldado:</th>
                                            <td class="text-right">$ {{ number_format($recibo->importe_saldado, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Total Retenciones:</th>
                                            <td class="text-right text-danger">$ {{ number_format($recibo->total_retenciones, 2) }}</td>
                                        </tr>
                                        <tr class="h5">
                                            <th>TOTAL GENERAL:</th>
                                            <td class="text-right"><strong>$ {{ number_format($recibo->importe_total, 2) }}</strong></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-sm-6">
                                    <h6>Desglose de Retenciones</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <th>IVA:</th>
                                            <td class="text-right">$ {{ number_format($recibo->iva, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Ganancia:</th>
                                            <td class="text-right">$ {{ number_format($recibo->ganancia, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>IIBB:</th>
                                            <td class="text-right">$ {{ number_format($recibo->iibb, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Percepción I.B.:</th>
                                            <td class="text-right">$ {{ number_format($recibo->percepcion_ib, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <hr>
                            <strong><i class="fas fa-info-circle mr-1"></i> Detalles de Pago</strong>
                            <p class="text-muted">{{ $recibo->detalles_pago ?? 'Sin detalles adicionales.' }}</p>
                        </div>

                        {{-- Tab OPs Vinculadas --}}
                        @if($recibo->motivo === 'pedido')
                            <div class="tab-pane" id="ops">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID OP</th>
                                            <th>Nro OP</th>
                                            <th>Fecha</th>
                                            <th>Monto en OP</th>
                                            <th>Saldado aquí</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recibo->ordenesPago as $op)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('ordenes-pago.show', $op) }}">
                                                        OP-{{ str_pad($op->id, 4, '0', STR_PAD_LEFT) }}
                                                    </a>
                                                </td>
                                                <td>{{ $op->nro_op }}</td>
                                                <td>{{ $op->fecha ? $op->fecha->format('d/m/Y') : 'N/A' }}</td>
                                                <td class="text-right">$ {{ number_format($op->importe_pagado, 2) }}</td>
                                                <td class="text-right">$ {{ number_format($op->pivot->saldado, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="text-right">
                <a href="{{ route('recibos.index') }}" class="btn btn-secondary">Volver al listado</a>
            </div>
        </div>
    </div>

@stop
