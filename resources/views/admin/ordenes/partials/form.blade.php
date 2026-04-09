<div class="row">
    <div class="col-md-3">
        <label>Número de OC</label>
        <input type="number" name="numero_oc" class="form-control" value="{{ old('numero_oc', $orden->numero_oc ?? '') }}" required>
    </div>

    <div class="col-md-3">
        <label>Fecha</label>
        <input type="date"
            name="fecha"
            class="form-control"
            value="{{ old('fecha', isset($orden) ? $orden->fecha : now()->format('Y-m-d')) }}"
            required>
    </div>

    <div class="col-md-3">
        <label>Motivo</label>
        <select name="motivo" id="motivo" class="form-control" required>
            <option value="">Seleccionar...</option>
            <option value="cotizacion" {{ old('motivo', $orden->motivo ?? '') == 'cotizacion' ? 'selected' : '' }}>
                Cotización
            </option>
            <option value="stock" {{ old('motivo', $orden->motivo ?? '') == 'stock' ? 'selected' : '' }}>
                Stock
            </option>
        </select>
    </div>

    <div class="col-md-3" id="grupo-cotizacion" style="display: none;">
        <label>Vincular Cotización</label>
        <select name="cotizacion_id" id="cotizacion_id" class="form-control">
            <option value="">(Ninguna)</option>
        </select>
    </div>


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

                {{-- ID cliente --}}
                <input type="hidden"
                    name="id_cliente"
                    id="id_cliente"
                    value="{{ old('id_cliente') }}">

                {{-- dropdown --}}
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
        <input type="text"
               id="cuit"
               name="cuit"
               class="form-control"
               maxlength="11"
               value="{{ old('cuit', $orden->cuit ?? '') }}"
               required>
    </div>

    <div class="col-md-3">
        <label>Dirección</label>
        <input type="text"
               id="direccion"
               name="direccion"
               class="form-control"
               value="{{ old('direccion', $orden->direccion ?? '') }}">
    </div>

    <div class="col-md-3">
        <label>Teléfono</label>
        <input type="text"
               id="telefono"
               name="telefono"
               class="form-control"
               value="{{ old('telefono', $orden->telefono ?? '') }}">
    </div>

    <div class="col-md-3">
        <label>Email</label>
        <input type="email"
               id="email"
               name="email"
               class="form-control"
               value="{{ old('email', $orden->email ?? '') }}">
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4">
        <label>Moneda</label>
        <select name="moneda" class="form-control" required>
            <option value="ARS" {{ old('moneda', $orden->moneda ?? 'ARS') == 'ARS' ? 'selected' : '' }}>
                ARS - Peso Argentino
            </option>

            <option value="USD_BILLETE" {{ old('moneda', $orden->moneda ?? '') == 'USD_BILLETE' ? 'selected' : '' }}>
                USD - Billete
            </option>

            <option value="USD_DIVISA" {{ old('moneda', $orden->moneda ?? '') == 'USD_DIVISA' ? 'selected' : '' }}>
                USD - Divisa
            </option>
        </select>
    </div>

    <div class="col-md-4">
            <label>Condición de compra</label>
            <input type="text" name="condicion_compra" class="form-control" value="{{ old('condicion_compra', $orden->condicion_compra ?? '') }}" required>
        </div>

        <div class="col-md-4">
        <label>Archivo (PDF / Imagen)</label>

        <input type="file"
            name="archivo"
            class="form-control"
            accept=".pdf,.png,.jpg,.jpeg">

        @if(isset($orden) && $orden->archivo)
            <small class="text-muted d-block mt-1">
                Archivo actual:
                <a href="{{ asset('storage/'.$orden->archivo) }}" target="_blank">
                    Ver archivo
                </a>
            </small>
        @endif
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
            <th style="width:15%;">Unidad</th>
            <th>Precio Unitario</th>
            <th>IVA (%)</th>
            <th>Desc. (%)</th>
            <th>Fecha Entrega</th>
            <th>Total</th>
            <th></th>
        </tr>
    </thead>

    <tbody id="items-table">
        @php
            $oldItems = old('items');
            $items = $oldItems ?? ($orden->items ?? [ [] ]);
        @endphp

        @foreach($items as $i => $item)
            <tr>
                <td><input type="text" name="items[{{ $i }}][codigo]" class="form-control"
                    value="{{ $item['codigo'] ?? '' }}"></td>

                <td><input type="text" name="items[{{ $i }}][descripcion]" class="form-control"
                    value="{{ $item['descripcion'] ?? '' }}"></td>

                <td><input type="number" step="0.01" name="items[{{ $i }}][cantidad]" class="form-control"
                    value="{{ $item['cantidad'] ?? '' }}"></td>

                <td>
                    <select name="items[{{ $i }}][unidad]" class="form-control">
                        <option value="">seleccionar...</option>

                        @php
                            $unidadSeleccionada = $item['unidad'] ?? '';
                        @endphp

                        <option value="1"  {{ $unidadSeleccionada == 1 ? 'selected' : '' }}>kilogramos</option>
                        <option value="2"  {{ $unidadSeleccionada == 2 ? 'selected' : '' }}>metros</option>
                        <option value="3"  {{ $unidadSeleccionada == 3 ? 'selected' : '' }}>metros cuadrados</option>
                        <option value="4"  {{ $unidadSeleccionada == 4 ? 'selected' : '' }}>metros cúbicos</option>
                        <option value="5"  {{ $unidadSeleccionada == 5 ? 'selected' : '' }}>litros</option>
                        <option value="6"  {{ $unidadSeleccionada == 6 ? 'selected' : '' }}>1000 kWh</option>
                        <option value="7"  {{ $unidadSeleccionada == 7 ? 'selected' : '' }}>unidades</option>
                        <option value="8"  {{ $unidadSeleccionada == 8 ? 'selected' : '' }}>pares</option>
                        <option value="9"  {{ $unidadSeleccionada == 9 ? 'selected' : '' }}>docenas</option>
                        <option value="10" {{ $unidadSeleccionada == 10 ? 'selected' : '' }}>quilates</option>
                        <option value="11" {{ $unidadSeleccionada == 11 ? 'selected' : '' }}>millares</option>
                        <option value="14" {{ $unidadSeleccionada == 14 ? 'selected' : '' }}>gramos</option>
                        <option value="15" {{ $unidadSeleccionada == 15 ? 'selected' : '' }}>milímetros</option>
                        <option value="16" {{ $unidadSeleccionada == 16 ? 'selected' : '' }}>mm cúbicos</option>
                        <option value="17" {{ $unidadSeleccionada == 17 ? 'selected' : '' }}>kilómetros</option>
                        <option value="18" {{ $unidadSeleccionada == 18 ? 'selected' : '' }}>hectolitros</option>
                        <option value="20" {{ $unidadSeleccionada == 20 ? 'selected' : '' }}>centímetros</option>
                        <option value="25" {{ $unidadSeleccionada == 25 ? 'selected' : '' }}>jgo. pqt. mazo naipes</option>
                        <option value="27" {{ $unidadSeleccionada == 27 ? 'selected' : '' }}>cm cúbicos</option>
                        <option value="29" {{ $unidadSeleccionada == 29 ? 'selected' : '' }}>toneladas</option>
                        <option value="30" {{ $unidadSeleccionada == 30 ? 'selected' : '' }}>dam cúbicos</option>
                        <option value="31" {{ $unidadSeleccionada == 31 ? 'selected' : '' }}>hm cúbicos</option>
                        <option value="32" {{ $unidadSeleccionada == 32 ? 'selected' : '' }}>km cúbicos</option>
                        <option value="33" {{ $unidadSeleccionada == 33 ? 'selected' : '' }}>microgramos</option>
                        <option value="34" {{ $unidadSeleccionada == 34 ? 'selected' : '' }}>nanogramos</option>
                        <option value="35" {{ $unidadSeleccionada == 35 ? 'selected' : '' }}>picogramos</option>
                        <option value="41" {{ $unidadSeleccionada == 41 ? 'selected' : '' }}>miligramos</option>
                        <option value="47" {{ $unidadSeleccionada == 47 ? 'selected' : '' }}>mililitros</option>
                        <option value="48" {{ $unidadSeleccionada == 48 ? 'selected' : '' }}>curie</option>
                        <option value="49" {{ $unidadSeleccionada == 49 ? 'selected' : '' }}>milicurie</option>
                        <option value="50" {{ $unidadSeleccionada == 50 ? 'selected' : '' }}>microcurie</option>
                        <option value="51" {{ $unidadSeleccionada == 51 ? 'selected' : '' }}>uiacthor</option>
                        <option value="52" {{ $unidadSeleccionada == 52 ? 'selected' : '' }}>muiacthor</option>
                        <option value="53" {{ $unidadSeleccionada == 53 ? 'selected' : '' }}>kg base</option>
                        <option value="54" {{ $unidadSeleccionada == 54 ? 'selected' : '' }}>gruesa</option>
                        <option value="61" {{ $unidadSeleccionada == 61 ? 'selected' : '' }}>kg bruto</option>
                        <option value="62" {{ $unidadSeleccionada == 62 ? 'selected' : '' }}>uiactant</option>
                        <option value="63" {{ $unidadSeleccionada == 63 ? 'selected' : '' }}>muiactant</option>
                        <option value="64" {{ $unidadSeleccionada == 64 ? 'selected' : '' }}>uiactig</option>
                        <option value="65" {{ $unidadSeleccionada == 65 ? 'selected' : '' }}>muiactig</option>
                        <option value="66" {{ $unidadSeleccionada == 66 ? 'selected' : '' }}>kg activo</option>
                        <option value="67" {{ $unidadSeleccionada == 67 ? 'selected' : '' }}>gramo activo</option>
                        <option value="68" {{ $unidadSeleccionada == 68 ? 'selected' : '' }}>gramo base</option>
                        <option value="96" {{ $unidadSeleccionada == 96 ? 'selected' : '' }}>packs</option>
                        <option value="98" {{ $unidadSeleccionada == 98 ? 'selected' : '' }}>otras unidades</option>
                    </select>
                </td>

                <td><input type="number" step="0.01" name="items[{{ $i }}][precio_unitario]" class="form-control"
                    value="{{ $item['precio_unitario'] ?? '' }}"></td>

                <td>
                    <input type="number" step="0.01"
                        name="items[{{ $i }}][iva]"
                        class="form-control"
                        value="{{ $item['iva'] ?? 21 }}">
                </td>


                <td><input type="number" step="0.01" name="items[{{ $i }}][descuento]" class="form-control"
                    value="{{ $item['descuento'] ?? 0 }}"></td>

                <td><input type="date" name="items[{{ $i }}][fecha_entrega]" class="form-control"
                    value="{{ $item['fecha_entrega'] ?? '' }}"></td>

                <td><input type="number" step="0.01" name="items[{{ $i }}][total]" class="form-control item-total"
                    value="{{ $item['total'] ?? '' }}" readonly></td>

                <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
            </tr>
        @endforeach

    </tbody>

