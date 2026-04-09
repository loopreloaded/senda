@extends('adminlte::page')

@section('title', 'Listado de Cotizaciones')

@section('content_header')
    <h2>Listado de Cotizaciones</h2>
@stop

@section('content')

@can('crear cotizaciones')
<a href="{{ route('cotizaciones.create') }}" class="btn btn-primary mb-3">
    Nueva Cotización
</a>
@endcan

{{-- FILTRO DE COTIZACIONES --}}
<form method="GET" action="{{ route('cotizaciones.index') }}" class="mb-3">
    <div class="row">

        {{-- Cliente --}}
        <div class="col-md-3">
            <label>Cliente</label>
            <input type="text"
                   name="cliente"
                   class="form-control"
                   value="{{ request('cliente') }}"
                   placeholder="Razón social...">
        </div>

        {{-- Motivo --}}
        <div class="col-md-2">
            <label>Motivo</label>
            <select name="motivo" class="form-control">
                <option value="">Todos</option>
                <option value="PEDIDO" {{ request('motivo') == 'PEDIDO' ? 'selected' : '' }}>
                    Pedido
                </option>
                <option value="PARTICULAR" {{ request('motivo') == 'PARTICULAR' ? 'selected' : '' }}>
                    Particular
                </option>
            </select>
        </div>

        {{-- Estado --}}
        <div class="col-md-2">
            <label>Estado</label>
            <select name="estado" class="form-control">
                <option value="">Todos</option>
                <option value="VIGENTE" {{ request('estado') == 'VIGENTE' ? 'selected' : '' }}>
                    Vigente
                </option>
                <option value="VENCIDA" {{ request('estado') == 'VENCIDA' ? 'selected' : '' }}>
                    Vencida
                </option>
            </select>
        </div>

        {{-- Botones --}}
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-dark mr-2 w-50">
                <i class="fas fa-search"></i>
            </button>

            <a href="{{ route('cotizaciones.index') }}"
               class="btn btn-secondary w-50"
               title="Limpiar filtros">
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
            <th>Cliente</th>
            <th>Fecha</th>
            <th>Nro. Cot.</th>
            <th>Motivo</th>
            <th>Cant. Art. Cot.</th>
            <th>Cant. Art. OC</th>
            <th>Art. OC</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>

    <tbody>
        @foreach($cotizaciones as $cotizacion)
            <tr>
                {{-- Nº Cotización --}}
                <td>{{ $cotizacion->id_cotizacion }}</td>

                {{-- Cliente --}}
                <td>{{ $cotizacion->cliente->razon_social ?? '—' }}</td>

                {{-- Fecha --}}
                <td>{{ optional($cotizacion->fecha_cot)->format('d/m/Y') }}</td>

                {{-- Nro. Cot --}}
                <td>{{ $cotizacion->nro_cotizacion ?? '—' }}</td>

                {{-- Motivo --}}
                <td>
                    @if($cotizacion->motivo == 'pedido')
                        <span class="badge badge-info">Pedido</span>
                    @elseif($cotizacion->motivo == 'particular')
                        <span class="badge badge-secondary">Particular</span>
                    @else
                        —
                    @endif
                </td>

                {{-- Cant. Art. Cot --}}
                <td>{{ $cotizacion->cant_art_cot }}</td>

                {{-- Cant. Art. OC --}}
                <td>
                    {{ $cotizacion->ordenesCompra->sum(function($oc) { return $oc->items->sum('cantidad'); }) }}
                </td>

                {{-- Art. OC --}}
                <td>
                    <small>
                    {{ $cotizacion->ordenesCompra->flatMap->items->pluck('descripcion')->unique()->implode(', ') ?: '—' }}
                    </small>
                </td>

                {{-- Estado --}}
                <td>
                    @if($cotizacion->vigencia_oferta && now()->gt($cotizacion->vigencia_oferta) && $cotizacion->estado_cotizacion != 'a')
                        <span class="badge badge-danger">Vencida</span>
                    @else
                        @switch($cotizacion->estado_cotizacion)
                            @case('v')
                                <span class="badge badge-success">Vigente</span>
                                @break

                            @case('r')
                                <span class="badge badge-dark">Rechazada</span>
                                @break

                            @case('p')
                                <span class="badge badge-warning">Parcial</span>
                                @break

                            @case('a')
                                <span class="badge badge-primary">Aceptada</span>
                                @break

                            @default
                                <span class="badge badge-light">Vigente</span>
                        @endswitch
                    @endif
                </td>


                {{-- Acciones --}}
                <td>

                    {{-- Ver PDF --}}
                    <a href="{{ route('cotizaciones.pdf', $cotizacion->id_cotizacion) }}"
                    class="btn btn-sm btn-light"
                    target="_blank"
                    title="Ver PDF">
                        <i class="fas fa-file-pdf text-danger"></i>
                    </a>

                    {{-- Editar --}}
                    <a href="{{ route('cotizaciones.edit', $cotizacion->id_cotizacion) }}"
                    class="btn btn-sm btn-warning"
                    title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>

                    {{-- Rechazada --}}
                    <form action="{{ route('cotizaciones.rechazar', $cotizacion->id_cotizacion) }}"
                        method="POST"
                        style="display:inline">
                        @csrf
                        <button class="btn btn-sm btn-secondary"
                                title="Rechazar"
                                onclick="return confirm('¿Marcar como rechazada esta cotización?')">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>

                    {{-- Eliminar --}}
                    <form action="{{ route('cotizaciones.destroy', $cotizacion->id_cotizacion) }}"
                        method="POST"
                        style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('¿Eliminar esta cotización?')"
                                title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>

                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $cotizaciones->links() }}


@stop
