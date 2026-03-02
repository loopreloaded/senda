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

<div class="card">
    <div class="card-body table-responsive">

        <table class="table table-bordered table-striped">
            <thead class="thead-light">
                <tr>
                    <th>Número de remito</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>OC asociada</th>
                    <th>Factura relacionada</th>
                    <th>Estado</th>
                    <th width="220px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($remitos as $remito)
                    <tr>
                        <td>{{ $remito->numero_remito }}</td>

                        <td>
                            {{ optional($remito->cliente)->razon_social }}
                        </td>

                        <td>
                            {{ $remito->fecha ? $remito->fecha->format('d/m/Y') : '-' }}
                        </td>

                        <td>
                            {{ optional($remito->ordenCompra)->numero_oc ?? '-' }}
                        </td>

                        <td>
                            {{ optional($remito->factura)->numero_factura ?? '-' }}
                        </td>

                        <td>
                            @if($remito->estado === 'emitido')
                                <span class="badge badge-warning">Emitido</span>
                            @elseif($remito->estado === 'confirmado')
                                <span class="badge badge-success">Confirmado</span>
                            @elseif($remito->estado === 'anulado')
                                <span class="badge badge-danger">Anulado</span>
                            @endif
                        </td>

                        <td>
                            {{-- VER PDF --}}
                            <a href="{{ route('remitos.pdf', $remito) }}"
                               class="btn btn-sm btn-info"
                               target="_blank">
                                <i class="fas fa-file-pdf"></i>
                            </a>

                            {{-- ACCIONES SEGÚN ESTADO --}}
                            @if($remito->estado === 'emitido')

                                {{-- EDITAR --}}
                                <a href="{{ route('remitos.edit', $remito) }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>

                                {{-- ANULAR --}}
                                <form action="{{ route('remitos.destroy', $remito) }}"
                                      method="POST"
                                      style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('¿Anular este remito?')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>

                            @endif

                            {{-- COMENTAR (para todos los estados) --}}
                            <a href="{{ route('remitos.show', $remito) }}"
                               class="btn btn-sm btn-secondary">
                                <i class="fas fa-comment"></i>
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

    </div>
</div>

@stop
