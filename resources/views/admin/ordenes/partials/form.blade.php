<div class="row">
    <div class="col-md-3">
        <label>Número de OC</label>
        <input type="number" name="numero_oc" class="form-control" value="{{ old('numero_oc') }}" required>
    </div>

    <div class="col-md-3">
        <label>Fecha</label>
        <input type="date" name="fecha" class="form-control" value="{{ old('fecha') }}" required>
    </div>

    <div class="col-md-6">
        <label>Proveedor</label>
        <input type="text" name="proveedor" class="form-control" value="{{ old('proveedor') }}" required>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-3">
        <label>CUIT</label>
        <input type="text" name="cuit" class="form-control" maxlength="11" value="{{ old('cuit') }}" required>
    </div>

    <div class="col-md-3">
        <label>Dirección</label>
        <input type="text" name="direccion" class="form-control" value="{{ old('direccion') }}">
    </div>

    <div class="col-md-3">
        <label>Teléfono</label>
        <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}">
    </div>

    <div class="col-md-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email') }}">
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4">
        <label>Moneda</label>
        <input type="text" name="moneda" class="form-control" value="{{ old('moneda', 'ARS') }}" required>
    </div>

    <div class="col-md-4">
        <label>Condición de Compra</label>
        <input type="text" name="condicion_compra" class="form-control" value="{{ old('condicion_compra') }}" required>
    </div>

    <div class="col-md-4">
        <label>Solicitud de Compra</label>
        <input type="text" name="solicitud_compra" class="form-control" value="{{ old('solicitud_compra') }}">
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
            <th>Total</th>
            <th></th>
        </tr>
    </thead>
    <tbody id="items-table">
        <tr>
            <td><input type="text" name="items[0][codigo]" class="form-control"></td>
            <td><input type="text" name="items[0][descripcion]" class="form-control"></td>
            <td><input type="number" step="0.01" name="items[0][cantidad]" class="form-control"></td>
            <td><input type="text" name="items[0][unidad]" class="form-control" placeholder="Ej: kg, u., mts"></td>
            <td><input type="number" step="0.01" name="items[0][precio_unitario]" class="form-control"></td>
            <td><input type="number" step="0.01" name="items[0][descuento]" class="form-control" value="0"></td>
            <td><input type="number" step="0.01" name="items[0][total]" class="form-control"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
        </tr>
    </tbody>
</table>

<button type="button" id="add-row" class="btn btn-primary btn-sm">Agregar Ítem</button>

<hr class="mt-4">

<div class="row mt-3">
    <div class="col-md-12">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="3">{{ old('observaciones') }}</textarea>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6">
        <label>Adjuntar PDF</label>
        <input type="file" name="adjunto_pdf" accept="application/pdf" class="form-control">
    </div>

    <div class="col-md-6">
        <label>Total General</label>
        <input type="number" step="0.01" name="total" class="form-control" value="{{ old('total') }}" required>
    </div>
</div>

<script>
let row = 1;

document.getElementById('add-row').addEventListener('click', function() {
    let table = document.getElementById('items-table');
    let newRow = `
        <tr>
            <td><input type="text" name="items[${row}][codigo]" class="form-control"></td>
            <td><input type="text" name="items[${row}][descripcion]" class="form-control"></td>
            <td><input type="number" step="0.01" name="items[${row}][cantidad]" class="form-control"></td>
            <td><input type="text" name="items[${row}][unidad]" class="form-control"></td>
            <td><input type="number" step="0.01" name="items[${row}][precio_unitario]" class="form-control"></td>
            <td><input type="number" step="0.01" name="items[${row}][descuento]" class="form-control" value="0"></td>
            <td><input type="number" step="0.01" name="items[${row}][total]" class="form-control"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
        </tr>
    `;
    table.insertAdjacentHTML('beforeend', newRow);
    row++;
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-row')) {
        e.target.closest('tr').remove();
    }
});
</script>
