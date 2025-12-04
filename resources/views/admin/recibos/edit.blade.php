@extends('adminlte::page')

@section('title', 'Editar Recibo')

@section('content_header')
    <h1>Editar Recibo</h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">

        <form action="{{ route('remitos.update', $remito->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.remitos.partials.form')
            <button type="submit" class="btn btn-primary mt-3">Actualizar</button>
            <a href="{{ route('remitos.index') }}" class="btn btn-secondary mt-3">Cancelar</a>
        </form>

    </div>
</div>

@stop
