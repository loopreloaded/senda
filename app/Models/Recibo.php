<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recibo extends Model
{
    protected $table = 'recibos';
    protected $primaryKey = 'id_recibo';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nro_recibo',
        'fecha',
    ];
}

