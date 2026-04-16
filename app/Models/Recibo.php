<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recibo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'recibos';
    protected $primaryKey = 'id_recibo';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'cliente_id',
        'nro_recibo',
        'fecha',
        'motivo',
        'importe_saldado',
        'iva',
        'ganancia',
        'iibb',
        'percepcion_ib',
        'total_retenciones',
        'importe_total',
        'detalles_pago',
        'estado',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha'             => 'date',
        'importe_saldado'   => 'decimal:2',
        'iva'               => 'decimal:2',
        'ganancia'          => 'decimal:2',
        'iibb'              => 'decimal:2',
        'percepcion_ib'     => 'decimal:2',
        'total_retenciones' => 'decimal:2',
        'importe_total'     => 'decimal:2',
    ];

    const ESTADO_EMITIDA = 'Emitida';
    const ESTADO_CERRADA = 'Cerrada';

    const MOTIVO_PEDIDO = 'pedido';
    const MOTIVO_PARTICULAR = 'particular';

    /* =============================
       RELACIONES
    ============================== */

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function ordenesPago()
    {
        return $this->belongsToMany(OrdenPago::class, 'op_recibo', 'id_rec', 'id_op')
                    ->withPivot('saldado')
                    ->withTimestamps();
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /* =============================
       BOOT
    ============================== */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->created_by && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }
}
