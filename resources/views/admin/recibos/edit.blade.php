@extends('adminlte::page')

@section('title', 'Editar Recibo')

@section('content_header')
    <h1>Editar Recibo</h1>
@stop

@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

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
            @if($recibo->estado === 'Cerrada')
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Este recibo está <strong>Cerrado</strong> y no puede ser modificado.
                </div>
                <a href="{{ route('recibos.index') }}" class="btn btn-secondary">Volver</a>
            @else
                <form action="{{ route('recibos.update', $recibo) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @include('admin.recibos.partials.form')

                    <button type="submit" class="btn btn-primary mt-3">Actualizar Recibo</button>
                    <a href="{{ route('recibos.index') }}" class="btn btn-secondary mt-3">Cancelar</a>
                </form>
            @endif
        </div>
    </div>

@stop
