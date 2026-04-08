<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CondicionIibb extends Model
{
    protected $table = 'condiciones_iibb';
    protected $fillable = ['codigo', 'nombre'];
    public $timestamps = false;
}
