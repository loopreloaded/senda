<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $fillable = [
        'cliente_id',
        'tipo_comprobante',
        'punto_venta',
        'fecha_emision',
        'concepto',
        'condicion_venta',
        'importe_total',
        'estado',
        'observaciones',
        'creado_por',
        'aprobado_por'
    ];


    //
    public function items()
    {
        return $this->hasMany(FacturaItem::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }


}
