<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';
    protected $primaryKey = 'id_cotizacion';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'fecha_cot',
        'id_cliente',
        'moneda',
        'forma_pago',
        'lugar_entrega',
        'plazo_entrega',
        'vigencia_oferta',
        'especificaciones_tecnicas',
        'observaciones',
        'importe_total'
    ];

    protected $casts = [
        'fecha_cot' => 'datetime',
        'vigencia_oferta' => 'date',
        'importe_total' => 'decimal:2'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function pedidos()
    {
        return $this->hasMany(PedidoCotizacion::class, 'id_cotizacion');
    }
}
