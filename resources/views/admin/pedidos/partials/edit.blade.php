<div class="row">


    {{-- Fecha --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Fecha *</label>
        <input type="date"
                name="fecha"
                class="form-control @error('fecha') is-invalid @enderror"
                value="{{ old('fecha', \Carbon\Carbon::parse($pedido->fecha)->format('Y-m-d')) }}"
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
                    {{ old('id_cliente', $pedido->id_cliente) == $cliente->id ? 'selected' : '' }}>
                    {{ $cliente->razon_social }}
                </option>
            @endforeach
        </select>
        @error('id_cliente')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Nro Solicitud --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">N° Solicitud</label>
        <input type="text"
               name="nro_solicitud"
               class="form-control @error('nro_solicitud') is-invalid @enderror"
               placeholder="Ingrese N° de solicitud..."
               value="{{ old('nro_solicitud', $pedido->nro_solicitud ?? '') }}">
        @error('nro_solicitud')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

</div>

<div class="row">

    {{-- Cantidad --}}
    <div class="col-md-3 mb-3">
        <label class="form-label">Cantidad de Artículos *</label>
        <input type="number"
               name="cantidad"
               class="form-control @error('cantidad') is-invalid @enderror"
               min="1"
               placeholder="Total"
               value="{{ old('cantidad', $pedido->cantidad ?? '') }}"
               required>
        @error('cantidad')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
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

        @if($pedido->archivo)
            <div class="mt-2">
                <a href="{{ asset('storage/'.$pedido->archivo) }}"
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
                    class="form-control @error('observaciones') is-invalid @enderror">{{ old('observaciones', $pedido->observaciones) }}</textarea>
        @error('observaciones')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

</div>
