<div class="row">

    {{-- ID Cotizacion (Auto) --}}
    <div class="col-md-2">
        <label>ID COT (#)</label>
        <input type="text" class="form-control" readonly placeholder="Auto"
            value="{{ isset($cotizacion) ? 'COT-' . $cotizacion->id_cotizacion : 'COT-' . ($nextId ?? '') }}">
    </div>

    {{-- Nro Cotizacion (Libre) --}}
    <div class="col-md-2">
        <label>Nro. Cot.</label>
        <input type="text" name="nro_cotizacion" class="form-control" placeholder="Nro. Interno"
            value="{{ old('nro_cotizacion', $cotizacion->nro_cotizacion ?? '') }}">
    </div>

    {{-- Fecha --}}
    <div class="col-md-2">
        <label>Fecha</label>
        <input type="date" name="fecha_cot" class="form-control" value="{{ old('fecha_cot', isset($cotizacion)
    ? optional($cotizacion->fecha_cot)->format('Y-m-d')
    : now()->format('Y-m-d')) }}" required>
    </div>

    {{-- Quien Cotiza --}}
    <div class="col-md-3">
        <label>Quién Cotiza</label>
        <input type="text" name="quien_cotiza" class="form-control" placeholder="Nombre"
            value="{{ old('quien_cotiza', $cotizacion->quien_cotiza ?? '') }}">
    </div>

    {{-- Cliente --}}
    <div class="col-md-3">

        <label>Cliente</label>

        <div class="position-relative">
            <input type="text" id="razon_social" class="form-control" autocomplete="off" value="{{ old(
    'razon_social',
    isset($cotizacion) && $cotizacion->cliente
    ? $cotizacion->cliente->razon_social . ' - ' . $cotizacion->cliente->cuit
    : ''
) }}" required>

            <input type="hidden" name="id_cliente" id="id_cliente"
                value="{{ old('id_cliente', $cotizacion->id_cliente ?? '') }}">

            <div id="dropdown-clientes" class="list-group position-absolute w-100 shadow"
                style="z-index:9999; max-height:240px; overflow-y:auto; display:none;">
            </div>
        </div>
    </div>

    {{-- Moneda --}}
    <div class="col-md-2">
        <label>Moneda</label>
        <select name="moneda" class="form-control" required>
            <option value="ARS" {{ old('moneda', $cotizacion->moneda ?? 'ARS') == 'ARS' ? 'selected' : '' }}>
                ARS
            </option>
            <option value="USD_BILLETE" {{ old('moneda', $cotizacion->moneda ?? '') == 'USD_BILLETE' ? 'selected' : '' }}>
                USD Billete
            </option>
            <option value="USD_DIVISA" {{ old('moneda', $cotizacion->moneda ?? '') == 'USD_DIVISA' ? 'selected' : '' }}>
                USD Divisa
            </option>
        </select>
    </div>

</div>

<div class="row mt-3">

    <div class="col-md-4">
        <label>Forma de Pago</label>
        <select name="forma_pago" class="form-control" required>
            <option value="CTA_CTE" {{ old('forma_pago', $cotizacion->forma_pago ?? '') == 'CTA_CTE' ? 'selected' : '' }}>
                Cuenta Corriente</option>
            <option value="CONTADO" {{ old('forma_pago', $cotizacion->forma_pago ?? '') == 'CONTADO' ? 'selected' : '' }}>
                Contado</option>
            <option value="MIXTO" {{ old('forma_pago', $cotizacion->forma_pago ?? '') == 'MIXTO' ? 'selected' : '' }}>
                Mixto</option>
            <option value="ANTICIPADO" {{ old('forma_pago', $cotizacion->forma_pago ?? '') == 'ANTICIPADO' ? 'selected' : '' }}>Anticipado</option>
        </select>
    </div>

    <div class="col-md-4">
        <label>Lugar de Entrega</label>
        <input type="text" name="lugar_entrega" class="form-control"
            value="{{ old('lugar_entrega', $cotizacion->lugar_entrega ?? '') }}">
    </div>

    <div class="col-md-4">
        <label>Plazo de Entrega</label>
        <input type="text" name="plazo_entrega" class="form-control"
            value="{{ old('plazo_entrega', $cotizacion->plazo_entrega ?? '') }}">
    </div>

</div>

