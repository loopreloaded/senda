{{-- resources/views/admin/ordenes/partials/edit.blade.php --}}

<div class="row">

    {{-- Fecha --}}
    <div class="col-md-3">
        <label>Fecha</label>
        <input type="date"
               name="fecha"
               class="form-control"
               value="{{ old('fecha', isset($orden)
                        ? optional($orden->fecha)->format('Y-m-d')
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
                   value="{{ old('razon_social',
                        isset($orden) && $orden->cliente
                            ? $orden->cliente->razon_social . ' - ' . $orden->cliente->cuit
                            : ''
                    ) }}"
                   required>

            <input type="hidden"
                   name="id_cliente"
                   id="id_cliente"
                   value="{{ old('id_cliente', $orden->id_cliente ?? '') }}">

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
            <option value="ARS" {{ old('moneda', $orden->moneda ?? 'ARS') == 'ARS' ? 'selected' : '' }}>ARS</option>
            <option value="USD_BILLETE" {{ old('moneda', $orden->moneda ?? '') == 'USD_BILLETE' ? 'selected' : '' }}>USD Billete</option>
            <option value="USD_DIVISA" {{ old('moneda', $orden->moneda ?? '') == 'USD_DIVISA' ? 'selected' : '' }}>USD Divisa</option>
        </select>
    </div>

</div>


<div class="row mt-3">

    <div class="col-md-4">
        <label>Forma de Pago</label>
        <select name="forma_pago" class="form-control" required>
            <option value="CTA_CTE" {{ old('forma_pago', $orden->forma_pago ?? '') == 'CTA_CTE' ? 'selected' : '' }}>Cuenta Corriente</option>
            <option value="CONTADO" {{ old('forma_pago', $orden->forma_pago ?? '') == 'CONTADO' ? 'selected' : '' }}>Contado</option>
            <option value="MIXTO" {{ old('forma_pago', $orden->forma_pago ?? '') == 'MIXTO' ? 'selected' : '' }}>Mixto</option>
            <option value="ANTICIPADO" {{ old('forma_pago', $orden->forma_pago ?? '') == 'ANTICIPADO' ? 'selected' : '' }}>Anticipado</option>
        </select>
    </div>

    <div class="col-md-4">
        <label>Lugar de Entrega</label>
        <input type="text"
               name="lugar_entrega"
               class="form-control"
               value="{{ old('lugar_entrega', $orden->lugar_entrega ?? '') }}">
    </div>

    <div class="col-md-4">
        <label>Plazo de Entrega</label>
        <input type="text"
               name="plazo_entrega"
               class="form-control"
               value="{{ old('plazo_entrega', $orden->plazo_entrega ?? '') }}">
    </div>

</div>

<hr class="mt-4">

<h4>Ítems de la Orden</h4>

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
            $items = $oldItems ?? ($orden->items ?? []);
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
                <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
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
    <div class="col-md-12">
        <label>Total General</label>
        <input type="number"
               step="0.01"
               name="importe_total"
               class="form-control"
               value="{{ old('importe_total', $orden->importe_total ?? 0) }}"
               readonly>
    </div>
</div>
