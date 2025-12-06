<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'factura_id',
        'codigo',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'iva',
        'subtotal',
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }
}
