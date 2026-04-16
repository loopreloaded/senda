<h3>Datos de Emision</h3>

{{-- ============================
     DATOS DEL COMPROBANTE
   ============================ --}}

<div class="row">

    {{-- ID FAC (Auto) --}}
    <div class="col-md-2">
        <label>ID FAC (#)</label>
        <input type="text" class="form-control" readonly placeholder="Auto"
            value="{{ isset($factura->id) ? 'FAC-' . $factura->id : 'FAC-' . ($nextId ?? '') }}">
    </div>

    {{-- Tipo de Comprobante --}}
    <div class="col-md-3">
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

    {{-- Motivo --}}
    <div class="col-md-4">
        <div class="form-group">
            <label for="motivo">Motivo de la Operación</label>
            <select name="motivo" id="motivo" class="form-control" required>
                <option value="particular" {{ old('motivo') == 'particular' ? 'selected' : '' }}>Particular</option>
                <option value="pedido" {{ old('motivo') == 'pedido' ? 'selected' : '' }}>Vinculado (Pedido)</option>
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
            <select name="moneda"
                    id="moneda"
                    class="form-control"
                    required
                    onchange="actualizarCampoDolar()">
                <option value="ARS" {{ old('moneda') == 'ARS' ? 'selected' : '' }}>Peso argentino</option>
                <option value="USD" {{ old('moneda') == 'USD' ? 'selected' : '' }}>USD billete</option>
                <option value="USD div" {{ old('moneda') == 'USD div' ? 'selected' : '' }}>USD divisa</option>
            </select>
        </div>
    </div>

    {{-- Tipo de cambio + botón refrescar --}}
    <div class="col-md-4" id="bloque-valor-dolar">
        <label for="valor_dolar">Tipo de Cambio (USD)</label>

        <div class="input-group">
            <input type="number"
                step="0.01"
                name="valor_dolar"
                id="valor_dolar"
                class="form-control"
                value="{{ old('valor_dolar', 1) }}">

            <button type="button" class="btn btn-info" id="btn-cargar-dolar">
                <i class="fa fa-sync"></i>
            </button>
        </div>

        <small class="text-muted">
            Chequear valor en <a href="https://www.bna.com.ar/Personas" target="_blank">BNA</a>
        </small>
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

            <div class="position-relative">
                <input type="text"
                    name="razon_social"
                    id="razon_social"
                    value="{{ old('razon_social') }}"
                    class="form-control"
                    autocomplete="off"
                    required>

                {{-- para guardar el id del cliente --}}
                <input type="hidden" name="cliente_id" id="cliente_id" value="{{ old('cliente_id') }}">

                {{-- dropdown sugerencias --}}
                <div id="dropdown-clientes"
                    class="list-group position-absolute w-100 shadow"
                    style="z-index: 9999; max-height: 240px; overflow-y: auto; display:none;">
                </div>
            </div>


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

                <option value="RI" {{ old('condicion_iva') == 'RI' ? 'selected' : '' }}>
                    IVA Responsable Inscripto
                </option>

                <option value="MT" {{ old('condicion_iva') == 'MT' ? 'selected' : '' }}>
                    Responsable Monotributo
                </option>

                <option value="MS" {{ old('condicion_iva') == 'MS' ? 'selected' : '' }}>
                    Monotributista Social
                </option>

                <option value="MTIP" {{ old('condicion_iva') == 'MTIP' ? 'selected' : '' }}>
                    Monotributista Trabajador Independiente Promovido
                </option>
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
    <div class="col-md-3">
        <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input type="email" name="email" id="email"
                   value="{{ old('email') }}"
                   class="form-control">
        </div>
    </div>

    {{-- Condición IIBB --}}
    <div class="col-md-3">
        <div class="form-group">
            <label for="condicion_iibb">Condición IIBB</label>
            <select name="condicion_iibb" id="condicion_iibb" class="form-control">
                <option value="">Seleccione...</option>
                <option value="L" {{ old('condicion_iibb') == 'L' ? 'selected' : '' }}>Local</option>
                <option value="CM" {{ old('condicion_iibb') == 'CM' ? 'selected' : '' }}>Convenio Multilateral</option>
                <option value="EX" {{ old('condicion_iibb') == 'EX' ? 'selected' : '' }}>Exento</option>
            </select>
            <input type="hidden" name="alicuota_iibb" id="alicuota_iibb" value="{{ old('alicuota_iibb', 0) }}">
        </div>
    </div>

</div>

<div id="section-remitos" style="display:none;">
    <div class="row mb-2">
        <div class="col-md-4">
            <select id="select-remito-ajax" class="form-control">
                <option value="">Buscar remito...</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-info btn-block" id="btn-vincular-remito">
                <i class="fas fa-link"></i> Vincular
            </button>
        </div>
    </div>

    <table class="table table-bordered" id="tabla-remitos">
        <thead>
            <tr>
                <th style="width: 150px;">ID REM</th>
                <th style="width: 180px;">Comprobante</th>
                <th style="width: 180px;">Fecha Remito</th>
                <th style="width: 60px;"></th>
            </tr>
        </thead>
        <tbody id="remitos-body">
            <!-- Aquí se insertan -->
        </tbody>
    </table>
</div>

<button type="button" class="btn btn-primary btn-sm" id="agregar-remito" style="display:none;">Agregar Remito Manual</button>

<br><br>

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
            <th style="width: 200px; display: none;" class="col-remito">Remito</th>
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
        <tr style="display:none;">
            <td class="text-end fw-bold">Percepción de IVA</td>

            <td>
                <input type="text"
                    id="percepcion_iva_detalle"
                    name="percepcion_iva_detalle"
                    class="form-control"
                    value="">
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
                    value="Percepcion IIBB CM">
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

    <!-- FILA 1 -->
    <div class="col-md-6">
        <div class="form-group">
            <label for="subtotal_sin_iva">Subtotal s/IVA</label>
            <input type="text"
                   id="subtotal_sin_iva"
                   name="subtotal_sin_iva"
                   class="form-control"
                   readonly>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="importe_total_otros_tributos">
                Subtotal Otros Tributos
            </label>
            <input type="text"
                   id="importe_total_otros_tributos"
                   name="importe_total_otros_tributos"
                   class="form-control"
                   readonly>
        </div>
    </div>

    <!-- FILA 2 -->
    <div class="col-md-6">
        <div class="form-group">
            <label for="subtotal_con_iva">Subtotal c/IVA</label>
            <input type="text"
                   id="subtotal_con_iva"
                   name="subtotal_con_iva"
                   class="form-control"
                   readonly>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="importe_total">Importe Total</label>
            <input type="text"
                   id="importe_total"
                   name="importe_total"
                   class="form-control"
                   readonly>
        </div>
    </div>

</div>

<input type="hidden" id="importe_total_items" name="importe_total_items" value="0">


{{--

============================
     SCRIPTS
   ============================

--}}

<script>
let fila = 0;
let filaRemito = 0;

function updateOperacionVisibility() {
    const motivo = document.getElementById('motivo').value;
    const sectionRemitos = document.getElementById('section-remitos');
    const btnAgregarItem = document.getElementById('agregar-item');
    const btnAgregarRemitoManual = document.getElementById('agregar-remito');
    const colRemitoElements = document.querySelectorAll('.col-remito');

    if (motivo === 'pedido') {
        if (sectionRemitos) sectionRemitos.style.display = 'block';
        if (btnAgregarItem) btnAgregarItem.style.display = 'none';
        if (btnAgregarRemitoManual) btnAgregarRemitoManual.style.display = 'inline-block';
        colRemitoElements.forEach(el => el.style.display = 'table-cell'); // Usar table-cell para TDs/THs
        // Excepto si es un contenedor diferente
        colRemitoElements.forEach(el => {
            if (el.tagName !== 'TD' && el.tagName !== 'TH') el.style.display = 'block';
        });
    } else {
        if (sectionRemitos) sectionRemitos.style.display = 'none';
        if (btnAgregarItem) btnAgregarItem.style.display = 'inline-block';
        if (btnAgregarRemitoManual) btnAgregarRemitoManual.style.display = 'none';
        colRemitoElements.forEach(el => el.style.display = 'none');
    }
}

// Escuchar cambios
document.getElementById('motivo').addEventListener('change', updateOperacionVisibility);

// Al cargar el DOM
document.addEventListener('DOMContentLoaded', updateOperacionVisibility);

// Por compatibilidad con AdminLTE/window load
window.addEventListener('load', updateOperacionVisibility);

/* ============================================================
   BÚSQUEDA DE REMITOS AJAX
   ============================================================ */
function cargarRemitosCliente() {
    const clienteId = document.getElementById('cliente_id').value;
    const selectRemito = document.getElementById('select-remito-ajax');

    if (!clienteId) return;

    const url = `{{ url('/api/remitos/cliente') }}/${clienteId}`;
    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(r => r.json())
        .then(data => {
            selectRemito.innerHTML = '<option value="">Seleccione un remito...</option>';
            data.forEach(rem => {
                const opt = document.createElement('option');
                opt.value = rem.id;
                opt.textContent = `REM ${rem.numero_remito} - ${rem.fecha}`;
                opt.dataset.json = JSON.stringify(rem);
                selectRemito.appendChild(opt);
            });

            // También poblar todos los selects de remito en las filas de la tabla
            document.querySelectorAll('.select-remito').forEach(sel => {
                poblarSelectRemitosDesdeData(sel, data);
            });
        });
}

function poblarSelectRemitosDesdeData(selectElement, remitos) {
    const selectedId = selectElement.getAttribute('data-selected');
    selectElement.innerHTML = '<option value="">(Ninguno)</option>';
    remitos.forEach(rem => {
        const isSelected = (rem.id == selectedId);
        const opt = new Option(`REM ${rem.numero_remito} - ${rem.fecha}`, rem.id, isSelected, isSelected);
        selectElement.appendChild(opt);
    });
}

async function poblarSelectRemitosCliente(selectElement, clienteId) {
    if (!clienteId) return;
    const url = `{{ url('/api/remitos/cliente') }}/${clienteId}`;
    const resp = await fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    if (resp.ok) {
        const remitos = await resp.json();
        poblarSelectRemitosDesdeData(selectElement, remitos);
    }
}

// Observar cambio de cliente para recargar remitos
const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
            cargarRemitosCliente();
        }
    });
});
observer.observe(document.getElementById('cliente_id'), { attributes: true });

