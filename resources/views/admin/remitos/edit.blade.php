@extends('adminlte::page')

@section('title', 'Editar SC')

@section('content_header')
    <h2>Editar SC</h2>
@stop

@section('content')

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

        {{-- FORM UPDATE --}}
        <form action="{{ route('clientes.update', $cliente->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- PARTIAL --}}
            @include('admin.clientes.partials.edit', ['cliente' => $cliente])

            <div class="mt-3 d-flex justify-content-between">
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar cambios
                </button>
            </div>

        </form>

    </div>
</div>

@stop
