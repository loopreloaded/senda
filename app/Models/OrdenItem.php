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
        'iva',
        'descuento',
        'total',
        'fecha_entrega',
        'id_cotizacion_item',
        'id_cotizacion'
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenCompra::class, 'orden_compra_id');
    }

    public function cotizacionItem()
    {
        return $this->belongsTo(CotizacionItem::class, 'id_cotizacion_item');
    }

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'id_cotizacion');
    }
}
