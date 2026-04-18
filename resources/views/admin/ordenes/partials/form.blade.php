<div class="row">
    {{-- ID OC (Auto) --}}
    <div class="col-md-2">
        <label>ID OC (#)</label>
        <input type="text" class="form-control" readonly placeholder="Auto"
            value="{{ isset($orden->id) ? 'OC-' . $orden->id : 'OC-' . ($nextId ?? '') }}">
    </div>

    <div class="col-md-2">
        <label>Nro OC (Ext.)</label>
        <input type="number" name="numero_oc" class="form-control" value="{{ old('numero_oc', $orden->numero_oc ?? '') }}" required>
    </div>

    <div class="col-md-2">
        <label>Fecha</label>
        <input type="date"
            name="fecha"
            class="form-control"
            value="{{ old('fecha', isset($orden->fecha) ? $orden->fecha : now()->format('Y-m-d')) }}"
            required>
    </div>

    <div class="col-md-3">
        <label>Motivo</label>
        <select name="motivo" id="motivo" class="form-control" required>
            <option value="">Seleccionar...</option>
            <option value="pedido" {{ old('motivo', $orden->motivo ?? '') == 'pedido' ? 'selected' : '' }}>
                Pedido (Vincular a Cotizaciones)
            </option>
            <option value="particular" {{ old('motivo', $orden->motivo ?? '') == 'particular' ? 'selected' : '' }}>
                Particular (Sin Vínculos)
            </option>
        </select>
    </div>

    {{-- Razón Social --}}
    <div class="col-md-3">
        <div class="form-group">
            <label for="razon_social">Cliente</label>
            <div class="position-relative">
                <input type="text"
                    name="razon_social"
                    id="razon_social"
                    value="{{ old('razon_social', $orden->cliente->razon_social ?? '') }}"
                    class="form-control"
                    autocomplete="off"
                    required>

                <input type="hidden" name="id_cliente" id="id_cliente" value="{{ old('id_cliente', $orden->id_cliente ?? '') }}">

                <div id="dropdown-clientes"
                    class="list-group position-absolute w-100 shadow"
                    style="z-index:9999; max-height:240px; overflow-y:auto; display:none;">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-3">
        <label>CUIT</label>
        <input type="text" id="cuit" name="cuit" class="form-control" maxlength="11" value="{{ old('cuit', $orden->cuit ?? '') }}" required>
    </div>

    <div class="col-md-3">
        <label>Dirección</label>
        <input type="text" id="direccion" name="direccion" class="form-control" value="{{ old('direccion', $orden->direccion ?? '') }}">
    </div>

    <div class="col-md-3">
        <label>Teléfono</label>
        <input type="text" id="telefono" name="telefono" class="form-control" value="{{ old('telefono', $orden->telefono ?? '') }}">
    </div>

    <div class="col-md-3">
        <label>Email</label>
        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $orden->email ?? '') }}">
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6">
        <label>Moneda</label>
        <select name="moneda" class="form-control" required>
            <option value="ARS" {{ old('moneda', $orden->moneda ?? 'ARS') == 'ARS' ? 'selected' : '' }}>ARS - Peso Argentino</option>
            <option value="USD_BILLETE" {{ old('moneda', $orden->moneda ?? '') == 'USD_BILLETE' ? 'selected' : '' }}>USD - Billete</option>
            <option value="USD_DIVISA" {{ old('moneda', $orden->moneda ?? '') == 'USD_DIVISA' ? 'selected' : '' }}>USD - Divisa</option>
        </select>
    </div>

    <div class="col-md-6">
        <label>Condición de compra</label>
        <input type="text" name="condicion_compra" class="form-control" value="{{ old('condicion_compra', $orden->condicion_compra ?? '') }}" required>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4">
        <label>Archivo OC (PDF / Imagen)</label>
        <input type="file" name="archivo" class="form-control" accept=".pdf,.png,.jpg,.jpeg">
        @if(isset($orden) && $orden->archivo)
            <small class="text-muted d-block mt-1">
                Archivo actual: <a href="{{ asset('storage/'.$orden->archivo) }}" target="_blank">Ver archivo</a>
            </small>
        @endif
    </div>
</div>



<style>
    .celda-bloqueada {
        opacity: 0.5;
        pointer-events: none;
        background-color: #f8f9fa;
    }
</style>

<hr class="mt-4">

