{{-- resources/views/admin/clientes/partials/form.blade.php --}}

{{-- FILA 1 --}}
<div class="row">

    <div class="col-md-8">
        <label>Razón Social</label>
        <input type="text"
               name="razon_social"
               class="form-control"
               value="{{ old('razon_social') }}"
               required>
    </div>

    <div class="col-md-4">
        <label>CUIT</label>
        <input type="text"
               name="cuit"
               class="form-control"
               maxlength="11"
               value="{{ old('cuit') }}"
               required>
    </div>

</div>


{{-- FILA 2 --}}
<div class="row mt-3">

    <div class="col-md-4">
        <label>Dirección (Localidad - Provincia)</label>
        <input type="text"
               name="domicilio_comercial"
               class="form-control"
               value="{{ old('domicilio_comercial') }}"
               required>
    </div>

    <div class="col-md-4">
        <label>Teléfono</label>
        <input type="text"
               name="telefono"
               class="form-control"
               value="{{ old('telefono') }}">
    </div>

    <div class="col-md-4">
        <label>Email</label>
        <input type="email"
               name="email"
               class="form-control"
               value="{{ old('email') }}">
    </div>

</div>


{{-- FILA 3 --}}
<div class="row mt-3">

    <div class="col-md-4">
        <label>Condición IVA</label>
        <select name="condicion_iva_id" class="form-control" required>
            <option value="">Seleccione...</option>
            @foreach($condicionesIva as $iva)
                <option value="{{ $iva->id }}" {{ old('condicion_iva_id') == $iva->id ? 'selected' : '' }}>
                    {{ $iva->nombre }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label>Condición IIBB</label>
        <select name="condicion_iibb_id" class="form-control" required>
            <option value="">Seleccione...</option>
            @foreach($condicionesIibb as $iibb)
                <option value="{{ $iibb->id }}" {{ old('condicion_iibb_id') == $iibb->id ? 'selected' : '' }}>
                    {{ $iibb->nombre }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label>Tipo</label>
        <select name="tipo" class="form-control" required>
            <option value="">Seleccione...</option>
            <option value="C" {{ old('tipo') == 'C' ? 'selected' : '' }}>Cliente</option>
            <option value="P" {{ old('tipo') == 'P' ? 'selected' : '' }}>Proveedor</option>
            <option value="A" {{ old('tipo') == 'A' ? 'selected' : '' }}>Ambos</option>
        </select>
    </div>

</div>