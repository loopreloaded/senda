@extends('adminlte::page')

@section('title', 'Listado de Facturas')

@section('content_header')
    <h1>Listado de Facturas</h1>
@stop

@section('content')

    {{-- Mensajes de éxito o error --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Botón para crear nueva factura --}}
    <div class="mb-3">
        <a href="{{ route('facturas.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Factura
        </a>
    </div>

    {{-- FILTRO DE BÚSQUEDA --}}
    <form method="GET" action="{{ route('facturas.index') }}" class="mb-3">

        <div class="row">

            {{-- Cliente --}}
            <div class="col-md-3">
                <label>Cliente</label>
                <input type="text" name="cliente" value="{{ request('cliente') }}"
                    class="form-control" placeholder="Razón Social / Nombre">
            </div>

            {{-- Tipo --}}
            <div class="col-md-2">
                <label>Tipo</label>
                <select name="tipo" class="form-control">
                    <option value="">Todos</option>
                    <option value="A" {{ request('tipo')=='A' ? 'selected' : '' }}>Factura A</option>
                    <option value="B" {{ request('tipo')=='B' ? 'selected' : '' }}>Factura B</option>
                </select>
            </div>

            {{-- Fecha desde --}}
            <div class="col-md-2">
                <label>Desde</label>
                <input type="date" name="desde" value="{{ request('desde') }}" class="form-control">
            </div>

            {{-- Fecha hasta --}}
            <div class="col-md-2">
                <label>Hasta</label>
                <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control">
            </div>

            {{-- Estado --}}
            <div class="col-md-2">
                <label>Estado</label>
                <select name="estado" class="form-control">
                    <option value="">Todos</option>
                    <option value="borrador" {{ request('estado')=='borrador'?'selected':'' }}>Borrador</option>
                    <option value="emitida" {{ request('estado')=='emitida'?'selected':'' }}>Emitida</option>
                    <option value="parcial" {{ request('estado')=='parcial'?'selected':'' }}>Parcial</option>
                    <option value="pagada" {{ request('estado')=='pagada'?'selected':'' }}>Pagada</option>
                    <option value="rechazada por arca" {{ request('estado')=='rechazada por arca'?'selected':'' }}>Rechazada por ARCA</option>
                </select>
            </div>

            {{-- BOTONES --}}
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-dark w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            {{-- LIMPIAR FILTROS --}}
            <div class="col-md-1 d-flex align-items-end">
                <a href="{{ route('facturas.index') }}" class="btn btn-secondary w-100" title="Quitar filtros">
                    <i class="fas fa-broom"></i>
                </a>
            </div>

        </div>

    </form>



    {{-- Tabla de facturas --}}
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID FAC (#)</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Nro. Fac.</th>
                        <th>Motivo</th>
                        <th>Tipo Fac.</th>
                        <th>Moneda</th>
                        <th>Importe Fac.</th>
                        <th>Importe Pagado</th>
                        <th>Estados</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facturas as $factura)
                        <tr>
                            <td><span class="badge badge-dark">FAC-{{ $factura->id }}</span></td>
                            <td>
                                {{ mb_convert_case($factura->cliente->razon_social ?? '—', MB_CASE_TITLE, 'UTF-8') }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y') }}</td>
                            <td>{{ str_pad($factura->punto_venta, 4, '0', STR_PAD_LEFT) }}-{{ $factura->numero_comprobante_afip ?? '—' }}</td>
                            <td>
                                @if($factura->motivo == 'pedido')
                                    <span class="badge badge-info">Vinculado</span>
                                @else
                                    <span class="badge badge-secondary">Particular</span>
                                @endif
                            </td>
                            <td>{{ $factura->tipo_comprobante ?? '—' }}</td>
                            <td>{{ $factura->moneda ?? '—' }}</td>
                            <td>${{ number_format($factura->importe_total, 2, ',', '.') }}</td>
                            <td>${{ number_format($factura->importe_pagado, 2, ',', '.') }}</td>
                            <td>
                                @if($factura->estado == 'borrador')
                                    <span class="badge badge-secondary">Borrador</span>
                                @elseif($factura->estado == 'emitida')
                                    <span class="badge badge-success">Emitida</span>
                                @elseif($factura->estado == 'parcial')
                                    <span class="badge badge-warning">Parcial</span>
                                @elseif($factura->estado == 'pagada')
                                    <span class="badge badge-primary">Pagada</span>
                                @elseif($factura->estado == 'rechazada por arca')
                                    <span class="badge badge-danger">Rechazada por ARCA</span>
                                @else
                                    <span class="badge badge-light">{{ ucfirst($factura->estado) }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('facturas.show', $factura->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @role('admin|ingeniero')
                                    <button class="btn btn-sm btn-warning btn-observacion"
                                            data-id="{{ $factura->id }}"
                                            data-observacion="{{ $factura->observaciones ?? '' }}">
                                        <i class="fas fa-comment-dots"></i>
                                    </button>
                                @endrole

                                <a href="{{ route('facturas.pdf', $factura->id) }}"
                                    class="btn btn-sm btn-danger" target="_blank" title="Descargar PDF">
                                        <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No hay facturas registradas aún.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Paginación --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $facturas->links() }}
            </div>
        </div>
    </div>

    <!-- MODAL PARA CARGAR OBSERVACIÓN -->
    <div class="modal fade" id="modalObservacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

        <div class="modal-header bg-warning">
            <h5 class="modal-title"><i class="fas fa-comment"></i> Agregar/Editar Observación</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formObservacion" method="POST" action="">
            @csrf
            @method('PUT')

            <div class="modal-body">
            <label><strong>Observación:</strong></label>
            <textarea name="observaciones" class="form-control" rows="4"></textarea>
            </div>

            <div class="modal-footer">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Guardar
            </button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                Cancelar
            </button>

            </div>

        </form>

        </div>
    </div>
</div>

@stop

@section('css')
    {{-- Estilos personalizados opcionales --}}
@stop

@section('js')
    <script>
    document.querySelectorAll(".btn-observacion").forEach(boton => {
        boton.addEventListener("click", function() {

            let id = this.dataset.id;
            let observacion = this.dataset.observacion;

            // Ruta automática al update
            document.getElementById("formObservacion").action = `/facturas/${id}/observacion`;
            document.querySelector("#formObservacion textarea").value = observacion || "";

            new bootstrap.Modal(document.getElementById('modalObservacion')).show();
        });
    });
    </script>

@stop