/* ============================================================
   VINCULAR REMITO Y CARGAR ÍTEMS
   ============================================================ */
document.getElementById('btn-vincular-remito').addEventListener('click', function() {
    const select = document.getElementById('select-remito-ajax');
    if (!select.value) return;

    const opt = select.options[select.selectedIndex];
    const remito = JSON.parse(opt.dataset.json);

    // 1. Agregar a tabla remitos asociados
    const tbodyRemitos = document.getElementById('remitos-body');
    const trRemito = `
        <tr data-id="${remito.id}">
            <td>
                ${remito.id}
                <input type="hidden" name="remitos[]" value="${remito.id}">
            </td>
            <td>${remito.numero_remito}</td>
            <td>${remito.fecha}</td>
            <td><button type="button" class="btn btn-danger btn-sm eliminar-remito-asoc">&times;</button></td>
        </tr>
    `;
    tbodyRemitos.insertAdjacentHTML('beforeend', trRemito);

    // 2. Cargar sus ítems a la tabla de ítems de factura
    if (remito.items) {
        remito.items.forEach(item => {
            agregarItemDesdeRemito(item, remito.id);
        });
    }

    // Actualizar todos los combos de remitos en la tabla (para que incluyan el nuevo vinculado?)
    // O tal vez solo poblar el de las nuevas filas.

    // Quitar de las opciones para no repetir?
    opt.remove();
});