<h4>Detalle de Ítems (General)</h4>
<table class="table table-bordered mt-2">
    <thead>
        <tr>
            <th>Código</th>
            <th>Descripción</th>
            <th>Cotización</th>
            <th>Cantidad</th>
            <th style="width:15%;">Unidad</th>
            <th>Precio Unitario</th>
            <th>IVA (%)</th>
            <th>Desc. (%)</th>
            <th>Fecha Entrega</th>
            <th>Total</th>
            <th style="width:40px;"></th>
        </tr>
    </thead>
    <tbody id="items-table">
        @php
            $oldItems = old('items');
            $items = $oldItems ?? ($orden->items ?? [ [] ]);
        @endphp
        @foreach($items as $i => $item)
            <tr>
                <td><input type="text" name="items[{{ $i }}][codigo]" class="form-control" value="{{ $item['codigo'] ?? '' }}"></td>
                <td><input type="text" name="items[{{ $i }}][descripcion]" class="form-control" value="{{ $item['descripcion'] ?? '' }}"></td>
                @php
                    $id_cot_item = is_object($item) ? $item->id_cotizacion_item : ($item['id_cotizacion_item'] ?? null);
                    $nro_cot = '';
                    if (is_object($item) && $item->cotizacionItem) {
                        $nro_cot = $item->cotizacionItem->id_cotizacion;
                    }
                @endphp
                <td class="col-cotizacion">
                    <input type="hidden" name="items[{{ $i }}][id_cotizacion_item]" class="hidden-id-item" value="{{ $id_cot_item }}">
                    <input type="hidden" name="items[{{ $i }}][id_cotizacion]" class="hidden-id-cot" value="{{ is_object($item) ? $item->id_cotizacion : ($item['id_cotizacion'] ?? '') }}">
                    
                    <select class="form-control form-control-sm select-cotizacion no-select2" 
                        data-selected="{{ $id_cot_item }}"
                        style="display: {{ old('motivo', $orden->motivo ?? '') == 'pedido' ? 'block' : 'none' }};">
                        <option value="">(Ninguno)</option>
                    </select>

                    <span class="text-muted small badge-no-cot" style="display: {{ old('motivo', $orden->motivo ?? '') == 'pedido' ? 'none' : 'inline' }};">-</span>
                </td>
                <td><input type="number" step="0.01" name="items[{{ $i }}][cantidad]" class="form-control" value="{{ $item['cantidad'] ?? '' }}"></td>
                <td>
                    <select name="items[{{ $i }}][unidad]" class="form-control">
                        <option value="">seleccionar...</option>
                        @php $un = $item['unidad'] ?? ''; @endphp
                        <option value="1" {{ $un == 1 ? 'selected' : '' }}>kg</option>
                        <option value="7" {{ $un == 7 ? 'selected' : '' }}>unidades</option>
                    </select>
                </td>
                <td><input type="number" step="0.01" name="items[{{ $i }}][precio_unitario]" class="form-control" value="{{ $item['precio_unitario'] ?? '' }}"></td>
                <td><input type="number" step="0.01" name="items[{{ $i }}][iva]" class="form-control" value="{{ $item['iva'] ?? 21 }}"></td>
                <td><input type="number" step="0.01" name="items[{{ $i }}][descuento]" class="form-control" value="{{ $item['descuento'] ?? 0 }}"></td>
                <td><input type="date" name="items[{{ $i }}][fecha_entrega]" class="form-control" value="{{ $item['fecha_entrega'] ?? '' }}"></td>
                <td><input type="number" step="0.01" name="items[{{ $i }}][total]" class="form-control item-total" value="{{ $item['total'] ?? '' }}" readonly></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
            </tr>
        @endforeach
    </tbody>
</table>
<button type="button" id="add-row" class="btn btn-primary btn-sm">Agregar Ítem</button>

<div class="row mt-4">
    <div class="col-md-9">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="3">{{ old('observaciones', $orden->observaciones ?? '') }}</textarea>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Subtotal c/IVA</label>
            <input type="number" step="0.01" name="subtotal" class="form-control" value="{{ old('subtotal', $orden->subtotal ?? 0) }}" readonly>
        </div>
        <div class="form-group">
            <label>Total Final</label>
            <input type="number" step="0.01" name="total" class="form-control" value="{{ old('total', $orden->total ?? 0) }}" readonly>
        </div>
    </div>
</div>

