<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenCompra extends Model
{
    use HasFactory;

    protected $table = 'orden_compras';

    protected $fillable = [
        'cotizacion_id',
        'numero_oc',
        'fecha',
        'fecha_entrega',
        'id_cliente',
        'cuit',
        'direccion',
        'telefono',
        'email',
        'moneda',
        'condicion_compra',
        'solicitud_compra',
        'motivo',
        'subtotal',
        'total',
        'observaciones',
        'archivo',
        'estado'
    ];


    public function items()
    {
        return $this->hasMany(OrdenItem::class, 'orden_compra_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id', 'id_cotizacion');
    }


}
