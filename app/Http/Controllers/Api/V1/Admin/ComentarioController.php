<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comentario;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ComentarioController extends Controller
{
    /**
     * Listado de comentarios para admin
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
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

            $perPage = $request->get('per_page', 20);
            $comentarios = $query->latest()->paginate($perPage);

            // Estadísticas
            $estadisticas = [
                'total' => Comentario::count(),
                'hoy' => Comentario::whereDate('fecha', today())->count(),
                'esta_semana' => Comentario::where('fecha', '>=', now()->subDays(7))->count(),
                'usuarios_activos' => Comentario::distinct('user_id')->count('user_id'),
            ];

            return response()->json([
                'success' => true,
                'data' => $comentarios,
                'estadisticas' => $estadisticas,
                'message' => 'Comentarios obtenidos exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener comentarios: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los comentarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de comentario
     */
    public function show($id)
    {
        try {
            $comentario = Comentario::with(['usuario', 'comentable'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $comentario,
                'message' => 'Comentario obtenido exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Comentario no encontrado'
            ], 404);
        }
    }

    /**
     * Actualizar comentario
     */
    public function update(Request $request, $id)
    {
        try {
            $comentario = Comentario::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Comentario no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'contenido' => 'required|string|min:3|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $comentario->update([
                'contenido' => $request->contenido,
            ]);

            return response()->json([
                'success' => true,
                'data' => $comentario->fresh(['usuario', 'comentable']),
                'message' => 'Comentario actualizado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al actualizar comentario: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el comentario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar comentario
     */
    public function destroy($id)
    {
        try {
            $comentario = Comentario::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Comentario no encontrado'
            ], 404);
        }

        try {
            $comentario->delete();

            return response()->json([
                'success' => true,
                'message' => 'Comentario eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al eliminar comentario: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el comentario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Acción masiva (eliminar varios)
     */
    public function masivo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'accion' => 'required|in:eliminar',
            'comentarios' => 'required|array',
            'comentarios.*' => 'exists:comentarios,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($request->accion === 'eliminar') {
                Comentario::whereIn('id', $request->comentarios)->delete();
                $mensaje = 'Comentarios eliminados exitosamente.';
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'data' => [
                    'eliminados' => count($request->comentarios)
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error en acción masiva: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al realizar la acción masiva',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
