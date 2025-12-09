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
        'valor_dolar',
        'importe_total',
        'estado',
        'observaciones',
        'creado_por',
        'aprobado_por',
        'bonificacion',
        'importe_bonificacion',
        'percepcion_iva',
        'percepcion_ingresos_brutos',
        'subtotal',
        'total_iva',
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

    public function remitos()
    {
        return $this->hasMany(FacturaRemito::class);
    }



}
