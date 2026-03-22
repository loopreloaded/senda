<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Remito extends Model
{
    protected $table = 'remitos';

    protected $primaryKey = 'id_remito';
    // public $incrementing = true;
    // protected $keyType = 'int';

    protected $fillable = [
        'numero_remito',
        'fecha',
        'id_cliente',
        'id_orden_compra',
        'id_factura',
        'estado',

        // NUEVOS CAMPOS
        'condicion_venta',

        // FLETE
        'transportista',
        'domicilio_transportista',
        'iva_transportista',
        'cuit_transportista',
        'observacion',

        // CAI
        'cai',
        'cai_vto',

        // otros
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

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'id_factura');
    }

    // 🔥 RELACIÓN NUEVA (recomendada)
    public function items()
    {
        return $this->hasMany(RemitoItem::class, 'id_remito');
    }

    /*
    |--------------------------------------------------------------------------
    | ROUTE MODEL BINDING
    |--------------------------------------------------------------------------
    */

    public function getRouteKeyName()
    {
        return 'id_remito';
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function esEmitido()
    {
        return strtolower($this->estado) === 'emitido';
    }

    public function esConfirmado()
    {
        return strtolower($this->estado) === 'confirmado';
    }

    public function esAnulado()
    {
        return strtolower($this->estado) === 'anulado';
    }
}
