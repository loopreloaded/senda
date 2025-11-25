<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenCompra extends Model
{
    use HasFactory;

    protected $table = 'orden_compras';

    protected $fillable = [
        'numero_oc',
        'fecha',
        'fecha_entrega',
        'proveedor',
        'cuit',
        'direccion',
        'telefono',
        'email',
        'moneda',
        'condicion_compra',
        'solicitud_compra',
        'subtotal',
        'total',
        'observaciones',
        'estado'
    ];


    public function items()
    {
        return $this->hasMany(OrdenItem::class, 'orden_compra_id');
    }

}
