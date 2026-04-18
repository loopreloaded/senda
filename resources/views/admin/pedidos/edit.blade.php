@extends('adminlte::page')

@section('title', 'Editar Pedido de Cotización')

@section('content_header')
    <h1>Editar Pedido de Cotización </h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">

        <form action="{{ route('pedidos.update', $pedido) }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf
            @method('PUT')

            {{-- Reutilizamos el mismo formulario --}}
            @include('admin.pedidos.partials.edit', ['pedido' => $pedido])

            <div class="mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Actualizar
                </button>

                <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>

        </form>

    </div>
</div>

@stop
