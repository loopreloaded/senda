@extends('adminlte::page')

@section('title', 'Crear Factura')

@section('content_header')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <h2>Crear Nueva Factura</h2>
        </div>
    </div>
@stop

@section('content')

    {{-- Mensajes de éxito --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Mensajes de error --}}
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
            {{-- Formulario de creación --}}
            <form method="POST"
                  action="{{ route('facturas.store') }}"
                  autocomplete="off">

                @csrf

                {{-- Incluye los campos del formulario desde el partial --}}
                @include('admin.facturas.partials.form')

                <div class="mt-4 d-flex justify-content-end">
                    <a class="btn btn-secondary mr-2" href="{{ route('facturas.index') }}">
                        {{ __('Cancelar') }}
                    </a>
                    <button type="submit" class="btn btn-success">
                        {{ __('Crear Factura') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    {{-- Aquí podés agregar estilos adicionales si lo necesitás --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    {{-- Scripts personalizados --}}
    <script>
        $(document).ready(function() {
            // Inicializar Select2 si hay selects con clase .sel2
            if ($.fn.select2) {
                $('.sel2').select2();
            }
        });
    </script>
@stop
