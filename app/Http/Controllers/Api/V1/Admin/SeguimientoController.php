<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Adopcion;
use App\Models\SeguimientoAdopcion;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SeguimientoController extends Controller
{
    /**
     * Listar seguimientos de una adopción
     */
    public function index($adopcionId)
    {
        $adopcion = Adopcion::findOrFail($adopcionId);

        $seguimientos = SeguimientoAdopcion::where('adopcion_id', $adopcionId)
            ->with(['realizadoPor'])
            ->orderBy('fecha_seguimiento', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $seguimientos
        ]);
    }

    /**
     * Crear un nuevo seguimiento
     */
    public function store(Request $request, $adopcionId)
    {
        $adopcion = Adopcion::findOrFail($adopcionId);

        $validator = Validator::make($request->all(), [
            'tipo_seguimiento' => 'required|in:virtual,domiciliario,telefonico',
            'fecha_seguimiento' => 'required|date',
            'proximo_seguimiento' => 'nullable|date|after:fecha_seguimiento',
            'observaciones' => 'required|string',
            'recomendaciones' => 'nullable|string',
            'estado_mascota' => 'required|in:excelente,bueno,regular,preocupante',
            'resultado' => 'required|in:satisfactorio,observaciones,incumplimiento,reingreso',
            'foto_url' => 'nullable|image|max:2048',
            'fotos_adicionales' => 'nullable|array',
            'fotos_adicionales.*' => 'image|max:2048',
            'video_url' => 'nullable|url',
            'documento_url' => 'nullable|url',
            'condiciones_hogar' => 'nullable|in:optimas,aceptables,mejorables,precarias',
            'observaciones_hogar' => 'nullable|string',
            'convive_con_otros_animales' => 'nullable|boolean',
            'comportamiento_observado' => 'nullable|string',
            'requiere_nuevo_seguimiento' => 'boolean',
            'firma_adoptante' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['adopcion_id'] = $adopcionId;
            $data['realizado_por'] = auth()->id();
            $data['realizado_por_nombre'] = auth()->user()->nombre;

            // Subir foto si existe
            if ($request->hasFile('foto_url')) {
                $data['foto_url'] = $request->file('foto_url')->store('seguimientos', 'public');
            }

            // Subir fotos adicionales
            if ($request->hasFile('fotos_adicionales')) {
                $fotos = [];
                foreach ($request->file('fotos_adicionales') as $foto) {
                    $fotos[] = $foto->store('seguimientos', 'public');
                }
                $data['fotos_adicionales'] = json_encode($fotos);
            }

            $seguimiento = SeguimientoAdopcion::create($data);

            // Si requiere nuevo seguimiento, actualizar la adopción
            if ($data['requiere_nuevo_seguimiento'] ?? false) {
                $adopcion->update(['requiere_seguimiento' => true]);
            }

            // Si el resultado es reingreso, cambiar estado de la mascota
            if ($data['resultado'] === 'reingreso') {
                $adopcion->mascota->update(['estado' => 'En adopcion']);
                $adopcion->update(['estado' => 'reingresada']);
            }

            // Notificar al adoptante
            Notificacion::create([
                'user_id' => $adopcion->user_id,
                'contenido' => "Se ha registrado un seguimiento de tu adopción. Resultado: " . $this->getResultadoTexto($data['resultado']),
                'creado_por_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Seguimiento registrado exitosamente',
                'data' => $seguimiento->load('realizadoPor')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar seguimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver detalle de un seguimiento
     */
    public function show($id)
    {
        $seguimiento = SeguimientoAdopcion::with(['adopcion', 'realizadoPor'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $seguimiento
        ]);
    }

    /**
     * Actualizar un seguimiento
     */
    public function update(Request $request, $id)
    {
        $seguimiento = SeguimientoAdopcion::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'tipo_seguimiento' => 'sometimes|in:virtual,domiciliario,telefonico',
            'fecha_seguimiento' => 'sometimes|date',
            'proximo_seguimiento' => 'nullable|date',
            'observaciones' => 'sometimes|string',
            'recomendaciones' => 'nullable|string',
            'estado_mascota' => 'sometimes|in:excelente,bueno,regular,preocupante',
            'resultado' => 'sometimes|in:satisfactorio,observaciones,incumplimiento,reingreso',
            'condiciones_hogar' => 'nullable|in:optimas,aceptables,mejorables,precarias',
            'observaciones_hogar' => 'nullable|string',
            'convive_con_otros_animales' => 'nullable|boolean',
            'comportamiento_observado' => 'nullable|string',
            'requiere_nuevo_seguimiento' => 'boolean',
            'firma_adoptante' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $seguimiento->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Seguimiento actualizado',
                'data' => $seguimiento
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un seguimiento
     */
    public function destroy($id)
    {
        $seguimiento = SeguimientoAdopcion::findOrFail($id);

        try {
            $seguimiento->delete();

            return response()->json([
                'success' => true,
                'message' => 'Seguimiento eliminado'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de seguimientos
     */
    public function estadisticas($adopcionId)
    {
        $adopcion = Adopcion::findOrFail($adopcionId);

        $stats = [
            'total' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->count(),
            'por_tipo' => [
                'virtual' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('tipo_seguimiento', 'virtual')->count(),
                'domiciliario' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('tipo_seguimiento', 'domiciliario')->count(),
                'telefonico' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('tipo_seguimiento', 'telefonico')->count(),
            ],
            'por_resultado' => [
                'satisfactorio' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('resultado', 'satisfactorio')->count(),
                'observaciones' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('resultado', 'observaciones')->count(),
                'incumplimiento' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('resultado', 'incumplimiento')->count(),
                'reingreso' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('resultado', 'reingreso')->count(),
            ],
            'estado_mascota' => [
                'excelente' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('estado_mascota', 'excelente')->count(),
                'bueno' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('estado_mascota', 'bueno')->count(),
                'regular' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('estado_mascota', 'regular')->count(),
                'preocupante' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('estado_mascota', 'preocupante')->count(),
            ],
            'ultimos' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)
                ->with(['realizadoPor'])
                ->orderBy('fecha_seguimiento', 'desc')
                ->limit(5)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Obtener texto del resultado
     */
    private function getResultadoTexto($resultado)
    {
        return match($resultado) {
            'satisfactorio' => 'Satisfactorio',
            'observaciones' => 'Requiere observaciones',
            'incumplimiento' => 'Incumplimiento detectado',
            'reingreso' => 'Mascota requiere reingreso',
            default => $resultado,
        };
    }
}
