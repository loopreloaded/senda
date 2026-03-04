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

        {{-- NO Cotización --}}
        <div class="col-md-3">
            <label>NO Cotización</label>
            <input type="text"
                   name="id"
                   class="form-control"
                   value="{{ request('id') }}"
                   placeholder="Ej: 15">
        </div>

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
            <th>NO Cotización</th>
            <th>Cliente</th>
            <th>Fecha</th>
            <th>Motivo</th>
            <th>Moneda</th>
            <th>Total</th>
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

                {{-- Motivo --}}
                <td>
                    @if($cotizacion->motivo == 'pedido')
                        <span class="badge badge-primary">Pedido</span>
                    @elseif($cotizacion->motivo == 'particular')
                        <span class="badge badge-secondary">Particular</span>
                    @else
                        —
                    @endif
                </td>

                {{-- Moneda --}}
                <td>{{ $cotizacion->moneda }}</td>

                {{-- Total --}}
                <td>
                    ${{ number_format($cotizacion->importe_total, 2, ',', '.') }}
                </td>

                {{-- Estado --}}
                <td>
                    @if($cotizacion->vigencia_oferta && now()->gt($cotizacion->vigencia_oferta))
                        <span class="badge badge-danger">Vencida</span>
                    @else
                        <span class="badge badge-success">Vigente</span>
                    @endif
                </td>

                {{-- Acciones --}}
                <td>

                    <a href="{{ route('cotizaciones.show', $cotizacion->id_cotizacion) }}"
                    class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i>
                    </a>

                    <a href="{{ route('cotizaciones.edit', $cotizacion->id_cotizacion) }}"
                    class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i>
                    </a>

                    <a href="{{ route('cotizaciones.pdf', $cotizacion->id_cotizacion) }}"
                    class="btn btn-sm btn-light"
                    target="_blank">
                        <i class="fas fa-file-pdf text-danger"></i>
                    </a>

                    <form action="{{ route('cotizaciones.destroy', $cotizacion->id_cotizacion) }}"
                        method="POST"
                        style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('¿Eliminar esta cotización?')">
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
