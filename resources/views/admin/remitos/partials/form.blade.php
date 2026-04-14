@csrf

<div class="row">

    {{-- ID REM (Auto) --}}
    <div class="col-md-4 mb-3">
        <label>ID REM (#)</label>
        <input type="text" class="form-control" value="{{ isset($remito) ? 'REM-'.$remito->id : 'REM-'.($nextId ?? '') }}" readonly>
    </div>

    {{-- Número de Remito --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Número de Remito</label>
        <input type="text" name="numero_remito" class="form-control"
               value="{{ old('numero_remito', $remito->numero_remito ?? '') }}" required>
    </div>

    {{-- Cliente --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Cliente</label>
        <select name="id_cliente" id="id_cliente" class="form-control select2" required>
            <option value="">Seleccione cliente</option>
            @foreach($clientes as $cliente)
                <option value="{{ $cliente->id }}"
                    {{ old('id_cliente', $remito->id_cliente ?? '') == $cliente->id ? 'selected' : '' }}>
                    {{ $cliente->razon_social }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Fecha --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Fecha</label>
        <input type="date" name="fecha" class="form-control"
               value="{{ old('fecha', isset($remito->fecha) ? $remito->fecha->format('Y-m-d') : date('Y-m-d')) }}" required>
    </div>

    {{-- Motivo (Pedido / Particular) --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Motivo</label>
        <select name="motivo" id="motivo" class="form-control" required>
            <option value="pedido" {{ old('motivo', $remito->motivo ?? 'pedido') == 'pedido' ? 'selected' : '' }}>Vinculado (Pedido)</option>
            <option value="particular" {{ old('motivo', $remito->motivo ?? '') == 'particular' ? 'selected' : '' }}>Particular</option>
        </select>
    </div>

    {{-- Condición de Venta --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Condición de Venta</label>
        <select name="condicion_venta" class="form-control" required>
            @foreach(['Cuenta corriente','Contado','Mixto','Anticipado','Transferencia diferida'] as $cond)
                <option value="{{ $cond }}"
                    {{ old('condicion_venta', $remito->condicion_venta ?? '') == $cond ? 'selected' : '' }}>
                    {{ $cond }}
                </option>
            @endforeach
        </select>
    </div>

</div>

<div id="section-oc" class="{{ old('motivo', $remito->motivo ?? 'pedido') == 'pedido' ? '' : 'd-none' }}">
    <div class="row align-items-end">
        <div class="col-md-6 mb-3">
            <label>Agregar Orden de Compra</label>
            <select id="select-oc" class="form-control select2">
                <option value="">Seleccione OC</option>
                @foreach($ordenes as $o)
                    <option value="{{ $o->id }}">{{ $o->numero_oc }} ({{ $o->fecha->format('d/m/Y') }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 mb-3">
             <button type="button" id="btn-add-oc" class="btn btn-info btn-block">Agregar Ítems</button>
        </div>
    </div>
</div>

<hr>

{{-- ================== ITEMS ================== --}}
<h5>Detalles del Envío</h5>

<table class="table table-bordered" id="tabla-items">
    <thead class="thead-light">
        <tr>
            <th>Código</th>
            <th>Artículo / OC</th>
            <th>Cantidad</th>
            <th>Descripción</th>
            <th width="50">Acción</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($remito) && $remito->items->count() > 0)
            @foreach($remito->items as $idx => $item)
                <tr>
                    <td>
                        <input type="text" name="items[{{ $idx }}][codigo]" class="form-control" value="{{ $item->codigo }}">
                    </td>
                    <td>
                        <input type="text" name="items[{{ $idx }}][articulo]" class="form-control" value="{{ $item->articulo }}" required>
                        @if($item->id_orden_item)
                             <small class="text-info">Ref OC Ítem #{{ $item->id_orden_item }}</small>
                             <input type="hidden" name="items[{{ $idx }}][id_orden_item]" value="{{ $item->id_orden_item }}">
                        @endif
                    </td>
                    <td>
                        <input type="number" name="items[{{ $idx }}][cantidad]" class="form-control" value="{{ $item->cantidad }}" required>
                    </td>
                    <td>
                        <input type="text" name="items[{{ $idx }}][descripcion]" class="form-control" value="{{ $item->descripcion }}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm eliminar-fila">X</button>
                    </td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>

<button type="button" class="btn btn-success btn-sm" id="agregar-item-manual">
    <i class="fas fa-plus"></i> Agregar ítem manual
</button>

<hr>

{{-- ================== FLETE ================== --}}
<h5>Flete y Observaciones</h5>

<div class="row">
    <div class="col-md-4 mb-3">
        <label>Transportista / Chofer</label>
        <input type="text" name="transportista" class="form-control"
               value="{{ old('transportista', $remito->transportista ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label>Domicilio Transportista</label>
        <input type="text" name="domicilio_transportista" class="form-control"
               value="{{ old('domicilio_transportista', $remito->domicilio_transportista ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label>IVA Transporte</label>
        <select name="iva_transportista" class="form-control">
            @foreach(['Responsable inscripto','Exento','No responsable','Consumidor final','Monotributista'] as $iva)
                <option value="{{ $iva }}" {{ (old('iva_transportista', $remito->iva_transportista ?? '') == $iva) ? 'selected' : '' }}>{{ $iva }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label>CUIT Transporte</label>
        <input type="text" name="cuit_transportista" class="form-control" maxlength="11"
               value="{{ old('cuit_transportista', $remito->cuit_transportista ?? '') }}">
    </div>

    <div class="col-md-8 mb-3">
        <label>Observación</label>
        <input type="text" name="observacion" class="form-control"
               value="{{ old('observacion', $remito->observacion ?? '') }}">
    </div>
</div>

<hr>

{{-- CAI y VTO (Omitidos de acciones automáticas por ahora, se mantienen como inputs manuales si existen en la vista) --}}
<div class="row d-none">
    <div class="col-md-6 mb-3">
        <label>CAI</label>
        <input type="text" name="cai" class="form-control" value="{{ old('cai', $remito->cai ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label>Vencimiento CAI</label>
        <input type="date" name="cai_vto" class="form-control" value="{{ old('cai_vto', $remito->cai_vto ?? '') }}">
    </div>
</div>

{{-- ================== SCRIPT LÓGICA FORMULARIO ================== --}}
<script>
let itemIndex = {{ isset($remito) ? $remito->items->count() : 0 }};

document.addEventListener('DOMContentLoaded', function() {
    
    // Toggle Motivo
    document.getElementById('motivo').addEventListener('change', function() {
        if (this.value === 'pedido') {
            document.getElementById('section-oc').classList.remove('d-none');
        } else {
            document.getElementById('section-oc').classList.add('d-none');
        }
    });

    // Agregar ítem manual
    document.getElementById('agregar-item-manual').addEventListener('click', function () {
        addRow();
    });

    // Elminar fila
    document.addEventListener('click', function(e){
        if(e.target.classList.contains('eliminar-fila')){
            e.target.closest('tr').remove();
        }
    });

    // Agregar OC por AJAX
    document.getElementById('btn-add-oc').addEventListener('click', function() {
        let ocId = document.getElementById('select-oc').value;
        if(!ocId) return alert('Seleccione una OC');

        let url = "{{ route('ordenes.jsonItems', ['orden' => ':id']) }}".replace(':id', ocId);
        fetch(url)
            .then(response => response.json())
            .then(items => {
                items.forEach(item => {
                    addRow(item, ocId);
                });
            })
            .catch(error => {
                console.error('Error fetching OC items:', error);
                alert('No se pudieron cargar los ítems de la OC');
            });
    });

    // --- FILTRADO DE OC POR CLIENTE ---
    const selectCliente = document.getElementById('id_cliente');
    const selectOC = document.getElementById('select-oc');

    // Usamos el evento de jQuery que Select2 siempre dispara para asegurar captura del cambio
    let lastClienteId = "{{ old('id_cliente', $remito->id_cliente ?? '') }}";

    $(document).on('change', '#id_cliente', function() {
        const clienteId = this.value;
        
        // Evitar limpiar si es el mismo cliente que ya estaba cargado (ej. al inicio del Edit)
        if (clienteId && clienteId == lastClienteId) {
            console.log("Senda: Mismo cliente detected, ignorando clear");
            return;
        }
        
        console.log("Senda: Cambio detectado en id_cliente:", clienteId);
        lastClienteId = clienteId;

        // 1. Limpiar la tabla de ítems
        document.querySelector('#tabla-items tbody').innerHTML = '';

        // 2. Limpiar el selector de OC
        selectOC.innerHTML = '<option value="">Seleccione OC</option>';
        if (typeof $ !== 'undefined' && $(selectOC).data('select2')) {
            $(selectOC).val(null).trigger('change');
        }

        if (!clienteId) return;

        // 3. Cargar OCs del cliente por AJAX
        fetchOrdenes(clienteId);
    });

    function fetchOrdenes(clienteId) {
        let url = "{{ route('ordenes.byCliente', ['clienteId' => ':id']) }}".replace(':id', clienteId);
        console.log("Senda: Fetching OCs desde:", url);

        fetch(url)
            .then(response => response.json())
            .then(data => {
                console.log("Senda: OCs recibidas:", data.length);
                data.forEach(oc => {
                    const fecha = oc.fecha ? new Date(oc.fecha).toLocaleDateString('es-AR') : 'S/F';
                    const option = new Option(`${oc.numero_oc} (${fecha})`, oc.id);
                    selectOC.add(option);
                });
                
                if (typeof $ !== 'undefined' && $(selectOC).data('select2')) {
                    $(selectOC).trigger('change');
                }
            })
            .catch(error => {
                console.error('Senda Error fetching OCs:', error);
            });
    }

    // Si al cargar ya hay un cliente (ej. Edit o validación fallida), nos aseguramos de que el selector de OC sea coherente
    if (selectCliente.value && selectOC.options.length <= 1) {
        fetchOrdenes(selectCliente.value);
    }
});

function addRow(itemData = null, ocId = null) {
    let codigo = itemData ? (itemData.codigo || '') : '';
    let articulo = itemData ? (itemData.descripcion || '') : '';
    let cantidad = itemData ? (itemData.cantidad || 1) : 1;
    let ocInfo = ocId ? `<small class="text-info">Ref OC #${ocId}</small><input type="hidden" name="items[${itemIndex}][id_orden_compra]" value="${ocId}"><input type="hidden" name="items[${itemIndex}][id_orden_item]" value="${itemData.id}">` : '';

    let row = `
        <tr>
            <td><input type="text" name="items[${itemIndex}][codigo]" class="form-control" value="${codigo}"></td>
            <td>
                <input type="text" name="items[${itemIndex}][articulo]" class="form-control" value="${articulo}" required>
                ${ocInfo}
            </td>
            <td><input type="number" name="items[${itemIndex}][cantidad]" class="form-control" value="${cantidad}" required></td>
            <td><input type="text" name="items[${itemIndex}][descripcion]" class="form-control"></td>
            <td><button type="button" class="btn btn-danger btn-sm eliminar-fila">X</button></td>
        </tr>
    `;
    document.querySelector('#tabla-items tbody').insertAdjacentHTML('beforeend', row);
    itemIndex++;
}
</script>
