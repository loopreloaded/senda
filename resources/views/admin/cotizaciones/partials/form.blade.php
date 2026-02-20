<div class="row">

    {{-- Fecha --}}
    <div class="col-md-3">
        <label>Fecha</label>
        <input type="date"
            name="fecha_cot"
            class="form-control"
            value="{{ old('fecha_cot', isset($cotizacion)
                            ? optional($cotizacion->fecha_cot)->format('Y-m-d')
                            : now()->format('Y-m-d')) }}"
            required>
    </div>

    {{-- Cliente --}}
    <div class="col-md-6">
        <label>Cliente</label>

        <div class="position-relative">
            <input type="text"
                   id="razon_social"
                   class="form-control"
                   autocomplete="off"
                   value="{{ old('razon_social', $cotizacion->cliente->razon_social ?? '') }}"
                   required>

            <input type="hidden"
                   name="id_cliente"
                   id="cliente_id"
                   value="{{ old('id_cliente', $cotizacion->id_cliente ?? '') }}">

            <div id="dropdown-clientes"
                 class="list-group position-absolute w-100 shadow"
                 style="z-index:9999; max-height:240px; overflow-y:auto; display:none;">
            </div>
        </div>
    </div>

    {{-- Moneda --}}
    <div class="col-md-3">
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
            <option value="CTA_CTE" {{ old('forma_pago', $cotizacion->forma_pago ?? '') == 'CTA_CTE' ? 'selected' : '' }}>Cuenta Corriente</option>
            <option value="CONTADO" {{ old('forma_pago', $cotizacion->forma_pago ?? '') == 'CONTADO' ? 'selected' : '' }}>Contado</option>
            <option value="MIXTO" {{ old('forma_pago', $cotizacion->forma_pago ?? '') == 'MIXTO' ? 'selected' : '' }}>Mixto</option>
            <option value="ANTICIPADO" {{ old('forma_pago', $cotizacion->forma_pago ?? '') == 'ANTICIPADO' ? 'selected' : '' }}>Anticipado</option>
        </select>
    </div>

    <div class="col-md-4">
        <label>Lugar de Entrega</label>
        <input type="text"
               name="lugar_entrega"
               class="form-control"
               value="{{ old('lugar_entrega', $cotizacion->lugar_entrega ?? '') }}">
    </div>

    <div class="col-md-4">
        <label>Plazo de Entrega</label>
        <input type="text"
               name="plazo_entrega"
               class="form-control"
               value="{{ old('plazo_entrega', $cotizacion->plazo_entrega ?? '') }}">
    </div>

</div>

<div class="row mt-3">
    <div class="col-md-4">
        <label>Vigencia de Oferta</label>
        <input type="date"
               name="vigencia_oferta"
               class="form-control"
               value="{{ old('vigencia_oferta', isset($cotizacion)
                        ? optional($cotizacion->vigencia_oferta)->format('Y-m-d')
                        : now()->format('Y-m-d')) }}">
    </div>
</div>

<hr class="mt-4">

<h4>Ítems de la Cotización</h4>

<table class="table table-bordered mt-2">
    <thead class="table-light">
        <tr>
            <th width="35%">Producto</th>
            <th width="10%">Cantidad</th>
            <th width="15%">Precio Unit.</th>
            <th width="10%">IVA %</th>
            <th width="15%">Total</th>
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
                <input type="text"
                       name="items[{{ $index }}][producto]"
                       class="form-control"
                       value="{{ $item['producto'] ?? $item->producto ?? '' }}"
                       required>
            </td>

            <td>
                <input type="number"
                       step="1"
                       name="items[{{ $index }}][cantidad]"
                       class="form-control"
                       value="{{ $item['cantidad'] ?? $item->cantidad ?? 1 }}"
                       required>
            </td>

            <td>
                <input type="number"
                       step="0.01"
                       name="items[{{ $index }}][precio_unitario]"
                       class="form-control"
                       value="{{ $item['precio_unitario'] ?? $item->precio_unitario ?? 0 }}"
                       required>
            </td>

            <td>
                <input type="number"
                       step="0.01"
                       name="items[{{ $index }}][iva]"
                       class="form-control"
                       value="{{ $item['iva'] ?? $item->iva ?? 21 }}">
            </td>

            <td>
                <input type="number"
                       step="0.01"
                       class="form-control item-total"
                       readonly>
            </td>

            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row">
                    X
                </button>
            </td>
        </tr>
        @empty
        <tr>
            <td><input type="text" name="items[0][producto]" class="form-control" required></td>
            <td><input type="number" step="1" name="items[0][cantidad]" class="form-control" value="1" required></td>
            <td><input type="number" step="0.01" name="items[0][precio_unitario]" class="form-control" value="0" required></td>
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
        <textarea name="especificaciones_tecnicas"
                  class="form-control"
                  rows="3">{{ old('especificaciones_tecnicas', $cotizacion->especificaciones_tecnicas ?? '') }}</textarea>
    </div>

    <div class="col-md-6">
        <label>Observaciones</label>
        <textarea name="observaciones"
                  class="form-control"
                  rows="3">{{ old('observaciones', $cotizacion->observaciones ?? '') }}</textarea>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <label>Total General</label>
        <input type="number"
               step="0.01"
               name="importe_total"
               class="form-control"
               value="{{ old('importe_total', $cotizacion->importe_total ?? 0) }}"
               readonly>
    </div>
