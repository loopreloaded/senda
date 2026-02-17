<div class="row">
    <div class="col-md-3">
        <label>Número de OC</label>
        <input type="number" name="numero_oc" class="form-control" value="{{ old('numero_oc', $orden->numero_oc ?? '') }}" required>
    </div>

    <div class="col-md-3">
        <label>Fecha</label>
        <input type="date" name="fecha" class="form-control" value="{{ old('fecha', $orden->fecha ?? '') }}" required>
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
                    name="cliente_id"
                    id="cliente_id"
                    value="{{ old('cliente_id') }}">

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

</div>

<hr class="mt-4">

<h4>Ítems</h4>

<table class="table table-bordered mt-2">
    <thead>
        <tr>
            <th>Código</th>
            <th>Descripción</th>
            <th>Cantidad</th>
            <th>Unidad</th>
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

                <td><input type="text" name="items[{{ $i }}][unidad]" class="form-control"
                    value="{{ $item['unidad'] ?? '' }}"></td>

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
    <div class="col-md-6">
        <label>Subtotal c/IVA</label>
        <input type="number" step="0.01" name="subtotal" class="form-control" value="{{ old('subtotal', $orden->subtotal ?? 0) }}" readonly>
    </div>

    <div class="col-md-6">
        <label>Total General</label>
        <input type="number" step="0.01" name="total" class="form-control" value="{{ old('total', $orden->total ?? 0) }}" readonly>
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
        + '  <td><input type="text" name="items['+row+'][unidad]" class="form-control"></td>'
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
            e.target.name.indexOf('[descuento]') !== -1
        ) {
            calcularTotales();
        }
    });

    function calcularTotales() {
        var filas = document.querySelectorAll('#items-table tr');
        var subtotalConIVA = 0;
        var totalGeneral   = 0;

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

            var totalBase = cantidad * precio;

            // agregar IVA
            var totalConIVA = totalBase + (totalBase * (iva / 100));

            // aplicar descuento
            var totalFinal = totalConIVA - (totalConIVA * (descuento / 100));

            inputTotal.value = totalFinal.toFixed(2);

            subtotalConIVA += totalConIVA;
            totalGeneral   += totalFinal;
        });

        document.querySelector('input[name="subtotal"]').value =
            subtotalConIVA.toFixed(2);

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
    const inputClienteId = document.getElementById('cliente_id');

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

                ocultarDropdown();
            });


            dropdownClientes.appendChild(item);
        });

        mostrarDropdown();
    }

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
