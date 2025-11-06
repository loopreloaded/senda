<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FacturaController extends Controller
{
    //
    public function pendientes()
    {
        $facturas = \App\Models\Factura::where('estado', 'pendiente')->get();
        return view('facturas.pendientes', compact('facturas'));
    }

}
