<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'factura_id',
        'codigo',
        'unidad',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'iva',
        'bonificacion_porcentaje',
        'bonificacion_importe',
        'subtotal_sin_iva',
        'subtotal_con_iva',
        'subtotal',
    ];


    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    public function getUnidadTextoAttribute(): string
    {
        $unidades = [
            1  => 'kilogramos',
            2  => 'metros',
            3  => 'metros cuadrados',
            4  => 'metros cúbicos',
            5  => 'litros',
            6  => '1000 kWh',
            7  => 'unidades',
            8  => 'pares',
            9  => 'docenas',
            10 => 'quilates',
            11 => 'millares',
            14 => 'gramos',
            15 => 'milímetros',
            16 => 'mm cúbicos',
            17 => 'kilómetros',
            18 => 'hectolitros',
            20 => 'centímetros',
            25 => 'jgo. pqt. mazo naipes',
            27 => 'cm cúbicos',
            29 => 'toneladas',
            30 => 'dam cúbicos',
            31 => 'hm cúbicos',
            32 => 'km cúbicos',
            33 => 'microgramos',
            34 => 'nanogramos',
            35 => 'picogramos',
            41 => 'miligramos',
            47 => 'mililitros',
            48 => 'curie',
            49 => 'milicurie',
            50 => 'microcurie',
            51 => 'uiacthor',
            52 => 'muiacthor',
            53 => 'kg base',
            54 => 'gruesa',
            61 => 'kg bruto',
            62 => 'uiactant',
            63 => 'muiactant',
            64 => 'uiactig',
            65 => 'muiactig',
            66 => 'kg activo',
            67 => 'gramo activo',
            68 => 'gramo base',
            96 => 'packs',
            98 => 'otras unidades',
        ];

        return $unidades[$this->unidad] ?? '—';
    }

}