<div class="row mt-3">
    <div class="col-md-4">
        <label>Vigencia de Oferta</label>
        <input type="date" name="vigencia_oferta" class="form-control" value="{{ old('vigencia_oferta', isset($cotizacion)
    ? optional($cotizacion->vigencia_oferta)->format('Y-m-d')
    : now()->format('Y-m-d')) }}">
    </div>


    {{-- Motivo --}}
    <div class="col-md-3">
        <label>Motivo</label>
        <select name="motivo" id="motivo" class="form-control" required>
            <option value="">Seleccione...</option>
            <option value="pedido" {{ old('motivo', $cotizacion->motivo ?? '') == 'pedido' ? 'selected' : '' }}>
                Pedido
            </option>
            <option value="particular" {{ old('motivo', $cotizacion->motivo ?? '') == 'particular' ? 'selected' : '' }}>
                Particular
            </option>
        </select>
    </div>
</div>


<hr class="mt-4">

<h4>Ítems de la Cotización</h4>

<table class="table table-bordered mt-2">
    <thead class="table-light">
        <tr>
            <th width="25%">Nro. Pedido</th>
            <th width="25%">Producto</th>
            <th width="10%">Cantidad</th>
            <th width="15%">Precio Unit.</th>
            <th width="10%">IVA %</th>
            <th width="10%">Total</th>
            <th width="5%"></th>
        </tr>
    </thead>

    <tbody id="items-table">

        @php
            $oldItems = old('items');
            $items = $oldItems ?? ($cotizacion->items ?? []);
        @endphp

        @forelse($items as $index => $item)
            <tr>
                <td>
                    @php
                        $selectedPedido = $item['id_pedido_cot'] ?? ($item->id_pedido_cot ?? '');
                    @endphp
                    <select name="items[{{ $index }}][id_pedido_cot]" class="form-control select-pedido no-select2"
                        data-selected="{{ $selectedPedido }}">
                        <option value="">(Ninguno)</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="items[{{ $index }}][producto]" class="form-control"
                        value="{{ $item['producto'] ?? $item->producto ?? '' }}" required>
                </td>

                <td>
                    <input type="number" step="1" name="items[{{ $index }}][cantidad]" class="form-control"
                        value="{{ $item['cantidad'] ?? $item->cantidad ?? 1 }}" required>
                </td>

                <td>
                    <input type="number" step="0.01" name="items[{{ $index }}][precio_unitario]" class="form-control"
                        value="{{ $item['precio_unitario'] ?? $item->precio_unitario ?? 0 }}" required>
                </td>

                <td>
                    <input type="number" step="0.01" name="items[{{ $index }}][iva]" class="form-control"
                        value="{{ $item['iva'] ?? $item->iva ?? 21 }}">
                </td>

                <td>
                    <input type="number" step="0.01" class="form-control item-total" readonly>
                </td>

                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-row">
                        X
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td>
                    <select name="items[0][id_pedido_cot]" class="form-control select-pedido no-select2"
                        data-selected="{{ old('items[0][id_pedido_cot]', '') }}">
                        <option value="">(Ninguno)</option>
                    </select>
                </td>
                <td><input type="text" name="items[0][producto]" class="form-control" required></td>
                <td><input type="number" step="1" name="items[0][cantidad]" class="form-control" value="1" required></td>
                <td><input type="number" step="0.01" name="items[0][precio_unitario]" class="form-control" value="0"
                        required></td>
                <td><input type="number" step="0.01" name="items[0][iva]" class="form-control" value="21"></td>
                <td><input type="number" step="0.01" class="form-control item-total" readonly></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
            </tr>
        @endforelse

    </tbody>
</table>

<button type="button" id="add-row" class="btn btn-primary btn-sm">
    + Agregar Ítem
</button>
<hr class="mt-4">

<div class="row mt-3">
    <div class="col-md-6">
        <label>Especificaciones Técnicas</label>
        <textarea name="especificaciones_tecnicas" class="form-control"
            rows="3">{{ old('especificaciones_tecnicas', $cotizacion->especificaciones_tecnicas ?? '') }}</textarea>
    </div>

    <div class="col-md-6">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control"
            rows="3">{{ old('observaciones', $cotizacion->observaciones ?? '') }}</textarea>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <label>Total General</label>
        <input type="number" step="0.01" name="importe_total" class="form-control"
            value="{{ old('importe_total', $cotizacion->importe_total ?? 0) }}" readonly>
    </div>
</div>

