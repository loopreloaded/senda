<div class="row">

    {{-- Tipo de Comprobante --}}
    <div class="col-md-4">
        <div class="form-group">
            <label for="tipo_comprobante">Tipo de Comprobante</label>
            <select class="form-control" name="tipo_comprobante" id="tipo_comprobante" required>
                <option value="A">Factura A</option>
                <option value="B">Factura B</option>
            </select>
        </div>
    </div>

    {{-- Punto de Venta --}}
    <div class="col-md-4">
        <div class="form-group">
            <label for="punto_venta">Punto de Venta</label>
            <input type="number" name="punto_venta" id="punto_venta" class="form-control"
                   value="{{ old('punto_venta', 1) }}" required>
        </div>
    </div>

    {{-- Fecha de Emisión --}}
    <div class="col-md-4">
        <div class="form-group">
            <label for="fecha_emision">Fecha de Emisión</label>
            <input type="date" name="fecha_emision" id="fecha_emision" class="form-control"
                   value="{{ old('fecha_emision', date('Y-m-d')) }}" required>
        </div>
    </div>

</div>

<div class="row">

    {{-- Concepto (AFIP) --}}
    <div class="col-md-4">
        <div class="form-group">
            <label for="concepto_afip">Concepto</label>
            <select name="concepto_afip" id="concepto_afip" class="form-control">
                <option value="1">Productos</option>
                <option value="2">Servicios</option>
                <option value="3">Productos y Servicios</option>
            </select>
        </div>
    </div>

    {{-- Condición de Venta --}}
    <div class="col-md-4">
        <div class="form-group">
            <label for="condicion_venta">Condición de Venta</label>
            <select class="form-control" name="condicion_venta" id="condicion_venta">
                <option value="contado">Contado</option>
                <option value="cta_cte">Cuenta Corriente</option>
                <option value="tarjeta">Tarjeta de Crédito</option>
                <option value="transferencia">Transferencia Bancaria</option>
            </select>
        </div>
    </div>

    {{-- Cliente --}}
    <div class="col-md-4">
        <div class="form-group">
            <label for="cliente_id">Cliente</label>
            <select name="cliente_id" id="cliente_id" class="form-control sel2" required>
                <option value="">Seleccione un cliente</option>
                @foreach ($clientes as $cliente)
                    <option value="{{ $cliente->id }}">
                        {{ $cliente->razon_social }} ({{ $cliente->cuit }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

</div>

<hr>

{{-- Ítems --}}
<h4>Ítems / Servicios</h4>

<div id="items-container">

    <div class="item-row border p-3 mb-3">

        <div class="row">

            {{-- Descripción --}}
            <div class="col-md-6">
                <label>Descripción</label>
                <input type="text" name="items[0][descripcion]" class="form-control" required>
            </div>

            {{-- Cantidad --}}
            <div class="col-md-2">
                <label>Cantidad</label>
                <input type="number" step="0.01" min="0.01" name="items[0][cantidad]" class="form-control item-cantidad" required>
            </div>

            {{-- Precio Unitario --}}
            <div class="col-md-2">
                <label>Precio Unitario</label>
                <input type="number" step="0.01" name="items[0][precio]" class="form-control item-precio" required>
            </div>

            {{-- IVA --}}
            <div class="col-md-2">
                <label>Alicuota IVA</label>
                <select class="form-control item-iva" name="items[0][iva]">
                    <option value="0">0% (Exento)</option>
                    <option value="10.5">10.5%</option>
                    <option value="21" selected>21%</option>
                    <option value="27">27%</option>
                </select>
            </div>

        </div>

    </div>

</div>

<button type="button" class="btn btn-primary mb-3" id="add-item-btn">
    Agregar Ítem
</button>

<hr>

{{-- Total --}}
<div class="row">
    <div class="col-md-4 offset-md-8">
        <div class="form-group">
            <label for="total_factura">Importe Total</label>
            <input type="number" step="0.01" readonly class="form-control" id="total_factura" name="total_factura">
        </div>
    </div>
</div>

{{-- Script para agregar ítems --}}
@section('js')
<script>
let itemIndex = 1;

$('#add-item-btn').click(function() {
    let newItem = `
    <div class="item-row border p-3 mb-3">

        <div class="row">

            <div class="col-md-6">
                <label>Descripción</label>
                <input type="text" name="items[${itemIndex}][descripcion]" class="form-control" required>
            </div>

            <div class="col-md-2">
                <label>Cantidad</label>
                <input type="number" step="0.01" min="0.01" name="items[${itemIndex}][cantidad]"
                    class="form-control item-cantidad" required>
            </div>

            <div class="col-md-2">
                <label>Precio Unitario</label>
                <input type="number" step="0.01" name="items[${itemIndex}][precio]"
                    class="form-control item-precio" required>
            </div>

            <div class="col-md-2">
                <label>Alicuota IVA</label>
                <select class="form-control item-iva" name="items[${itemIndex}][iva]">
                    <option value="0">0%</option>
                    <option value="10.5">10.5%</option>
                    <option value="21" selected>21%</option>
                    <option value="27">27%</option>
                </select>
            </div>

        </div>

    </div>`;

    $('#items-container').append(newItem);
    itemIndex++;
});

// Cálculo automático del total
$(document).on('input', '.item-cantidad, .item-precio, .item-iva', function() {
    let total = 0;

    $('.item-row').each(function() {
        let cantidad = parseFloat($(this).find('.item-cantidad').val()) || 0;
        let precio = parseFloat($(this).find('.item-precio').val()) || 0;
        let iva = parseFloat($(this).find('.item-iva').val()) || 0;

        let subtotal = cantidad * precio;
        subtotal += subtotal * (iva / 100);

        total += subtotal;
    });

    $('#total_factura').val(total.toFixed(2));
});
</script>
@endsection
