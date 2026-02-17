@extends('adminlte::page')

@section('title', 'Nueva Cotización')

@section('content_header')
    <h2>Nueva Cotización</h2>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body">

        <form method="POST" action="{{ route('cotizaciones.store') }}">
            @csrf

            <div class="row">

                {{-- Fecha --}}
                <div class="col-md-3">
                    <label>Fecha</label>
                    <input type="datetime-local"
                           name="fecha_cot"
                           class="form-control"
                           value="{{ old('fecha_cot') }}"
                           required>
                </div>

                {{-- Cliente --}}
                <div class="col-md-4">
                    <label>Cliente</label>
                    <select name="id_cliente" class="form-control" required>
                        <option value="">Seleccione cliente</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}"
                                {{ old('id_cliente') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->razon_social }} - {{ $cliente->cuit ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Moneda --}}
                <div class="col-md-2">
                    <label>Moneda</label>
                    <select name="moneda" class="form-control" required>
                        <option value="ARS">ARS</option>
                        <option value="USD_BILLETE">USD Billete</option>
                        <option value="USD_DIVISA">USD Divisa</option>
                    </select>
                </div>

                {{-- Forma de Pago --}}
                <div class="col-md-3">
                    <label>Forma de Pago</label>
                    <select name="forma_pago" class="form-control" required>
                        <option value="CTA_CTE">Cuenta Corriente</option>
                        <option value="CONTADO">Contado</option>
                        <option value="MIXTO">Mixto</option>
                        <option value="ANTICIPADO">Anticipado</option>
                    </select>
                </div>

            </div>

            <hr>

            <div class="row">

                {{-- Lugar Entrega --}}
                <div class="col-md-4">
                    <label>Lugar de Entrega</label>
                    <input type="text"
                           name="lugar_entrega"
                           class="form-control"
                           value="{{ old('lugar_entrega') }}">
                </div>

                {{-- Plazo Entrega --}}
                <div class="col-md-4">
                    <label>Plazo de Entrega</label>
                    <input type="text"
                           name="plazo_entrega"
                           class="form-control"
                           value="{{ old('plazo_entrega') }}">
                </div>

                {{-- Vigencia --}}
                <div class="col-md-4">
                    <label>Vigencia de Oferta</label>
                    <input type="date"
                           name="vigencia_oferta"
                           class="form-control"
                           value="{{ old('vigencia_oferta') }}">
                </div>

            </div>

            <hr>

            <div class="row">

                {{-- Especificaciones --}}
                <div class="col-md-6">
                    <label>Especificaciones Técnicas</label>
                    <textarea name="especificaciones_tecnicas"
                              class="form-control"
                              rows="3">{{ old('especificaciones_tecnicas') }}</textarea>
                </div>

                {{-- Observaciones --}}
                <div class="col-md-6">
                    <label>Observaciones</label>
                    <textarea name="observaciones"
                              class="form-control"
                              rows="3">{{ old('observaciones') }}</textarea>
                </div>

            </div>

            <hr>

            {{-- Importe Total --}}
            <div class="row">
                <div class="col-md-4">
                    <label>Importe Total</label>
                    <input type="number"
                           step="0.01"
                           name="importe_total"
                           class="form-control"
                           value="{{ old('importe_total', 0) }}">
                </div>
            </div>

            <div class="mt-4 text-right">
                <button type="submit" class="btn btn-success">
                    Guardar Cotización
                </button>

                <a href="{{ route('cotizaciones.index') }}"
                   class="btn btn-secondary">
                    Cancelar
                </a>
            </div>

        </form>

    </div>
</div>

@stop
