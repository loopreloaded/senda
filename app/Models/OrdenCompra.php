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
        'proveedor',
        'cuit',
        'moneda',
        'condicion_compra',
        'subtotal',
        'descuento',
        'total',
        'estado',
        'firma_digital',
    ];
}
