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
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>OC</th>
                    <th>Factura</th>
                    <th>Estado</th>
                    <th width="240px">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($remitos as $remito)
                    <tr>

                        <td>{{ $remito->numero_remito }}</td>

                        <td>{{ optional($remito->cliente)->razon_social ?? '-' }}</td>

                        <td>
                            {{ $remito->fecha ? $remito->fecha->format('d/m/Y') : '-' }}
                        </td>

                        <td>{{ optional($remito->ordenCompra)->id ?? '-' }}</td>

                        <td>{{ optional($remito->factura)->id ?? '-' }}</td>

                        {{-- ESTADO --}}
                        <td>
                            @if($remito->estado === 'Emitido')
                                <span class="badge badge-warning">Emitido</span>
                            @elseif($remito->estado === 'Confirmado')
                                <span class="badge badge-success">Confirmado</span>
                            @elseif($remito->estado === 'Anulado')
                                <span class="badge badge-danger">Anulado</span>
                            @else
                                <span class="badge badge-secondary">{{ $remito->estado }}</span>
                            @endif
                        </td>

                        {{-- ACCIONES --}}
                        <td>

                            {{-- PDF --}}
                            <a href="{{ route('remitos.pdf', $remito) }}"
                               class="btn btn-sm btn-info"
                               target="_blank"
                               title="PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>

                            {{-- SOLO SI ESTÁ EMITIDO --}}
                            @if($remito->estado === 'Emitido')

                                {{-- EDITAR --}}
                                <a href="{{ route('remitos.edit', $remito) }}"
                                   class="btn btn-sm btn-primary"
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>

                                {{-- CONFIRMAR --}}
                                <a href="{{ route('remitos.confirmar', $remito) }}"
                                   class="btn btn-sm btn-success"
                                   onclick="return confirm('¿Confirmar remito?')"
                                   title="Confirmar">
                                    <i class="fas fa-check"></i>
                                </a>

                                {{-- ANULAR --}}
                                <form action="{{ route('remitos.destroy', $remito) }}"
                                      method="POST"
                                      style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('¿Anular este remito?')"
                                            title="Anular">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>

                            @endif

                            {{-- VER / COMENTAR --}}
                            <a href="{{ route('remitos.show', $remito) }}"
                               class="btn btn-sm btn-secondary"
                               title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>

                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">
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