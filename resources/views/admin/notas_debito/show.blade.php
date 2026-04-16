@extends('adminlte::page')

@section('title','Nota de Débito')

@section('content_header')
    <h1>Nota de Débito #{{ $nota->id }}</h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">

        <p><strong>Factura origen:</strong> {{ $nota->factura->tipo_comprobante }} {{ $nota->factura->numero }}</p>
        <p><strong>Cliente:</strong> {{ $nota->cliente->razon_social }}</p>
        <p><strong>Tipo:</strong> {{ $nota->tipo_comprobante }}</p>
        <p><strong>Total:</strong> $ {{ number_format($nota->importe_total,2,',','.') }}</p>
        <p><strong>Estado:</strong> {{ ucfirst($nota->estado) }}</p>
        <p><strong>CAE:</strong> {{ $nota->cae ?? '-' }}</p>

        <h4>Ítems</h4>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Cant</th>
                    <th>Precio</th>
                    <th>IVA</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($nota->items as $i)
                <tr>
                    <td>{{ $i->descripcion }}</td>
                    <td>{{ $i->cantidad }}</td>
                    <td>{{ $i->precio_unitario }}</td>
                    <td>{{ $i->iva }}%</td>
                    <td>{{ number_format($i->subtotal,2,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @can('enviar nota de debito afip')
            @if($nota->estado != 'emitida')
                <form action="{{ route('notasdebito.afip',$nota->id) }}" method="POST">
                    @csrf
                    <button class="btn btn-success">Enviar a AFIP</button>
                </form>
            @endif
        @endcan

    </div>
</div>

@stop
