@extends('adminlte::page')

@section('title', 'Remitos')

@section('content_header')
    <h1>Remitos</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<a href="{{ route('remitos.create') }}" class="btn btn-primary mb-3">Nuevo Remito</a>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Cliente</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($remitos as $remito)
            <tr>
                <td>{{ $remito->id }}</td>
                <td>{{ $remito->razon_social }}</td>
                <td>{{ $remito->fecha }}</td>
                <td><span class="badge badge-info">{{ $remito->estado }}</span></td>
                <td>
                    <a href="{{ route('remitos.show', $remito->id) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('remitos.edit', $remito->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i>
                    </a>
                   <a href="{{ route('remitos.pdf', $remito->id) }}"
                        class="btn btn-sm btn-light" target="_blank" title="Descargar PDF">
                            <i class="fas fa-file-pdf"></i>
                    </a>
                    <form action="{{ route('remitos.destroy', $remito->id) }}" method="POST" style="display:inline-block;">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>

</table>

@stop
