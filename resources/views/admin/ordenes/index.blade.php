@extends('adminlte::page')

@section('title', 'Listado Orden de compra')

@section('content_header')
    <h2>Listado Orden de compra</h2>
@stop

@section('content')
<a href="{{ route('ordenes.create') }}" class="btn btn-primary mb-3">Nueva Orden de compra</a>

{{-- FILTRO DE ÓRDENES --}}
<form method="GET" action="{{ route('ordenes.index') }}" class="mb-3">

    <div class="row">

        {{-- Razon social --}}
        <div class="col-md-3">
            <label>Razón social</label>
            <input type="text"
                name="razon_social"
                class="form-control"
                value="{{ request('razon_social') }}"
                placeholder="Buscar cliente...">
        </div>

        {{-- Motivo --}}
        <div class="col-md-2">
            <label>Motivo</label>
            <select name="motivo" class="form-control">
                <option value="">Todos</option>

                <option value="cotizacion"
                    {{ request('motivo')=='cotizacion'?'selected':'' }}>
                    Cotización
                </option>

                <option value="stock"
                    {{ request('motivo')=='stock'?'selected':'' }}>
                    Stock
                </option>
            </select>
        </div>

        {{-- Estado --}}
        <div class="col-md-2">
            <label>Estado</label>
            <select name="estado" class="form-control">
                <option value="">Todos</option>
                <option value="pendiente" {{ request('estado')=='pendiente'?'selected':'' }}>Pendiente</option>
                <option value="anulada" {{ request('estado')=='anulada'?'selected':'' }}>Anulada</option>
                <option value="parcial" {{ request('estado')=='parcial'?'selected':'' }}>Parcial</option>
                <option value="cumplida" {{ request('estado')=='cumplida'?'selected':'' }}>Cumplida</option>
            </select>
        </div>

        {{-- Buscar --}}
        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-dark w-100">
                <i class="fas fa-search"></i>
            </button>
        </div>

        {{-- Limpiar --}}
        <div class="col-md-1 d-flex align-items-end">
            <a href="{{ route('ordenes.index') }}"
            class="btn btn-secondary w-100"
            title="Limpiar filtros">
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
            <th>Archivo</th>
            <th>Cliente</th>
            <th>Fecha</th>
            <th>Motivo</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>

    <tbody>
        @foreach($ordenes as $orden)
        <tr>

            {{-- # --}}
            <td>{{ $orden->numero_oc }}</td>

            {{-- Archivo --}}
            <td>
                @if($orden->archivo)
                    <a href="{{ asset('storage/'.$orden->archivo) }}" target="_blank">
                        <i class="fas fa-paperclip"></i>
                    </a>
                @else
                    -
                @endif
            </td>

            {{-- Cliente --}}
            <td>{{ $orden->cliente->razon_social ?? '-' }}</td>

            {{-- Fecha --}}
            <td>{{ \Carbon\Carbon::parse($orden->fecha)->format('d/m/Y') }}</td>

            {{--  --}}
            <td>
                @if($orden->motivo == 'cotizacion')
                    <span class="badge badge-info">Cotización</span>
                @elseif($orden->motivo == 'stock')
                    <span class="badge badge-secondary">Stock</span>
                @else
                    -
                @endif
            </td>
            {{-- Estado --}}
            <td>
                @if($orden->estado == 'pendiente')
                    <span class="badge badge-warning">Pendiente</span>
                @elseif($orden->estado == 'parcial')
                    <span class="badge badge-info">Parcial</span>
                @elseif($orden->estado == 'cumplida')
                    <span class="badge badge-success">Cumplida</span>
                @elseif($orden->estado == 'anulada')
                    <span class="badge badge-danger">Anulada</span>
                @else
                    -
                @endif
            </td>

            {{-- Acciones --}}
            <td>

                @hasanyrole('admin|ingeniero')
                <a href="{{ route('ordenes.edit', $orden->id) }}"
                   class="btn btn-sm btn-primary"
                   title="Editar">
                    <i class="fas fa-edit"></i>
                </a>
                @endhasanyrole

                <a href="{{ route('ordenes.pdf', $orden->id) }}"
                   class="btn btn-sm btn-light"
                   title="PDF"
                   target="_blank">
                    <i class="fas fa-file-pdf" style="color:#d9534f;"></i>
                </a>

                @hasanyrole('admin|ingeniero')
                <form action="{{ route('ordenes.destroy', $orden->id) }}"
                      method="POST"
                      style="display:inline">
                    @csrf
                    @method('DELETE')

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

