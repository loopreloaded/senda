<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'razon_social',
        'cuit',
        'condicion_iva_id',
        'condicion_iibb_id',
        'indice',
        'direccion',
        'email',
        'telefono',
        'tipo',
        'codigo_postal',
        'localidad',
        'provincia',
        'pais',
        'tipo_doc',
        'nro_doc',
        'observaciones',
    ];

    public function condicionIva()
    {
        return $this->belongsTo(CondicionIva::class, 'condicion_iva_id');
    }

    public function condicionIibb()
    {
        return $this->belongsTo(CondicionIibb::class, 'condicion_iibb_id');
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }

    public function getCondicionIvaTextoAttribute()
    {
        return $this->condicionIva ? $this->condicionIva->nombre : '-';
    }


    public function getCondicionIibbTextoAttribute()
    {
        return $this->condicionIibb ? $this->condicionIibb->nombre : '-';
    }

    public function ordenes()
    {
        return $this->hasMany(OrdenCompra::class, 'id_cliente');
    }

}
