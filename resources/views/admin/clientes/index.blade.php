@extends('adminlte::page')

@section('title', 'Listado de socios comerciales')

@section('content_header')
    <h2>Listado de socios comerciales</h2>
@stop

@section('content')

<div class="d-flex gap-2 mb-3">

    <a href="{{ route('clientes.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Nuevo SC
    </a>

    {{-- BOTÓN IMPORTAR EXCEL --}}
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalImportExcel">
        <i class="fas fa-file-excel"></i> Importar Excel
    </button>

</div>

{{-- MODAL IMPORTACIÓN --}}
<div class="modal fade" id="modalImportExcel" tabindex="-1" role="dialog" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('clientes.import.excel') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title" id="modalImportExcelLabel">Importar Excel</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <div class="alert alert-info">
                    El archivo debe contener las columnas:
                    <b>CUIT</b>, <b>DENOMINACION</b>, <b>CONDICION</b> y <b>INDICE</b>
                </div>


                <div class="form-group">
                    <label>Archivo Excel</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-upload"></i> Importar
                </button>
            </div>

        </form>
    </div>
</div>


{{-- FILTRO DE CLIENTES --}}
<form method="GET" action="{{ route('clientes.index') }}" class="mb-3">

    <div class="row">

        <div class="col-md-3">
            <label>CUIT</label>
            <input type="text"
                   name="cuit"
                   class="form-control"
                   value="{{ request('cuit') }}"
                   placeholder="Ej. 30-12345678-9">
        </div>

        <div class="col-md-4">
            <label>Razón Social</label>
            <input type="text"
                   name="razon_social"
                   class="form-control"
                   value="{{ request('razon_social') }}"
                   placeholder="Empresa-Ejemplo S.A.">
        </div>

        <div class="col-md-3">
            <label>Tipo</label>
            <select name="tipo" class="form-control">
                <option value="">Todos</option>

                <option value="C" {{ request('tipo') == 'C' ? 'selected' : '' }}>
                    Cliente
                </option>

                <option value="P" {{ request('tipo') == 'P' ? 'selected' : '' }}>
                    Proveedor
                </option>

                <option value="A" {{ request('tipo') == 'A' ? 'selected' : '' }}>
                    Ambos
                </option>
            </select>
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
            <th>Tipo</th>
            <th>Condición IVA</th>
            <th>Condición IIBB</th>
            <th width="120">Acciones</th>
        </tr>
    </thead>


    <tbody>
        @forelse($clientes as $cliente)
            <tr>
                <td>{{ $cliente->id }}</td>
                <td>{{ $cliente->cuit }}</td>
                <td>{{ $cliente->razon_social }}</td>
                <td>
                    @switch($cliente->tipo)
                        @case('C')
                            Cliente
                            @break

                        @case('P')
                            Proveedor
                            @break

                        @case('A')
                            Ambos
                            @break

                        @default
                            -
                    @endswitch
                </td>
                <td>{{ $cliente->condicion_iva_texto }}</td>
                <td>{{ $cliente->condicion_iibb_texto }}</td>

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
                    No se encontraron socios comerciales
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{ $clientes->links() }}

@stop
