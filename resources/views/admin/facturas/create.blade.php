@extends('adminlte::page')

@section('title', 'Crear Factura')

@section('content_header')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <h2>Nueva Factura</h2>
        </div>
    </div>
@stop

@if ($errors->any())
    <div style="background:#f8d7da; padding:10px; margin-bottom:10px;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif



@section('content')

    {{-- Mensajes de éxito --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Mensajes de error --}}
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
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

        document.addEventListener("DOMContentLoaded", function () {

            const concepto = document.querySelector('select[name="concepto"]');
            const bloque   = document.getElementById('bloque-servicios');

            if (!concepto || !bloque) {
                console.error("No se encontró concepto o bloque-servicios");
                return;
            }

            function actualizar() {
                const v = concepto.value;

                if (v == "2" || v == "3") {
                    bloque.style.display = "flex"; // se ve lindo con bootstrap
                } else {
                    bloque.style.display = "none";
                }
            }

            // primera ejecución
            actualizar();

            // evento normal (por las dudas)
            concepto.addEventListener('change', actualizar);

            // evento especial de SELECT2
            if (typeof $ !== "undefined") {
                $(concepto).on('select2:select', function () {
                    actualizar();
                });
            }

        });

    </script>

@stop
