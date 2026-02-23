<div class="row">
    <div class="col-md-3">
        <label>Número de OC</label>
        <input type="number"
               name="numero_oc"
               class="form-control"
               value="{{ old('numero_oc', $orden->numero_oc) }}"
               required>
    </div>

    <div class="col-md-3">
        <label>Fecha</label>
        <input type="date"
               name="fecha"
               class="form-control"
               value="{{ old('fecha', $orden->fecha) }}"
               required>
    </div>

    {{-- Razón Social --}}
    <div class="col-md-6">
        <label>Razón Social</label>

        <div class="position-relative">
            <input type="text"
                   name="razon_social"
                   id="razon_social"
                   class="form-control"
                   value="{{ old('razon_social', $orden->cliente->razon_social ?? '') }}"
                   autocomplete="off"
                   required>

            <input type="hidden"
                   name="id_cliente"
                   id="id_cliente"
                   value="{{ old('id_cliente', $orden->id_cliente) }}">

            <div id="dropdown-clientes"
                 class="list-group position-absolute w-100 shadow"
                 style="z-index:9999; max-height:240px; overflow-y:auto; display:none;">
            </div>
        </div>
    </div>
</div>
