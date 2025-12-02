{{-- ============================
     DATOS DEL COMPROBANTE
   ============================ --}}
<div class="row">

    {{-- Tipo de Comprobante --}}
    <div class="col-md-4">
        <div class="form-group">
            <label for="tipo_comprobante">Tipo de Comprobante</label>
            <select name="tipo_comprobante" id="tipo_comprobante" class="form-control" required>
                <option value="">Seleccione...</option>
                <option value="A" {{ old('tipo_comprobante') == 'A' ? 'selected' : '' }}>Factura A</option>
                <option value="B" {{ old('tipo_comprobante') == 'B' ? 'selected' : '' }}>Factura B</option>
            </select>
        </div>
    </div>


    {{-- Fecha de Emisión --}}
    <div class="col-md-4">
        <div class="form-group">
            <label for="fecha_emision">Fecha de Emisión</label>
            <input type="date" name="fecha_emision" id="fecha_emision"
                   value="{{ old('fecha_emision', date('Y-m-d')) }}"
                   class="form-control" required>
        </div>
    </div>

</div>

{{-- ============================
     DATOS DEL CLIENTE
   ============================ --}}
<h4 class="mt-4">Datos del Cliente</h4>

<div class="row">

    {{-- Razón Social --}}
    <div class="col-md-6">
        <div class="form-group">
            <label for="razon_social">Razón Social</label>
            <input type="text" name="razon_social" id="razon_social"
                   value="{{ old('razon_social') }}"
                   class="form-control" required>
        </div>
    </div>

    {{-- CUIT --}}
    <div class="col-md-3">
        <div class="form-group">
            <label for="cuit">CUIT</label>
            <input type="text" name="cuit" id="cuit"
                   value="{{ old('cuit') }}"
                   class="form-control" required pattern="\d{11}" maxlength="11"
                   placeholder="Solo números">
        </div>
    </div>

    {{-- Condición IVA --}}
    <div class="col-md-3">
        <div class="form-group">
            <label for="condicion_iva">Condición frente al IVA</label>
            <select name="condicion_iva" id="condicion_iva" class="form-control" required>
                <option value="">Seleccione...</option>
                <option value="RI" {{ old('condicion_iva') == 'RI' ? 'selected' : '' }}>Responsable Inscripto</option>
                <option value="MT" {{ old('condicion_iva') == 'MT' ? 'selected' : '' }}>Monotributista</option>
                <option value="CF" {{ old('condicion_iva') == 'CF' ? 'selected' : '' }}>Consumidor Final</option>
                <option value="EX" {{ old('condicion_iva') == 'EX' ? 'selected' : '' }}>Exento</option>
            </select>
        </div>
    </div>

</div>

<div class="row">

    {{-- Dirección --}}
    <div class="col-md-6">
        <div class="form-group">
            <label for="direccion">Dirección</label>
            <input type="text" name="direccion" id="direccion"
                   value="{{ old('direccion') }}"
                   class="form-control" required>
        </div>
    </div>

    {{-- Email --}}
    <div class="col-md-6">
        <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input type="email" name="email" id="email"
                   value="{{ old('email') }}"
                   class="form-control">
        </div>
    </div>

</div>

{{-- ============================
     CONCEPTO / CONDICIÓN DE VENTA
   ============================ --}}
<div class="row">

    {{-- Concepto --}}
    <div class="col-md-4">
        <div class="form-group">
            <label for="concepto">Concepto</label>
            <select name="concepto" id="concepto" class="form-control" required>
                <option value="">Seleccione...</option>
                <option value="1" {{ old('concepto') == 1 ? 'selected' : '' }}>Productos (Bienes)</option>
                <option value="2" {{ old('concepto') == 2 ? 'selected' : '' }}>Servicios</option>
                <option value="3" {{ old('concepto') == 3 ? 'selected' : '' }}>Ambos</option>
            </select>
        </div>
    </div>

    {{-- Condición de Venta --}}
    <div class="col-md-4">
        <div class="form-group">
            <label for="condicion_venta">Condición de Venta</label>
            <select name="condicion_venta" id="condicion_venta" class="form-control" required>
                <option value="">Seleccione...</option>
                <option value="contado" {{ old('condicion_venta') == 'contado' ? 'selected' : '' }}>Contado</option>
                <option value="cuenta_corriente" {{ old('condicion_venta') == 'cuenta_corriente' ? 'selected' : '' }}>Cuenta Corriente</option>
                <option value="tarjeta" {{ old('condicion_venta') == 'tarjeta' ? 'selected' : '' }}>Tarjeta de Crédito</option>
                <option value="transferencia" {{ old('condicion_venta') == 'transferencia' ? 'selected' : '' }}>Transferencia Bancaria</option>
            </select>
        </div>
    </div>

</div>
{{-- ============================
     MONEDA / TIPO DE CAMBIO USD
   ============================ --}}
