<div class="row">
    <div class="col-md-3">
        <label>CUIT</label>
        <input type="text"
               name="cuit"
               class="form-control"
               maxlength="11"
               value="{{ old('cuit', $orden->cuit ?? '') }}"
               required>
    </div>

    <div class="col-md-5">
        <label>Razón Social</label>
        <input type="text"
               name="razon_social"
               class="form-control"
               value="{{ old('razon_social', $orden->razon_social ?? '') }}"
               required>
    </div>

    <div class="col-md-4">
        <label>Email</label>
        <input type="email"
               name="email"
               class="form-control"
               value="{{ old('email', $orden->email ?? '') }}">
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <label>Domicilio Comercial</label>
        <input type="text"
               name="domicilio_comercial"
               class="form-control"
               value="{{ old('domicilio_comercial', $orden->domicilio_comercial ?? '') }}"
               required>
    </div>
</div>
