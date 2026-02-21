{{-- resources/views/admin/clientes/partials/edit.blade.php --}}

{{-- FILA 1 --}}
<div class="row">

    <div class="col-md-8">
        <label>Razón Social</label>
        <input type="text"
               name="razon_social"
               class="form-control"
               value="{{ old('razon_social', $cliente->razon_social ?? '') }}"
               required>
    </div>

    <div class="col-md-4">
        <label>CUIT</label>
        <input type="text"
               name="cuit"
               class="form-control"
               maxlength="11"
               value="{{ old('cuit', $cliente->cuit ?? '') }}"
               required>
    </div>

</div>


{{-- FILA 2 --}}
<div class="row mt-3">

    <div class="col-md-4">
        <label>Dirección (Localidad - Provincia)</label>
        <input type="text"
               name="direccion"
               class="form-control"
               value="{{ old('direccion', $cliente->direccion ?? '') }}"
               required>
    </div>

    <div class="col-md-4">
        <label>Teléfono</label>
        <input type="text"
               name="telefono"
               class="form-control"
               value="{{ old('telefono', $cliente->telefono ?? '') }}">
    </div>

    <div class="col-md-4">
        <label>Email</label>
        <input type="email"
               name="email"
               class="form-control"
               value="{{ old('email', $cliente->email ?? '') }}">
    </div>

</div>


{{-- FILA 3 --}}
<div class="row mt-3">

    <div class="col-md-4">
        <label for="condicion_iva">Condición IVA</label>
        <select name="condicion_iva" id="condicion_iva" class="form-control" required>
            <option value="">Seleccione...</option>

            <option value="RI" {{ old('condicion_iva', $cliente->condicion_iva ?? '') == 'RI' ? 'selected' : '' }}>
                Responsable Inscripto
            </option>

            <option value="EX" {{ old('condicion_iva', $cliente->condicion_iva ?? '') == 'EX' ? 'selected' : '' }}>
                Exento
            </option>

            <option value="NR" {{ old('condicion_iva', $cliente->condicion_iva ?? '') == 'NR' ? 'selected' : '' }}>
                No Responsable
            </option>

            <option value="CF" {{ old('condicion_iva', $cliente->condicion_iva ?? '') == 'CF' ? 'selected' : '' }}>
                Consumidor Final
            </option>

            <option value="MT" {{ old('condicion_iva', $cliente->condicion_iva ?? '') == 'MT' ? 'selected' : '' }}>
                Responsable Monotributo
            </option>
        </select>
    </div>

    <div class="col-md-4">
        <label for="condicion_iibb">Condición IIBB</label>
        <select name="condicion_iibb" id="condicion_iibb" class="form-control" required>
            <option value="">Seleccione...</option>

            <option value="L" {{ old('condicion_iibb', $cliente->condicion_iibb ?? '') == 'L' ? 'selected' : '' }}>
                Local
            </option>

            <option value="CM" {{ old('condicion_iibb', $cliente->condicion_iibb ?? '') == 'CM' ? 'selected' : '' }}>
                Convenio Multilateral
            </option>
        </select>
    </div>

    <div class="col-md-4">
        <label>Tipo</label>
        <select name="tipo" class="form-control" required>
            <option value="">Seleccione...</option>

            <option value="C" {{ old('tipo', $cliente->tipo ?? '') == 'C' ? 'selected' : '' }}>
                Cliente
            </option>

            <option value="P" {{ old('tipo', $cliente->tipo ?? '') == 'P' ? 'selected' : '' }}>
                Proveedor
            </option>

            <option value="A" {{ old('tipo', $cliente->tipo ?? '') == 'A' ? 'selected' : '' }}>
                Ambos
            </option>
        </select>
    </div>

</div>
