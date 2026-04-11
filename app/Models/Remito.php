<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Remito extends Model
{
    protected $table = 'remitos';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id_cliente',
        'numero_remito',
        'motivo',
        'id_cot',
        'fecha',
        'condicion_venta',
        'estado',
        'creado_por',
        'transportista',
        'domicilio_transportista',
        'iva_transportista',
        'cuit_transportista',
        'observacion',
        'cai',
        'cai_vto',
        'comentarios'
    ];

    protected $casts = [
        'fecha'   => 'date',
        'cai_vto' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    /**
     * Relación N a N con Orden de Compra según especificación 5.5
     */
    public function ordenesCompra()
    {
        return $this->belongsToMany(OrdenCompra::class, 'oc_remito', 'id_rem', 'id_oc')
                    ->withPivot('articulo', 'cantidad')
                    ->withTimestamps();
    }

    public function items()
    {
        return $this->hasMany(RemitoItem::class, 'remito_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Trazabilidad 5.2)
    |--------------------------------------------------------------------------
    */

    /**
     * Cantidad total de artículos en el remito
     */
    public function getCantArtRemAttribute()
    {
        return $this->items()->sum('cantidad');
    }

    /**
     * Cantidad total de artículos facturados
     * Se basa en la relación con FacturaRemito (lógica actual mantenida)
     */
    public function getCantArtFacAttribute()
    {
        $facturaRemitos = FacturaRemito::where('comprobante', $this->numero_remito)->get();
        
        $total = 0;
        foreach($facturaRemitos as $fr) {
            $factura = $fr->factura;
            if ($factura && in_array(strtolower($factura->estado), ['aprobada', 'facturada', 'confirmada', 'cae_asignado'])) {
                $total += $factura->items()->sum('cantidad');
            }
        }
        return $total;
    }

    /**
     * Listado de artículos facturados
     */
    public function getArtFacAttribute()
    {
        $facturaRemitos = FacturaRemito::where('comprobante', $this->numero_remito)->get();
        $articulos = collect();

        foreach($facturaRemitos as $fr) {
            $factura = $fr->factura;
            if ($factura) {
                $articulos = $articulos->concat($factura->items()->pluck('descripcion'));
            }
        }
        return $articulos->unique()->implode(', ');
    }

    /*
    |--------------------------------------------------------------------------
    | LÓGICA DE ESTADOS (5.3)
    |--------------------------------------------------------------------------
    */

    public function actualizarEstado()
    {
        if ($this->estado === 'Anulado') return;

        $cantRem = $this->cant_art_rem;
        $cantFac = $this->cant_art_fac;

        if ($cantFac <= 0) {
            $nuevoEstado = 'Emitido';
        } elseif ($cantFac < $cantRem) {
            $nuevoEstado = 'Parcial';
        } else {
            $nuevoEstado = 'Facturado';
        }

        if ($this->estado !== $nuevoEstado) {
            $this->update(['estado' => $nuevoEstado]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EVENTOS
    |--------------------------------------------------------------------------
    */
    protected static function booted()
    {
        static::saved(function ($remito) {
            // Actualizar estado de las OCs vinculadas
            foreach ($remito->ordenesCompra as $oc) {
                $oc->actualizarEstado();
            }
        });

        static::deleted(function ($remito) {
            foreach ($remito->ordenesCompra as $oc) {
                $oc->actualizarEstado();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function esEmitido()
    {
        return strtolower($this->estado) === 'emitido';
    }

    public function esAnulado()
    {
        return strtolower($this->estado) === 'anulado';
    }
}
