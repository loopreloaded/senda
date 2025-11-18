@extends('adminlte::page')

@section('title', 'Notas de Débito')

@section('content_header')
    <h1>Notas de Débito</h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">

        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Factura</th>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>CAE</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($notas as $n)
                    <tr>
                        <td>{{ $n->id }}</td>
                        <td>{{ $n->factura->tipo_comprobante }} {{ $n->factura->numero }}</td>
                        <td>{{ $n->cliente->razon_social }}</td>
                        <td>{{ $n->tipo_comprobante }}</td>
                        <td>$ {{ number_format($n->importe_total,2,',','.') }}</td>
                        <td>{{ ucfirst($n->estado) }}</td>
                        <td>{{ $n->cae ?? '-' }}</td>
                        <td>
                            <a href="{{ route('notasdebito.show',$n->id) }}" class="btn btn-sm btn-primary">Ver</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $notas->links() }}

    </div>
</div>

@stop