{{-- =========================
SCRIPT GLOBAL COTIZACIONES
========================= --}}
<style>
    .celda-bloqueada {
        opacity: 0.5;
        pointer-events: none;
        background-color: #f8f9fa;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', async function () {
        console.log('DOM Cargado - Iniciando Scripts de Cotización');

        const inputRazon = document.getElementById('razon_social');
        const dropdownClientes = document.getElementById('dropdown-clientes');
        const inputClienteId = document.getElementById('id_cliente');
        const itemsTable = document.getElementById('items-table');
        const addRowBtn = document.getElementById('add-row');

        let rowCount = document.querySelectorAll('#items-table tr').length;
        let debounceTimer = null;

        // --- FUNCIONES ---

        function ocultarDropdown() {
            dropdownClientes.style.display = 'none';
            dropdownClientes.innerHTML = '';
        }

        function mostrarDropdown() {
            dropdownClientes.style.display = 'block';
        }

        async function buscarClientes(q) {
            try {
                const resp = await fetch(`{{ route('clientes.buscar') }}?q=${encodeURIComponent(q)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!resp.ok) return [];
                return await resp.json();
            } catch (e) {
                console.error('Error buscando clientes:', e);
                return [];
            }
        }

        async function poblarSelectPedidos(selectElement, clienteId) {
            if (!clienteId) return;

            try {
                const $select = $(selectElement);
                const currentVal = $select.val();
                const selectedId = $select.attr('data-selected');

                // Incluir el ID seleccionado actualmente para asegurar que el server lo devuelva
                const url = `{{ route('pedidos.buscar') }}?cliente_id=${clienteId}&include_id=${selectedId || ''}`;

                const resp = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!resp.ok) {
                    console.error('Error respuesta fetch pedidos:', resp.status);
                    return;
                }

                const pedidos = await resp.json();
                console.log(`Pedidos encontrados para fila: ${pedidos.length}`);

                // 3. Destruir Select2 SI existe para reemplazar opciones limpiamente
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                // 4. Limpiar y cargar opciones
                selectElement.innerHTML = '<option value="">(Ninguno)</option>';
                pedidos.forEach(p => {
                    let fechaFormateada = 'S/F';
                    if (p.fecha) {
                        const partes = p.fecha.split(' ')[0].split('-');
                        if (partes.length === 3) {
                            fechaFormateada = `${partes[2]}/${partes[1]}/${partes[0]}`;
                        }
                    }

                    const text = `${p.nro_solicitud || 'S/N'} (${fechaFormateada})`;
                    const isSelected = (p.id_ped_cot == currentVal || p.id_ped_cot == selectedId);
                    const opt = new Option(text, p.id_ped_cot, isSelected, isSelected);
                    selectElement.appendChild(opt);
                });

                // 5. Forzar el valor en el select nativo antes de Select2
                if (selectedId) {
                    $select.val(selectedId);
                }

                // 6. Re-inicializar Select2
                $select.select2({
                    placeholder: '(Ninguno)',
                    allowClear: true,
                    width: '100%'
                });

            } catch (e) {
                console.error('Error en poblarSelectPedidos:', e);
            }
        }

        function calcularTotales() {
            let filas = document.querySelectorAll('#items-table tr');
            let totalGeneral = 0;

            filas.forEach(function (row) {
                let cantidad = parseFloat(row.querySelector('[name*="[cantidad]"]')?.value) || 0;
                let precio = parseFloat(row.querySelector('[name*="[precio_unitario]"]')?.value) || 0;
                let iva = parseFloat(row.querySelector('[name*="[iva]"]')?.value) || 0;

                let totalInput = row.querySelector('.item-total');
                let base = cantidad * precio;
                let total = base + (base * iva / 100);

                if (totalInput) totalInput.value = total.toFixed(2);
                totalGeneral += total;
            });

            const totalInputG = document.querySelector('input[name="importe_total"]');
            if (totalInputG) totalInputG.value = totalGeneral.toFixed(2);
        }

        /**
         * Ajusta el estado de los combos de pedido según el motivo
         */
        function actualizarEstadoPedidos() {
            const motivoEl = document.getElementById('motivo');
            if (!motivoEl) return;
            
            const motivo = motivoEl.value;
            const selectPedidos = document.querySelectorAll('.select-pedido');
            const isParticular = (motivo === 'particular');
            const isPedido = (motivo === 'pedido');

            console.log('Validación Motivo -> Seleccionado:', motivo, '| Particular:', isParticular);

            // Evitamos problemas visuales bloqueando/desbloqueando Select2 dinámicamente
            selectPedidos.forEach(sel => {
                const $sel = $(sel);

                if (isParticular) {
                    $sel.val("").trigger('change');
                }

                // Deshabilitar nativo (Select2 también lo lee)
                sel.disabled = isParticular;
                sel.required = isPedido;
                
                // Actualizar estado de Select2 explícitamente sin destruirlo
                if ($sel.hasClass('select2-hidden-accessible')) {
                    $sel.prop('disabled', isParticular);
                }

                const td = sel.closest('td');
                if (td) {
                    if (isParticular) {
                        td.classList.add('celda-bloqueada');
                    } else {
                        td.classList.remove('celda-bloqueada');
                    }
                }
            });
        }

        // --- EVENTOS ---

        addRowBtn.addEventListener('click', function () {
            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td>
                <select name="items[${rowCount}][id_pedido_cot]" class="form-control select-pedido no-select2">
                    <option value="">(Ninguno)</option>
                </select>
            </td>
            <td><input type="text" name="items[${rowCount}][producto]" class="form-control" required></td>
            <td><input type="number" step="1" name="items[${rowCount}][cantidad]" class="form-control" value="1" required></td>
            <td><input type="number" step="0.01" name="items[${rowCount}][precio_unitario]" class="form-control" value="0" required></td>
            <td><input type="number" step="0.01" name="items[${rowCount}][iva]" class="form-control" value="21"></td>
            <td><input type="number" step="0.01" class="form-control item-total" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
        `;
            itemsTable.appendChild(tr);

            // Si hay cliente, cargar pedidos para esta nueva fila
            const cliId = inputClienteId.value;
            if (cliId) {
                poblarSelectPedidos(tr.querySelector('.select-pedido'), cliId).then(() => {
                    actualizarEstadoPedidos();
                });
            } else {
                actualizarEstadoPedidos();
            }

            rowCount++;
            actualizarEstadoPedidos();
        });

        $('#motivo').on('change', actualizarEstadoPedidos);

        itemsTable.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-row')) {
                e.target.closest('tr').remove();
                calcularTotales();
            }
        });

        itemsTable.addEventListener('input', function (e) {
            calcularTotales();
        });

        inputRazon.addEventListener('input', function () {
            inputClienteId.value = '';
            let q = this.value.trim();
            if (q.length < 2) { ocultarDropdown(); return; }

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(async () => {
                let clientes = await buscarClientes(q);
                dropdownClientes.innerHTML = '';
                clientes.forEach(cli => {
                    let btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action';
                    btn.innerHTML = `<strong>${cli.razon_social}</strong><br><small>${cli.cuit ?? ''}</small>`;

                    btn.onclick = async function () {
                        console.log('Cliente seleccionado:', cli.razon_social, 'ID:', cli.id);
                        inputRazon.value = `${cli.razon_social} - ${cli.cuit ?? ''}`;
                        inputClienteId.value = cli.id;
                        ocultarDropdown();

                        // Cargar pedidos para todos los combos
                        const selects = document.querySelectorAll('.select-pedido');
                        for (const sel of selects) {
                            await poblarSelectPedidos(sel, cli.id);
                        }
                        // Re-aplicar estado del motivo DESPUÉS de poblar todos los combos
                        actualizarEstadoPedidos();
                    };
                    dropdownClientes.appendChild(btn);
                });
                mostrarDropdown();
            }, 250);
        });

        document.addEventListener('click', function (e) {
            if (!dropdownClientes.contains(e.target) && !inputRazon.contains(e.target)) {
                ocultarDropdown();
            }
        });

        // Validación extra al enviar
        const form = document.getElementById('motivo').closest('form');
        form.addEventListener('submit', function (e) {
            const motivo = document.getElementById('motivo').value;
            if (motivo === 'pedido') {
                const selects = document.querySelectorAll('.select-pedido');
                let valid = true;
                selects.forEach(sel => {
                    if (!sel.value) {
                        valid = false;
                        sel.style.border = '2px solid red';
                        // Si es Select2, resaltar el container
                        const $sel = $(sel);
                        if ($sel.hasClass('select2-hidden-accessible')) {
                            $sel.next('.select2-container').css('border', '2px solid red');
                        }
                    } else {
                        sel.style.border = '';
                        const $sel = $(sel);
                        if ($sel.hasClass('select2-hidden-accessible')) {
                            $sel.next('.select2-container').css('border', '');
                        }
                    }
                });

                if (!valid) {
                    e.preventDefault();
                    alert('Debe vincular cada ítem a un pedido cuando el motivo es "Pedido"');
                }
            }
        });

        // --- INICIO ---
        calcularTotales();
        actualizarEstadoPedidos();

        const initialCliId = inputClienteId.value;
        if (initialCliId) {
            console.log('Carga inicial: poblando pedidos para cliente', initialCliId);
            const selects = document.querySelectorAll('.select-pedido');
            for (const sel of selects) {
                await poblarSelectPedidos(sel, initialCliId);
            }
        }
        // Aplicar estado de motivo DESPUÉS de toda la inicialización
        actualizarEstadoPedidos();
    });
</script>