</div>

{{-- =========================
     SCRIPT ITEMS DINÁMICOS
========================= --}}
<script>

let row = document.querySelectorAll('#items-table tr').length;

document.getElementById('add-row').addEventListener('click', function() {

    let html = `
    <tr>
        <td><input type="text" name="items[${row}][producto]" class="form-control" required></td>
        <td><input type="number" step="1" name="items[${row}][cantidad]" class="form-control" value="1" required></td>
        <td><input type="number" step="0.01" name="items[${row}][precio_unitario]" class="form-control" value="0" required></td>
        <td><input type="number" step="0.01" name="items[${row}][iva]" class="form-control" value="21"></td>
        <td><input type="number" step="0.01" class="form-control item-total" readonly></td>
        <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
    </tr>
    `;

    document.getElementById('items-table').insertAdjacentHTML('beforeend', html);
    row++;
    calcularTotales();
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-row')) {
        e.target.closest('tr').remove();
        calcularTotales();
    }
});

document.addEventListener('input', function(e) {
    if (e.target.closest('#items-table')) {
        calcularTotales();
    }
});

function calcularTotales() {

    let filas = document.querySelectorAll('#items-table tr');
    let totalGeneral = 0;

    filas.forEach(function(row) {

        let cantidad = parseFloat(row.querySelector('[name*="[cantidad]"]')?.value) || 0;
        let precio   = parseFloat(row.querySelector('[name*="[precio_unitario]"]')?.value) || 0;
        let iva      = parseFloat(row.querySelector('[name*="[iva]"]')?.value) || 0;

        let totalInput = row.querySelector('.item-total');

        let base = cantidad * precio;
        let total = base + (base * iva / 100);

        if (totalInput) totalInput.value = total.toFixed(2);

        totalGeneral += total;
    });

    document.querySelector('input[name="importe_total"]').value =
        totalGeneral.toFixed(2);
}

// Calcular al cargar si es edición
document.addEventListener('DOMContentLoaded', function() {
    calcularTotales();
});

</script>

{{-- =========================
     AUTOCOMPLETADO CLIENTES
========================= --}}
<script>

const inputRazon = document.getElementById('razon_social');
const dropdownClientes = document.getElementById('dropdown-clientes');
const inputClienteId = document.getElementById('cliente_id');

let debounceTimer = null;

function ocultarDropdown() {
    dropdownClientes.style.display = 'none';
    dropdownClientes.innerHTML = '';
}

function mostrarDropdown() {
    dropdownClientes.style.display = 'block';
}

async function buscarClientes(q) {

    const resp = await fetch(`{{ route('clientes.buscar') }}?q=${encodeURIComponent(q)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    if (!resp.ok) return [];
    return await resp.json();
}

inputRazon.addEventListener('input', function() {

    inputClienteId.value = '';
    let q = this.value.trim();

    if (q.length < 2) {
        ocultarDropdown();
        return;
    }

    clearTimeout(debounceTimer);

    debounceTimer = setTimeout(async () => {
        let clientes = await buscarClientes(q);

        dropdownClientes.innerHTML = '';

        clientes.forEach(cli => {
            let item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action';

            item.innerHTML = `<strong>${cli.razon_social}</strong><br><small>${cli.cuit ?? ''}</small>`;

            item.onclick = function() {
                inputRazon.value = cli.razon_social;
                inputClienteId.value = cli.id;
                ocultarDropdown();
            };

            dropdownClientes.appendChild(item);
        });

        mostrarDropdown();
    }, 250);
});

document.addEventListener('click', function(e) {
    if (!dropdownClientes.contains(e.target) && !inputRazon.contains(e.target)) {
        ocultarDropdown();
    }
});

</script>
