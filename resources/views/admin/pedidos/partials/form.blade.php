
<div class="row">

    {{-- Archivo --}}
    <div class="col-md-6">
        <label>Archivo Adjunto (PDF / Imagen)</label>

        <input type="file"
               name="archivo"
               class="form-control"
               accept=".pdf,.jpg,.jpeg,.png">

        @if(isset($pedido) && $pedido->archivo)
            <div class="mt-2">
                <a href="{{ asset('storage/' . $pedido->archivo) }}"
                   target="_blank"
                   class="btn btn-sm btn-light">
                    <i class="fas fa-file"></i> Ver archivo actual
                </a>
            </div>
        @endif
    </div>

</div>

<hr>

<div class="row">

    {{-- Fecha --}}
    <div class="col-md-3">
        <label>Fecha</label>
        <input type="date"
               name="fecha"
               class="form-control"
               value="{{ old('fecha', isset($pedido) ? $pedido->fecha : now()->format('Y-m-d')) }}"
               required>
    </div>

    {{-- Nro Solicitud --}}
    <div class="col-md-3">
        <label>N° Solicitud</label>
        <input type="text"
               name="nro_solicitud"
               class="form-control"
               placeholder="Ingrese N° de solicitud..."
               value="{{ old('nro_solicitud', $pedido->nro_solicitud ?? '') }}">
    </div>

    {{-- Cantidad --}}
    <div class="col-md-2">
        <label>Cantidad de Artículos</label>
        <input type="number"
               name="cantidad"
               class="form-control"
               min="1"
               placeholder="Total"
               value="{{ old('cantidad', $pedido->cantidad ?? '') }}"
               required>
    </div>

    {{-- Cliente --}}
    <div class="col-md-4">
        <div class="form-group">
            <label for="razon_social">Cliente</label>

            <div class="position-relative">
                <input type="text"
                       name="razon_social"
                       id="razon_social"
                       value="{{ old('razon_social', $pedido->cliente->razon_social ?? '') }}"
                       class="form-control"
                       autocomplete="off"
                       required>

                {{-- ID cliente --}}
                <input type="hidden"
                       name="id_cliente"
                       id="id_cliente"
                       value="{{ old('id_cliente', $pedido->id_cliente ?? '') }}">

                {{-- dropdown --}}
                <div id="dropdown-clientes"
                     class="list-group position-absolute w-100 shadow"
                     style="z-index:9999; max-height:240px; overflow-y:auto; display:none;">
                </div>
            </div>
        </div>
    </div>

</div>

<div class="row">

    {{-- Items Excluidos --}}
    <div class="col-md-12">
        <label>Articulos Excluidos</label>
        <input type="text"
               name="items_excluidos"
               class="form-control"
               placeholder="Ingrese los items que quedan excluidos del pedido..."
               value="{{ old('items_excluidos', $pedido->items_excluidos ?? '') }}">
    </div>

</div>

<hr>

<div class="row">
    <div class="col-md-12">
        <label>Observaciones</label>
        <textarea name="observaciones"
                  class="form-control"
                  rows="4"
                  placeholder="Ingrese observaciones del pedido...">{{ old('observaciones', $pedido->observaciones ?? '') }}</textarea>
    </div>
</div>


{{-- ============================================================
   SCRIPT AUTOCOMPLETADO CLIENTES – PEDIDOS
   ============================================================ --}}
<script>
const inputRazon = document.getElementById('razon_social');
const dropdownClientes = document.getElementById('dropdown-clientes');
const inputClienteId = document.getElementById('id_cliente');

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
