@extends('adminlte::page')

@section('title', 'Editar Orden de Compra')

@section('content_header')
    <h1>Editar Orden de Compra #{{ $orden->numero_oc }}</h1>
@stop

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Hay errores en el formulario:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body">

        <form action="{{ route('ordenes.update', $orden->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- FORMULARIO DE EDICIÓN --}}
            @include('admin.ordenes.partials.edit', ['orden' => $orden])

            <div class="mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Actualizar Orden
                </button>

                <a href="{{ route('ordenes.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>

        </form>

    </div>
</div>

@stop
