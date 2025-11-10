<div class="row">
    <div class="col-md-4">
        <label>Número de OC</label>
        <input type="text" name="numero_oc" class="form-control" value="{{ old('numero_oc') }}" required>
    </div>
    <div class="col-md-4">
        <label>Fecha</label>
        <input type="date" name="fecha" class="form-control" value="{{ old('fecha') }}" required>
    </div>
    <div class="col-md-4">
        <label>Proveedor</label>
        <input type="text" name="proveedor" class="form-control" value="{{ old('proveedor') }}" required>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4">
        <label>CUIT</label>
        <input type="text" name="cuit" class="form-control" maxlength="11" value="{{ old('cuit') }}" required>
    </div>
    <div class="col-md-4">
        <label>Moneda</label>
        <input type="text" name="moneda" class="form-control" value="{{ old('moneda', 'ARS') }}" required>
    </div>
    <div class="col-md-4">
        <label>Condición de Compra</label>
        <input type="text" name="condicion_compra" class="form-control" value="{{ old('condicion_compra') }}" required>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4">
        <label>Subtotal</label>
        <input type="number" step="0.01" name="subtotal" class="form-control" value="{{ old('subtotal') }}" required>
    </div>
    <div class="col-md-4">
        <label>Descuento</label>
        <input type="number" step="0.01" name="descuento" class="form-control" value="{{ old('descuento', 0) }}">
    </div>
    <div class="col-md-4">
        <label>Total</label>
        <input type="number" step="0.01" name="total" class="form-control" value="{{ old('total') }}" required>
    </div>
</div>
