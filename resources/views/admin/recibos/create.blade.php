@extends('adminlte::page')

@section('title', 'Nuevo Recibo')

@section('content_header')
    <h1>Nuevo Recibo</h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">

        <form action="{{ route('recibos.store') }}" method="POST">
            @csrf
            @include('admin.recibos.partials.form')
            <button type="submit" class="btn btn-success mt-3">Guardar Recibo</button>
            <a href="{{ route('recibos.index') }}" class="btn btn-secondary mt-3">Cancelar</a>
        </form>

    </div>
</div>

@stop
