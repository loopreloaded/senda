@extends('adminlte::page')

@section('title', 'Editar Cotizacion')

@section('content_header')
    <h1>Editar Cotizacion </h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">

        <form action="{{ route('cotizaciones.update', $cotizacion) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Reutilizamos el mismo formulario --}}
            @include('admin.cotizaciones.partials.form', ['cotizacion' => $cotizacion])

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
