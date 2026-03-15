<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tienda;
use Illuminate\Http\Request;

class TiendaController extends \App\Http\Controllers\Controller
{
    public function index()
    {
        $tiendas = Tienda::with('vendedor')->get();
        return view('admin.tiendas.index', compact('tiendas'));
    }

    public function create()
    {
        return view('admin.tiendas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255', // ✅ Cambiado
            'direccion' => 'required|string|unique:tiendas', // ✅ Cambiado
            'telefono' => 'required|string|unique:tiendas', // ✅ Cambiado
            'email' => 'required|email|unique:tiendas', // ✅ Cambiado
            'tipo' => 'required|in:veterinaria,fundacion', // ✅ Agregado
            'user_id' => 'required|exists:users,id', // ✅ Agregado
        ]);

        Tienda::create($request->all());

        return redirect()->route('admin.tiendas.index')
            ->with('success', 'Tienda creada exitosamente.');
    }

    public function show($id)
    {
        $tienda = Tienda::with('productos', 'vendedor')->findOrFail($id);
        return view('admin.tiendas.show', compact('tienda'));
    }

    public function edit($id)
    {
        $tienda = Tienda::findOrFail($id);
        return view('admin.tiendas.edit', compact('tienda'));
    }

    public function update(Request $request, $id)
    {
        $tienda = Tienda::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255', // ✅ Cambiado
            'direccion' => 'required|string|unique:tiendas,direccion,' . $id, // ✅ Cambiado
            'telefono' => 'required|string|unique:tiendas,telefono,' . $id, // ✅ Cambiado
            'email' => 'required|email|unique:tiendas,email,' . $id, // ✅ Cambiado
            'tipo' => 'required|in:veterinaria,fundacion',
        ]);

        $tienda->update($request->all());

        return redirect()->route('admin.tiendas.index')
            ->with('success', 'Tienda actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $tienda = Tienda::findOrFail($id);
        $tienda->delete();

        return redirect()->route('admin.tiendas.index')
            ->with('success', 'Tienda eliminada exitosamente.');
    }

    // ✅ Estos métodos pueden eliminarse si no se usan
    // public function ventas($id = null) { ... }
    // public function inventario($id = null) { ... }
}
