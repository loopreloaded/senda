<form action="{{ route('pedidos-cotizacion.update', $pedido_cotizacion->id_ped_cot) }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf
    @method('PUT')

    <div class="row">


        {{-- Fecha --}}
        <div class="col-md-4 mb-3">
            <label class="form-label">Fecha *</label>
            <input type="date"
                   name="fecha"
                   class="form-control @error('fecha') is-invalid @enderror"
                   value="{{ old('fecha', \Carbon\Carbon::parse($pedido_cotizacion->fecha)->format('Y-m-d')) }}"
                   required>
            @error('fecha')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Cliente --}}
        <div class="col-md-4 mb-3">
            <label class="form-label">Cliente *</label>
            <select name="id_cliente"
                    class="form-select @error('id_cliente') is-invalid @enderror"
                    required>
                <option value="">-- Seleccionar --</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}"
                        {{ old('id_cliente', $pedido_cotizacion->id_cliente) == $cliente->id ? 'selected' : '' }}>
                        {{ $cliente->razon_social }}
                    </option>
                @endforeach
            </select>
            @error('id_cliente')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Estado --}}
        <div class="col-md-4 mb-3">
            <label class="form-label">Estado *</label>
            <select name="estado"
                    class="form-select @error('estado') is-invalid @enderror"
                    required>
                <option value="p" {{ old('estado', $pedido_cotizacion->estado) == 'p' ? 'selected' : '' }}>
                    Pendiente
                </option>
                <option value="c" {{ old('estado', $pedido_cotizacion->estado) == 'c' ? 'selected' : '' }}>
                    Cotizado
                </option>
            </select>
            @error('estado')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

    </div>

    <div class="row">

        {{-- Archivo --}}
        <div class="col-md-6 mb-3">
            <label class="form-label">Archivo (PDF / Imagen)</label>
            <input type="file"
                   name="archivo"
                   class="form-control @error('archivo') is-invalid @enderror">
            @error('archivo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if($pedido_cotizacion->archivo)
                <div class="mt-2">
                    <a href="{{ asset('storage/'.$pedido_cotizacion->archivo) }}"
                       target="_blank"
                       class="btn btn-sm btn-outline-primary">
                        Ver archivo actual
                    </a>
                </div>
            @endif
        </div>

        {{-- Observaciones --}}
        <div class="col-md-6 mb-3">
            <label class="form-label">Observaciones</label>
            <textarea name="observaciones"
                      rows="3"
                      class="form-control @error('observaciones') is-invalid @enderror">{{ old('observaciones', $pedido_cotizacion->observaciones) }}</textarea>
            @error('observaciones')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

    </div>

</form>
