@extends('adminlte::page')

@section('title', 'Órdenes de Pago')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Órdenes de Pago</h1>
        </div>
        <div class="col-sm-6 text-right">
            <a href="{{ route('ordenes-pago.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Orden de Pago
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Órdenes de Pago</h3>
            <div class="card-tools">
                <form action="{{ route('ordenes-pago.index') }}" method="GET" class="form-inline">
                    <select name="estado" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                        <option value="">Estados: Recibida/Parcial</option>
                        <option value="Recibida" {{ request('estado') == 'Recibida' ? 'selected' : '' }}>Recibida</option>
                        <option value="Parcial" {{ request('estado') == 'Parcial' ? 'selected' : '' }}>Parcial</option>
                        <option value="Pagada" {{ request('estado') == 'Pagada' ? 'selected' : '' }}>Pagada</option>
                        <option value="Anulada" {{ request('estado') == 'Anulada' ? 'selected' : '' }}>Anulada</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Archivo</th>
                        <th>Nro de OP</th>
                        <th>Motivo</th>
                        <th>Importe Pagado</th>
                        <th>Importe Saldado</th>
                        <th>Estado</th>
                        <th width="150">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ordenes as $op)
                        <tr>
                            <td>{{ $op->formatted_id }}</td>
                            <td>{{ $op->cliente->razon_social }}</td>
                            <td>{{ $op->fecha->format('d/m/Y') }}</td>
                            <td>
                                @if($op->archivo)
                                    <a href="{{ asset('storage/' . $op->archivo) }}" target="_blank" class="btn btn-xs btn-info">
                                        <i class="fas fa-file-pdf"></i> Ver
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $op->nro_op }}</td>
                            <td>
                                <span class="badge {{ $op->motivo == 'pedido' ? 'badge-primary' : 'badge-secondary' }}">
                                    {{ ucfirst($op->motivo) }}
                                </span>
                            </td>
                            <td>${{ number_format($op->importe_pagado, 2, ',', '.') }}</td>
                            <td>${{ number_format($op->importe_saldado, 2, ',', '.') }}</td>
                            <td>
                                @php
                                    $color = match($op->estado) {
                                        'Recibida' => 'info',
                                        'Parcial' => 'warning',
                                        'Pagada' => 'success',
                                        'Anulada' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge badge-{{ $color }}">{{ $op->estado }}</span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('ordenes-pago.show', $op) }}" class="btn btn-default btn-sm" title="Ver OP">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($op->estado == 'Recibida')
                                        <a href="{{ route('ordenes-pago.edit', $op) }}" class="btn btn-default btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-default btn-sm text-danger" title="Anular"
                                                onclick="confirmAnular('{{ route('ordenes-pago.anular', $op) }}')">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                    <form action="{{ route('ordenes-pago.destroy', $op) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-default btn-sm text-danger" title="Eliminar"
                                                onclick="return confirm('¿Está seguro de eliminar esta OP?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">No se encontraron órdenes de pago.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            <div class="float-right">
                {{ $ordenes->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <form id="form-anular" method="POST" style="display: none;">
        @csrf
    </form>
@stop

@section('js')
    <script>
        function confirmAnular(url) {
            Swal.fire({
                title: '¿Está seguro de anular esta Orden de Pago?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, anular',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('form-anular');
                    form.action = url;
                    form.submit();
                }
            })
        }
    </script>
@stop
