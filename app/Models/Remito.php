<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Remito extends Model
{
    protected $table = 'remitos';

    protected $primaryKey = 'id_remito';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'numero_remito',
        'fecha',
        'id_cliente',
        'id_orden_compra',
        'id_factura',
        'estado',
        'comentarios'
    ];

    protected $casts = [
        'fecha' => 'date'
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
        return $this->estado === 'emitido';
    }

    public function esConfirmado()
    {
        return $this->estado === 'confirmado';
    }

    public function esAnulado()
    {
        return $this->estado === 'anulado';
    }
}
