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
        'cantidad',
        'observaciones',
        'comentarios',
        'estado_pc'
    ];

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'nro_pedido_asoc', 'id_ped_cot');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id');
    }
}
