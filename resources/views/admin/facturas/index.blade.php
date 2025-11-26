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

    {{-- Tabla de facturas --}}
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Punto Venta</th>
                        <th>Fecha</th>
                        <th>Fecha creacion</th>
                        <th>Importe Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facturas as $factura)
                        <tr>
                            <td>{{ $factura->id }}</td>
                            <td>{{ $factura->cliente->razon_social ?? '—' }}</td>
                            <td>{{ $factura->tipo_comprobante ?? '—' }}</td>
                            <td>{{ $factura->punto_venta ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($factura->created_at)->format('d/m/Y') }}</td>
                            <td>${{ number_format($factura->importe_total, 2, ',', '.') }}</td>
                            <td>
                                @if($factura->estado == 'pendiente')
                                    <span class="badge badge-warning">Pendiente</span>
                                @elseif($factura->estado == 'aprobada')
                                    <span class="badge badge-success">Aprobada</span>
                                @elseif($factura->estado == 'enviada_afip')
                                    <span class="badge badge-info">Enviada AFIP</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($factura->estado) }}</span>
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
                                {{-- BOTÓN PDF (activo o deshabilitado según estado) --}}
                                @if($factura->estado === 'aprobada')
                                    {{-- Activo --}}
                                    <a href="{{ route('facturas.pdf', $factura->id) }}"
                                    class="btn btn-sm btn-danger" target="_blank" title="Descargar PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                @else
                                    {{-- Deshabilitado gris --}}
                                    <button class="btn btn-sm btn-secondary"
                                            style="pointer-events: none; opacity: 0.5;"
                                            title="Disponible solo cuando la factura esté aprobada">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                @endif
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
