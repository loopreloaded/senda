<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $cotizaciones = DB::table('cotizaciones')
            ->whereNotNull('nro_pedido_asoc')
            ->where('nro_pedido_asoc', 'REGEXP', '^[0-9]+$') // Solo numéricos
            ->get();

        foreach ($cotizaciones as $cot) {
            $idPedido = (int)$cot->nro_pedido_asoc;

            // Verificar si el pedido existe antes de intentar vincular
            $pedidoExiste = DB::table('pedidos_cotizacion')
                ->where('id_ped_cot', $idPedido)
                ->exists();

            if (!$pedidoExiste) continue;

            $items = DB::table('cotizacion_items')
                ->where('id_cotizacion', $cot->id_cotizacion)
                ->get();

            foreach ($items as $item) {
                // Evitar duplicados
                $exists = DB::table('pedido_cotizacion')
                    ->where('id_cotizacion', $cot->id_cotizacion)
                    ->where('id_pedido_cot', $idPedido)
                    ->where('producto', $item->producto)
                    ->exists();

                if (!$exists) {
                    DB::table('pedido_cotizacion')->insert([
                        'id_cotizacion' => $cot->id_cotizacion,
                        'id_pedido_cot' => $idPedido,
                        'producto'      => $item->producto,
                        'cantidad'      => $item->cantidad,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es estrictamente reversible sin riesgo de borrar datos nuevos, 
        // pero podríamos vaciar la tabla si fuera necesario.
        // DB::table('pedido_cotizacion')->truncate();
    }
};
