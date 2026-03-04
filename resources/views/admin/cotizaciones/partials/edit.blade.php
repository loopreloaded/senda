{{-- resources/views/admin/cotizaciones/partials/edit.blade.php --}}

{{-- FILA 1 --}}
<div class="row">

    {{-- Fecha --}}
    <div class="col-md-3">
        <label>Fecha</label>
        <input type="date"
               name="fecha_cot"
               class="form-control"
               value="{{ old('fecha_cot', optional($cotizacion->fecha_cot)->format('Y-m-d')) }}"
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
                        $cotizacion->cliente
                            ? $cotizacion->cliente->razon_social . ' - ' . $cotizacion->cliente->cuit
                            : ''
                    ) }}"
                   required>

            <input type="hidden"
                   name="id_cliente"
                   id="id_cliente"
                   value="{{ old('id_cliente', $cotizacion->id_cliente) }}">

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
            <option value="ARS" {{ old('moneda', $cotizacion->moneda) == 'ARS' ? 'selected' : '' }}>ARS</option>
            <option value="USD_BILLETE" {{ old('moneda', $cotizacion->moneda) == 'USD_BILLETE' ? 'selected' : '' }}>USD Billete</option>
            <option value="USD_DIVISA" {{ old('moneda', $cotizacion->moneda) == 'USD_DIVISA' ? 'selected' : '' }}>USD Divisa</option>
        </select>
    </div>

</div>


{{-- FILA 2 --}}
<div class="row mt-3">

    <div class="col-md-4">
        <label>Forma de Pago</label>
        <select name="forma_pago" class="form-control" required>
            <option value="CTA_CTE" {{ old('forma_pago', $cotizacion->forma_pago) == 'CTA_CTE' ? 'selected' : '' }}>Cuenta Corriente</option>
            <option value="CONTADO" {{ old('forma_pago', $cotizacion->forma_pago) == 'CONTADO' ? 'selected' : '' }}>Contado</option>
            <option value="MIXTO" {{ old('forma_pago', $cotizacion->forma_pago) == 'MIXTO' ? 'selected' : '' }}>Mixto</option>
            <option value="ANTICIPADO" {{ old('forma_pago', $cotizacion->forma_pago) == 'ANTICIPADO' ? 'selected' : '' }}>Anticipado</option>
        </select>
    </div>

    <div class="col-md-4">
        <label>Lugar de Entrega</label>
        <input type="text"
               name="lugar_entrega"
               class="form-control"
               value="{{ old('lugar_entrega', $cotizacion->lugar_entrega) }}">
    </div>

    <div class="col-md-4">
        <label>Plazo de Entrega</label>
        <input type="text"
               name="plazo_entrega"
               class="form-control"
               value="{{ old('plazo_entrega', $cotizacion->plazo_entrega) }}">
    </div>

</div>


{{-- FILA 3 --}}
<div class="row mt-3">

    <div class="col-md-4">
        <label>Vigencia de Oferta</label>
        <input type="date"
               name="vigencia_oferta"
               class="form-control"
               value="{{ old('vigencia_oferta', optional($cotizacion->vigencia_oferta)->format('Y-m-d')) }}">
    </div>

    <div class="col-md-4">
        <label>Motivo</label>
        <select name="motivo" class="form-control" required>
            <option value="">Seleccione...</option>
            <option value="pedido" {{ old('motivo', $cotizacion->motivo) == 'pedido' ? 'selected' : '' }}>Pedido</option>
            <option value="particular" {{ old('motivo', $cotizacion->motivo) == 'particular' ? 'selected' : '' }}>Particular</option>
        </select>
    </div>

</div>


<hr class="mt-4">

<div class="row mt-3">
    <div class="col-md-6">
        <label>Especificaciones Técnicas</label>
        <textarea name="especificaciones_tecnicas"
                  class="form-control"
                  rows="3">{{ old('especificaciones_tecnicas', $cotizacion->especificaciones_tecnicas) }}</textarea>
    </div>

    <div class="col-md-6">
        <label>Observaciones</label>
        <textarea name="observaciones"
                  class="form-control"
                  rows="3">{{ old('observaciones', $cotizacion->observaciones) }}</textarea>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <label>Total General</label>
        <input type="number"
               step="0.01"
               name="importe_total"
               class="form-control"
               value="{{ old('importe_total', $cotizacion->importe_total) }}"
               readonly>
    </div>
</div>
