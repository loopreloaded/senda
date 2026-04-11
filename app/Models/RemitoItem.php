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
            // Recargar remito para asegurar que tenemos el id_orden_compra
            $remito = $item->remito;
            if ($remito && $remito->id_orden_compra) {
                $oc = OrdenCompra::find($remito->id_orden_compra);
                if ($oc) $oc->actualizarEstado();
            }
        });

        static::deleted(function ($item) {
            $remito = $item->remito;
            if ($remito && $remito->id_orden_compra) {
                $oc = OrdenCompra::find($remito->id_orden_compra);
                if ($oc) $oc->actualizarEstado();
            }
        });
    }

    public function remito()
    {
        return $this->belongsTo(Remito::class, 'remito_id');
    }
}
