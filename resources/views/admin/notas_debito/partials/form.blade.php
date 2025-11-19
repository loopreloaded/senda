{{-- FACTURA ORIGEN --}}
<div class="form-group mb-3">
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
<div class="form-group mb-3">
    <label>Fecha Emisión *</label>
    <input type="date" name="fecha_emision" class="form-control" required value="{{ date('Y-m-d') }}">
</div>

{{-- ITEMS --}}
<h4 class="mt-4">Ítems</h4>

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
    <tbody></tbody>
</table>

<button type="button" id="add-item" class="btn btn-secondary mb-3">
    Agregar Ítem
</button>

{{-- TOTAL --}}
<div class="form-group">
    <label>Total</label>
    <input type="text" id="total" class="form-control" readonly>
</div>
