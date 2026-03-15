<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Models\Comentario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComentarioController extends Controller
{
    /**
     * Listado de comentarios del usuario
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $comentarios = Comentario::with('comentable')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $comentarios
        ]);
    }

    /**
     * Actualizar comentario propio
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $comentario = Comentario::where('user_id', $user->id)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'contenido' => 'required|string|min:3|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $comentario->update([
            'contenido' => $request->contenido
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comentario actualizado',
            'data' => $comentario
        ]);
    }

    /**
     * Eliminar comentario propio
     */
    public function destroy($id)
    {
        $user = request()->user();

        $comentario = Comentario::where('user_id', $user->id)
            ->findOrFail($id);

        $comentario->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comentario eliminado'
        ]);
    }
}
