<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    //
    public function empresa()
    {
        // En el futuro: mostrar/editar datos de la empresa
        return view('configuracion.empresa');
    }
}
