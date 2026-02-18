{{-- FILA 1 --}}
<div class="row">
    <div class="col-md-6">
        <label>Razón Social</label>
        <input type="text"
               name="razon_social"
               class="form-control"
               value="{{ old('razon_social', $orden->razon_social ?? '') }}"
               required>
    </div>

    <div class="col-md-3">
        <label>CUIT</label>
        <input type="text"
               name="cuit"
               class="form-control"
               maxlength="11"
               value="{{ old('cuit', $orden->cuit ?? '') }}"
               required>
    </div>

    <div class="col-md-3">
        <label>Dirección (Localidad - Provincia)</label>
        <input type="text"
               name="domicilio_comercial"
               class="form-control"
               value="{{ old('domicilio_comercial', $orden->domicilio_comercial ?? '') }}"
               required>
    </div>
</div>


{{-- FILA 2 --}}
<div class="row mt-3">

    <div class="col-md-4">
        <label>Teléfono</label>
        <input type="text"
               name="telefono"
               class="form-control"
               value="{{ old('telefono', $orden->telefono ?? '') }}">
    </div>

    <div class="col-md-4">
        <label>Email</label>
        <input type="email"
               name="email"
               class="form-control"
               value="{{ old('email', $orden->email ?? '') }}">
    </div>

    <div class="col-md-4">
        <label>Tipo</label>
        <select name="tipo" class="form-control" required>
            <option value="">Seleccione...</option>

            <option value="C" {{ old('tipo', $orden->tipo ?? '') == 'C' ? 'selected' : '' }}>
                Cliente
            </option>

            <option value="P" {{ old('tipo', $orden->tipo ?? '') == 'P' ? 'selected' : '' }}>
                Proveedor
            </option>

            <option value="A" {{ old('tipo', $orden->tipo ?? '') == 'A' ? 'selected' : '' }}>
                Ambos
            </option>
        </select>
    </div>

</div>


{{-- FILA 3 --}}
<div class="row mt-3">

    <div class="col-md-4">
        <div class="form-group">
            <label for="condicion_arca">Condición IVA</label>
            <select name="condicion_arca" id="condicion_arca" class="form-control" required>
                <option value="">Seleccione...</option>

                <option value="RI" {{ old('condicion_arca', $orden->condicion_arca ?? '') == 'RI' ? 'selected' : '' }}>
                    Responsable Inscripto
                </option>

                <option value="EX" {{ old('condicion_arca', $orden->condicion_arca ?? '') == 'EX' ? 'selected' : '' }}>
                    Exento
                </option>

                <option value="NR" {{ old('condicion_arca', $orden->condicion_arca ?? '') == 'NR' ? 'selected' : '' }}>
                    No Responsable
                </option>

                <option value="CF" {{ old('condicion_arca', $orden->condicion_arca ?? '') == 'CF' ? 'selected' : '' }}>
                    Consumidor Final
                </option>

                <option value="MT" {{ old('condicion_arca', $orden->condicion_arca ?? '') == 'MT' ? 'selected' : '' }}>
                    Responsable Monotributo
                </option>
            </select>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="condicion_iibb">Condición IIBB</label>
            <select name="condicion_iibb" id="condicion_iibb" class="form-control" required>
                <option value="">Seleccione...</option>

                <option value="L" {{ old('condicion_iibb', $orden->condicion_iibb ?? '') == 'L' ? 'selected' : '' }}>
                    Local
                </option>

                <option value="CM" {{ old('condicion_iibb', $orden->condicion_iibb ?? '') == 'CM' ? 'selected' : '' }}>
                    Convenio Multilateral
                </option>
            </select>
        </div>
    </div>

</div>
