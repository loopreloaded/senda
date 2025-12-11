<h3>Datos del Emisor</h3>

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


    {{-- Fecha de Recibo --}}
    <div class="col-md-4">
        <div class="form-group">
            <label for="fecha_emision">Fecha Facturacion</label>
            <input type="date" name="fecha_emision" id="fecha_emision"
                   value="{{ old('fecha_emision', date('Y-m-d')) }}"
                   class="form-control" required>
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

        </div>
        <small class="text-muted">Chequear valor en <a href="https://www.bna.com.ar/Personas" target="_blank">BNA</a></small>
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
            <input type="text" class="form-control" value="otra" disabled>

            <input type="hidden" name="condicion_venta" value="otra">
        </div>
    </div>


</div>


{{-- ============================
     CAMPOS PARA SERVICIOS
   ============================ --}}
<div class="row mt-3" id="bloque-servicios" style="display: none;">

    <div class="col-md-4">
        <div class="form-group">
            <label for="fecha_desde">Fecha Desde</label>
            <input type="date" name="fecha_desde" id="fecha_desde"
                   class="form-control" value="{{ old('fecha_desde') }}">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="fecha_hasta">Fecha Hasta</label>
            <input type="date" name="fecha_hasta" id="fecha_hasta"
                   class="form-control" value="{{ old('fecha_hasta') }}">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="vencimiento_pago">Vencimiento de Pago</label>
            <input type="date" name="vencimiento_pago" id="vencimiento_pago"
                   class="form-control" value="{{ old('vencimiento_pago') }}">
        </div>
    </div>

</div>


{{-- ============================
     REMITOS ASOCIADOS
   ============================ --}}
<h4 class="mt-4">Remitos Asociados</h4>

<table class="table table-bordered" id="tabla-remitos">
    <thead>
        <tr>
            <th style="width: 150px;">Pto. Venta</th>
            <th style="width: 180px;">Comprobante</th>
            <th style="width: 180px;">Fecha Remito</th>
            <th style="width: 60px;"></th>
        </tr>
    </thead>

    <tbody id="remitos-body">
        <!-- Aquí se insertan -->
    </tbody>
</table>

<button type="button" class="btn btn-secondary btn-sm" id="agregar-remito">Agregar Remito</button>

<h3>Datos del Receptor</h3>

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


<h3>Datos de la operacion</h3>

{{-- ============================
     ÍTEMS / SERVICIOS DETALLADOS
   ============================ --}}

<h4 class="mt-4">Ítems / Servicios</h4>

<table class="table table-bordered" id="tabla-items">
    <thead>
        <tr>
            <th style="width: 100px;">Código</th>
            <th>Descripción</th>
            <th style="width: 120px;">Cant.</th>
            <th style="width: 140px;">Unidad</th>
            <th style="width: 150px;">Precio Unit.</th>
            <th style="width: 120px;">IVA (%)</th>
            <th style="width: 120px;">% Bonif.</th>
            <th style="width: 150px;">Subtotal c/IVA</th>
            <th style="width: 40px;"></th>
        </tr>
    </thead>

    <tbody id="items-body">
        <!-- Se cargan dinámicamente -->
    </tbody>
</table>

<button type="button" class="btn btn-primary btn-sm" id="agregar-item">Agregar Ítem</button>

{{-- ============================
     BONIFICACIONES / PERCEPCIONES
   ============================ --}}
<div class="row mt-3">

    {{-- Percepción de IVA --}}
    <div class="col-md-3">
        <div class="form-group">
            <label for="percepcion_iva">Percepción de IVA</label>
            <input type="number"
                   name="percepcion_iva"
                   id="percepcion_iva"
                   class="form-control"
                   step="0.01"
                   placeholder="0.00"
                   value="{{ old('percepcion_iva') }}">
        </div>
    </div>

    {{-- Percepción de Ingresos Brutos --}}
    <div class="col-md-3">
        <div class="form-group">
            <label for="percepcion_iibb">Percepción de Ingresos Brutos</label>
            <input type="number"
                   name="percepcion_iibb"
                   id="percepcion_iibb"
                   class="form-control"
                   step="0.01"
                   placeholder="0.00"
                   value="{{ old('percepcion_iibb') }}">
        </div>
    </div>

</div>


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

