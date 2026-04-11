<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenCompra extends Model
{
    use HasFactory;

    protected $table = 'orden_compras';

    protected $fillable = [
        'cotizacion_id',
        'numero_oc',
        'fecha',
        'fecha_entrega',
        'id_cliente',
        'cuit',
        'direccion',
        'telefono',
        'email',
        'moneda',
        'condicion_compra',
        'solicitud_compra',
        'motivo',
        'subtotal',
        'total',
        'observaciones',
        'archivo',
        'estado'
    ];

    /**
     * Motivos posibles: 'pedido' (vinculado a cotización), 'particular'.
     */
    const MOTIVO_PEDIDO = 'pedido';
    const MOTIVO_PARTICULAR = 'particular';

    /**
     * Estados posibles según flujo comercial.
     */
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_ANULADA = 'anulada';
    const ESTADO_PARCIAL = 'parcial';
    const ESTADO_COMPLETA = 'completa';


    public function items()
    {
        return $this->hasMany(OrdenItem::class, 'orden_compra_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function cotizaciones()
    {
        return $this->belongsToMany(Cotizacion::class, 'cotizacion_oc', 'id_oc', 'id_cot')
                    ->withPivot('articulo', 'cantidad')
                    ->withTimestamps();
    }

    public function remitos()
    {
        return $this->hasMany(Remito::class, 'id_orden_compra');
    }

    /**
     * CÁLCULOS Y ACCESSORS
     */

    public function getCantArtOcAttribute()
    {
        if ($this->motivo === self::MOTIVO_PEDIDO) {
            return $this->cotizaciones()->sum('cotizacion_oc.cantidad');
        }
        return $this->items()->sum('cantidad');
    }

    public function getArtCotAttribute()
    {
        if ($this->motivo === self::MOTIVO_PEDIDO) {
            return $this->cotizaciones()->pluck('articulo')->unique()->implode(', ');
        }
        return $this->items()->pluck('descripcion')->unique()->implode(', ');
    }

    public function getCantArtRemAttribute()
    {
        // Sumar todos los items de remitos asociados (Emitidos o Confirmados)
        // La tabla remitos usa 'id' como PK y remito_items usa 'remito_id' como FK
        $remitoIds = $this->remitos()
            ->whereIn('estado', ['Emitido', 'Confirmado'])
            ->pluck('id');

        return RemitoItem::whereIn('remito_id', $remitoIds)->sum('cantidad');
    }

    public function getArtRemAttribute()
    {
        $remitoIds = $this->remitos()
            ->whereIn('estado', ['Emitido', 'Confirmado'])
            ->pluck('id');

        return RemitoItem::whereIn('remito_id', $remitoIds)
                         ->pluck('articulo')
                         ->unique()
                         ->implode(', ');
    }

    /**
     * LÓGICA DE ESTADOS
     */
    public function actualizarEstado()
    {
        // Si está anulada, no cambiar automáticamente
        if ($this->estado === self::ESTADO_ANULADA) return;

        $cantOC = $this->cant_art_oc;
        $cantRem = $this->cant_art_rem;

        if ($cantRem <= 0) {
            $nuevoEstado = self::ESTADO_PENDIENTE;
        } elseif ($cantRem < $cantOC) {
            $nuevoEstado = self::ESTADO_PARCIAL;
        } else {
            $nuevoEstado = self::ESTADO_COMPLETA;
        }

        if ($this->estado !== $nuevoEstado) {
            $this->update(['estado' => $nuevoEstado]);
        }
    }


}
