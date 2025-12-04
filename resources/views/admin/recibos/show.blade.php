@extends('adminlte::page')

@section('title', 'Detalle Recibo')

@section('content_header')
    <h1>Detalle del Recibo</h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">

        <p><strong>Cliente:</strong> {{ $remito->cliente->razon_social }}</p>
        <p><strong>Fecha:</strong> {{ $remito->fecha }}</p>
        <p><strong>Estado:</strong> {{ $remito->estado }}</p>

        <h4>Ítems</h4>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($remito->items as $item)
                    <tr>
                        <td>{{ $item->descripcion }}</td>
                        <td>{{ $item->cantidad }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <a href="{{ route('remitos.index') }}" class="btn btn-secondary mt-3">Volver</a>

    </div>
</div>

@stop
