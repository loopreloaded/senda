<?php

namespace App\Imports;

use App\Models\Cliente;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;


class ClientesImport implements
    OnEachRow,
    WithHeadingRow,
    WithChunkReading,
    WithEvents
{
    /**
     * ⚠️ IMPORTANTE:
     * En esta versión, NO es static
     */
    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {

                $reader = $event->getReader()->getDelegate();

                // 👉 SOLO si es XLSX
                if ($reader instanceof Xlsx) {
                    $reader->setReadDataOnly(true);
                }
            },
        ];
    }

    public function onRow(Row $row)
    {
        $data = array_change_key_case(
            array_map('trim', $row->toArray()),
            CASE_LOWER
        );

        // CUIT
        $cuit = isset($data['cuit'])
            ? preg_replace('/\D/', '', $data['cuit'])
            : null;

        if (!$cuit || strlen($cuit) < 10 || strlen($cuit) > 11) {
            return;
        }

        // RAZÓN SOCIAL
        $razonSocial = $data['denominacion'] ?? null;

        if (!$razonSocial || str_starts_with($razonSocial, '=')) {
            return;
        }

        $razonSocial = mb_substr($razonSocial, 0, 191);

        // CONDICIÓN IIBB
        $condicionRaw = strtoupper(trim($data['condicion'] ?? ''));
        $condicionIibb = null;

        if ($condicionRaw !== '') {
            if (str_contains($condicionRaw, 'CM')) {
                $condicionIibb = 'CM';
            } elseif (str_contains($condicionRaw, 'L')) {
                $condicionIibb = 'L';
            }
        }

        // ÍNDICE
        $indiceRaw = trim($data['indice'] ?? '');
        $indice = null;

        if ($indiceRaw !== '') {
            $indiceRaw = str_replace(',', '.', $indiceRaw);
            if (is_numeric($indiceRaw)) {
                $indice = (float) $indiceRaw;
            }
        }

        Cliente::updateOrCreate(
            ['cuit' => $cuit],
            [
                'razon_social'   => $razonSocial,
                'condicion_iibb' => $condicionIibb,
                'indice'         => $indice,
            ]
        );
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
