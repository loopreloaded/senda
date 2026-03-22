@csrf

<div class="row">

    {{-- Número de Remito --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Número de Remito</label>
        <input type="text" name="numero_remito" class="form-control"
               value="{{ old('numero_remito', $remito->numero_remito ?? '') }}" required>
    </div>

    {{-- Cliente --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Cliente</label>
        <select name="id_cliente" class="form-control" required>
            <option value="">Seleccione cliente</option>
            @foreach($clientes as $cliente)
                <option value="{{ $cliente->id }}"
                    {{ old('id_cliente', $remito->id_cliente ?? '') == $cliente->id ? 'selected' : '' }}>
                    {{ $cliente->razon_social }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Fecha --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Fecha</label>
        <input type="date" name="fecha" class="form-control"
               value="{{ old('fecha', isset($remito->fecha) ? $remito->fecha->format('Y-m-d') : '') }}" required>
    </div>

    {{-- Condición de Venta --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Condición de Venta</label>
        <select name="condicion_venta" class="form-control" required>
            @foreach(['Cuenta corriente','Contado','Mixto','Anticipado','Transferencia diferida'] as $cond)
                <option value="{{ $cond }}"
                    {{ old('condicion_venta', $remito->condicion_venta ?? '') == $cond ? 'selected' : '' }}>
                    {{ $cond }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- OC Asociada --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">OC Asociada</label>
        <input type="text" name="id_orden_compra" class="form-control"
               value="{{ old('id_orden_compra', $remito->id_orden_compra ?? '') }}">
    </div>

    {{-- Factura --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Factura Relacionada</label>
        <input type="text" name="id_factura" class="form-control"
               value="{{ old('id_factura', $remito->id_factura ?? '') }}">
    </div>

    {{-- Estado --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Estado</label>
        <select name="estado" class="form-control" required>
            @foreach(['Emitido','Confirmado','Anulado'] as $estado)
                <option value="{{ $estado }}"
                    {{ old('estado', $remito->estado ?? '') == $estado ? 'selected' : '' }}>
                    {{ $estado }}
                </option>
            @endforeach
        </select>
    </div>

</div>

<hr>

{{-- ================== ITEMS ================== --}}
<h5>Detalles del Envío</h5>

<table class="table table-bordered" id="tabla-items">
    <thead>
        <tr>
            <th>Artículo</th>
            <th>Cantidad</th>
            <th>Descripción</th>
            <th width="50">Acción</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><input type="text" name="items[0][articulo]" class="form-control"></td>
            <td><input type="number" name="items[0][cantidad]" class="form-control"></td>
            <td><input type="text" name="items[0][descripcion]" class="form-control"></td>
            <td><button type="button" class="btn btn-danger btn-sm eliminar-fila">X</button></td>
        </tr>
    </tbody>
</table>

<button type="button" class="btn btn-success btn-sm" id="agregar-item">Agregar ítem</button>

<hr>

{{-- ================== FLETE ================== --}}
<h5>Flete</h5>

<div class="row">

    <div class="col-md-6 mb-3">
        <label>Transportista / Chofer</label>
        <input type="text" name="transportista" class="form-control"
               value="{{ old('transportista', $remito->transportista ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label>Domicilio</label>
        <input type="text" name="domicilio_transportista" class="form-control"
               value="{{ old('domicilio_transportista', $remito->domicilio_transportista ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label>IVA</label>
        <select name="iva_transportista" class="form-control">
            @foreach(['Responsable inscripto','Exento','No responsable','Consumidor final','Monotributista'] as $iva)
                <option value="{{ $iva }}">{{ $iva }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label>CUIT</label>
        <input type="text" name="cuit_transportista" class="form-control" maxlength="11">
    </div>

    <div class="col-md-4 mb-3">
        <label>Observación</label>
        <input type="text" name="observacion" class="form-control">
    </div>

</div>

<hr>

{{-- ================== CAI ================== --}}
<div class="row">

    <div class="col-md-6 mb-3">
        <label>CAI</label>
        <input type="text" name="cai" class="form-control">
    </div>

    <div class="col-md-6 mb-3">
        <label>Vencimiento CAI</label>
        <input type="date" name="cai_vto" class="form-control">
    </div>

</div>

{{-- ================== SCRIPT ITEMS ================== --}}
<script>
let index = 1;

document.getElementById('agregar-item').addEventListener('click', function () {
    let fila = `
        <tr>
            <td><input type="text" name="items[${index}][articulo]" class="form-control"></td>
            <td><input type="number" name="items[${index}][cantidad]" class="form-control"></td>
            <td><input type="text" name="items[${index}][descripcion]" class="form-control"></td>
            <td><button type="button" class="btn btn-danger btn-sm eliminar-fila">X</button></td>
        </tr>
    `;
    document.querySelector('#tabla-items tbody').insertAdjacentHTML('beforeend', fila);
    index++;
});

document.addEventListener('click', function(e){
    if(e.target.classList.contains('eliminar-fila')){
        e.target.closest('tr').remove();
    }
});
</script>
