@extends('adminlte::page')

@section('title', 'Nueva Cotizacion')

@section('content_header')
    <h2>Nueva Cotizacion</h2>
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
        <form method="POST" action="{{ route('cotizaciones.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.cotizaciones.partials.form')
            <div class="mt-4 text-right">
                <button type="submit" class="btn btn-success">Guardar Cotización</button>
                <a href="{{ route('cotizaciones.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@stop
