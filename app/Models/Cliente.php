<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'razon_social',
        'cuit',
        'condicion_iva',
        'condicion_iibb',
        'direccion',
        'email',
        'telefono',
        'codigo_postal',
        'localidad',
        'provincia',
        'pais',
        'tipo_doc',
        'nro_doc',
        'observaciones',
    ];

    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }
}
