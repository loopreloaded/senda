@extends('adminlte::page')

@section('title', 'Nuevo pedido cotizacion')

@section('content_header')
    <h2>Nuevo pedido cotizacion</h2>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('pedidos-cotizacion.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.pedidos-cotizacion.partials.form')
            <div class="mt-4 text-right">
                <button type="submit" class="btn btn-success">Guardar Pedido</button>
                <a href="{{ route('pedidos-cotizacion.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@stop
