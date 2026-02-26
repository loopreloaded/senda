@extends('adminlte::page')

@section('title', 'Editar Pedido de Cotización')

@section('content_header')
    <h1>Editar Pedido de Cotización #{{ $pedido->id_ped_cot }}</h1>
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

        <form action="{{ route('pedidos-cotizacion.update', $pedido->id_ped_cot) }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="row">

                {{-- Fecha --}}
                <div class="col-md-4">
                    <label>Fecha</label>
                    <input type="date"
                           name="fecha"
                           class="form-control"
                           value="{{ old('fecha', optional($pedido->fecha)->format('Y-m-d')) }}"
                           required>
                </div>

                {{-- Cliente --}}
                <div class="col-md-4">
                    <label>Cliente</label>
                    <select name="id_cliente" class="form-control" required>
                        <option value="">-- Seleccionar --</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}"
                                {{ old('id_cliente', $pedido->id_cliente) == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->razon_social }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Estado --}}
                <div class="col-md-4">
                    <label>Estado</label>
                    <select name="estado" class="form-control" required>
                        <option value="p" {{ old('estado', $pedido->estado) == 'p' ? 'selected' : '' }}>
                            Pendiente
                        </option>
                        <option value="c" {{ old('estado', $pedido->estado) == 'c' ? 'selected' : '' }}>
                            Cotizado
                        </option>
                    </select>
                </div>

            </div>

            <div class="row mt-3">

                {{-- Archivo --}}
                <div class="col-md-6">
                    <label>Archivo (PDF / Imagen)</label>
                    <input type="file"
                           name="archivo"
                           class="form-control">

                    @if($pedido->archivo)
                        <div class="mt-2">
                            <a href="{{ asset('storage/'.$pedido->archivo) }}"
                               target="_blank"
                               class="btn btn-sm btn-outline-primary">
                                Ver archivo actual
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Observaciones --}}
                <div class="col-md-6">
                    <label>Observaciones</label>
                    <textarea name="observaciones"
                              rows="3"
                              class="form-control">{{ old('observaciones', $pedido->observaciones) }}</textarea>
                </div>

            </div>

            <hr class="mt-4">

            <div class="d-flex justify-content-between">
                <a href="{{ route('pedidos-cotizacion.index') }}"
                   class="btn btn-secondary">
                    Volver
                </a>

                <button type="submit" class="btn btn-primary">
                    Actualizar Pedido
                </button>
            </div>

        </form>

    </div>
</div>

@stop
