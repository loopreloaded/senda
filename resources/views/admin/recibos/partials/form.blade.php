{{-- FORMULARIO DE RECIBO --}}

<div class="row">
    {{-- Cliente --}}
    <div class="col-md-6">
        <div class="form-group">
            <label for="cliente_id">Cliente <span class="text-danger">*</span></label>
            <select name="cliente_id" id="cliente_id" class="form-control select2" required>
                <option value="">Seleccione un cliente...</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}" 
                        {{ old('cliente_id', $recibo->cliente_id) == $cliente->id ? 'selected' : '' }}
                        data-razon="{{ $cliente->razon_social }}"
                        data-cuit="{{ $cliente->cuit }}"
                        data-direccion="{{ $cliente->direccion }}">
                        {{ $cliente->razon_social }} ({{ $cliente->cuit }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Fecha --}}
    <div class="col-md-3">
        <div class="form-group">
            <label for="fecha">Fecha <span class="text-danger">*</span></label>
            <input type="date" name="fecha" id="fecha" class="form-control"
                   value="{{ old('fecha', $recibo->fecha ? $recibo->fecha->format('Y-m-d') : date('Y-m-d')) }}" required>
        </div>
    </div>

    {{-- Nro Recibo --}}
    <div class="col-md-3">
        <div class="form-group">
            <label for="nro_recibo">Nro. de Recibo <span class="text-danger">*</span></label>
            <input type="text" name="nro_recibo" id="nro_recibo" class="form-control" 
                   value="{{ old('nro_recibo', $recibo->nro_recibo) }}" 
                   placeholder="Ej: 0001-00003541" required>
        </div>
    </div>
</div>

