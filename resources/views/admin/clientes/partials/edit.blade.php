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
    <label>Domicilio Comercial</label>
        <input type="text"
            name="direccion"
            class="form-control"
            value="{{ old('direccion', $cliente->direccion ?? '') }}"
            required>
</div>
