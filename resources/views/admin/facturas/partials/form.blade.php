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

<button type="button" class="btn btn-primary btn-sm" id="agregar-remito">Agregar Remito</button>

<br><br>

<h3>Datos del Receptor</h3>

{{-- ============================
     DATOS DEL CLIENTE
   ============================ --}}

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
            <th style="width: 120px;">Bonif. Imp.</th>
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
     Otros tributos
   ============================ --}}
<h4 class="mt-4">Otros tributos</h5>

<table class="table table-bordered">
    <thead class="table-light">
        <tr>
            <th></th>
            <th>Detalle</th>
            <th style="width:180px;">Base Imponible</th>
            <th style="width:140px;">Alícuota (%)</th>
            <th style="width:180px;">Importe</th>
        </tr>
    </thead>
    <tbody>

        {{-- PERCEPCIÓN DE IVA --}}
        <tr>
            <td class="text-end fw-bold">Percepción de IVA</td>

            <td>
                <input type="text"
                    id="percepcion_iva_detalle"
                    name="percepcion_iva_detalle"
                    class="form-control"
                    value="Percepción de IVA">
            </td>
            <td>
                <input type="number"
                    id="percepcion_iva_base"
                    name="percepcion_iva_base"
                    class="form-control"
                    step="0.01" min="0">
            </td>
            <td>
                <input type="number"
                    id="percepcion_iva_alicuota"
                    name="percepcion_iva_alicuota"
                    class="form-control"
                    step="0.01" min="0">
            </td>
            <td>
                <input type="number"
                    id="percepcion_iva_importe"
                    name="percepcion_iva_importe"
                    class="form-control"
                    step="0.01" min="0" readonly>
            </td>
        </tr>

        {{-- PERCEPCIÓN DE INGRESOS BRUTOS --}}
        <tr>
            <td class="text-end fw-bold">Percepción de Ingresos Brutos</td>

            <td>
                <input type="text"
                    id="percepcion_iibb_detalle"
                    name="percepcion_iibb_detalle"
                    class="form-control"
                    value="Percepción de Ingresos Brutos">
            </td>
            <td>
                <input type="number"
                    id="percepcion_iibb_base"
                    name="percepcion_iibb_base"
                    class="form-control"
                    step="0.01" min="0">
            </td>
            <td>
                <input type="number"
                    id="percepcion_iibb_alicuota"
                    name="percepcion_iibb_alicuota"
                    class="form-control"
                    step="0.01" min="0">
            </td>
            <td>
                <input type="number"
                    id="percepcion_iibb_importe"
                    name="percepcion_iibb_importe"
                    class="form-control"
                    step="0.01" min="0" readonly>
            </td>
        </tr>

    </tbody>
</table>

{{-- ============================
     IMPORTE TOTAL (otros tributos)
   ============================ --}}
<div class="row mt-4">
    <div class="col-md-4 offset-md-8">
        <div class="form-group">
            <label for="importe_total_otros_tributos">Subtotal Otros Tributos</label>
            <input type="text" id="importe_total_otros_tributos" name="importe_total_otros_tributos"
                   class="form-control" readonly>
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
            <select name="items[${fila}][unidad]" class="form-control">
                <option value="">seleccionar...</option>
                <option value="1">kilogramos</option>
                <option value="2">metros</option>
                <option value="3">metros cuadrados</option>
                <option value="4">metros cúbicos</option>
                <option value="5">litros</option>
                <option value="6">1000 kWh</option>
                <option value="7">unidades</option>
                <option value="8">pares</option>
                <option value="9">docenas</option>
                <option value="10">quilates</option>
                <option value="11">millares</option>
                <option value="14">gramos</option>
                <option value="15">milímetros</option>
                <option value="16">mm cúbicos</option>
                <option value="17">kilómetros</option>
                <option value="18">hectolitros</option>
                <option value="20">centímetros</option>
                <option value="25">jgo. pqt. mazo naipes</option>
                <option value="27">cm cúbicos</option>
                <option value="29">toneladas</option>
                <option value="30">dam cúbicos</option>
                <option value="31">hm cúbicos</option>
                <option value="32">km cúbicos</option>
                <option value="33">microgramos</option>
                <option value="34">nanogramos</option>
                <option value="35">picogramos</option>
                <option value="41">miligramos</option>
                <option value="47">mililitros</option>
                <option value="48">curie</option>
                <option value="49">milicurie</option>
                <option value="50">microcurie</option>
                <option value="51">uiacthor</option>
                <option value="52">muiacthor</option>
                <option value="53">kg base</option>
                <option value="54">gruesa</option>
                <option value="61">kg bruto</option>
                <option value="62">uiactant</option>
                <option value="63">muiactant</option>
                <option value="64">uiactig</option>
                <option value="65">muiactig</option>
                <option value="66">kg activo</option>
                <option value="67">gramo activo</option>
                <option value="68">gramo base</option>
                <option value="96">packs</option>
                <option value="98">otras unidades</option>
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

        <!-- IMPORTE BONIFICACIÓN (NUEVO) -->
        <td>
            <input type="text"
                name="items[${fila}][bonificacion_importe]"
                class="form-control item-bonif-importe"
                readonly value="0.00">
        </td>

        <!-- Subtotal c/iva + HIDDEN -->
        <td>
            <input type="text" class="form-control item-subtotal" readonly>

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

    setTimeout(() => {
        recalcular();
    }, 50);
});