</table>


<button type="button" id="add-row" class="btn btn-primary btn-sm">Agregar Ítem</button>

<hr class="mt-4">

<div class="row mt-3">
    <div class="col-md-12">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="3">{{ old('observaciones', $orden->observaciones ?? '') }}</textarea>
    </div>
</div>

<div class="row mt-3">

    <div class="col-md-4">
        <label>Subtotal c/IVA</label>
        <input type="number" step="0.01"
               name="subtotal"
               class="form-control"
               value="{{ old('subtotal', $orden->subtotal ?? 0) }}"
               readonly>
    </div>

    <div class="col-md-4">
        <label>Descuentos Totales</label>
        <input type="number" step="0.01"
               name="descuentos_totales"
               class="form-control"
               value="{{ old('descuentos_totales', $orden->descuentos_totales ?? 0) }}"
               readonly>
    </div>

    <div class="col-md-4">
        <label>Total Final</label>
        <input type="number" step="0.01"
               name="total"
               class="form-control"
               value="{{ old('total', $orden->total ?? 0) }}"
               readonly>
    </div>

</div>

<script>
    let row = {{ count($items) }};

    document.getElementById('add-row').addEventListener('click', function() {
        var table = document.getElementById('items-table');

        var newRow = ''
        + '<tr>'
        + '  <td><input type="text" name="items['+row+'][codigo]" class="form-control"></td>'
        + '  <td><input type="text" name="items['+row+'][descripcion]" class="form-control"></td>'
        + '  <td><input type="number" step="0.01" name="items['+row+'][cantidad]" class="form-control"></td>'
        + '  <td>'
        + '    <select name="items['+row+'][unidad]" class="form-control">'
        + '        <option value="">seleccionar...</option>'
        + '        <option value="1">kilogramos</option>'
        + '        <option value="2">metros</option>'
        + '        <option value="3">metros cuadrados</option>'
        + '        <option value="4">metros cúbicos</option>'
        + '        <option value="5">litros</option>'
        + '        <option value="6">1000 kWh</option>'
        + '        <option value="7">unidades</option>'
        + '        <option value="8">pares</option>'
        + '        <option value="9">docenas</option>'
        + '        <option value="10">quilates</option>'
        + '        <option value="11">millares</option>'
        + '        <option value="14">gramos</option>'
        + '        <option value="15">milímetros</option>'
        + '        <option value="16">mm cúbicos</option>'
        + '        <option value="17">kilómetros</option>'
        + '        <option value="18">hectolitros</option>'
        + '        <option value="20">centímetros</option>'
        + '        <option value="25">jgo. pqt. mazo naipes</option>'
        + '        <option value="27">cm cúbicos</option>'
        + '        <option value="29">toneladas</option>'
        + '        <option value="30">dam cúbicos</option>'
        + '        <option value="31">hm cúbicos</option>'
        + '        <option value="32">km cúbicos</option>'
        + '        <option value="33">microgramos</option>'
        + '        <option value="34">nanogramos</option>'
        + '        <option value="35">picogramos</option>'
        + '        <option value="41">miligramos</option>'
        + '        <option value="47">mililitros</option>'
        + '        <option value="48">curie</option>'
        + '        <option value="49">milicurie</option>'
        + '        <option value="50">microcurie</option>'
        + '        <option value="51">uiacthor</option>'
        + '        <option value="52">muiacthor</option>'
        + '        <option value="53">kg base</option>'
        + '        <option value="54">gruesa</option>'
        + '        <option value="61">kg bruto</option>'
        + '        <option value="62">uiactant</option>'
        + '        <option value="63">muiactant</option>'
        + '        <option value="64">uiactig</option>'
        + '        <option value="65">muiactig</option>'
        + '        <option value="66">kg activo</option>'
        + '        <option value="67">gramo activo</option>'
        + '        <option value="68">gramo base</option>'
        + '        <option value="96">packs</option>'
        + '        <option value="98">otras unidades</option>'
        + '    </select>'
        + '  </td>'
        + '  <td><input type="number" step="0.01" name="items['+row+'][precio_unitario]" class="form-control"></td>'
        + '  <td><input type="number" step="0.01" name="items['+row+'][iva]" class="form-control" value="21"></td>'
        + '  <td><input type="number" step="0.01" name="items['+row+'][descuento]" class="form-control" value="0"></td>'
        + '  <td><input type="date" name="items['+row+'][fecha_entrega]" class="form-control"></td>'
        + '  <td><input type="number" step="0.01" name="items['+row+'][total]" class="form-control item-total" readonly></td>'
        + '  <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>'
        + '</tr>';

        table.insertAdjacentHTML('beforeend', newRow);
        row++;

        calcularTotales();
    });

    // Eliminar fila
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('tr').remove();
            calcularTotales();
        }
    });

    document.addEventListener('input', function(e) {
        if (!e.target.name) return;

        if (
            e.target.name.indexOf('[cantidad]') !== -1 ||
            e.target.name.indexOf('[precio_unitario]') !== -1 ||
            e.target.name.indexOf('[descuento]') !== -1 ||
            e.target.name.indexOf('[iva]') !== -1
        ) {
            calcularTotales();
        }
    });

    function calcularTotales() {
        var filas = document.querySelectorAll('#items-table tr');
        var subtotalConIVA = 0;
        var totalGeneral   = 0;
        var descuentosTotales = 0;

        filas.forEach(function(row) {

            var inputCantidad  = row.querySelector('input[name*="[cantidad]"]');
            var inputPrecio    = row.querySelector('input[name*="[precio_unitario]"]');
            var inputIVA       = row.querySelector('input[name*="[iva]"]');
            var inputDescuento = row.querySelector('input[name*="[descuento]"]');
            var inputTotal     = row.querySelector('input[name*="[total]"]');

            if (!inputCantidad || !inputPrecio || !inputTotal) return;

            var cantidad  = parseFloat(inputCantidad.value)  || 0;
            var precio    = parseFloat(inputPrecio.value)    || 0;
            var iva       = parseFloat(inputIVA?.value)      || 0;
            var descuento = parseFloat(inputDescuento.value) || 0;

            var totalBase   = cantidad * precio;
            var totalConIVA = totalBase + (totalBase * (iva / 100));
            var descuentoMonto = totalConIVA * (descuento / 100);
            var totalFinal  = totalConIVA - descuentoMonto;

            inputTotal.value = totalFinal.toFixed(2);

            subtotalConIVA   += totalConIVA;
            descuentosTotales += descuentoMonto;
            totalGeneral     += totalFinal;
        });

        document.querySelector('input[name="subtotal"]').value =
            subtotalConIVA.toFixed(2);

        document.querySelector('input[name="descuentos_totales"]').value =
            descuentosTotales.toFixed(2);

        document.querySelector('input[name="total"]').value =
            totalGeneral.toFixed(2);
    }



    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', calcularTotales);
    } else {
        calcularTotales();
    }