<script>
let fila = 0;
let filaRemito = 0;


/* ============================================================
   AGREGAR NUEVA FILA DE ÍTEM
   ============================================================ */
document.getElementById('agregar-item').addEventListener('click', function () {

    const tbody = document.getElementById('items-body');

    let nuevaFila = `
       <tr data-index="${fila}">

    <!-- Código -->
    <td>
        <input type="text"
               name="items[${fila}][codigo]"
               class="form-control item-codigo"
               readonly>
    </td>

    <!-- Descripción -->
    <td>
        <input type="text" name="items[${fila}][descripcion]" class="form-control item-desc" required>
    </td>

    <!-- Cantidad -->
    <td>
        <input type="number" name="items[${fila}][cantidad]"
               class="form-control item-cantidad"
               min="1" step="0.01" required>
    </td>

    <!-- Unidad -->
    <td>
        <select name="items[${fila}][unidad]" class="form-control item-unidad" required>
            <option value="">Sel...</option>
            <option value="7">Unidades</option>
            <option value="5">Litros</option>
            <option value="1">Kilogramos</option>
            <option value="2">Metros</option>
        </select>
    </td>

    <!-- Precio Unitario -->
    <td>
        <input type="number" name="items[${fila}][precio]"
               class="form-control item-precio"
               min="0" step="0.01" required>
    </td>

    <!-- IVA -->
    <td>
        <select name="items[${fila}][iva]" class="form-control item-iva">
            <option value="0">No Gravado</option>
            <option value="0">Exento</option>
            <option value="0">0%</option>
            <option value="2.5">2,5%</option>
            <option value="5">5%</option>
            <option value="10.5">10,5%</option>
            <option value="21" selected>21%</option>
            <option value="27">27%</option>
        </select>
    </td>

    <!-- % Bonificación -->
    <td>
        <input type="number" name="items[${fila}][bonificacion_porcentaje]"
               class="form-control item-bonif"
               min="0" max="100" step="0.01" value="0">
    </td>

    <!-- Subtotal c/iva + HIDDEN (LOS IMPORTANTES) -->
    <td>
        <input type="text" class="form-control item-subtotal" readonly>

        <input type="hidden" name="items[${fila}][bonificacion_importe]" class="bonif-importe-hidden">
        <input type="hidden" name="items[${fila}][subtotal_sin_iva]" class="subtotal-sin-iva-hidden">
        <input type="hidden" name="items[${fila}][subtotal_con_iva]" class="subtotal-con-iva-hidden">
    </td>



    <!-- Eliminar -->
    <td>
        <button type="button" class="btn btn-danger btn-sm eliminar-item">&times;</button>
    </td>
</tr>

    `;

    tbody.insertAdjacentHTML('beforeend', nuevaFila);
    fila++;
    actualizarCodigos();
    reindexarItems();

    // <<< NUEVO >>>
    setTimeout(() => {
        recalcular();
    }, 50);


});


/* ============================================================
   RECALCULAR IMPORTES
   ============================================================ */
function recalcular() {

    let totalFactura = 0;

    document.querySelectorAll('#tabla-items tbody tr').forEach(function (fila) {

        let cantidad = parseFloat(fila.querySelector('.item-cantidad').value) || 0;
        let precio   = parseFloat(fila.querySelector('.item-precio').value) || 0;

        let iva_raw  = fila.querySelector('.item-iva').value;

        // Convertir IVA: si es NG o EX => 0%
        let iva = (!iva_raw || isNaN(parseFloat(iva_raw))) ? 0 : parseFloat(iva_raw);

        let bonif    = parseFloat(fila.querySelector('.item-bonif').value) || 0;

        // =============================
        // CALCULOS
        // =============================

        // SUBTOTAL BRUTO
        let subtotal_bruto = cantidad * precio;

        // IMPORTE DE BONIFICACIÓN
        let bonif_importe = subtotal_bruto * (bonif / 100);

        // SUBTOTAL SIN IVA
        let subtotal_sin_iva = subtotal_bruto - bonif_importe;

        // IMPORTE IVA
        let iva_importe = subtotal_sin_iva * (iva / 100);

        // SUBTOTAL FINAL CON IVA
        let subtotal_con_iva = subtotal_sin_iva + iva_importe;

        // =============================
        // MOSTRAR EN TABLA
        // =============================
        fila.querySelector('.item-subtotal').value =
            subtotal_con_iva.toFixed(2);

        // =============================
        // GUARDAR EN LOS HIDDEN
        // =============================
        fila.querySelector('.bonif-importe-hidden').value =
            bonif_importe.toFixed(2);

        fila.querySelector('.subtotal-sin-iva-hidden').value =
            subtotal_sin_iva.toFixed(2);

        fila.querySelector('.subtotal-con-iva-hidden').value =
            subtotal_con_iva.toFixed(2); // ← FALTABA (era la causa del error)

        // =============================
        // ACUMULAR TOTAL FACTURA
        // =============================
        totalFactura += subtotal_con_iva;

    });

    // =============================
    // ACTUALIZAR TOTAL GENERAL
    // =============================
    let totalInput = document.getElementById('importe_total');
    if (totalInput) {
        totalInput.value = totalFactura.toFixed(2);
    }
}



