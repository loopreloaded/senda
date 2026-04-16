<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenPago extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ordenes_pago';

    protected $fillable = [
        'cliente_id',
        'fecha',
        'archivo',
        'nro_op',
        'observaciones',
        'motivo',
        'importe_pagado',
        'importe_saldado',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'importe_pagado' => 'decimal:2',
        'importe_saldado' => 'decimal:2',
    ];

    const ESTADO_RECIBIDA = 'Recibida';
    const ESTADO_ANULADA = 'Anulada';
    const ESTADO_PARCIAL = 'Parcial';
    const ESTADO_PAGADA = 'Pagada';

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function facturas()
    {
        return $this->belongsToMany(Factura::class, 'factura_op', 'orden_pago_id', 'factura_id')
                    ->withPivot('pagado')
                    ->withTimestamps();
    }

    public function recibos()
    {
        return $this->belongsToMany(Recibo::class, 'orden_pago_recibo', 'orden_pago_id', 'recibo_id')
                    ->withPivot('monto')
                    ->withTimestamps();
    }

    public function getFormattedIdAttribute()
    {
        return 'OP-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }
}
