@extends('adminlte::page')

@section('title', 'Editar Orden de Compra')

@section('content_header')
    <h1>Editar Orden de Compra #{{ $orden->numero_oc }}</h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">

        <form action="{{ route('ordenes.update', $orden->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Reutilizamos el mismo formulario --}}
            @include('admin.ordenes.partials.form', ['orden' => $orden])

            <div class="mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Actualizar
                </button>

                <a href="{{ route('ordenes.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>

        </form>

    </div>
</div>

@stop