document.addEventListener('click', function(e) {
    if (e.target.matches('.eliminar-remito-asoc')) {
        e.target.closest('tr').remove();
        // Nota: esto no quita los ítems que se agregaron automáticamente. 
        // El usuario debería quitarlos manualmente o podríamos implementar lógica de limpieza.
    }
});

function agregarItemDesdeRemito(item, remitoId) {
    const tbody = document.getElementById('items-body');

    let nuevaFila = `
       <tr data-index="${fila}" data-remito-id="${remitoId}">
        <td>
            <input type="text" name="items[${fila}][codigo]" class="form-control item-codigo" value="${item.codigo || ''}" readonly>
        </td>
        <td>
            <input type="text" name="items[${fila}][descripcion]" class="form-control item-desc" value="${item.articulo}" readonly>
        </td>
        <td class="col-remito" style="display: none;">
            <select name="items[${fila}][remito_id]" class="form-control select-remito no-select2" data-selected="${remitoId}">
                <option value="">(Ninguno)</option>
            </select>
        </td>
        <td>
            <input type="number" name="items[${fila}][cantidad]" class="form-control item-cantidad" value="${item.cantidad}" min="0.01" step="0.01" required>
            <small class="text-muted">Orig: ${item.cantidad}</small>
        </td>
        <td>
            <select name="items[${fila}][unidad]" class="form-control">
                <option value="7" selected>unidades</option>
                <!-- mas opciones de unidad aca si es necesario -->
            </select>
        </td>
        <td>
            <input type="number" name="items[${fila}][precio]" class="form-control item-precio" min="0" step="0.01" required>
        </td>
        <td>
            <select name="items[${fila}][iva]" class="form-control item-iva">
                <option value="21" selected>21%</option>
                <option value="10.5">10.5%</option>
                <option value="27">27%</option>
                <option value="0">Exento</option>
            </select>
        </td>
        <td>
            <input type="number" name="items[${fila}][bonificacion_porcentaje]" class="form-control item-bonif" min="0" max="100" step="0.01" value="0">
        </td>
        <td>
            <input type="text" name="items[${fila}][bonificacion_importe]" class="form-control item-bonif-importe" readonly value="0.00">
        </td>
        <td>
            <input type="text" class="form-control item-subtotal" readonly>
            <input type="hidden" name="items[${fila}][subtotal_sin_iva]" class="subtotal-sin-iva-hidden">
            <input type="hidden" name="items[${fila}][subtotal_con_iva]" class="subtotal-con-iva-hidden">
            <input type="hidden" name="items[${fila}][remito_id]" value="${remitoId}">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm eliminar-item">&times;</button>
        </td>
    </tr>
    `;

    tbody.insertAdjacentHTML('beforeend', nuevaFila);

    // Poblar el combo de remitos para esta nueva fila
    const sel = tbody.lastElementChild.querySelector('.select-remito');
    poblarSelectRemitosCliente(sel, document.getElementById('cliente_id').value);

    fila++;
    actualizarCodigos();
    reindexarItems();
    recalcular();
    updateOperacionVisibility(); // Asegurar visibilidad de la nueva fila
}


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

        <!-- Remito Source -->
        <td class="col-remito" style="display: none;">
            <select name="items[${fila}][remito_id]" class="form-control select-remito no-select2">
                <option value="">(Ninguno)</option>
            </select>
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
    
    // Poblar el combo de remitos para esta nueva fila
    const sel = tbody.lastElementChild.querySelector('.select-remito');
    poblarSelectRemitosCliente(sel, document.getElementById('cliente_id').value);

    fila++;
    actualizarCodigos();
    reindexarItems();

    setTimeout(() => {
        recalcular();
        updateOperacionVisibility(); // Asegurar visibilidad de la nueva fila
    }, 50);
});

