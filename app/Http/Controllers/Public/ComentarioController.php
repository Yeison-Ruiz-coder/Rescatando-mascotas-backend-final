<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Comentario;
use App\Models\Mascota;
use App\Models\Evento;
use Illuminate\Http\Request;

class ComentarioController extends Controller
{
    /**
     * Mostrar comentarios de una entidad
     */
    public function index(Request $request)
    {
        $request->validate([
            'entidad_tipo' => 'required|in:mascota,evento,fundacion', // ✅ Agregar más tipos si aplica
            'entidad_id' => 'required|integer',
        ]);

        $comentarios = Comentario::where('comentable_type', 'App\\Models\\' . ucfirst($request->entidad_tipo))
                                 ->where('comentable_id', $request->entidad_id)
                                 ->with('usuario')
                                 ->latest()
                                 ->get();

        return response()->json($comentarios);
    }

    /**
     * Mostrar comentarios de una entidad (vista)
     */
    public function show($entidadTipo, $entidadId)
    {
        $modelClass = 'App\\Models\\' . ucfirst($entidadTipo);

        if (!class_exists($modelClass)) {
            abort(404);
        }

        $entidad = $modelClass::findOrFail($entidadId);

        $comentarios = Comentario::where('comentable_type', $modelClass)
                                 ->where('comentable_id', $entidadId)
                                 ->with('usuario')
                                 ->latest()
                                 ->paginate(10);

        return view('public.comentarios.show', compact('comentarios', 'entidad', 'entidadTipo'));
    }

    /**
     * Guardar comentario
     */
    public function store(Request $request)
    {
        $request->validate([
            'contenido' => 'required|string|min:3|max:1000',
            'entidad_tipo' => 'required|in:mascota,evento,fundacion',
            'entidad_id' => 'required|integer|exists:' . strtolower($request->entidad_tipo) . 's,id',
        ]);

        $comentario = Comentario::create([
            'contenido' => $request->contenido,
            'user_id' => auth()->id(),
            'comentable_type' => 'App\\Models\\' . ucfirst($request->entidad_tipo),
            'comentable_id' => $request->entidad_id,
            'fecha' => now(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'comentario' => $comentario->load('usuario')
            ]);
        }

        return back()->with('success', 'Comentario publicado');
    }
}
