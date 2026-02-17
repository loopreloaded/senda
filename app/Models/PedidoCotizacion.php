<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoCotizacion extends Model
{
    protected $table = 'pedidos_cotizacion';
    protected $primaryKey = 'id_ped_cot';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_cotizacion',
        'archivo',
        'observaciones'
    ];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'id_cotizacion');
    }
}