<div class="row">
    {{-- Motivo --}}
    <div class="col-md-3">
        <div class="form-group">
            <label for="motivo">Motivo <span class="text-danger">*</span></label>
            <select name="motivo" id="motivo" class="form-control no-select2" required>
                <option value="pedido" {{ old('motivo', $recibo->motivo ?? 'pedido') == 'pedido' ? 'selected' : '' }}>Pedido</option>
                <option value="particular" {{ old('motivo', $recibo->motivo) == 'particular' ? 'selected' : '' }}>Particular</option>
            </select>
        </div>
    </div>

    {{-- Detalles de Pago --}}
    <div class="col-md-6">
        <div class="form-group">
            <label for="detalles_pago">Detalles del Pago</label>
            <input type="text" name="detalles_pago" id="detalles_pago" class="form-control" 
                   value="{{ old('detalles_pago', $recibo->detalles_pago) }}" 
                   placeholder="Transferencia, Cheque #..., Efectivo, etc.">
        </div>
    </div>

    {{-- ID REC (Informativo) --}}
    <div class="col-md-3">
        <div class="form-group">
            <label>ID REC (#)</label>
            <input type="text" class="form-control" value="{{ $formattedId ?? '#' . str_pad($recibo->id_recibo, 4, '0', STR_PAD_LEFT) }}" disabled>
        </div>
    </div>
</div>

<hr>

{{-- Sección Órdenes de Pago (Pedido) --}}
<div id="section-ops" style="{{ old('motivo', $recibo->motivo ?? 'pedido') == 'pedido' ? '' : 'display: none;' }}">
    <h5>Órdenes de Pago Disponibles</h5>
    <div class="table-responsive">
        <table class="table table-sm table-bordered" id="table-ops">
            <thead class="thead-light">
                <tr>
                    <th>ID OP</th>
                    <th>Nro OP</th>
                    <th>Fecha</th>
                    <th>Pagado en OP</th>
                    <th>Ya Saldado</th>
                    <th style="width: 150px;">A Saldar ahora</th>
                </tr>
            </thead>
            <tbody>
                {{-- Se llena dinámicamente o por Laravel si hay error de validación --}}
                @if(old('ops') || (isset($recibo) && $recibo->ordenesPago->count() > 0))
                    @php
                        $ops_selected = old('ops', []);
                        if (empty($ops_selected) && isset($recibo)) {
                            foreach($recibo->ordenesPago as $op) {
                                $ops_selected[$op->id] = ['id' => $op->id, 'saldado' => $op->pivot->saldado];
                            }
                        }
                    @endphp
                    @foreach($ops_selected as $id => $op_array)
                        @php $op_full = \App\Models\OrdenPago::find($id); @endphp
                        @if($op_full)
                            <tr>
                                <td>OP-{{ str_pad($op_full->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $op_full->nro_op }}</td>
                                <td>{{ $op_full->fecha->format('d/m/Y') }}</td>
                                <td>$ {{ number_format($op_full->importe_pagado, 2) }}</td>
                                <td>$ {{ number_format($op_full->importe_saldado, 2) }}</td>
                                <td>
                                    <input type="hidden" name="ops[{{ $id }}][id]" value="{{ $id }}">
                                    <input type="number" name="ops[{{ $id }}][saldado]" 
                                           class="form-control form-control-sm op-saldado" 
                                           step="0.01" min="0" max="{{ $op_full->importe_pagado }}"
                                           value="{{ $op_array['saldado'] }}">
                                </td>
                            </tr>
                        @endif
                    @endforeach
                @else
                    <tr class="placeholder-row">
                        <td colspan="6" class="text-center text-muted">Seleccione un cliente para ver sus OPs</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

{{-- Sección Importe Manual (Particular) --}}
<div id="section-particular" style="{{ old('motivo', $recibo->motivo) == 'particular' ? '' : 'display: none;' }}">
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="importe_saldado_input">Importe Saldado <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                    <input type="number" name="importe_saldado" id="importe_saldado_input" 
                           class="form-control" step="0.01" min="0" 
                           value="{{ old('importe_saldado', $recibo->importe_saldado) }}">
                </div>
            </div>
        </div>
    </div>
</div>

<hr>

<div class="row">
    {{-- Retenciones --}}
    <div class="col-md-8">
        <h5>Retenciones Impositivas</h5>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="iva">IVA</label>
                    <input type="number" name="iva" id="iva" class="form-control retencion-input" 
                           step="0.01" min="0" value="{{ old('iva', $recibo->iva ?? 0) }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="ganancia">Ganancia</label>
                    <input type="number" name="ganancia" id="ganancia" class="form-control retencion-input" 
                           step="0.01" min="0" value="{{ old('ganancia', $recibo->ganancia ?? 0) }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="iibb">Ingresos Brutos (IIBB)</label>
                    <input type="number" name="iibb" id="iibb" class="form-control retencion-input" 
                           step="0.01" min="0" value="{{ old('iibb', $recibo->iibb ?? 0) }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="percepcion_ib">Percepción I.B.</label>
                    <input type="number" name="percepcion_ib" id="percepcion_ib" class="form-control retencion-input" 
                           step="0.01" min="0" value="{{ old('percepcion_ib', $recibo->percepcion_ib ?? 0) }}">
                </div>
            </div>
        </div>
    </div>

    {{-- Totales --}}
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Importe Saldado:</span>
                    <strong id="display-saldado">$ 0.00</strong>
                </div>
                <div class="d-flex justify-content-between mb-2 text-danger">
                    <span>Total Retenciones:</span>
                    <strong id="display-retenciones">$ 0.00</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between h5">
                    <span>TOTAL GENERAL:</span>
                    <strong id="display-total">$ 0.00</strong>
                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap4' });

        function updateTotals() {
            let saldado = 0;
            if ($('#motivo').val() === 'pedido') {
                $('.op-saldado').each(function() {
                    saldado += parseFloat($(this).val()) || 0;
                });
            } else {
                saldado = parseFloat($('#importe_saldado_input').val()) || 0;
            }

            let retenciones = 0;
            $('.retencion-input').each(function() {
                retenciones += parseFloat($(this).val()) || 0;
            });

            let total = saldado + retenciones;

            $('#display-saldado').text('$ ' + saldado.toLocaleString('es-AR', {minimumFractionDigits: 2}));
            $('#display-retenciones').text('$ ' + retenciones.toLocaleString('es-AR', {minimumFractionDigits: 2}));
            $('#display-total').text('$ ' + total.toLocaleString('es-AR', {minimumFractionDigits: 2}));
        }

        $('#cliente_id').on('change', function() {
            let clienteId = $(this).val();
            if (!clienteId || $('#motivo').val() !== 'pedido') return;

            let url = "{{ route('recibos.ops', ':id') }}";
            url = url.replace(':id', clienteId);

            $.get(url, function(data) {
                let tbody = $('#table-ops tbody');
                tbody.empty();
                if (data.length === 0) {
                    tbody.append('<tr><td colspan="6" class="text-center text-muted">No hay OPs disponibles para este cliente</td></tr>');
                } else {
                    data.forEach(function(op) {
                        let formattedId = 'OP-' + String(op.id).padStart(4, '0');
                        let fecha = new Date(op.fecha).toLocaleDateString('es-AR');
                        tbody.append(`
                            <tr>
                                <td>${formattedId}</td>
                                <td>${op.nro_op ?? '-'}</td>
                                <td>${fecha}</td>
                                <td>$ ${parseFloat(op.importe_pagado).toLocaleString('es-AR', {minimumFractionDigits: 2})}</td>
                                <td>$ ${parseFloat(op.importe_saldado).toLocaleString('es-AR', {minimumFractionDigits: 2})}</td>
                                <td>
                                    <input type="hidden" name="ops[${op.id}][id]" value="${op.id}">
                                    <input type="number" name="ops[${op.id}][saldado]" 
                                           class="form-control form-control-sm op-saldado" 
                                           step="0.01" min="0" max="${op.importe_pagado}"
                                           value="0">
                                </td>
                            </tr>
                        `);
                    });
                }
                updateTotals();
            });
        });

        $('#motivo').on('change', function() {
            if ($(this).val() === 'pedido') {
                $('#section-ops').show();
                $('#section-particular').hide();
                $('#cliente_id').trigger('change');
            } else {
                $('#section-ops').hide();
                $('#section-particular').show();
            }
            updateTotals();
        });

        $(document).on('input', '.op-saldado, #importe_saldado_input, .retencion-input', function() {
            updateTotals();
        });

        updateTotals();
    });
</script>
@stop
