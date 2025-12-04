{{-- FORMULARIO DE RECIBO --}}

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">

    {{-- Fecha --}}
    <div class="col-md-3">
        <div class="form-group">
            <label for="fecha">Fecha</label>
            <input
                type="date"
                name="fecha"
                id="fecha"
                class="form-control"
                value="{{ old('fecha', isset($recibo) && $recibo->fecha ? $recibo->fecha->format('Y-m-d') : date('Y-m-d')) }}"
                required
            >
        </div>
    </div>

    {{-- Nro Recibo --}}
    <div class="col-md-3">
        <div class="form-group">
            <label for="nro_recibo">Nro Recibo</label>
            <input
                type="text"
                name="nro_recibo"
                id="nro_recibo"
                maxlength="20" {{-- coincide con varchar(20) --}}
                class="form-control"
                value="{{ old('nro_recibo', $recibo->nro_recibo ?? '') }}"
                required
            >
        </div>
    </div>

</div>
