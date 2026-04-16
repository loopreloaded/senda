@extends('adminlte::page')

@section('title', 'Detalle Orden de Pago')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Detalle de Orden de Pago: {{ $ordenPago->formatted_id }}</h1>
        </div>
        <div class="col-sm-6 text-right">
            <a href="{{ route('ordenes-pago.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al listado
            </a>
            @if($ordenPago->estado == 'Recibida')
                <a href="{{ route('ordenes-pago.edit', $ordenPago) }}" class="btn btn-warning text-white">
                    <i class="fas fa-edit"></i> Editar
                </a>
            @endif
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-outline card-primary h-100">
                <div class="card-header">
                    <h3 class="card-title">Información General</h3>
                </div>
                <div class="card-body">
                    <strong><i class="fas fa-user mr-1 text-primary"></i> Cliente</strong>
                    <p class="text-muted">{{ $ordenPago->cliente->razon_social }}</p>
                    <hr>
                    <strong><i class="fas fa-calendar-alt mr-1 text-primary"></i> Fecha</strong>
                    <p class="text-muted">{{ $ordenPago->fecha->format('d/m/Y') }}</p>
                    <hr>
                    <strong><i class="fas fa-hashtag mr-1 text-primary"></i> Nro de OP (Cliente)</strong>
                    <p class="text-muted">{{ $ordenPago->nro_op }}</p>
                    <hr>
                    <strong><i class="fas fa-tag mr-1 text-primary"></i> Motivo</strong>
                    <p>
                        <span class="badge {{ $ordenPago->motivo == 'pedido' ? 'badge-primary' : 'badge-secondary' }}">
                            {{ ucfirst($ordenPago->motivo) }}
                        </span>
                    </p>
                    <hr>
                    <strong><i class="fas fa-info-circle mr-1 text-primary"></i> Estado</strong>
                    <p>
                        @php
                            $color = match($ordenPago->estado) {
                                'Recibida' => 'info',
                                'Parcial' => 'warning',
                                'Pagada' => 'success',
                                'Anulada' => 'danger',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge badge-{{ $color }} shadow-sm px-3">{{ $ordenPago->estado }}</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-outline card-success shadow-none border">
                <div class="card-body p-0">
                    <div class="row text-center py-3">
                        <div class="col-sm-6 border-right">
                            <div class="px-3">
                                <h5 class="text-primary text-bold">${{ number_format($ordenPago->importe_pagado, 2, ',', '.') }}</h5>
                                <span class="text-uppercase text-xs text-muted">Importe Pagado Total</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="px-3">
                                <h5 class="text-success text-bold">${{ number_format($ordenPago->importe_saldado, 2, ',', '.') }}</h5>
                                <span class="text-uppercase text-xs text-muted">Importe Saldado (Recibos)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($ordenPago->motivo == 'pedido')
            <div class="card">
                <div class="card-header bg-light">
                    <h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-1"></i> Facturas Vinculadas</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Comprobante</th>
                                <th class="text-right">Total Factura</th>
                                <th class="text-right">Pagado en esta OP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ordenPago->facturas as $f)
                                <tr>
                                    <td>
                                        <a href="{{ route('facturas.show', $f->id) }}" class="text-bold">
                                            {{ $f->tipo_comprobante }} {{ str_pad($f->punto_venta, 4, '0', STR_PAD_LEFT) }}-{{ str_pad($f->numero_comprobante_afip, 8, '0', STR_PAD_LEFT) }}
                                        </a>
                                    </td>
                                    <td class="text-right text-muted">${{ number_format($f->importe_total, 2, ',', '.') }}</td>
                                    <td class="text-right text-bold text-success">${{ number_format($f->pivot->pagado, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No hay facturas vinculadas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    @if($ordenPago->archivo)
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h3 class="card-title"><i class="fas fa-paperclip mr-1"></i> Documento Adjunto</h3>
                        </div>
                        <div class="card-body text-center d-flex flex-column justify-content-center">
                            <div class="mb-3">
                                <i class="fas fa-file-pdf fa-4x text-danger opacity-75"></i>
                            </div>
                            <a href="{{ asset('storage/' . $ordenPago->archivo) }}" target="_blank" class="btn btn-outline-primary shadow-sm">
                                <i class="fas fa-external-link-alt mr-1"></i> Ver Documento Original
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="col-md-6">
                    @if($ordenPago->observaciones)
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h3 class="card-title"><i class="fas fa-comment shadow-none mr-1"></i> Observaciones</h3>
                        </div>
                        <div class="card-body bg-light text-muted italic">
                            {{ $ordenPago->observaciones }}
                        </div>
                    </div>
                    @else
                    <div class="card h-100 border-dashed">
                        <div class="card-body text-center d-flex align-items-center justify-content-center text-muted">
                            <div><i class="fas fa-comment-slash fa-2x mb-2 opacity-25"></i><br>Sin observaciones</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if($ordenPago->recibos->count() > 0)
            <div class="card mt-3">
                <div class="card-header bg-warning disabled">
                    <h3 class="card-title text-white"><i class="fas fa-receipt mr-1"></i> Recibos Aplicados</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Recibo</th>
                                <th>Fecha</th>
                                <th class="text-right">Monto Aplicado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ordenPago->recibos as $r)
                                <tr>
                                    <td>
                                        <a href="{{ route('recibos.show', $r->id_recibo) }}" class="text-bold text-warning">
                                            #{{ $r->nro_recibo }}
                                        </a>
                                    </td>
                                    <td>{{ $r->fecha ? $r->fecha->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="text-right text-bold text-orange">${{ number_format($r->pivot->saldado, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
@stop
