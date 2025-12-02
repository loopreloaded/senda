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
       RELACIÓN CON REMITO
    =================================*/

    public function remito()
    {
        return $this->belongsTo(Remito::class);
    }
}