/* ============================================================
   RECALCULAR IMPORTES
   ============================================================ */
function recalcular() {

    const filas = document.querySelectorAll('#items-body tr');
    let importeTotal = 0;

    filas.forEach(fila => {

        const cantidad = parseFloat(fila.querySelector('.item-cantidad')?.value) || 0;
        const precio = parseFloat(fila.querySelector('.item-precio')?.value) || 0;
        const bonifPorc = parseFloat(fila.querySelector('.item-bonif')?.value) || 0;
        const iva = parseFloat(fila.querySelector('.item-iva')?.value) || 0;

        // ===== BONIFICACIÓN =====
        const bonifUnit = precio * (bonifPorc / 100);
        const precioFinalUnit = precio - bonifUnit;

        // ===== SUBTOTALES =====
        const subtotalSinIva = cantidad * precioFinalUnit;
        const ivaImporte = subtotalSinIva * (iva / 100);
        const subtotalConIva = subtotalSinIva + ivaImporte;

        // ===== ACUMULAR TOTAL =====
        if (!isNaN(subtotalConIva)) {
            importeTotal += subtotalConIva;
        }

        // ===== ASIGNAR A INPUTS (SEGURO) =====
        const bonifInput = fila.querySelector('.item-bonif-importe');
        if (bonifInput) {
            bonifInput.value = bonifUnit.toFixed(2);
        }

        const subtotalInput = fila.querySelector('.item-subtotal');
        if (subtotalInput) {
            subtotalInput.value = subtotalConIva.toFixed(2);
        }

        const bonifHidden = fila.querySelector('.bonif-importe-hidden');
        if (bonifHidden) {
            bonifHidden.value = bonifUnit.toFixed(2);
        }

        const sinIvaHidden = fila.querySelector('.subtotal-sin-iva-hidden');
        if (sinIvaHidden) {
            sinIvaHidden.value = subtotalSinIva.toFixed(2);
        }

        const conIvaHidden = fila.querySelector('.subtotal-con-iva-hidden');
        if (conIvaHidden) {
            conIvaHidden.value = subtotalConIva.toFixed(2);
        }
    });

    // ===== IMPORTE TOTAL FINAL =====
    const totalInput = document.getElementById('importe_total');
    if (totalInput) {
        totalInput.value = importeTotal.toFixed(2);
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

<script>

function calcularPercepcion(baseId, alicuotaId, importeId) {

    const base = parseFloat(document.getElementById(baseId)?.value) || 0;
    const alicuota = parseFloat(document.getElementById(alicuotaId)?.value) || 0;

    const importe = base * (alicuota / 100);

    const importeInput = document.getElementById(importeId);
    if (importeInput) {
        importeInput.value = importe.toFixed(2);
    }

    // 👉 recalcular total otros tributos
    calcularTotalOtrosTributos();
}


// Listeners Percepción IVA
document.getElementById('percepcion_iva_base')?.addEventListener('input', () => {
    calcularPercepcion(
        'percepcion_iva_base',
        'percepcion_iva_alicuota',
        'percepcion_iva_importe'
    );
});

document.getElementById('percepcion_iva_alicuota')?.addEventListener('input', () => {
    calcularPercepcion(
        'percepcion_iva_base',
        'percepcion_iva_alicuota',
        'percepcion_iva_importe'
    );
});

// Listeners Percepción IIBB
document.getElementById('percepcion_iibb_base')?.addEventListener('input', () => {
    calcularPercepcion(
        'percepcion_iibb_base',
        'percepcion_iibb_alicuota',
        'percepcion_iibb_importe'
    );
});

document.getElementById('percepcion_iibb_alicuota')?.addEventListener('input', () => {
    calcularPercepcion(
        'percepcion_iibb_base',
        'percepcion_iibb_alicuota',
        'percepcion_iibb_importe'
    );
});

function calcularTotalOtrosTributos() {

    const iva = parseFloat(document.getElementById('percepcion_iva_importe')?.value) || 0;
    const iibb = parseFloat(document.getElementById('percepcion_iibb_importe')?.value) || 0;

    const total = iva + iibb;

    console.log("total : ", total)

    const totalInput = document.getElementById('importe_total_otros_tributos');
    if (totalInput) {
        totalInput.value = total.toFixed(2);
    }
}
</script>