/* ============================================================

   ============================================================ */
function recalcularBaseImpIIBB() {
    let totalBase = 0;

    document.querySelectorAll('#items-body tr').forEach(row => {
        const cantidad = parseFloat(row.querySelector('.item-cantidad')?.value) || 0;
        const precio   = parseFloat(row.querySelector('.item-precio')?.value) || 0;
        const bonifPct = parseFloat(row.querySelector('.item-bonif')?.value) || 0;

        const subtotal = cantidad * precio;
        const descuento = subtotal * (bonifPct / 100);
        const totalItem = subtotal - descuento;

        totalBase += totalItem;
    });

    // 🔥 AHORA VA DIRECTO A percepcion_iibb_base
    const inputBaseIibb = document.getElementById('percepcion_iibb_base');
    if (inputBaseIibb) {
        inputBaseIibb.value = totalBase.toFixed(3);

        // recalcular percepción automáticamente
        inputBaseIibb.dispatchEvent(new Event('input', { bubbles: true }));
    }
}

document.addEventListener('input', function (e) {
    if (
        e.target.classList.contains('item-cantidad') ||
        e.target.classList.contains('item-precio') ||
        e.target.classList.contains('item-bonif')
    ) {
        recalcularBaseImpIIBB();
    }
});

// =============
    // Cargar cotización dólar mayorista desde API
    // =============
    document.getElementById('btn-cargar-dolar').addEventListener('click', function () {
        const moneda = document.getElementById('moneda').value;
        const subId  = (moneda === 'USD billete') ? 'blue' : 'oficial';

        fetch("https://api.bluelytics.com.ar/v2/latest")
            .then(r => r.json())
            .then(data => {
                if (data[subId]?.value_sell){
                    document.getElementById('valor_dolar').value = data[subId].value_sell;
                } else {
                    alert("No se pudo obtener el valor del dólar.");
                }
            })
            .catch(e => alert("Error consultando API"));
    });
