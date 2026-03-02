@csrf

<div class="row">

    {{-- Número de Remito --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Número de Remito</label>
        <input type="text"
               name="numero"
               class="form-control"
               value="{{ old('numero', $remito->numero ?? '') }}"
               required>
    </div>

    {{-- Cliente --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Cliente</label>
        <select name="cliente_id" class="form-control" required>
            <option value="">Seleccione cliente</option>
            @foreach($clientes as $cliente)
                <option value="{{ $cliente->id }}"
                    {{ old('cliente_id', $remito->cliente_id ?? '') == $cliente->id ? 'selected' : '' }}>
                    {{ $cliente->razon_social }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Fecha --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Fecha</label>
        <input type="date"
               name="fecha"
               class="form-control"
               value="{{ old('fecha', isset($remito->fecha) ? $remito->fecha->format('Y-m-d') : '') }}"
               required>
    </div>

    {{-- OC Asociada --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">OC Asociada</label>
        <input type="text"
               name="oc_asociada"
               class="form-control"
               value="{{ old('oc_asociada', $remito->oc_asociada ?? '') }}">
    </div>

    {{-- Factura Relacionada --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Factura Relacionada</label>
        <input type="text"
               name="factura_relacionada"
               class="form-control"
               value="{{ old('factura_relacionada', $remito->factura_relacionada ?? '') }}">
    </div>

    {{-- Estado --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Estado</label>
        <select name="estado" class="form-control" required>
            <option value="Emitido"
                {{ old('estado', $remito->estado ?? '') == 'Emitido' ? 'selected' : '' }}>
                Emitido
            </option>
            <option value="Confirmado"
                {{ old('estado', $remito->estado ?? '') == 'Confirmado' ? 'selected' : '' }}>
                Confirmado
            </option>
            <option value="Anulado"
                {{ old('estado', $remito->estado ?? '') == 'Anulado' ? 'selected' : '' }}>
                Anulado
            </option>
        </select>
    </div>

</div>

<div class="mt-3">
    <button type="submit" class="btn btn-primary">
        Guardar
    </button>
    <a href="{{ route('remitos.index') }}" class="btn btn-secondary">
        Cancelar
    </a>
</div>
