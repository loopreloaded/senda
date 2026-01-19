{{-- resources/views/admin/clientes/partials/edit.blade.php --}}

<div class="row">
    <div class="col-md-3">
        <label>CUIT</label>
        <input type="text"
               name="cuit"
               class="form-control"
               maxlength="11"
               value="{{ old('cuit', $cliente->cuit ?? '') }}"
               required>
    </div>

    <div class="col-md-5">
        <label>Razón Social</label>
        <input type="text"
               name="razon_social"
               class="form-control"
               value="{{ old('razon_social', $cliente->razon_social ?? '') }}"
               required>
    </div>

    <div class="col-md-4">
        <label>Email</label>
        <input type="email"
               name="email"
               class="form-control"
               value="{{ old('email', $cliente->email ?? '') }}">
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4">
        <div class="form-group">
            <label for="condicion_arca">Condición ARCA</label>
            <select name="condicion_arca" id="condicion_arca" class="form-control" required>
                <option value="">Seleccione...</option>

                <option value="RI" {{ old('condicion_arca', $cliente->condicion_iva ?? '') == 'RI' ? 'selected' : '' }}>
                    Responsable Inscripto
                </option>

                <option value="EX" {{ old('condicion_arca', $cliente->condicion_iva ?? '') == 'EX' ? 'selected' : '' }}>
                    Exento
                </option>

                <option value="NR" {{ old('condicion_arca', $cliente->condicion_iva ?? '') == 'NR' ? 'selected' : '' }}>
                    No Responsable
                </option>

                <option value="CF" {{ old('condicion_arca', $cliente->condicion_iva ?? '') == 'CF' ? 'selected' : '' }}>
                    Consumidor Final
                </option>

                <option value="MT" {{ old('condicion_arca', $cliente->condicion_iva ?? '') == 'MT' ? 'selected' : '' }}>
                    Responsable Monotributo
                </option>
            </select>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="condicion_iibb">Condición Ingreso Bruto</label>
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
    </div>

    <div class="col-md-4">
        <label>Domicilio Comercial</label>
        <input type="text"
               name="direccion"
               class="form-control"
               value="{{ old('direccion', $cliente->direccion ?? '') }}"
               required>
    </div>
</div>
