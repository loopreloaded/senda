<div class="row">
    <div class="col-md-3">
        <label>Número de OC</label>
        <input type="number" name="numero_oc" class="form-control" value="{{ old('numero_oc', $orden->numero_oc ?? '') }}" required>
    </div>

    <div class="col-md-3">
        <label>Fecha</label>
        <input type="date" name="fecha" class="form-control" value="{{ old('fecha', $orden->fecha ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label>Proveedor</label>
        <input type="text" name="proveedor" class="form-control" value="{{ old('proveedor', $orden->proveedor ?? '') }}" required>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-3">
        <label>CUIT</label>
        <input type="text" name="cuit" class="form-control" maxlength="11" value="{{ old('cuit', $orden->cuit ?? '') }}" required>
    </div>

    <div class="col-md-3">
        <label>Dirección</label>
        <input type="text" name="direccion" class="form-control" value="{{ old('direccion', $orden->direccion ?? '') }}">
    </div>

    <div class="col-md-3">
        <label>Teléfono</label>
        <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $orden->telefono ?? '') }}">
    </div>

    <div class="col-md-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $orden->email ?? '') }}">
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4">
        <label>Moneda</label>
        <input type="text" name="moneda" class="form-control" value="{{ old('moneda', $orden->moneda ?? 'ARS') }}" required>
    </div>

    <div class="col-md-4">
        <label>Condición de Compra</label>
        <input type="text" name="condicion_compra" class="form-control" value="{{ old('condicion_compra', $orden->condicion_compra ?? '') }}" required>
    </div>

    <div class="col-md-4">
        <label>Solicitud de Compra</label>
        <input type="text" name="solicitud_compra" class="form-control" value="{{ old('solicitud_compra', $orden->solicitud_compra ?? '') }}">
    </div>
</div>

<hr class="mt-4">

<h4>Ítems</h4>

<table class="table table-bordered mt-2">
    <thead>
        <tr>
            <th>Código</th>
            <th>Descripción</th>
            <th>Cantidad</th>
            <th>Unidad</th>
            <th>Precio Unitario</th>
            <th>Descuento</th>
            <th>Fecha Entrega</th>
            <th>Total</th>
            <th></th>
        </tr>
    </thead>

    <tbody id="items-table">
        <tr>
            <td><input type="text" name="items[0][codigo]" class="form-control"></td>
            <td><input type="text" name="items[0][descripcion]" class="form-control"></td>
            <td><input type="number" step="0.01" name="items[0][cantidad]" class="form-control"></td>
            <td><input type="text" name="items[0][unidad]" class="form-control"></td>
            <td><input type="number" step="0.01" name="items[0][precio_unitario]" class="form-control"></td>
            <td><input type="number" step="0.01" name="items[0][descuento]" class="form-control" value="0"></td>
            <td><input type="date" name="items[0][fecha_entrega]" class="form-control"></td>
            <td><input type="number" step="0.01" name="items[0][total]" class="form-control item-total" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
        </tr>
    </tbody>
</table>


<button type="button" id="add-row" class="btn btn-primary btn-sm">Agregar Ítem</button>

<hr class="mt-4">

<div class="row mt-3">
    <div class="col-md-12">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="3">{{ old('observaciones', $orden->observaciones ?? '') }}</textarea>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6">
        <label>Subtotal</label>
        <input type="number" step="0.01" name="subtotal" class="form-control" value="{{ old('subtotal', $orden->subtotal ?? 0) }}" readonly>
    </div>

    <div class="col-md-6">
        <label>Total General</label>
        <input type="number" step="0.01" name="total" class="form-control" value="{{ old('total', $orden->total ?? 0) }}" readonly>
    </div>
</div>


<script>
    let row = {{ isset($orden) ? count($orden->items) : 1 }};

    document.getElementById('add-row').addEventListener('click', function() {
        var table = document.getElementById('items-table');

        var newRow = ''
        + '<tr>'
        + '  <td><input type="text" name="items['+row+'][codigo]" class="form-control"></td>'
        + '  <td><input type="text" name="items['+row+'][descripcion]" class="form-control"></td>'
        + '  <td><input type="number" step="0.01" name="items['+row+'][cantidad]" class="form-control"></td>'
        + '  <td><input type="text" name="items['+row+'][unidad]" class="form-control"></td>'
        + '  <td><input type="number" step="0.01" name="items['+row+'][precio_unitario]" class="form-control"></td>'
        + '  <td><input type="number" step="0.01" name="items['+row+'][descuento]" class="form-control" value="0"></td>'
        + '  <td><input type="date" name="items['+row+'][fecha_entrega]" class="form-control"></td>'
        + '  <td><input type="number" step="0.01" name="items['+row+'][total]" class="form-control item-total" readonly></td>'
        + '  <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>'
        + '</tr>';

        table.insertAdjacentHTML('beforeend', newRow);
        row++;

        calcularTotales();
    });

    // Eliminar fila
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('tr').remove();
            calcularTotales();
        }
    });

    document.addEventListener('input', function(e) {
        if (!e.target.name) return;

        if (
            e.target.name.indexOf('[cantidad]') !== -1 ||
            e.target.name.indexOf('[precio_unitario]') !== -1 ||
            e.target.name.indexOf('[descuento]') !== -1
        ) {
            calcularTotales();
        }
    });

    function calcularTotales() {
        var filas = document.querySelectorAll('#items-table tr');
        var subtotal = 0;
        var totalGeneral = 0;

        filas.forEach(function(row) {
            var inputCantidad  = row.querySelector('input[name*="[cantidad]"]');
            var inputPrecio    = row.querySelector('input[name*="[precio_unitario]"]');
            var inputDescuento = row.querySelector('input[name*="[descuento]"]');
            var inputTotal     = row.querySelector('input[name*="[total]"]');

            if (!inputCantidad || !inputPrecio || !inputTotal) {
                return;
            }

            var cantidad  = parseFloat(inputCantidad.value)  || 0;
            var precio    = parseFloat(inputPrecio.value)    || 0;
            var descuento = inputDescuento ? (parseFloat(inputDescuento.value) || 0) : 0;

            var totalSinDesc = cantidad * precio;
            var totalConDesc = totalSinDesc - descuento;

            inputTotal.value = totalConDesc.toFixed(2);

            subtotal     += totalSinDesc;
            totalGeneral += totalConDesc;
        });

        var inputSubtotal = document.querySelector('input[name="subtotal"]');
        var inputTotal    = document.querySelector('input[name="total"]');

        if (inputSubtotal) inputSubtotal.value = subtotal.toFixed(2);
        if (inputTotal)    inputTotal.value    = totalGeneral.toFixed(2);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', calcularTotales);
    } else {
        calcularTotales();
    }
</script>