/* ============================================================
   RECALCULAR IMPORTES
   ============================================================ */
function recalcular() {

    const filas = document.querySelectorAll('#items-body tr');

    let totalSinIva = 0;
    let totalConIva = 0;

    filas.forEach(fila => {

        const cantidad  = parseFloat(fila.querySelector('.item-cantidad')?.value) || 0;
        const precio    = parseFloat(fila.querySelector('.item-precio')?.value) || 0;
        const bonifPorc = parseFloat(fila.querySelector('.item-bonif')?.value) || 0;
        const iva       = parseFloat(fila.querySelector('.item-iva')?.value) || 0;

        // ===== BONIFICACIÓN =====
        const bonifUnit = precio * (bonifPorc / 100);
        const precioFinalUnit = precio - bonifUnit;

        // ===== SUBTOTALES =====
        const subtotalSinIva = cantidad * precioFinalUnit;
        const ivaImporte     = subtotalSinIva * (iva / 100);
        const subtotalConIva = subtotalSinIva + ivaImporte;

        // ===== ACUMULAR TOTALES =====
        if (!isNaN(subtotalSinIva)) {
            totalSinIva += subtotalSinIva;
        }

        if (!isNaN(subtotalConIva)) {
            totalConIva += subtotalConIva;
        }

        // ===== ASIGNAR A INPUTS DE LA FILA =====
        const bonifInput = fila.querySelector('.item-bonif-importe');
        if (bonifInput) {
            bonifInput.value = bonifUnit.toFixed(3);
        }

        const subtotalInput = fila.querySelector('.item-subtotal');
        if (subtotalInput) {
            subtotalInput.value = subtotalConIva.toFixed(3);
        }

        const sinIvaHidden = fila.querySelector('.subtotal-sin-iva-hidden');
        if (sinIvaHidden) {
            sinIvaHidden.value = subtotalSinIva.toFixed(3);
        }

        const conIvaHidden = fila.querySelector('.subtotal-con-iva-hidden');
        if (conIvaHidden) {
            conIvaHidden.value = subtotalConIva.toFixed(3);
        }
    });

    // ===== SUBTOTALES GENERALES =====
    const sinIvaInput = document.getElementById('subtotal_sin_iva');
    if (sinIvaInput) {
        sinIvaInput.value = totalSinIva.toFixed(3);
    }

    const conIvaInput = document.getElementById('subtotal_con_iva');
    if (conIvaInput) {
        conIvaInput.value = totalConIva.toFixed(3);
    }

    // ===== TOTAL ÍTEMS (CON IVA) – INPUT HIDDEN YA EXISTENTE =====
    const totalItemsInput = document.getElementById('importe_total_items');
    if (totalItemsInput) {
        totalItemsInput.value = totalConIva.toFixed(3);
    }

    // ===== BASE IIBB =====
    recalcularBaseImpIIBB();

    // ===== TOTAL FINAL =====
    recalcularImporteTotalFinal();
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
        importeInput.value = importe.toFixed(3);
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

    const iva  = parseFloat(document.getElementById('percepcion_iva_importe')?.value) || 0;
    const iibb = parseFloat(document.getElementById('percepcion_iibb_importe')?.value) || 0;

    const total = iva + iibb;

    const totalInput = document.getElementById('importe_total_otros_tributos');
    if (totalInput) {
        totalInput.value = total.toFixed(3);
    }

    // 🔑 IMPORTANTE
    recalcularImporteTotalFinal();
}


