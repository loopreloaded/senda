<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaDebito extends Model
{
    protected $table = 'notas_debito';

    protected $fillable = [
        'factura_id',
        'cliente_id',
        'tipo_comprobante',
        'punto_venta',
        'numero',
        'fecha_emision',
        'concepto',
        'condicion_venta',
        'importe_total',
        'estado',
        'creado_por',
        'cae',
        'vto_cae',
        'aprobado_por',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }

    public function items()
    {
        return $this->hasMany(NotaDebitoItem::class, 'nota_id');
    }
}
