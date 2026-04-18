<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';
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
        return $this->belongsToMany(Cotizacion::class, 'pedido_cotizacion', 'id_pedido_cot', 'id_cotizacion')
                    ->withPivot('producto', 'cantidad')
                    ->withTimestamps();
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id');
    }
}