<div class="row mt-3">

    {{-- Moneda --}}
    <div class="col-md-4">
        <div class="form-group">
            <label for="moneda">Moneda</label>
            <select name="moneda" id="moneda" class="form-control" required>
                <option value="ARS" {{ old('moneda') == 'ARS' ? 'selected' : '' }}>ARS (Pesos)</option>
                <option value="USD" {{ old('moneda') == 'USD' ? 'selected' : '' }}>USD (Dólares)</option>
            </select>
        </div>
    </div>

    {{-- Tipo de cambio + botón refrescar --}}
    <div class="col-md-4">
        <label for="valor_dolar">Tipo de Cambio (USD)</label>
        <div class="input-group">
            <input type="number" step="0.01" name="valor_dolar" id="valor_dolar"
                   class="form-control" value="{{ old('valor_dolar', 1) }}" required>

            <button type="button" class="btn btn-info" id="btn-cargar-dolar">
                <i class="fa fa-sync"></i>
            </button>
        </div>
        <small class="text-muted">Click para actualizar automáticamente desde API</small>
    </div>

</div>


{{-- ============================
     ÍTEMS / SERVICIOS DETALLADOS
   ============================ --}}

<h4 class="mt-4">Ítems / Servicios</h4>

<table class="table table-bordered" id="tabla-items">
    <thead>
    <tr>
        <th>Descripción</th>
        <th style="width: 120px;">Cant.</th>
        <th style="width: 150px;">Precio Unit.</th>
        <th style="width: 150px;">IVA</th>
        <th style="width: 120px;">Subtotal</th>
        <th style="width: 60px;"></th>
    </tr>
    </thead>

    <tbody>
    {{-- Fila inicial --}}
    <tr>
        <td>
            <input type="text" name="items[0][descripcion]" class="form-control" required>
        </td>
        <td>
            <input type="number" name="items[0][cantidad]" class="form-control item-cantidad" min="1" step="1" required>
        </td>
        <td>
            <input type="number" name="items[0][precio]" class="form-control item-precio" min="0" step="0.01" required>
        </td>
        <td>
            <select name="items[0][iva]" class="form-control item-iva">
                <option value="0">0% (Exento)</option>
                <option value="10.5">10,5%</option>
                <option value="21" selected>21%</option>
                <option value="27">27%</option>
            </select>
        </td>
        <td>
            <input type="text" class="form-control item-subtotal" readonly>
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm eliminar-item">&times;</button>
        </td>
    </tr>
    </tbody>
</table>

<button type="button" class="btn btn-primary btn-sm" id="agregar-item">Agregar Ítem</button>

{{-- ============================
     IMPORTE TOTAL
   ============================ --}}
<div class="row mt-4">
    <div class="col-md-4 offset-md-8">
        <div class="form-group">
            <label for="importe_total">Importe Total</label>
            <input type="text" id="importe_total" name="importe_total"
                   class="form-control" readonly>
        </div>
    </div>
</div>

{{-- ============================
     SCRIPTS JS PARA ÍTEMS
   ============================ --}}
<script>
    let fila = 1;

    // Agregar fila
    document.getElementById('agregar-item').addEventListener('click', function () {
        const tabla = document.querySelector('#tabla-items tbody');
        let nuevaFila = `
            <tr>
                <td><input type="text" name="items[${fila}][descripcion]" class="form-control" required></td>
                <td><input type="number" name="items[${fila}][cantidad]" class="form-control item-cantidad" min="1" step="1" required></td>
                <td><input type="number" name="items[${fila}][precio]" class="form-control item-precio" min="0" step="0.01" required></td>
                <td>
                    <select name="items[${fila}][iva]" class="form-control item-iva">
                        <option value="0">0%</option>
                        <option value="10.5">10,5%</option>
                        <option value="21" selected>21%</option>
                        <option value="27">27%</option>
                    </select>
                </td>
                <td><input type="text" class="form-control item-subtotal" readonly></td>
                <td><button type="button" class="btn btn-danger btn-sm eliminar-item">&times;</button></td>
            </tr>
        `;
        tabla.insertAdjacentHTML('beforeend', nuevaFila);
        fila++;
    });

    // Delegación de eventos
    document.addEventListener('input', function (e) {
        if (e.target.matches('.item-cantidad, .item-precio, .item-iva')) {
            recalcular();
        }
    });

    document.addEventListener('click', function (e) {
        if (e.target.matches('.eliminar-item')) {
            e.target.closest('tr').remove();
            recalcular();
        }
    });

    // Recalcular subtotales + total
    function recalcular() {
        let total = 0;
        document.querySelectorAll('#tabla-items tbody tr').forEach(function (fila) {
            let cantidad = parseFloat(fila.querySelector('.item-cantidad').value) || 0;
            let precio   = parseFloat(fila.querySelector('.item-precio').value) || 0;
            let iva      = parseFloat(fila.querySelector('.item-iva').value) || 0;

            let subtotal = cantidad * precio * (1 + iva / 100);
            fila.querySelector('.item-subtotal').value = subtotal.toFixed(2);

            total += subtotal;
        });
        document.getElementById('importe_total').value = total.toFixed(2);
    }

    // =============
    // Cargar cotización dólar mayorista desde API
    // =============
    document.getElementById('btn-cargar-dolar').addEventListener('click', function () {
        fetch("https://api.bluelytics.com.ar/v2/latest")
            .then(r => r.json())
            .then(data => {
                if (data?.oficial?.value_sell){
                    document.getElementById('valor_dolar').value = data.oficial.value_sell; // << CORRECTO
                } else {
                    alert("No se pudo obtener el dólar oficial");
                }
            })
            .catch(e => alert("Error consultando API"));
    });

</script>
