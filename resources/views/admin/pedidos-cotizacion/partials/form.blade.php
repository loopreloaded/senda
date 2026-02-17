<div class="row">

    {{-- Archivo --}}
    <div class="col-md-6">
        <label>Archivo Adjunto (PDF / Imagen)</label>

        <input type="file"
               name="archivo"
               class="form-control"
               accept=".pdf,.jpg,.jpeg,.png">

        @if(isset($pedido) && $pedido->archivo)
            <div class="mt-2">
                <a href="{{ asset('storage/' . $pedido->archivo) }}"
                   target="_blank"
                   class="btn btn-sm btn-light">
                    <i class="fas fa-file"></i> Ver archivo actual
                </a>
            </div>
        @endif
    </div>

</div>

<hr>

<div class="row">
    <div class="col-md-12">
        <label>Observaciones</label>
        <textarea name="observaciones"
                  class="form-control"
                  rows="4"
                  placeholder="Ingrese observaciones del pedido...">{{ old('observaciones', $pedido->observaciones ?? '') }}</textarea>
    </div>
</div>
