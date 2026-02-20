<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CotizacionItem extends Model
{
    protected $table = 'cotizacion_items';

    protected $primaryKey = 'id_cot_item';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false; // Cambiar a true si la tabla tiene created_at y updated_at

    protected $fillable = [
        'id_cotizacion',
        'producto',
        'cantidad',
        'precio_unitario',
        'iva',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'iva' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'id_cotizacion');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors opcionales
    |--------------------------------------------------------------------------
    */

    public function getSubtotalAttribute()
    {
        return $this->cantidad * $this->precio_unitario;
    }

    public function getTotalConIvaAttribute()
    {
        return $this->subtotal + $this->iva;
    }
}
