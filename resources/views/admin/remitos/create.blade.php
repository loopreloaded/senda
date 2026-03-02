@extends('adminlte::page')

@section('title', 'Nuevo Remito')

@section('content_header')
    <h1>Nuevo Remito</h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">

        <form action="{{ route('remitos.store') }}" method="POST">
            @csrf

            <div class="row">

                {{-- Número de Remito --}}
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Número de Remito *</label>
                        <input type="text"
                               name="numero_remito"
                               class="form-control @error('numero_remito') is-invalid @enderror"
                               value="{{ old('numero_remito') }}"
                               required>

                        @error('numero_remito')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Fecha --}}
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Fecha *</label>
                        <input type="date"
                               name="fecha"
                               class="form-control @error('fecha') is-invalid @enderror"
                               value="{{ old('fecha', date('Y-m-d')) }}"
                               required>

                        @error('fecha')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Cliente --}}
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Cliente *</label>
                        <select name="id_cliente"
                                class="form-control @error('id_cliente') is-invalid @enderror"
                                required>
                            <option value="">Seleccione...</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id_cliente }}"
                                    {{ old('id_cliente') == $cliente->id_cliente ? 'selected' : '' }}>
                                    {{ $cliente->razon_social }}
                                </option>
                            @endforeach
                        </select>

                        @error('id_cliente')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

            </div>

            <div class="row">

                {{-- Orden de Compra --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label>OC asociada</label>
                        <select name="id_orden_compra" class="form-control">
                            <option value="">Ninguna</option>
                            @foreach($ordenes as $orden)
                                <option value="{{ $orden->id_orden }}">
                                    {{ $orden->numero_oc }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Factura --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Factura relacionada</label>
                        <select name="id_factura" class="form-control">
                            <option value="">Ninguna</option>
                            @foreach($facturas as $factura)
                                <option value="{{ $factura->id_factura }}">
                                    {{ $factura->numero_factura }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>

            {{-- Comentarios --}}
            <div class="form-group">
                <label>Comentarios</label>
                <textarea name="comentarios"
                          class="form-control"
                          rows="3">{{ old('comentarios') }}</textarea>
            </div>

            <hr>

            <div class="mt-3">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar
                </button>

                <a href="{{ route('remitos.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>

        </form>

    </div>
</div>

@stop
