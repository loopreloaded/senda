@extends('adminlte::page')

@section('title', 'Remitos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Listado de Remitos</h1>

        <a href="{{ route('remitos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Remito
        </a>
    </div>
@stop

@section('content')

{{-- ALERTAS --}}
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body table-responsive">

        <table class="table table-bordered table-striped">
            <thead class="thead-light">
                <tr>
                    <th>ID REM (#)</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Nro. Rem.</th>
                    <th>Motivo</th>
                    <th>Cant. Art. Rem.</th>
                    <th>Cant. Art. Fac.</th>
                    <th>Art. Fac.</th>
                    <th>Estado</th>
                    <th width="150px">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($remitos as $remito)
                    <tr>
                        <td><span class="badge badge-dark">REM-{{ $remito->id }}</span></td>
                        <td>{{ optional($remito->cliente)->razon_social ?? '-' }}</td>
                        <td>{{ $remito->fecha ? $remito->fecha->format('d/m/Y') : '-' }}</td>
                        <td>{{ $remito->numero_remito }}</td>
                        <td>
                            <span class="badge {{ $remito->motivo == 'pedido' ? 'badge-info' : 'badge-secondary' }}">
                                {{ ucfirst($remito->motivo == 'pedido' ? 'Vinculado' : 'Particular') }}
                            </span>
                        </td>
                        <td>{{ $remito->cant_art_rem }}</td>
                        <td>{{ $remito->cant_art_fac }}</td>
                        <td>
                            <small class="text-muted">{{ Str::limit($remito->art_fac, 30) }}</small>
                        </td>

                        {{-- ESTADO --}}
                        <td>
                            @if($remito->estado === 'Emitido')
                                <span class="badge badge-warning">Emitido</span>
                            @elseif($remito->estado === 'Facturado')
                                <span class="badge badge-success">Facturado</span>
                            @elseif($remito->estado === 'Parcial')
                                <span class="badge badge-primary">Parcial</span>
                            @elseif($remito->estado === 'Anulado')
                                <span class="badge badge-danger">Anulado</span>
                            @else
                                <span class="badge badge-secondary">{{ $remito->estado }}</span>
                            @endif
                        </td>

                        {{-- ACCIONES --}}
                        <td>
                            <div class="btn-group">
                                {{-- PDF --}}
                                <a href="{{ route('remitos.pdf', $remito) }}"
                                   class="btn btn-sm btn-info"
                                   target="_blank"
                                   title="Ver PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                {{-- SHOW --}}
                                <a href="{{ route('remitos.show', $remito) }}"
                                   class="btn btn-sm btn-secondary"
                                   title="Ver Detalle">
                                    <i class="fas fa-eye"></i>
                                </a>

                                {{-- EDITAR / ANULAR (Solo si es Emitido o Parcial) --}}
                                @if(in_array($remito->estado, ['Emitido', 'Parcial']))
                                    <a href="{{ route('remitos.edit', $remito) }}"
                                       class="btn btn-sm btn-primary"
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('remitos.destroy', $remito) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('¿Anular este remito?')"
                                                title="Anular">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">
                            No hay remitos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- PAGINACIÓN --}}
        {{ $remitos->links() }}

    </div>
</div>

@stop