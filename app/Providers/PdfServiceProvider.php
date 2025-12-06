<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Barryvdh\DomPDF\Facade\Pdf as DomPDF;

class PdfServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Registrar primero DOMPDF en el contenedor
        $this->app->alias(DomPDF::class, 'PDF');

        // Y luego forzar alias para pisar el de TCPDF
        AliasLoader::getInstance()->alias('PDF', DomPDF::class);
    }

    public function boot()
    {
        //
    }
}
