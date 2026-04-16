<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Factura extends Model
{
    use HasFactory;

    protected $table = 'facturas';

    /**
     * Lógica automática de estados basada en pagos y facturación
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($factura) {
            // Solo si ya fue emitida o está en estados de pago
            if (in_array($factura->estado, [self::ESTADO_EMITIDA, self::ESTADO_PARCIAL, self::ESTADO_PAGADA])) {
                
                $total = (float)($factura->importe_total ?? 0);
                $pagado = (float)($factura->importe_pagado ?? 0);

                if ($pagado <= 0) {
                    $factura->estado = self::ESTADO_EMITIDA;
                } elseif ($pagado < $total) {
                    $factura->estado = self::ESTADO_PARCIAL;
                } else {
                    $factura->estado = self::ESTADO_PAGADA;
                }
            }
        });
    }

    // Estados
    const ESTADO_BORRADOR   = 'borrador';
    const ESTADO_EMITIDA    = 'emitida';
    const ESTADO_PARCIAL    = 'parcial';
    const ESTADO_PAGADA     = 'pagada';
    const ESTADO_RECHAZADA  = 'rechazada por arca';

    protected $fillable = [

        // Relaciones
        'cliente_id',

        // Datos AFIP / comprobante
        'tipo_comprobante',              // A / B / C
        'punto_venta',
        'fecha_emision',
        'concepto',
        'condicion_venta',
        'moneda',                        // Peso argentino / USD billete / USD divisa
        'valor_dolar',

        // Estado AFIP / Trazabilidad
        'estado',
        'motivo',                        // pedido / particular
        'cae',
        'vto_cae',
        'numero_comprobante_afip',
        'aprobado_por',

        // Totales
        'subtotal',
        'total_iva',
        'importe_total',
        'importe_pagado',
        'importe_total_otros_tributos',

        // Percepción IVA
        'percepcion_iva_detalle',
        'percepcion_iva_base',
        'percepcion_iva_alicuota',
        'percepcion_iva_importe',

        // Percepción IIBB
        'percepcion_iibb_detalle',
        'percepcion_iibb_base',
        'percepcion_iibb_alicuota',
        'percepcion_iibb_importe',

        // Fechas comerciales
        'fecha_desde',
        'fecha_hasta',
        'vencimiento_pago',

        // Trazabilidad Remito (Summary)
        'art_fac',
        'cant_art_fac',

        // Otros
        'observaciones',
        'creado_por',
    ];

    /* =============================
       CASTS (MUY IMPORTANTE)
    ============================== */
    protected $casts = [
        'fecha_emision'    => 'date',
        'vto_cae'          => 'date',

        'fecha_desde'      => 'datetime',
        'fecha_hasta'      => 'datetime',
        'vencimiento_pago' => 'datetime',

        'subtotal'                     => 'decimal:2',
        'total_iva'                    => 'decimal:2',
        'importe_total'                => 'decimal:2',
        'importe_pagado'               => 'decimal:2',
        'importe_total_otros_tributos' => 'decimal:2',

        'percepcion_iva_base'       => 'decimal:2',
        'percepcion_iva_alicuota'   => 'decimal:2',
        'percepcion_iva_importe'    => 'decimal:2',

        'percepcion_iibb_base'      => 'decimal:2',
        'percepcion_iibb_alicuota'  => 'decimal:2',
        'percepcion_iibb_importe'   => 'decimal:2',

        'valor_dolar' => 'decimal:2',
        'cant_art_fac' => 'decimal:2',
    ];

    /* =============================
       ACCESSORS ÚTILES AFIP
    ============================== */

    /**
     * Código AFIP del tipo de comprobante
     * A = 1 | B = 6 | C = 11
     */
    public function getTipoComprobanteAfipAttribute()
    {
        return match ($this->tipo_comprobante) {
            'A' => 1,
            'B' => 6,
            'C' => 11,
            default => null,
        };
    }

    /**
     * Moneda AFIP (AFIP exige PES / USD)
     */
    public function getMonedaAfipAttribute()
    {
        return $this->moneda === 'ARS' ? 'PES' : $this->moneda;
    }

    /* =============================
       RELACIONES
    ============================== */

    public function items()
    {
        return $this->hasMany(FacturaItem::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function remitos()
    {
        return $this->belongsToMany(Remito::class, 'remito_factura', 'id_fac', 'id_rem')
                    ->withPivot('articulo', 'cantidad')
                    ->withTimestamps();
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function ordenesPago()
    {
        return $this->belongsToMany(OrdenPago::class, 'factura_op', 'factura_id', 'orden_pago_id')
                    ->withPivot('pagado')
                    ->withTimestamps();
    }

    /**
     * Recalcula el importe total pagado basado en las OPs vinculadas que no estén anuladas.
     */
    public function actualizarImportePagado()
    {
        $totalPagado = $this->ordenesPago()
            ->where('estado', '!=', OrdenPago::ESTADO_ANULADA)
            ->sum('factura_op.pagado');

        $this->importe_pagado = $totalPagado;
        $this->save(); // El boot se encarga de actualizar el estado
    }
}
