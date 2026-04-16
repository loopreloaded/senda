@extends('adminlte::page')

@section('title', 'Listado de Recibos')

@section('content_header')
    <h1>Listado de Recibos</h1>
@stop

@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route('recibos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Nuevo Recibo
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped" id="tabla-recibos">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Nro. Rec.</th>
                    <th>Motivo</th>
                    <th>Importe Saldado</th>
                    <th>Retenciones</th>
                    <th>Estado</th>
                    <th style="width: 250px">Acciones</th>
                </tr>
                </thead>

                <tbody>
                @foreach($recibos as $recibo)
                    <tr>
                        <td>{{ $recibo->id_recibo }}</td>
                        <td>{{ $recibo->cliente->razon_social ?? 'N/A' }}</td>
                        <td>{{ $recibo->fecha ? $recibo->fecha->format('d/m/Y') : 'N/A' }}</td>
                        <td>{{ $recibo->nro_recibo }}</td>
                        <td>
                            <span class="badge {{ $recibo->motivo === 'pedido' ? 'badge-info' : 'badge-secondary' }}">
                                {{ $recibo->motivo === 'pedido' ? 'Pedido' : 'Particular' }}
                            </span>
                        </td>
                        <td class="text-right">$ {{ number_format($recibo->importe_saldado, 2) }}</td>
                        <td class="text-right">$ {{ number_format($recibo->total_retenciones, 2) }}</td>
                        <td>
                            @php
                                $badgeClass = match($recibo->estado) {
                                    'Emitida' => 'badge-primary',
                                    'Cerrada' => 'badge-success',
                                    default => 'badge-dark'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $recibo->estado }}</span>
                        </td>
                        <td>
                            {{-- Ver PDF --}}
                            <a href="{{ route('recibos.pdf', $recibo) }}"
                               class="btn btn-sm btn-secondary" title="Ver PDF" target="_blank">
                                <i class="fas fa-file-pdf"></i>
                            </a>

                            @if($recibo->estado === 'Emitida')
                                {{-- Editar --}}
                                <a href="{{ route('recibos.edit', $recibo) }}"
                                   class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>

                                {{-- Cerrar --}}
                                <form action="{{ route('recibos.aprobar', $recibo->id_recibo) }}" 
                                      method="POST" style="display:inline-block"
                                      onsubmit="return confirm('¿Desea cerrar este recibo? No podrá editarlo luego.')">
                                    @csrf
                                    <button class="btn btn-sm btn-success" title="Cerrar Recibo">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>

                                {{-- Eliminar --}}
                                <form action="{{ route('recibos.destroy', $recibo) }}"
                                      method="POST" style="display:inline-block"
                                      onsubmit="return confirm('¿Eliminar este recibo?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#tabla-recibos').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
                },
                "order": [[ 0, "desc" ]]
            });
        });
    </script>
@stop
