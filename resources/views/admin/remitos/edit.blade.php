@extends('adminlte::page')

@section('title', 'Editar Remito')

@section('content_header')
    <h2>Editar Remito</h2>
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
        <form method="POST" action="{{ route('remitos.update', $remito->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            @include('admin.remitos.partials.form')

            <div class="mt-4 text-right">
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('remitos.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@stop
