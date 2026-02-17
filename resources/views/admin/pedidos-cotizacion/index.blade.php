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

{{-- FILTRO --}}
<form method="GET"
      action="{{ route('pedidos-cotizacion.index') }}"
      class="mb-3">

    <div class="row">

        <div class="col-md-3">
            <label>N° Cotización</label>
            <input type="text"
                   name="cotizacion"
                   class="form-control"
                   value="{{ request('cotizacion') }}"
                   placeholder="Ej: 15">
        </div>

        <div class="col-md-3">
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

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Archivo</th>
            <th>Fecha</th>
            <th>Observaciones</th>
            <th>Acciones</th>
        </tr>
    </thead>

    <tbody>
        @foreach($pedidos as $pedido)
            <tr>
                <td>{{ $pedido->id_ped_cot }}</td>

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


                <td>
                    {{ optional($pedido->created_at)->format('d/m/Y H:i') }}
                </td>
                <td>{{ $pedido->observaciones }}</td>

                <td>

                    {{-- EDITAR --}}
                    @can('editar pedidos cotizacion')
                    <a href="{{ route('pedidos-cotizacion.edit', $pedido->id_ped_cot) }}"
                       class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i>
                    </a>
                    @endcan

                    {{-- ELIMINAR --}}
                    @can('eliminar pedidos cotizacion')
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
                    @endcan

                </td>

            </tr>
        @endforeach
    </tbody>
</table>

{{ $pedidos->links() }}

@stop
