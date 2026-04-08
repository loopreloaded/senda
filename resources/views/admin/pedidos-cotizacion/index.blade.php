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
            <th>Cliente</th>
            <th>Fecha Ped.</th>
            <th>Archivo Ped.</th>
            <th>N° Ped.</th>
            <th>Cant. Art. Ped.</th>
            <th>Cant. Art. Cot.</th>
            <th>Art. Cot.</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>

    <tbody>
        @foreach($pedidos as $pedido)
            <tr>
                <td>{{ $pedido->id_ped_cot }}</td>

                {{-- Cliente --}}
                <td>
                    {{ $pedido->cliente->razon_social ?? '-' }}
                </td>

                {{-- Fecha --}}
                <td>
                    {{ \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') }}
                </td>

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


                {{-- N° Ped --}}
                <td>
                    {{ $pedido->nro_solicitud ?? '-' }}
                </td>

                {{-- Cant Art Ped --}}
                <td class="text-center">
                    {{ $pedido->cantidad }}
                </td>

                {{-- Cant Art Cot --}}
                <td class="text-center text-primary">
                    @php
                        $cantCot = 0;
                        foreach($pedido->cotizaciones as $cot) {
                            $cantCot += $cot->items->sum('cantidad');
                        }
                    @endphp
                    {{ $cantCot }}
                </td>

                {{-- Art Cot --}}
                <td>
                    @foreach($pedido->cotizaciones as $cot)
                        @foreach($cot->items as $item)
                            <small class="badge badge-light border">{{ $item->producto }} ({{ $item->cantidad }})</small>
                        @endforeach
                    @endforeach
                </td>

                {{-- Estado --}}
                <td>
                   @php
                        $estadoTexto = match($pedido->estado_pc) {
                            'p' => 'Pendiente',
                            's' => 'Parcial',
                            'c' => 'Cotizado',
                            'n' => 'No cotizó',
                            default => 'Sin definir'
                        };

                        $color = match($pedido->estado_pc) {
                            'p' => 'secondary',
                            's' => 'warning',
                            'c' => 'success',
                            'n' => 'danger',
                            default => 'dark'
                        };
                    @endphp

                    <span class="badge badge-{{ $color }}">
                        {{ $estadoTexto }}
                    </span>
                </td>

                <td>

                    {{-- EDITAR --}}
                    @if($pedido->estado_pc == 'p')
                        <a href="{{ route('pedidos-cotizacion.edit', $pedido->id_ped_cot) }}"
                        class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                    @else
                        <button class="btn btn-sm btn-warning" disabled title="Pedido ya cotizado">
                            <i class="fas fa-edit"></i>
                        </button>
                    @endif


                    {{-- BOTÓN NO COTIZÓ --}}
                    @if($pedido->estado_pc == 'p')
                        <form action="{{ route('pedidos-cotizacion.no-cotizo', $pedido->id_ped_cot) }}"
                            method="POST"
                            style="display:inline">

                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="btn btn-sm btn-outline-danger"
                                    title="Marcar como No Cotizó"
                                    onclick="return confirm('¿Marcar este pedido como NO COTIZÓ?')">

                                <i class="fas fa-ban"></i>

                            </button>

                        </form>
                    @else
                        <button class="btn btn-sm btn-outline-danger"
                                disabled
                                title="El pedido ya no está pendiente">

                            <i class="fas fa-ban"></i>

                        </button>
                    @endif


                    {{-- ELIMINAR --}}
                    @if($pedido->estado_pc == 'p')
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
                    @else
                        <button class="btn btn-sm btn-danger" disabled title="Pedido ya cotizado">
                            <i class="fas fa-trash"></i>
                        </button>
                    @endif

                </td>

            </tr>
        @endforeach
    </tbody>
</table>

{{ $pedidos->appends(request()->query())->links() }}


@stop
