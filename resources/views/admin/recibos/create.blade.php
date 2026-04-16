@extends('adminlte::page')

@section('title', 'Nuevo Recibo')

@section('content_header')
    <h1>Nuevo Recibo</h1>
@stop

@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
