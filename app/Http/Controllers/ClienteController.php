<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\CondicionIva;
use App\Models\CondicionIibb;
use Illuminate\Http\Request;
use App\Imports\ClientesImport;
use Maatwebsite\Excel\Facades\Excel;

class ClienteController extends Controller
{
    /**
     * Listado de clientes con filtros
     */
    public function index(Request $request)
    {
        $query = Cliente::query();

        // Si es desarrollador, permitir ver eliminados
        if (auth()->user() && auth()->user()->hasRole('desarrollador') && $request->has('ver_eliminados')) {
            $query->onlyTrashed();
        }

        if ($request->filled('cuit')) {
            $query->where('cuit', 'like', '%' . $request->cuit . '%');
        }

        if ($request->filled('razon_social')) {
            $query->where('razon_social', 'like', '%' . $request->razon_social . '%');
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $clientes = $query
            ->with(['condicionIva', 'condicionIibb'])
            ->orderBy('razon_social')
            ->paginate(10)
            ->withQueryString();

        return view('admin.clientes.index', compact('clientes'));
    }

    /**
     * Formulario alta cliente
     */
    public function create()
    {
        $condicionesIva = CondicionIva::all();
        $condicionesIibb = CondicionIibb::all();
        return view('admin.clientes.create', compact('condicionesIva', 'condicionesIibb'));
    }

    /**
     * Guardar cliente
     */
    public function store(Request $request)
    {
        $request->validate([
            'cuit'                => 'required|digits:11|unique:clientes,cuit',
            'razon_social'        => 'required|string|max:150',
            'domicilio_comercial' => 'required|string|max:255',
            'email'               => 'nullable|email|max:150',
            'tipo'                => 'required|in:C,P,A',
            'condicion_iva_id'    => 'required|exists:condiciones_iva,id',
            'condicion_iibb_id'   => 'required|exists:condiciones_iibb,id',
        ]);

        Cliente::create([
            'cuit'              => $request->cuit,
            'razon_social'      => $request->razon_social,
            'direccion'         => $request->domicilio_comercial,
            'email'             => $request->email,
            'tipo'              => $request->tipo,
            'telefono'          => $request->telefono,
            'condicion_iva_id'  => $request->condicion_iva_id,
            'condicion_iibb_id' => $request->condicion_iibb_id,
        ]);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Socio comercial creado correctamente');
    }

    /**
     * Formulario edición
     */
    public function edit(Cliente $cliente)
    {
        $condicionesIva = CondicionIva::all();
        $condicionesIibb = CondicionIibb::all();
        return view('admin.clientes.edit', compact('cliente', 'condicionesIva', 'condicionesIibb'));
    }

    /**
     * Actualizar cliente
     */
    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'cuit'              => 'required|digits:11|unique:clientes,cuit,' . $cliente->id,
            'razon_social'      => 'required|string|max:150',
            'direccion'         => 'required|string|max:255',
            'email'             => 'nullable|email|max:150',
            'tipo'              => 'required|in:C,P,A',
            'condicion_iva_id'  => 'required|exists:condiciones_iva,id',
            'condicion_iibb_id' => 'required|exists:condiciones_iibb,id',
        ]);

        $cliente->update([
            'cuit'              => $request->cuit,
            'razon_social'      => $request->razon_social,
            'direccion'         => $request->direccion,
            'email'             => $request->email,
            'tipo'              => $request->tipo,
            'telefono'          => $request->telefono,
            'condicion_iva_id'  => $request->condicion_iva_id,
            'condicion_iibb_id' => $request->condicion_iibb_id,
        ]);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Socio comercial actualizado correctamente');
    }

    /**
     * Eliminar cliente
     */
    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Socio comercial eliminado correctamente');
    }

    /**
     * Restaurar cliente eliminado
     */
    public function restore($id)
    {
        $cliente = Cliente::withTrashed()->findOrFail($id);
        $cliente->restore();

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Socio comercial recuperado correctamente');
    }

    public function buscar(Request $request)
    {
        $q = $request->get('q');

        if (!$q || strlen($q) < 2) {
            return response()->json([]);
        }

        $clientes = Cliente::query()
            ->where('razon_social', 'like', "%{$q}%")
            ->orWhere('cuit', 'like', "%{$q}%")
            ->limit(10)
            ->get([
                'id',
                'razon_social',
                'cuit',
                'telefono',
                'condicion_iva_id',
                'direccion',
                'email',
            ]);

        return response()->json($clientes);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        Excel::import(new ClientesImport, $request->file('file'));

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Socios comerciales importados correctamente.');
    }
}
