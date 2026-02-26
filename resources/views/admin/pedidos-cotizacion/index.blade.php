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

@stop
