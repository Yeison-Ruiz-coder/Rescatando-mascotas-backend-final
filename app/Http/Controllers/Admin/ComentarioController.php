<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comentario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComentarioController extends Controller
{
    /**
     * Listado de comentarios para admin
     */
    public function index(Request $request)
    {
        $query = Comentario::with(['usuario', 'comentable']);

        // Filtros
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha', '<=', $request->fecha_fin);
        }

        if ($request->filled('search')) {
            $query->where('contenido', 'like', "%{$request->search}%");
        }

        $comentarios = $query->latest()->paginate(20);

        return view('admin.comentarios.index', compact('comentarios'));
    }

    /**
     * Mostrar detalle de comentario
     */
    public function show(Comentario $comentario)
    {
        $comentario->load(['usuario', 'comentable']);
        return view('admin.comentarios.show', compact('comentario'));
    }

    /**
     * Formulario de edición
     */
    public function edit(Comentario $comentario)
    {
        return view('admin.comentarios.edit', compact('comentario'));
    }

    /**
     * Actualizar comentario
     */
    public function update(Request $request, Comentario $comentario)
    {
        $validator = Validator::make($request->all(), [
            'contenido' => 'required|string|min:3|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $comentario->update([
            'contenido' => $request->contenido,
        ]);

        return redirect()->route('admin.comentarios.index')
            ->with('success', 'Comentario actualizado');
    }

    /**
     * Eliminar comentario
     */
    public function destroy(Comentario $comentario)
    {
        $comentario->delete();

        return redirect()->route('admin.comentarios.index')
            ->with('success', 'Comentario eliminado');
    }

    /**
     * Acción masiva (eliminar varios)
     */
    public function masivo(Request $request)
    {
        $request->validate([
            'accion' => 'required|in:eliminar',
            'comentarios' => 'required|array',
            'comentarios.*' => 'exists:comentarios,id'
        ]);

        if ($request->accion === 'eliminar') {
            Comentario::whereIn('id', $request->comentarios)->delete();
            $mensaje = 'Comentarios eliminados exitosamente.';
        }

        return redirect()->route('admin.comentarios.index')
            ->with('success', $mensaje);
    }
}
