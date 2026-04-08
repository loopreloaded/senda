<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CondicionIva extends Model
{
    protected $table = 'condiciones_iva';
    protected $fillable = ['codigo', 'nombre', 'id_afip'];
    public $timestamps = false;
}
