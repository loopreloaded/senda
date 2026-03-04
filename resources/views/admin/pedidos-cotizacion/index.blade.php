@extends('adminlte::page')

@section('title', 'Listado de Pedidos de Cotización')

@section('content_header')
    <h2>Listado de Pedidos de Cotización</h2>
@stop

@section('content')

@can('crear pedidos cotizacion')
<a href="{{ route('pedidos-cotizacion.create') }}"
   class="btn btn-primary mb-3">
    Nuevo Pedido
</a>
@endcan

{{-- =========================
     FILTROS
========================= --}}
<form method="GET"
      action="{{ route('pedidos-cotizacion.index') }}"
      class="mb-3">

    <div class="row">

        {{-- Cliente --}}
        <div class="col-md-3">
            <label>Cliente</label>
            <input type="text"
                   name="cliente"
                   class="form-control"
                   value="{{ request('cliente') }}"
                   placeholder="Razón social">
        </div>

        {{-- Estado --}}
        <div class="col-md-2">
            <label>Estado</label>
            <select name="estado" class="form-control">
                <option value="">Todos</option>
                <option value="p" {{ request('estado') == 'p' ? 'selected' : '' }}>
                    Pendiente
                </option>
                <option value="c" {{ request('estado') == 'c' ? 'selected' : '' }}>
                    Cotizado
                </option>
            </select>
        </div>

        {{-- Fecha --}}
        <div class="col-md-2">
            <label>Fecha</label>
            <input type="date"
                   name="fecha"
                   class="form-control"
                   value="{{ request('fecha') }}">
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button type="submit"
                    class="btn btn-dark w-100">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <a href="{{ route('pedidos-cotizacion.index') }}"
               class="btn btn-secondary w-100">
                <i class="fas fa-broom"></i>
            </a>
        </div>

    </div>
</form>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif


{{-- =========================
     TABLA
========================= --}}
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Archivo</th>
            <th>Cliente</th>
            <th>Fecha</th>
            <th>Comentarios</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>

    <tbody>
        @foreach($pedidos as $pedido)
            <tr>
                <td>{{ $pedido->id_ped_cot }}</td>

                 {{-- Archivo --}}
                <td>
                    @if($pedido->archivo)
                        <a href="{{ asset('storage/' . $pedido->archivo) }}"
                           target="_blank"
                           class="btn btn-sm btn-light">
                            <i class="fas fa-file"></i> Ver
                        </a>
                    @else
                        <span class="text-muted">Sin archivo</span>
                    @endif
                </td>

                {{-- Cliente --}}
                <td>
                    {{ $pedido->cliente->razon_social ?? '-' }}
                </td>
                {{-- Fecha --}}
                <td>
                    {{ \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') }}
                </td>

               {{-- comentarios --}}
                <td>
                    {{ $pedido->comentarios ?? '-' }}
                </td>


                {{-- Estado --}}
                <td>
                    @php
                        $estadoTexto = match($pedido->estado_pc) {
                            'p' => 'Pendiente',
                            'c' => 'Cotizado',
                            default => 'Sin definir'
                        };

                        $color = match($pedido->estado_pc) {
                            'p' => 'warning',
                            'c' => 'success',
                            default => 'secondary'
                        };
                    @endphp

                    <span class="badge badge-{{ $color }}">
                        {{ $estadoTexto }}
                    </span>
                </td>

                <td>

                    <a href="{{ route('pedidos-cotizacion.edit', $pedido->id_ped_cot) }}"
                    class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i>
                    </a>

                    {{-- BOTÓN OBSERVACIÓN --}}
                    <button type="button"
                            class="btn btn-sm btn-secondary"
                            data-toggle="modal"
                            data-target="#modalComentarios"
                            data-id="{{ $pedido->id_ped_cot }}">
                        <i class="fas fa-comment"></i>
                    </button>

                    <form action="{{ route('pedidos-cotizacion.destroy', $pedido->id_ped_cot) }}"
                        method="POST"
                        style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('¿Eliminar este pedido?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>

                </td>

            </tr>
        @endforeach
    </tbody>
</table>

{{ $pedidos->appends(request()->query())->links() }}

{{-- =========================
     MODAL OBSERVACIÓN
========================= --}}
<div class="modal fade" id="modalComentarios" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST"
              action="{{ route('pedidos-cotizacion.comentarios.store') }}">
            @csrf
            <input type="hidden"
                   name="pedido_id"
                   id="id_pedido">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Agregar Comentarios
                    </h5>
                    <button type="button"
                            class="close"
                            data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Comentarios</label>
                        <textarea name="comentarios"
                                  class="form-control"
                                  rows="4"
                                  required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit"
                            class="btn btn-primary">
                        Guardar
                    </button>

                    <button type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal">
                        Cancelar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@section('js')

<script>
    $('#modalComentarios').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var pedidoId = button.data('id');
        $('#id_pedido').val(pedidoId);
    });
</script>

@endsection

@stop