</script>

<script>
/* ============================================================
   AUTOCOMPLETADO CLIENTES – ÓRDENES
   ============================================================ */

    const inputRazon = document.getElementById('razon_social');
    const dropdownClientes = document.getElementById('dropdown-clientes');
    const inputClienteId = document.getElementById('id_cliente');

    // campos opcionales
    const inputCuit      = document.getElementById('cuit');
    const inputDireccion = document.getElementById('direccion');
    const inputTelefono  = document.getElementById('telefono');
    const inputEmail     = document.getElementById('email');


    let debounceTimer = null;

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
            dropdownClientes.innerHTML =
                `<div class="list-group-item text-muted">Sin resultados</div>`;
            mostrarDropdown();
            return;
        }

        clientes.forEach(cli => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action';

            item.innerHTML = `
                <strong>${cli.razon_social ?? ''}</strong>
                <br>
                <small class="text-muted">${cli.cuit ?? ''}</small>
            `;

            item.addEventListener('click', () => {

                inputRazon.value     = cli.razon_social ?? '';
                inputClienteId.value = cli.id ?? '';

                if (inputCuit)      inputCuit.value      = cli.cuit ?? '';
                if (inputDireccion) inputDireccion.value = cli.direccion ?? '';
                if (inputTelefono)  inputTelefono.value  = cli.telefono ?? '';
                if (inputEmail)     inputEmail.value     = cli.email ?? '';
                
                if (inputClienteId.value) {
                    poblarSelectCotizaciones(inputClienteId.value);
                }

                ocultarDropdown();
            });


            dropdownClientes.appendChild(item);
        });

        mostrarDropdown();
    }

    async function poblarSelectCotizaciones(clienteId) {
        const select = document.getElementById('cotizacion_id');
        const resp = await fetch(`{{ route('cotizaciones.buscar') }}?cliente_id=${clienteId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!resp.ok) return;
        const cotizaciones = await resp.json();

        select.innerHTML = '<option value="">(Ninguna)</option>';
        cotizaciones.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id_cotizacion;
            opt.textContent = `${c.nro_cotizacion || ('# ' + c.id_cotizacion)} (${c.fecha_cot})`;
            select.appendChild(opt);
        });

        actualizarVisibilidadCotizacion();
    }

    function actualizarVisibilidadCotizacion() {
        const motivo = document.getElementById('motivo').value;
        const grupo = document.getElementById('grupo-cotizacion');
        if (motivo === 'cotizacion') {
            grupo.style.display = 'block';
        } else {
            grupo.style.display = 'none';
        }
    }

    document.getElementById('motivo').addEventListener('change', actualizarVisibilidadCotizacion);

    window.addEventListener('load', () => {
        actualizarVisibilidadCotizacion();
        const ci = document.getElementById('id_cliente').value;
        if (ci) poblarSelectCotizaciones(ci);
    });

    async function buscarClientes(q) {
        const url = `{{ route('clientes.buscar') }}?q=${encodeURIComponent(q)}`;

        const resp = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!resp.ok) return [];
        return await resp.json();
    }

    inputRazon.addEventListener('input', () => {
        inputClienteId.value = '';

        const q = inputRazon.value.trim();
        if (q.length < 2) {
            ocultarDropdown();
            return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            const clientes = await buscarClientes(q);
            renderSugerencias(clientes);
        }, 250);
    });

    // click afuera
    document.addEventListener('click', e => {
        if (!dropdownClientes.contains(e.target) && !inputRazon.contains(e.target)) {
            ocultarDropdown();
        }
    });

    // ESC
    inputRazon.addEventListener('keydown', e => {
        if (e.key === 'Escape') ocultarDropdown();
    });
</script>
