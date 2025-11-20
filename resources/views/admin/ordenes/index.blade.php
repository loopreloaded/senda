@extends('adminlte::page')

@section('title', 'Órdenes de Compra')

@section('content_header')
    <h2>Órdenes de Compra</h2>
@stop

@section('content')
<a href="{{ route('ordenes.create') }}" class="btn btn-primary mb-3">Nueva Orden de Compra</a>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Número OC</th>
            <th>Proveedor</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ordenes as $orden)
            <tr>
                <td>{{ $orden->id }}</td>
                <td>{{ $orden->numero_oc }}</td>
                <td>{{ $orden->proveedor }}</td>
                <td>{{ $orden->fecha }}</td>
                <td>${{ number_format($orden->total, 2) }}</td>
                <td>{{ ucfirst($orden->estado) }}</td>
                <td>
                    <!-- Editar -->
                    <a href="{{ route('ordenes.edit', $orden->id) }}"
                    class="btn btn-sm btn-warning"
                    title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>

                    <!-- PDF -->
                    <a href="{{ route('ordenes.pdf', $orden->id) }}"
                    class="btn btn-sm btn-light"
                    title="Imprimir PDF"
                    target="_blank">
                        <i class="fas fa-file-pdf" style="color:#d9534f;"></i>
                    </a>

                    <!-- Eliminar -->
                    <form action="{{ route('ordenes.destroy', $orden->id) }}"
                        method="POST"
                        style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-sm btn-danger"
                                title="Eliminar"
                                onclick="return confirm('¿Eliminar esta orden?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>

            </tr>
        @endforeach
    </tbody>
</table>

{{ $ordenes->links() }}
@stop