/* ============================================================
   EVENTOS DE RECALCULAR Y ELIMINAR
   ============================================================ */
    document.addEventListener('input', function (e) {
        if (
            e.target.matches('.item-cantidad') ||
            e.target.matches('.item-precio') ||
            e.target.matches('.item-iva') ||
            e.target.matches('.item-bonif')
        ) {
            recalcular();
        }
    });

    document.addEventListener('click', function (e) {
        if (e.target.matches('.eliminar-item')) {
            e.target.closest('tr').remove();
            recalcular();
            actualizarCodigos();
            reindexarItems();
        }
    });


/* ============================================================
   GENERAR CÓDIGOS 1.1, 1.2, 1.3...
   ============================================================ */
function actualizarCodigos() {

    let filas = document.querySelectorAll('#tabla-items tbody tr');
    let grupo = 1;
    let sub = 1;

    filas.forEach(tr => {
        let codigo = `${grupo}.${sub}`;
        tr.querySelector('.item-codigo').value = codigo;
        sub++;
    });
}


/* ============================================================
   REINDEXAR items[x] DESPUÉS DE CAMBIOS
   ============================================================ */
function reindexarItems() {

    let filas = document.querySelectorAll('#tabla-items tbody tr');
    let index = 0;

    filas.forEach(tr => {

        tr.setAttribute("data-index", index);

        tr.querySelectorAll("input, select").forEach(input => {

            if (input.name && input.name.includes("items[")) {
                input.name = input.name.replace(/items\[\d+\]/, `items[${index}]`);
            }
        });

        index++;
    });

    actualizarCodigos();
}

function agregarFilaRemito(i, data = null) {

    const tbody = document.getElementById('remitos-body');

    let nuevaFila = `
        <tr data-index="${i}">
            <td>
                <input type="number" name="remitos[${i}][pto_venta]"
                       class="form-control"
                       min="1"
                       value="${data?.pto_venta ?? ''}">
            </td>

            <td>
                <input type="number" name="remitos[${i}][comprobante]"
                       class="form-control"
                       min="1"
                       value="${data?.comprobante ?? ''}">
            </td>

            <td>
                <input type="date" name="remitos[${i}][fecha_emision]"
                       class="form-control"
                       value="${data?.fecha_emision ?? ''}">
            </td>

            <td>
                <button type="button" class="btn btn-danger btn-sm eliminar-remito">&times;</button>
            </td>
        </tr>
    `;

    tbody.insertAdjacentHTML("beforeend", nuevaFila);
}

document.addEventListener("DOMContentLoaded", function () {

    const btnRemito = document.getElementById("agregar-remito");

    btnRemito.addEventListener("click", function () {
        agregarFilaRemito(filaRemito);
        filaRemito++;
    });
});

document.addEventListener("click", function (e) {
    if (e.target.matches(".eliminar-remito")) {
        e.target.closest("tr").remove();
    }
});

document.addEventListener("DOMContentLoaded", function () {

    let oldRemitos = @json(old('remitos'));

    if (oldRemitos && Object.keys(oldRemitos).length > 0) {

        filaRemito = 0;

        Object.keys(oldRemitos).forEach(idx => {
            agregarFilaRemito(filaRemito, oldRemitos[idx]);
            filaRemito++;
        });

    } else {

        agregarFilaRemito(0);
        filaRemito = 1;

    }
});

</script>
