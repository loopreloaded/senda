@extends('adminlte::page')

@section('title','Cotización')

@section('content_header')
    <h1>Cotización #{{ $cotizacion->id_cotizacion }}</h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">

        <p><strong>Fecha:</strong>
            {{ \Carbon\Carbon::parse($cotizacion->fecha_cot)->format('d/m/Y') }}
        </p>

        <p><strong>Cliente:</strong>
            {{ $cotizacion->cliente->razon_social ?? '-' }}
        </p>

        <p><strong>Moneda:</strong>
            {{ $cotizacion->moneda }}
        </p>

        <p><strong>Forma de Pago:</strong>
            {{ $cotizacion->forma_pago }}
        </p>

        <p><strong>Lugar de Entrega:</strong>
            {{ $cotizacion->lugar_entrega ?? '-' }}
        </p>

        <p><strong>Plazo de Entrega:</strong>
            {{ $cotizacion->plazo_entrega ?? '-' }}
        </p>

        <p><strong>Vigencia de Oferta:</strong>
            {{ $cotizacion->vigencia_oferta ?? '-' }}
        </p>

        <hr>

        <h4>Ítems</h4>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cant</th>
                    <th>Precio Unit.</th>
                    <th>IVA</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @php $totalGeneral = 0; @endphp

                @foreach ($cotizacion->items as $i)

                    @php
                        $base = $i->cantidad * $i->precio_unitario;
                        $total = $base + ($base * $i->iva / 100);
                        $totalGeneral += $total;
                    @endphp

                    <tr>
                        <td>{{ $i->producto }}</td>
                        <td>{{ $i->cantidad }}</td>
                        <td>$ {{ number_format($i->precio_unitario,2,',','.') }}</td>
                        <td>{{ $i->iva }}%</td>
                        <td>$ {{ number_format($total,2,',','.') }}</td>
                    </tr>

                @endforeach
            </tbody>
        </table>

        <hr>

        <h4 class="text-right">
            Total General:
            $ {{ number_format($totalGeneral,2,',','.') }}
        </h4>

        <hr>

        <p><strong>Especificaciones Técnicas:</strong><br>
            {{ $cotizacion->especificaciones_tecnicas ?? '-' }}
        </p>

        <p><strong>Observaciones:</strong><br>
            {{ $cotizacion->observaciones ?? '-' }}
        </p>

        <div class="mt-3">
            <a href="{{ route('cotizaciones.index') }}" class="btn btn-secondary">
                Volver
            </a>

            <a href="{{ route('cotizaciones.edit', $cotizacion) }}" class="btn btn-primary">
                Editar
            </a>

            <button onclick="window.print()" class="btn btn-info">
                Imprimir
            </button>
        </div>

    </div>
</div>

@stop
