@extends('adminlte::page')

@section('title', 'Alta Orden de Pago')

@section('content_header')
    <h1>Registro de Orden de Pago</h1>
@stop

@section('content')
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
        <form action="{{ route('ordenes-pago.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                @include('admin.ordenes-pago.partials.form')
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Registrar Orden de Pago</button>
                <a href="{{ route('ordenes-pago.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@stop
