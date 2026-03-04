@extends('adminlte::page')

@section('title', 'Editar Cotizacion')

@section('content_header')
    <h1>Editar Cotizacion #{{ $cotizacion->numero_oc }}</h1>
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

        <form action="{{ route('cotizaciones.update', $cotizacion) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Reutilizamos el mismo formulario --}}
            @include('admin.cotizaciones.partials.edit', ['cotizacion' => $cotizacion])

            <div class="mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Actualizar
                </button>

                <a href="{{ route('cotizaciones.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>

        </form>

    </div>
</div>

@stop