function recalcularImporteTotalFinal() {
    console.log('recalcularImporteTotalFinal EJECUTADA');

    const totalItems = parseFloat(
        document.getElementById('importe_total_items')?.value
    ) || 0;

    const otrosTributos = parseFloat(
        document.getElementById('importe_total_otros_tributos')?.value
    ) || 0;

    console.log('otrosTributos ', otrosTributos);
    console.log('totalItems ', totalItems);


    const totalFinal = totalItems + otrosTributos;

    console.log('TtotalFinal ', totalFinal);

    const totalInput = document.getElementById('importe_total');


    if (totalInput) {
        totalInput.value = totalFinal.toFixed(3);
    }
}



function actualizarCampoDolar() {

    const moneda = document.getElementById('moneda');
    const bloque = document.getElementById('bloque-valor-dolar');
    const input  = document.getElementById('valor_dolar');

    if (!moneda || !bloque || !input) {
        console.warn('No se encontró moneda o campo dólar');
        return;
    }

    if (moneda.value === 'USD') {
        bloque.style.display = 'block';
        input.disabled = false;
        input.required = true;

        if (!input.value || input.value <= 0) {
            input.value = 1;
        }

    } else {
        bloque.style.display = 'none';
        input.disabled = true;
        input.required = false;
        input.value = 1;
    }
}

//
document.addEventListener('DOMContentLoaded', actualizarCampoDolar);


</script>

<script>
// Actualiza las opciones de `condicion_iva` según `tipo_comprobante`

function actualizarCondicionIva(useOld = false) {
    const tipo = document.getElementById('tipo_comprobante');
    const select = document.getElementById('condicion_iva');
    if (!tipo || !select) return;

    // FACTURA A
    const opcionesA = [
        { value: '', text: 'Seleccione...' },
        { value: 'RI', text: 'IVA Responsable Inscripto' },
        { value: 'MT', text: 'Responsable Monotributo' },
        { value: 'MS', text: 'Monotributista Social' },
        { value: 'MTIP', text: 'Monotributista Trabajador Independiente Promovido' }
    ];

    // FACTURA B
    const opcionesB = [
        { value: '', text: 'Seleccione...' },
        { value: 'EX', text: 'IVA Sujeto Exento' },
        { value: 'CF', text: 'Consumidor Final' },
        { value: 'NC', text: 'Sujeto No Categorizado' },
        { value: 'PE', text: 'Proveedor del Exterior' },
        { value: 'CE', text: 'Cliente del Exterior' },
        { value: 'IL', text: 'IVA Liberado - Ley N° 19.640' },
        { value: 'NA', text: 'IVA No Alcanzado' }
    ];

    // elegir conjunto según tipo de comprobante
    const conjunto = (tipo.value === 'B') ? opcionesB : opcionesA;

    // recuperar old() de Laravel si se solicita
    const oldCond = useOld ? @json(old('condicion_iva')) : null;

    // limpiar select
    select.innerHTML = '';

    // cargar opciones
    conjunto.forEach(opt => {
        const option = document.createElement('option');
        option.value = opt.value;
        option.textContent = opt.text;
        select.appendChild(option);
    });

    // restaurar valor anterior si existe
    if (oldCond) {
        for (let i = 0; i < select.options.length; i++) {
            if (select.options[i].value === oldCond) {
                select.selectedIndex = i;
                break;
            }
        }
    }
}


// Inicializar en carga y al cambiar tipo de comprobante
document.addEventListener('DOMContentLoaded', function () {
    actualizarCondicionIva(true);
    const tipo = document.getElementById('tipo_comprobante');
    if (tipo) {
        tipo.addEventListener('change', function () {
            actualizarCondicionIva(false);
        });
    }

    // Si Select2 está presente, también enganchar a sus eventos via jQuery
    if (window.jQuery && tipo) {
        try {
            const $tipo = jQuery(tipo);
            if ($tipo.data('select2') || $tipo.hasClass('select2-hidden-accessible')) {
                $tipo.on('select2:select select2:unselect', function (e) {
                    actualizarCondicionIva(false);
                });
                $tipo.on('change', function () {
                    actualizarCondicionIva(false);
                });
            }
        } catch (err) {
            console.warn('Error attaching select2 listeners', err);
        }
    }
});

// Listener delegado para detectar cambios si el elemento es reemplazado dinámicamente
document.addEventListener('change', function (e) {
    if (e.target && e.target.id === 'tipo_comprobante') {
        actualizarCondicionIva(false);
    }
});

