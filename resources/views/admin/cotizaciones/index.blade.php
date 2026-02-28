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

        {{-- Número --}}
        <div class="col-md-2">
            <label>N° Cotización</label>
            <input type="text" name="id"
                   class="form-control"
                   value="{{ request('id') }}"
                   placeholder="Ej: 15">
        </div>

        {{-- Cliente --}}
        <div class="col-md-3">
            <label>Cliente</label>
            <input type="text" name="cliente"
                   class="form-control"
                   value="{{ request('cliente') }}"
                   placeholder="Razon social...">
        </div>

        {{-- Fecha --}}
        <div class="col-md-2">
            <label>Fecha</label>
            <input type="date"
                   name="fecha"
                   class="form-control"
                   value="{{ request('fecha') }}">
        </div>

        {{-- Moneda --}}
        <div class="col-md-2">
            <label>Moneda</label>
            <select name="moneda" class="form-control">
                <option value="">Todas</option>
                <option value="ARS" {{ request('moneda') == 'ARS' ? 'selected' : '' }}>ARS</option>
                <option value="USD_BILLETE" {{ request('moneda') == 'USD_BILLETE' ? 'selected' : '' }}>USD Billete</option>
                <option value="USD_DIVISA" {{ request('moneda') == 'USD_DIVISA' ? 'selected' : '' }}>USD Divisa</option>
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
            <a href="{{ route('cotizaciones.index') }}"
               class="btn btn-secondary w-100"
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
