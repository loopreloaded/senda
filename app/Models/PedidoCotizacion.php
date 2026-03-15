<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoCotizacion extends Model
{
    protected $table = 'pedidos_cotizacion';
    protected $primaryKey = 'id_ped_cot';
    public $incrementing = true;
    protected $keyType = 'int';

    public function getRouteKeyName()
    {
        return 'id_ped_cot';
    }

    protected $fillable = [
        'id_cliente',
        'fecha',
        'archivo',
        'items_excluidos',
        'nro_solicitud',
        'observaciones',
        'comentarios',
        'estado_pc'
    ];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'id_cotizacion');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id');
    }
}
