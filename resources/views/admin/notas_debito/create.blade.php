@extends('adminlte::page')

@section('title', 'Nueva Nota de Débito')

@section('content_header')
    <h1>Cargar Nota de Débito</h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">

        <form action="{{ route('notasdebito.store') }}" method="POST">
            @csrf

            {{-- FACTURA ORIGEN --}}
            <div class="form-group">
                <label>Factura Origen *</label>
                <select name="factura_id" class="form-control" required>
                    <option value="">Seleccione una factura...</option>
                    @foreach ($facturas as $f)
                        <option value="{{ $f->id }}">
                            {{ $f->tipo_comprobante }} {{ $f->numero }} - {{ $f->cliente->razon_social }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- FECHA --}}
            <div class="form-group">
                <label>Fecha Emisión *</label>
                <input type="date" name="fecha_emision" class="form-control" required value="{{ date('Y-m-d') }}">
            </div>

            {{-- ITEMS --}}
            <h4>Ítems</h4>

            <table class="table table-bordered" id="items-table">
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th>Cant</th>
                        <th>Precio</th>
                        <th>IVA %</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>

            <button type="button" id="add-item" class="btn btn-secondary mb-3">Agregar Ítem</button>

            {{-- TOTAL --}}
            <div class="form-group">
                <label>Total</label>
                <input type="text" id="total" class="form-control" readonly>
            </div>

            <button class="btn btn-success float-right">Guardar Nota de Débito</button>

        </form>
    </div>
</div>

@stop

@section('js')
<script>
    function calcularTotales() {
        let total = 0;
        document.querySelectorAll('.subtotal').forEach(function(e){
            total += parseFloat(e.value || 0);
        });
        document.getElementById('total').value = total.toFixed(2);
    }

    document.getElementById('add-item').addEventListener('click', function() {
        let row = `
            <tr>
                <td><input type="text" name="items[][descripcion]" class="form-control" required></td>
                <td><input type="number" name="items[][cantidad]" class="form-control qty" value="1" min="1"></td>
                <td><input type="number" name="items[][precio_unitario]" class="form-control price" value="0" step="0.01"></td>
                <td><input type="number" name="items[][iva]" class="form-control iva" value="21"></td>
                <td><input type="text" class="form-control subtotal" readonly></td>
                <td><button class="btn btn-danger btn-sm remove">X</button></td>
            </tr>
        `;
        document.querySelector('#items-table tbody').insertAdjacentHTML('beforeend', row);
    });

    document.addEventListener('input', function(e){
        if (e.target.classList.contains('qty') ||
            e.target.classList.contains('price') ||
            e.target.classList.contains('iva')) {

            let tr = e.target.closest('tr');
            let qty = tr.querySelector('.qty').value;
            let price = tr.querySelector('.price').value;
            let iva = tr.querySelector('.iva').value;

            let subtotal = qty * price * (1 + iva/100);
            tr.querySelector('.subtotal').value = subtotal.toFixed(2);

            calcularTotales();
        }
    });

    document.addEventListener('click', function(e){
        if (e.target.classList.contains('remove')) {
            e.preventDefault();
            e.target.closest('tr').remove();
            calcularTotales();
        }
    });
</script>
@stop
