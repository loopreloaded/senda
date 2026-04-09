<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CotizacionItem;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';
    protected $primaryKey = 'id_cotizacion';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nro_cotizacion',
        'fecha_cot',
        'id_cliente',
        'moneda',
        'forma_pago',
        'lugar_entrega',
        'plazo_entrega',
        'vigencia_oferta',
        'especificaciones_tecnicas',
        'motivo',
        'quien_cotiza',
        'estado_cotizacion',
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


    public function items()
    {
        return $this->hasMany(CotizacionItem::class, 'id_cotizacion');
    }

    public function getRouteKeyName()
    {
        return 'id_cotizacion';
    }

    /**
     * Relación N:N con Pedidos de Cotización
     */
    public function pedidos()
    {
        return $this->belongsToMany(PedidoCotizacion::class, 'pedido_cotizacion', 'id_cotizacion', 'id_pedido_cot')
                    ->withPivot('producto', 'cantidad')
                    ->withTimestamps();
    }

    /**
     * Órdenes de Compra vinculadas
     */
    public function ordenesCompra()
    {
        return $this->hasMany(OrdenCompra::class, 'cotizacion_id');
    }

    /**
     * Atributo derivado para cantidad total cotizada
     */
    public function getCantArtCotAttribute()
    {
        return $this->items()->sum('cantidad');
    }

    /**
     * Atributo derivado para nombres de artículos
     */
    public function getArtCotAttribute()
    {
        return $this->items()->pluck('producto')->implode(', ');
    }
}
