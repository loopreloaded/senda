@extends('adminlte::page')

@section('title', 'Listado de Clientes')

@section('content_header')
    <h2>Listado de Clientes</h2>
@stop

@section('content')

<a href="{{ route('clientes.create') }}" class="btn btn-primary mb-3">
    <i class="fas fa-user-plus"></i> Nuevo Cliente
</a>

{{-- FILTRO DE CLIENTES --}}
<form method="GET" action="{{ route('clientes.index') }}" class="mb-3">

    <div class="row">

        <div class="col-md-3">
            <label>CUIT</label>
            <input type="text"
                   name="cuit"
                   class="form-control"
                   value="{{ request('cuit') }}"
                   placeholder="Ej: 20301234567">
        </div>

        <div class="col-md-4">
            <label>Razón Social</label>
            <input type="text"
                   name="razon_social"
                   class="form-control"
                   value="{{ request('razon_social') }}"
                   placeholder="Empresa / Cliente">
        </div>

        <div class="col-md-3">
            <label>Email</label>
            <input type="email"
                   name="email"
                   class="form-control"
                   value="{{ request('email') }}"
                   placeholder="email@cliente.com">
        </div>

        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-dark w-100">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <div class="col-md-1 d-flex align-items-end">
            <a href="{{ route('clientes.index') }}"
               class="btn btn-secondary w-100"
               title="Limpiar filtros">
                <i class="fas fa-broom"></i>
            </a>
        </div>

    </div>

</form>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>CUIT</th>
            <th>Razón Social</th>
            <th>Domicilio Comercial</th>
            <th>Email</th>
            <th width="120">Acciones</th>
        </tr>
    </thead>

    <tbody>
        @forelse($clientes as $cliente)
            <tr>
                <td>{{ $cliente->id }}</td>
                <td>{{ $cliente->cuit }}</td>
                <td>{{ $cliente->razon_social }}</td>
                <td>{{ $cliente->direccion }}</td>
                <td>{{ $cliente->email }}</td>
                <td>

                    {{-- EDITAR --}}
                    <a href="{{ route('clientes.edit', $cliente->id) }}"
                       class="btn btn-sm btn-info"
                       title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>

                    {{-- ELIMINAR --}}
                    @hasanyrole('admin|ingeniero')
                    <form action="{{ route('clientes.destroy', $cliente->id) }}"
                          method="POST"
                          style="display:inline">
                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="btn btn-sm btn-danger"
                                title="Eliminar"
                                onclick="return confirm('¿Eliminar este cliente?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @endhasanyrole

                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted">
                    No se encontraron clientes
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{ $clientes->links() }}

@stop
