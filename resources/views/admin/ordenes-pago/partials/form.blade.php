<div class="row">
    <div class="col-md-4 mb-3">
        <label>ID OP (#)</label>
        <input type="text" class="form-control" value="{{ $formattedId ?? ($ordenPago->formatted_id ?? '') }}" readonly>
    </div>

    <div class="col-md-4 mb-3">
        <label>Cliente</label>
        <select name="cliente_id" id="cliente_id" class="form-control select2" required>
            <option value="">Seleccione Cliente</option>
            @foreach($clientes as $cliente)
                <option value="{{ $cliente->id }}" {{ old('cliente_id', $ordenPago->cliente_id ?? '') == $cliente->id ? 'selected' : '' }}>
                    {{ $cliente->razon_social }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label>Fecha</label>
        <input type="date" name="fecha" class="form-control" value="{{ old('fecha', isset($ordenPago->fecha) ? $ordenPago->fecha->format('Y-m-d') : date('Y-m-d')) }}" required>
    </div>

    <div class="col-md-6 mb-3">
        <label>Nro OP (Referencia Cliente)</label>
        <input type="text" name="nro_op" class="form-control" value="{{ old('nro_op', $ordenPago->nro_op ?? '') }}" required>
    </div>

    <div class="col-md-6 mb-3">
        <label>Archivo Original (PDF/Imagen)</label>
        <input type="file" name="archivo" class="form-control">
        @if(isset($ordenPago) && $ordenPago->archivo)
            <small class="text-muted">Actual: <a href="{{ asset('storage/' . $ordenPago->archivo) }}" target="_blank">Ver documento</a></small>
        @endif
    </div>

    <div class="col-md-12 mb-3">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones', $ordenPago->observaciones ?? '') }}</textarea>
    </div>

    <div class="col-md-4 mb-3">
        <label>Motivo</label>
        <select name="motivo" id="motivo" class="form-control select2" required>
            <option value="pedido" {{ old('motivo', isset($ordenPago) ? $ordenPago->motivo : 'pedido') == 'pedido' ? 'selected' : '' }}>Vinculado (Pedido)</option>
            <option value="particular" {{ old('motivo', isset($ordenPago) ? $ordenPago->motivo : '') == 'particular' ? 'selected' : '' }}>Particular</option>
        </select>
    </div>

    <div class="col-md-8 mb-3" id="div-particular" style="{{ old('motivo', $ordenPago->motivo ?? 'pedido') == 'particular' ? '' : 'display:none;' }}">
        <label>Importe Pagado Total</label>
        <div class="input-group">
            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
            <input type="number" step="0.01" name="importe_pagado" class="form-control" value="{{ old('importe_pagado', $ordenPago->importe_pagado ?? 0) }}">
        </div>
    </div>
</div>

<div id="div-pedido" style="{{ old('motivo', $ordenPago->motivo ?? 'pedido') == 'pedido' ? '' : 'display:none;' }}">
    <hr>
    <h5>Facturas Vinculadas</h5>
    <div class="row align-items-end mb-3">
        <div class="col-md-8">
            <label>Seleccionar Factura Pendiente</label>
            <select id="select-factura" class="form-control select2">
                <option value="">Seleccione Factura</option>
            </select>
        </div>
        <div class="col-md-4">
            <button type="button" class="btn btn-info btn-block" id="btn-add-factura"><i class="fas fa-plus"></i> Agregar Factura</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm" id="tabla-facturas">
            <thead class="bg-gray">
                <tr>
                    <th>Factura</th>
                    <th width="150" class="text-right">Total</th>
                    <th width="150" class="text-right">Saldado</th>
                    <th width="150" class="text-right">Pendiente</th>
                    <th width="180">Importe a Pagar</th>
                    <th width="40"></th>
                </tr>
            </thead>
            <tbody>
                @if(old('facturas'))
                    @foreach(old('facturas') as $idx => $f)
                        <tr data-id="{{ $f['id'] }}">
                            <td>
                                 <input type="hidden" name="facturas[{{ $idx }}][id]" value="{{ $f['id'] }}">
                                 ID: {{ $f['id'] }}
                            </td>
                            <td class="text-right">-</td>
                            <td class="text-right">-</td>
                            <td class="text-right">-</td>
                            <td>
                                <input type="number" step="0.01" name="facturas[{{ $idx }}][pagado]" class="form-control form-control-sm input-pagado" value="{{ $f['pagado'] }}">
                            </td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-row">×</button></td>
                        </tr>
                    @endforeach
                @elseif(isset($ordenPago) && $ordenPago->motivo == 'pedido')
                    @foreach($ordenPago->facturas as $idx => $f)
                        <tr data-id="{{ $f->id }}">
                            <td>
                                 <input type="hidden" name="facturas[{{ $idx }}][id]" value="{{ $f->id }}">
                                 {{ $f->tipo_comprobante }} {{ $f->punto_venta }}-{{ $f->numero_comprobante_afip }}
                            </td>
                            <td class="text-right">${{ number_format($f->importe_total, 2, ',', '.') }}</td>
                            <td class="text-right">${{ number_format($f->importe_pagado - $f->pivot->pagado, 2, ',', '.') }}</td>
                            <td class="text-right text-bold text-danger">${{ number_format($f->importe_total - ($f->importe_pagado - $f->pivot->pagado), 2, ',', '.') }}</td>
                            <td>
                                <input type="number" step="0.01" name="facturas[{{ $idx }}][pagado]" class="form-control form-control-sm input-pagado" value="{{ $f->pivot->pagado }}">
                            </td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-row">×</button></td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr class="table-info">
                    <th colspan="4" class="text-right">Total Orden de Pago:</th>
                    <th id="total-op-display" class="text-bold text-lg text-right">$ 0,00</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@section('js')
<script>
    $(document).ready(function() {
        const selectCliente = $('#cliente_id');
        const selectFactura = $('#select-factura');
        const motivo = $('#motivo');
        const divParticular = $('#div-particular');
        const divPedido = $('#div-pedido');
        let facturaIndex = {{ old('facturas') ? count(old('facturas')) : (isset($ordenPago) ? $ordenPago->facturas->count() : 0) }};

        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        // Toggle Motivo
        motivo.on('change', function() {
            if ($(this).val() === 'particular') {
                divParticular.fadeIn();
                divPedido.fadeOut();
            } else {
                divParticular.fadeOut();
                divPedido.fadeIn();
            }
        });

        // Cargar facturas al cambiar cliente
        selectCliente.on('change', function() {
            const clienteId = $(this).val();
            selectFactura.empty().append('<option value="">Seleccione Factura</option>');
            
            // Si estamos creando, limpiamos la tabla. En edit, tal vez mejor no limpiar si es el mismo? 
            // Para simplicidad en este MVP, limpiamos si cambia.
            $('#tabla-facturas tbody').empty();
            updateTotal();

            if (!clienteId) return;

            $.get("{{ route('ordenes-pago.facturas', ':id') }}".replace(':id', clienteId), function(data) {
                data.forEach(f => {
                    const pendiente = parseFloat(f.importe_total) - parseFloat(f.importe_pagado);
                    const label = `${f.tipo_comprobante} ${f.punto_venta}-${f.numero_comprobante_afip} | Total: $${f.importe_total} | Pendiente: $${pendiente.toFixed(2)}`;
                    selectFactura.append(`<option value="${f.id}" data-total="${f.importe_total}" data-pagado="${f.importe_pagado}" data-pendiente="${pendiente}">${label}</option>`);
                });
            });
        });

        // Agregar factura a la tabla
        $('#btn-add-factura').on('click', function() {
            const selected = selectFactura.find(':selected');
            const id = selected.val();
            if (!id) return Swal.fire('Error', 'Seleccione una factura', 'error');

            if ($(`#tabla-facturas tbody tr[data-id="${id}"]`).length > 0) {
                return Swal.fire('Error', 'La factura ya está agregada', 'warning');
            }

            const total = parseFloat(selected.data('total'));
            const pagadoPrevio = parseFloat(selected.data('pagado'));
            const pendiente = parseFloat(selected.data('pendiente'));
            const label = selected.text().split('|')[0];

            const row = `
                <tr data-id="${id}">
                    <td>
                        <input type="hidden" name="facturas[${facturaIndex}][id]" value="${id}">
                        ${label}
                    </td>
                    <td class="text-right">$${total.toLocaleString('es-AR', {minimumFractionDigits: 2})}</td>
                    <td class="text-right">$${pagadoPrevio.toLocaleString('es-AR', {minimumFractionDigits: 2})}</td>
                    <td class="text-right text-bold text-danger">$${pendiente.toLocaleString('es-AR', {minimumFractionDigits: 2})}</td>
                    <td>
                        <input type="number" step="0.01" name="facturas[${facturaIndex}][pagado]" class="form-control form-control-sm input-pagado" value="${pendiente}" max="${pendiente}" required>
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">×</button></td>
                </tr>
            `;
            $('#tabla-facturas tbody').append(row);
            facturaIndex++;
            updateTotal();
        });

        // Eliminar fila
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            updateTotal();
        });

        // Actualizar total al cambiar montos
        $(document).on('change keyup', '.input-pagado', function() {
            const max = parseFloat($(this).attr('max'));
            const val = parseFloat($(this).val());
            
            if (val > max) {
                 Swal.fire('Atención', 'El importe pagado no debe superar el pendiente de la factura.', 'warning');
                 $(this).val(max);
            }
            updateTotal();
        });

        function updateTotal() {
            let total = 0;
            $('.input-pagado').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#total-op-display').text('$ ' + total.toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        }

        // Si es Edit, cargar facturas del cliente inicial (pero sin limpiar)
        if (selectCliente.val()) {
             // Aquí se podría poblar el select-factura sin limpiar la tabla
             $.get("{{ route('ordenes-pago.facturas', ':id') }}".replace(':id', selectCliente.val()), function(data) {
                data.forEach(f => {
                    const pendiente = parseFloat(f.importe_total) - parseFloat(f.importe_pagado);
                    const label = `${f.tipo_comprobante} ${f.punto_venta}-${f.numero_comprobante_afip} | Total: $${f.importe_total} | Pendiente: $${pendiente.toFixed(2)}`;
                    selectFactura.append(`<option value="${f.id}" data-total="${f.importe_total}" data-pagado="${f.importe_pagado}" data-pendiente="${pendiente}">${label}</option>`);
                });
            });
        }

        updateTotal();
    });
</script>
@append
