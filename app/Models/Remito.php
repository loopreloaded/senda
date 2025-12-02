<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remito extends Model
{
    use HasFactory;

    protected $table = 'remitos';

    protected $fillable = [
        'fecha',
        'razon_social',
        'domicilio',
        'localidad',
        'orden_compra',
        'cuit',
        'estado',
        'creado_por',
    ];

    /* ================================
       RELACIONES
    =================================*/

    // Un remito tiene muchos ítems
    public function items()
    {
        return $this->hasMany(RemitoItem::class);
    }

    // Usuario que creó el remito
    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
