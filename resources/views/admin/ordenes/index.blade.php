@extends('adminlte::page')

@section('title', 'Listado de Ordenes')

@section('content_header')
    <h2>Listado de Ordenes</h2>
@stop

@section('content')
<a href="{{ route('ordenes.create') }}" class="btn btn-primary mb-3">Nueva Orden de compra</a>

{{-- FILTRO DE ÓRDENES --}}
<form method="GET" action="{{ route('ordenes.index') }}" class="mb-3">

    <div class="row">

        {{-- Buscar por número de OC --}}
        <div class="col-md-3">
            <label>Número OC</label>
            <input type="text" name="numero" class="form-control"
                   value="{{ request('numero') }}" placeholder="Ej: OC-00125">
        </div>

        {{-- Buscar por proveedor --}}
        <div class="col-md-4">
            <label>Proveedor</label>
            <input type="text" name="proveedor" class="form-control"
                   value="{{ request('proveedor') }}" placeholder="Nombre del proveedor">
        </div>

        {{-- Fecha --}}
        <div class="col-md-3">
            <label>Fecha</label>
            <input type="date" name="fecha" class="form-control"
                   value="{{ request('fecha') }}">
        </div>

        {{-- Botones --}}
        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-dark w-100">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <div class="col-md-1 d-flex align-items-end">
            <a href="{{ route('ordenes.index') }}"
               class="btn btn-secondary w-100" title="Limpiar filtros">
                <i class="fas fa-broom"></i>
            </a>
        </div>

    </div>

</form>


@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Número OC</th>
            <th>Proveedor</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ordenes as $orden)
            <tr>
                <td>{{ $orden->id }}</td>
                <td>{{ $orden->numero_oc }}</td>
                <td>{{ $orden->proveedor }}</td>
                <td>{{ $orden->fecha }}</td>
                <td>${{ number_format($orden->total, 2) }}</td>
                <td>

                {{-- BOTÓN OBSERVACIONES (solo admin o ingeniero) --}}
                @hasanyrole('admin|ingeniero')
                <button class="btn btn-sm btn-warning btn-observacion"
                        data-id="{{ $orden->id }}"
                        data-observaciones="{{ $orden->observaciones ?? '' }}">
                    <i class="fas fa-comment-dots"></i>
                </button>
                @endhasanyrole

                {{-- PDF --}}
                <a href="{{ route('ordenes.pdf', $orden->id) }}"
                    class="btn btn-sm btn-light"
                    title="Imprimir PDF"
                    target="_blank">
                    <i class="fas fa-file-pdf" style="color:#d9534f;"></i>
                </a>

                {{-- ELIMINAR --}}
                @hasanyrole('admin|ingeniero')
                <form action="{{ route('ordenes.destroy', $orden->id) }}"
                    method="POST"
                    style="display:inline">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="btn btn-sm btn-danger"
                            onclick="return confirm('¿Eliminar esta orden?')">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
                @endhasanyrole

            </td>


            </tr>
        @endforeach
    </tbody>
</table>

{{ $ordenes->links() }}

<!-- MODAL OBSERVACIONES -->
<div class="modal fade" id="modalObservaciones" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('ordenes.observaciones.update') }}">
            @csrf
            <input type="hidden" name="id" id="obs_id">

            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Agregar Observaciones</h5>

                    {{-- ✨ Bootstrap 4: botón de cerrar --}}
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <textarea name="observaciones" id="obs_textarea" class="form-control"
                              rows="5" placeholder="Escriba las observaciones aquí..."></textarea>
                </div>

                <div class="modal-footer">
                    {{-- ✨ Bootstrap 4: cerrar modal --}}
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-success">
                        Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


@stop


@section('js')
<script>
    // Usamos jQuery porque AdminLTE 3 + Bootstrap 4 lo trae por defecto
    $(document).on('click', '.btn-observacion', function () {
        var btn = $(this);

        $('#obs_id').val(btn.data('id'));
        $('#obs_textarea').val(btn.data('observaciones') || '');

        $('#modalObservaciones').modal('show');
    });
</script>
@endsection

