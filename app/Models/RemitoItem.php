<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemitoItem extends Model
{
    use HasFactory;

    protected $table = 'remito_items';

    protected $fillable = [
        'remito_id',
        'codigo',
        'id_orden_item',
        'articulo',
        'cantidad',
        'descripcion',
    ];

    /* ================================
       EVENTOS
    =================================*/

    protected static function booted()
    {
        static::saved(function ($item) {
            $remito = $item->remito;
            if ($remito) {
                foreach ($remito->ordenesCompra as $oc) {
                    $oc->actualizarEstado();
                }
                $remito->actualizarEstado();
            }
        });

        static::deleted(function ($item) {
            $remito = $item->remito;
            if ($remito) {
                foreach ($remito->ordenesCompra as $oc) {
                    $oc->actualizarEstado();
                }
                $remito->actualizarEstado();
            }
        });
    }

    public function remito()
    {
        return $this->belongsTo(Remito::class, 'remito_id');
    }
}