<script>
    let rowIdx = {{ count($items) }};

    let allCotItems = []; // Almacén global para ítems de cotizaciones del cliente

    // --- MOTIVO LOGIC ---
    function actualizarEstadoCotizaciones() {
        const motivo = document.getElementById('motivo').value;
        const seccionVinculos = document.getElementById('seccion-vinculos');
        const selectCots = document.querySelectorAll('.select-cotizacion');
        const badgeNoCots = document.querySelectorAll('.badge-no-cot');
        const isPedido = (motivo === 'pedido');

        if (seccionVinculos) seccionVinculos.style.display = isPedido ? 'block' : 'none';

        selectCots.forEach(sel => {
            const $sel = $(sel);
            const td = sel.closest('td');

            if (!isPedido) {
                $sel.val("").trigger('change');
                if (td) td.classList.add('celda-bloqueada');
                sel.style.display = 'none';
            } else {
                if (td) td.classList.remove('celda-bloqueada');
                sel.style.display = 'block';
            }

            sel.required = isPedido;
            if ($sel.hasClass('select2-hidden-accessible')) {
                $sel.prop('disabled', !isPedido);
            }
        });

        badgeNoCots.forEach(b => {
            b.style.display = isPedido ? 'none' : 'inline';
        });
    }
    document.getElementById('motivo').addEventListener('change', actualizarEstadoCotizaciones);

    // --- CLIENTE AUTOCOMPLETE & COTIZACIONES ---
    const inputRazon = document.getElementById('razon_social');
    const inputClienteId = document.getElementById('id_cliente');
    const dropdownClientes = document.getElementById('dropdown-clientes');

    inputRazon.addEventListener('input', debounce(async function() {
        const q = inputRazon.value.trim();
        if (q.length < 2) { dropdownClientes.style.display = 'none'; return; }
        const resp = await fetch(`{{ route('clientes.buscar') }}?q=${q}`);
        const clientes = await resp.json();
        dropdownClientes.innerHTML = '';
        clientes.forEach(cli => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'list-group-item list-group-item-action';
            btn.innerHTML = `${cli.razon_social} <small class="text-muted">(${cli.cuit})</small>`;
            btn.onclick = () => selectCliente(cli);
            dropdownClientes.appendChild(btn);
        });
        dropdownClientes.style.display = 'block';
    }, 300));

    function selectCliente(cli) {
        inputRazon.value = cli.razon_social;
        inputClienteId.value = cli.id;
        document.getElementById('cuit').value = cli.cuit || '';
        document.getElementById('direccion').value = cli.direccion || '';
        document.getElementById('telefono').value = cli.telefono || '';
        document.getElementById('email').value = cli.email || '';
        dropdownClientes.style.display = 'none';
        cargarCotizaciones(cli.id);
    }

    async function cargarCotizaciones(clienteId) {
        const resp = await fetch(`{{ route('cotizaciones.buscar') }}?cliente_id=${clienteId}`);
        const cots = await resp.json();
        
        allCotItems = [];
        
        for (const c of cots) {
            
            const respItems = await fetch(`{{ url('cotizaciones') }}/${c.id_cotizacion}/json-items`);
            if (respItems.ok) {
                const items = await respItems.json();
                items.forEach(it => {
                    allCotItems.push({
                        ...it,
                        nro_cot: c.nro_cotizacion || c.id_cotizacion,
                        id_cot: c.id_cotizacion
                    });
                });
            }
        }

        // Poblar todos los combos de la tabla
        const selectors = document.querySelectorAll('.select-cotizacion');
        for (const sel of selectors) {
            await poblarSelectCotizacionRow(sel);
        }
    }

    async function poblarSelectCotizacionRow(selectElement) {
        const $select = $(selectElement);
        const selectedId = $select.attr('data-selected');

        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }

        selectElement.innerHTML = '<option value="">(Ninguno)</option>';
        allCotItems.forEach(it => {
            const isSelected = (it.id_cot_item == selectedId);
            const text = `Cot #${it.nro_cot} - ${it.producto}`;
            const opt = new Option(text, it.id_cot_item, isSelected, isSelected);
            opt.dataset.id_cot = it.id_cot;
            selectElement.appendChild(opt);
        });

        $select.select2({
            placeholder: '(Ninguno)',
            allowClear: true,
            width: '100%'
        });
        
        actualizarEstadoCotizaciones();
    }

    document.addEventListener('change', e => {
        if (e.target.classList.contains('select-cotizacion')) {
            const row = e.target.closest('tr');
            const hiddenIdItem = row.querySelector('.hidden-id-item');
            const hiddenIdCot = row.querySelector('.hidden-id-cot');
            const idItem = e.target.value;
            
            hiddenIdItem.value = idItem;

            if (idItem) {
                const it = allCotItems.find(x => x.id_cot_item == idItem);
                if (it) {
                    hiddenIdCot.value = it.id_cot;
                    row.querySelector('input[name*="[descripcion]"]').value = it.producto;
                    row.querySelector('input[name*="[precio_unitario]"]').value = it.precio_unitario;
                    row.querySelector('input[name*="[iva]"]').value = it.iva;
                    calcularTotales();
                }
            } else {
                hiddenIdCot.value = '';
            }
        }
    });



    function agregarItemGeneral(descripcion, cantidad, idCotItem = null, idCot = null, precio = 0, iva = 21) {
        // Buscar si ya existe un item con esa descripción y VÍNCULO para sumar
        const rows = document.querySelectorAll('#items-table tr');
        let found = false;
        rows.forEach(r => {
            const descInput = r.querySelector('input[name*="[descripcion]"]');
            const cotInput = r.querySelector('input[name*="[id_cotizacion_item]"]');
            if (descInput && descInput.value === descripcion && cotInput && cotInput.value == (idCotItem || '')) {
                const cantInput = r.querySelector('input[name*="[cantidad]"]');
                cantInput.value = (parseFloat(cantInput.value) || 0) + parseFloat(cantidad);
                found = true;
            }
        });

        if (!found) {
            const table = document.getElementById('items-table');
            const newRow = `
                <tr>
                    <td><input type="text" name="items[${rowIdx}][codigo]" class="form-control"></td>
                    <td><input type="text" name="items[${rowIdx}][descripcion]" class="form-control" value="${descripcion}"></td>
                    <td>
                        <input type="hidden" name="items[${rowIdx}][id_cotizacion_item]" class="hidden-id-item" value="${idCotItem || ''}">
                        <input type="hidden" name="items[${rowIdx}][id_cotizacion]" class="hidden-id-cot" value="${idCot || ''}">
                        <select class="form-control form-control-sm select-cotizacion no-select2" 
                            data-selected="${idCotItem || ''}"
                            style="display: ${document.getElementById('motivo').value === 'pedido' ? 'block' : 'none'};">
                            <option value="">(Ninguno)</option>
                        </select>
                        <span class="text-muted small badge-no-cot" style="display: ${document.getElementById('motivo').value === 'pedido' ? 'none' : 'inline'};">-</span>
                    </td>
                    <td><input type="number" step="0.01" name="items[${rowIdx}][cantidad]" class="form-control" value="${cantidad}"></td>
                    <td><select name="items[${rowIdx}][unidad]" class="form-control"><option value="7" selected>unidades</option></select></td>
                    <td><input type="number" step="0.01" name="items[${rowIdx}][precio_unitario]" class="form-control" value="${precio}"></td>
                    <td><input type="number" step="0.01" name="items[${rowIdx}][iva]" class="form-control" value="${iva}"></td>
                    <td><input type="number" step="0.01" name="items[${rowIdx}][descuento]" class="form-control" value="0"></td>
                    <td><input type="date" name="items[${rowIdx}][fecha_entrega]" class="form-control"></td>
                    <td><input type="number" step="0.01" name="items[${rowIdx}][total]" class="form-control item-total" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
                </tr>
            `;
            table.insertAdjacentHTML('beforeend', newRow);
            const newSel = table.lastElementChild.querySelector('.select-cotizacion');
            rowIdx++;
            poblarSelectCotizacionRow(newSel);
        }
        calcularTotales();
    }

    // --- ITEMS GENERAL ---
    document.getElementById('add-row').addEventListener('click', function() {
        const table = document.getElementById('items-table');
        const newRow = `
            <tr>
                <td><input type="text" name="items[${rowIdx}][codigo]" class="form-control"></td>
                <td><input type="text" name="items[${rowIdx}][descripcion]" class="form-control"></td>
                <td>
                    <input type="hidden" name="items[${rowIdx}][id_cotizacion_item]" class="hidden-id-item" value="">
                    <input type="hidden" name="items[${rowIdx}][id_cotizacion]" class="hidden-id-cot" value="">
                    <select class="form-control form-control-sm select-cotizacion no-select2" 
                        data-selected=""
                        style="display: ${document.getElementById('motivo').value === 'pedido' ? 'block' : 'none'};">
                        <option value="">(Ninguno)</option>
                    </select>
                    <span class="text-muted small badge-no-cot" style="display: ${document.getElementById('motivo').value === 'pedido' ? 'none' : 'inline'};">-</span>
                </td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][cantidad]" class="form-control"></td>
                <td><select name="items[${rowIdx}][unidad]" class="form-control"><option value="7">unidades</option></select></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][precio_unitario]" class="form-control"></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][iva]" class="form-control" value="21"></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][descuento]" class="form-control" value="0"></td>
                <td><input type="date" name="items[${rowIdx}][fecha_entrega]" class="form-control"></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][total]" class="form-control item-total" readonly></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
            </tr>
        `;
        table.insertAdjacentHTML('beforeend', newRow);
        const newSel = table.lastElementChild.querySelector('.select-cotizacion');
        rowIdx++;
        poblarSelectCotizacionRow(newSel);
        calcularTotales();
    });

    document.addEventListener('click', e => { if (e.target.classList.contains('remove-row')) { e.target.closest('tr').remove(); calcularTotales(); } });
    document.addEventListener('input', e => { if (e.target.name && e.target.name.includes('items[')) calcularTotales(); });

    function calcularTotales() {
        let subtotal = 0; let total = 0;
        document.querySelectorAll('#items-table tr').forEach(r => {
            const cant = parseFloat(r.querySelector('[name*="[cantidad]"]').value) || 0;
            const prec = parseFloat(r.querySelector('[name*="[precio_unitario]"]').value) || 0;
            const iva = parseFloat(r.querySelector('[name*="[iva]"]').value) || 0;
            const desc = parseFloat(r.querySelector('[name*="[descuento]"]').value) || 0;

            const base = cant * prec;
            const conIva = base * (1 + (iva/100));
            const itemTotal = conIva * (1 - (desc/100));

            r.querySelector('.item-total').value = itemTotal.toFixed(2);
            subtotal += conIva;
            total += itemTotal;
        });
        document.querySelector('[name="subtotal"]').value = subtotal.toFixed(2);
        document.querySelector('[name="total"]').value = total.toFixed(2);
    }

    function debounce(func, wait) { let timeout; return function(...args) { clearTimeout(timeout); timeout = setTimeout(() => func.apply(this, args), wait); }; }

    window.onload = async () => {
        actualizarEstadoCotizaciones();
        const cid = inputClienteId.value;
        if (cid) await cargarCotizaciones(cid);
        calcularTotales();
        
        // Forzar visibilidad si hay vínculos previos (Edición)
        if ({{ isset($orden) ? $orden->items->whereNotNull('id_cotizacion')->count() : 0 }} > 0) {
            document.getElementById('motivo').value = 'pedido';
            actualizarEstadoCotizaciones();
        }
    };

    // Validación y sincronización de valores al enviar el formulario
    const orderForm = document.getElementById('motivo').closest('form');
    orderForm.addEventListener('submit', function (e) {
        const motivo = document.getElementById('motivo').value;
        
        // PASO 1: Siempre sincronizar los valores del select al hidden antes de enviar
        document.querySelectorAll('.select-cotizacion').forEach(sel => {
            const row = sel.closest('tr');
            if (!row) return;
            const hiddenIdItem = row.querySelector('.hidden-id-item');
            const hiddenIdCot = row.querySelector('.hidden-id-cot');
            const idItem = sel.value;

            if (hiddenIdItem) hiddenIdItem.value = idItem;

            if (idItem) {
                const it = allCotItems.find(x => x.id_cot_item == idItem);
                if (it && hiddenIdCot) {
                    hiddenIdCot.value = it.id_cot;
                }
            } else {
                if (hiddenIdCot) hiddenIdCot.value = '';
            }
        });

        // PASO 2: Validar que todos los ítems estén vinculados si el motivo es Pedido
        if (motivo === 'pedido') {
            const selects = document.querySelectorAll('.select-cotizacion');
            let valid = true;
            selects.forEach(sel => {
                if (!sel.value) {
                    valid = false;
                    const $sel = $(sel);
                    if ($sel.hasClass('select2-hidden-accessible')) {
                        $sel.next('.select2-container').css('border', '2px solid red');
                    } else {
                        sel.style.border = '2px solid red';
                    }
                } else {
                    const $sel = $(sel);
                    if ($sel.hasClass('select2-hidden-accessible')) {
                        $sel.next('.select2-container').css('border', '');
                    } else {
                        sel.style.border = '';
                    }
                }
            });

            if (!valid) {
                e.preventDefault();
                alert('Debe vincular cada ítem a una cotización cuando el motivo es "Pedido"');
            }
        }
    });
</script>
