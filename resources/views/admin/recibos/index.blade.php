@extends('adminlte::page')

@section('title', 'Recibos')

@section('content_header')
    <h1>Recibos</h1>
@stop

@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    <a href="{{ route('recibos.create') }}" class="btn btn-primary mb-3">
        <i class="fas fa-plus-circle"></i> Nuevo Recibo
    </a>

    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>Nro Recibo</th>
            <th>Fecha</th>
            <th style="width: 210px">Acciones</th>
        </tr>
        </thead>

        <tbody>
        @forelse($recibos as $recibo)
            <tr>
                <td>{{ $recibo->id_recibo }}</td>
                <td>{{ $recibo->nro_recibo }}</td>

                {{-- Fecha formateada dd/mm/aaaa --}}
                <td>{{ \Carbon\Carbon::parse($recibo->fecha)->format('d/m/Y') }}</td>

                <td>

                    {{-- PDF --}}
                    <a href="{{ route('recibos.pdf', $recibo) }}"
                       class="btn btn-sm btn-secondary" title="Ver PDF" target="_blank">
                        <i class="fas fa-file-pdf"></i>
                    </a>

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

                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No se encontraron recibos.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

@stop
