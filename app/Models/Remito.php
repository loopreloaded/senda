<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Remito extends Model
{
    protected $table = 'remitos';

    // La base de datos usa 'id' como PK estándar
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_orden_compra',
        'id_cliente',
        'numero_remito',
        'fecha',
        'razon_social',
        'domicilio',
        'localidad',
        'orden_compra',
        'cuit',
        'estado',
        'creado_por',
        'condicion_venta',
        'transportista',
        'domicilio_transportista',
        'iva_transportista',
        'cuit_transportista',
        'observacion',
        'cai',
        'cai_vto',
        'comentarios'
    ];

    protected $casts = [
        'fecha'   => 'date',
        'cai_vto' => 'date',
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

    public function ordenCompra()
    {
        return $this->belongsTo(OrdenCompra::class, 'id_orden_compra');
    }

    public function items()
    {
        // La clave foránea en remito_items es 'remito_id'
        return $this->hasMany(RemitoItem::class, 'remito_id');
    }

    /*
    |--------------------------------------------------------------------------
    | EVENTOS
    |--------------------------------------------------------------------------
    */
    protected static function booted()
    {
        static::saved(function ($remito) {
            if ($remito->id_orden_compra) {
                $oc = OrdenCompra::find($remito->id_orden_compra);
                if ($oc) $oc->actualizarEstado();
            }
        });

        static::deleted(function ($remito) {
            if ($remito->id_orden_compra) {
                $oc = OrdenCompra::find($remito->id_orden_compra);
                if ($oc) $oc->actualizarEstado();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function esEmitido()
    {
        return strtolower($this->estado) === 'emitido' || strtolower($this->estado) === 'confirmado';
    }

    public function esAnulado()
    {
        return strtolower($this->estado) === 'anulado';
    }
}
