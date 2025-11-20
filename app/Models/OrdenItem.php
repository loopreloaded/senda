<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenItem extends Model
{
    protected $table = 'orden_compras_items';

    protected $fillable = [
        'orden_compra_id',
        'codigo',
        'descripcion',
        'cantidad',
        'unidad',
        'precio_unitario',
        'descuento',
        'total',
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenCompra::class, 'orden_compra_id');
    }
}
