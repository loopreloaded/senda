<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Listado de clientes con filtros
     */
    public function index(Request $request)
    {
        $query = Cliente::query();

        if ($request->filled('cuit')) {
            $query->where('cuit', 'like', '%' . $request->cuit . '%');
        }

        if ($request->filled('razon_social')) {
            $query->where('razon_social', 'like', '%' . $request->razon_social . '%');
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        $clientes = $query
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
        return view('admin.clientes.create');
    }

    /**
     * Guardar cliente
     */
    public function store(Request $request)
    {
        $request->validate([
            'cuit'                 => 'required|digits:11|unique:clientes,cuit',
            'razon_social'         => 'required|string|max:150',
            'domicilio_comercial'  => 'required|string|max:255',
            'email'                => 'nullable|email|max:150',
        ]);

        Cliente::create([
            'cuit'                => $request->cuit,
            'razon_social'        => $request->razon_social,
            'domicilio_comercial' => $request->domicilio_comercial,
            'email'               => $request->email,
        ]);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente creado correctamente');
    }

    /**
     * Mostrar cliente (opcional)
     */
    public function show(Cliente $cliente)
    {
        return view('admin.clientes.show', compact('cliente'));
    }

    /**
     * Formulario edición
     */
    public function edit(Cliente $cliente)
    {
        return view('admin.clientes.edit', compact('cliente'));
    }

    /**
     * Actualizar cliente
     */
    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'cuit'         => 'required|digits:11|unique:clientes,cuit,' . $cliente->id,
            'razon_social' => 'required|string|max:150',
            'direccion'    => 'required|string|max:255',
            'email'        => 'nullable|email|max:150',
        ]);

        $cliente->update([
            'cuit'         => $request->cuit,
            'razon_social' => $request->razon_social,
            'direccion'    => $request->direccion,
            'email'        => $request->email,
        ]);


        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente actualizado correctamente');
    }

    /**
     * Eliminar cliente
     */
    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente eliminado correctamente');
    }
}