</script>

<script>
/* ============================================================
   AUTOCOMPLETADO CLIENTES (Razón Social)
   ============================================================ */

const inputRazon = document.getElementById('razon_social');
const dropdownClientes = document.getElementById('dropdown-clientes');

const inputClienteId = document.getElementById('cliente_id');
const inputCuit = document.getElementById('cuit');
const selectCondIva = document.getElementById('condicion_iva');
const inputDireccion = document.getElementById('direccion');
const inputEmail = document.getElementById('email');

let debounceTimer = null;

function limpiarClienteSeleccionado() {
    inputClienteId.value = '';
}

function ocultarDropdown() {
    dropdownClientes.style.display = 'none';
    dropdownClientes.innerHTML = '';
}

function mostrarDropdown() {
    dropdownClientes.style.display = 'block';
}

function renderSugerencias(clientes) {
    dropdownClientes.innerHTML = '';

    if (!clientes.length) {
        dropdownClientes.innerHTML = `<div class="list-group-item text-muted">Sin resultados</div>`;
        mostrarDropdown();
        return;
    }

    clientes.forEach(cli => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'list-group-item list-group-item-action';

        item.innerHTML = `
            <div class="d-flex justify-content-between">
                <strong>${cli.razon_social ?? ''}</strong>
                <small class="text-muted">${cli.cuit ?? ''}</small>
            </div>
            <small class="text-muted">${cli.direccion ?? ''}</small>
        `;

        item.addEventListener('click', () => {
            // completar campos
            inputRazon.value = cli.razon_social ?? '';
            inputClienteId.value = cli.id ?? '';
            inputClienteId.dispatchEvent(new Event('change'));

            // Cargar remitos explícitamente al seleccionar cliente
            cargarRemitosCliente();

            inputCuit.value = cli.cuit ?? '';
            inputDireccion.value = cli.direccion ?? '';
            inputEmail.value = cli.email ?? '';

            // ============================
            // 👉 SETEAR IVA
            // ============================
            if (cli.condicion_iva) {
                if (selectCondIva) {
                    selectCondIva.value = cli.condicion_iva;
                    selectCondIva.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            // ============================
            // 👉 SETEAR IIBB Y ALÍCUOTA
            // ============================
            if (cli.condicion_iibb) {
                const selectCondIibb = document.getElementById('condicion_iibb');
                if (selectCondIibb) {
                    // Mapear según sea necesario o usar el código directo
                    selectCondIibb.value = cli.condicion_iibb_codigo || cli.condicion_iibb; 
                }
            }

            if (cli.indice !== null && cli.indice !== undefined) {

                const alicuotaIIBB = document.getElementById('percepcion_iibb_alicuota');
                const alicuotaIva = document.getElementById('percepcion_iva_alicuota');

                if (alicuotaIIBB) {
                    alicuotaIIBB.value = cli.indice;
                    // Disparar evento para que calcule el importe automáticamente
                    alicuotaIIBB.dispatchEvent(new Event('input', { bubbles: true }));
                }

                if (alicuotaIva) {
                    alicuotaIva.value = cli.indice;
                }
            }

            ocultarDropdown();
        });


        dropdownClientes.appendChild(item);
    });

    mostrarDropdown();
}

async function buscarClientes(q) {
    const url = `{{ route('clientes.buscar') }}?q=${encodeURIComponent(q)}`;

    const resp = await fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    if (!resp.ok) return [];

    return await resp.json();
}

inputRazon.addEventListener('input', () => {
    limpiarClienteSeleccionado();

    const q = inputRazon.value.trim();

    if (q.length < 2) {
        ocultarDropdown();
        return;
    }

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(async () => {
        try {
            const clientes = await buscarClientes(q);
            renderSugerencias(clientes);
        } catch (e) {
            console.error('Error buscando clientes:', e);
            ocultarDropdown();
        }
    }, 250);
});

// ocultar cuando haces click afuera
document.addEventListener('click', (e) => {
    const dentro = dropdownClientes.contains(e.target) || inputRazon.contains(e.target);
    if (!dentro) ocultarDropdown();
});

// opcional: ESC para cerrar
inputRazon.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') ocultarDropdown();
});
</script>
