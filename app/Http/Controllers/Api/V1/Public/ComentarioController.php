<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Comentario;
use App\Models\Mascota;
use App\Models\Evento;
use App\Models\Fundacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComentarioController extends Controller
{
    /**
     * Obtener comentarios de una entidad
     */
    public function index($entidadTipo, $entidadId)
    {
        $modelClass = $this->getModelClass($entidadTipo);

        if (!$modelClass) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de entidad no válido'
            ], 400);
        }

        // Verificar que la entidad existe
        $entidad = $modelClass::find($entidadId);
        if (!$entidad) {
            return response()->json([
                'success' => false,
                'message' => 'Entidad no encontrada'
            ], 404);
        }

        $comentarios = Comentario::where('comentable_type', $modelClass)
            ->where('comentable_id', $entidadId)
            ->with('usuario')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comentarios
        ]);
    }

    /**
     * Crear comentario (requiere autenticación)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contenido' => 'required|string|min:3|max:1000',
            'entidad_tipo' => 'required|in:mascota,evento,fundacion',
            'entidad_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $modelClass = $this->getModelClass($request->entidad_tipo);

        // Verificar que la entidad existe
        $entidad = $modelClass::find($request->entidad_id);
        if (!$entidad) {
            return response()->json([
                'success' => false,
                'message' => 'La entidad no existe'
            ], 404);
        }

        $comentario = Comentario::create([
            'contenido' => $request->contenido,
            'fecha' => now(),
            'user_id' => auth()->id(),
            'comentable_type' => $modelClass,
            'comentable_id' => $request->entidad_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comentario creado exitosamente',
            'data' => $comentario->load('usuario')
        ], 201);
    }

    /**
     * Obtener la clase del modelo según el tipo
     */
    private function getModelClass($tipo)
    {
        $map = [
            'mascota' => 'App\\Models\\Mascota',
            'evento' => 'App\\Models\\Evento',
            'fundacion' => 'App\\Models\\Fundacion',
        ];

        return $map[$tipo] ?? null;
    }
}
