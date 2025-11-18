<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaDebitoItem extends Model
{
    protected $table = 'notas_debito_items';

    protected $fillable = [
        'nota_id',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'iva',
        'subtotal',
    ];

    public function nota()
    {
        return $this->belongsTo(NotaDebito::class, 'nota_id');
    }
}
