<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fundacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FundacionController extends Controller
{
    /**
     * Listado de fundaciones
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:100',
            'recibe_voluntarios' => 'nullable|boolean',
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
            $query = Fundacion::with('user');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('Nombre_1', 'like', "%{$search}%")
                      ->orWhere('Email', 'like', "%{$search}%")
                      ->orWhere('Telefono', 'like', "%{$search}%")
                      ->orWhere('Direccion', 'like', "%{$search}%");
                });
            }

            if ($request->filled('recibe_voluntarios')) {
                $query->where('recibe_voluntarios', $request->recibe_voluntarios);
            }

            $perPage = $request->get('per_page', 15);
            $fundaciones = $query->orderBy('Nombre_1')->paginate($perPage);

            // Estadísticas
            $estadisticas = [
                'total' => Fundacion::count(),
                'reciben_voluntarios' => Fundacion::where('recibe_voluntarios', true)->count(),
                'total_mascotas' => Fundacion::withCount('mascotas')->get()->sum('mascotas_count'),
            ];

            return response()->json([
                'success' => true,
                'data' => $fundaciones,
                'estadisticas' => $estadisticas,
                'message' => 'Fundaciones obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener fundaciones: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las fundaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de fundación
     */
    public function show($id)
    {
        try {
            $fundacion = Fundacion::with([
                'user',
                'mascotas' => function($q) {
                    $q->whereIn('estado', ['En adopcion', 'Rescatada', 'En acogida']);
                },
                'rescates',
                'donaciones',
                'adopciones'
            ])->findOrFail($id);

            // Estadísticas
            $stats = [
                'mascotas_activas' => $fundacion->mascotas->count(),
                'adopciones_realizadas' => $fundacion->adopciones()->count(),
                'rescates_gestionados' => $fundacion->rescates()->count(),
                'total_donaciones' => $fundacion->donaciones()->sum('valor_donacion'),
                'donaciones_publicas' => $fundacion->donaciones()->where('publica', true)->sum('valor_donacion'),
                'donaciones_privadas' => $fundacion->donaciones()->where('publica', false)->sum('valor_donacion'),
            ];

            // Decodificar necesidades
            $necesidades = json_decode($fundacion->necesidades_actuales, true) ?? [];

            return response()->json([
                'success' => true,
                'data' => [
                    'fundacion' => $fundacion,
                    'estadisticas' => $stats,
                    'necesidades' => $necesidades,
                ],
                'message' => 'Fundación obtenida exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fundación no encontrada'
            ], 404);
        }
    }

    /**
     * Crear nueva fundación
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Nombre_1' => 'required|string|max:255',
            'Direccion' => 'required|string|unique:fundaciones',
            'Telefono' => 'required|string|unique:fundaciones',
            'Email' => 'required|email|unique:fundaciones',
            'registro_sanitario' => 'nullable|string|max:255',
            'capacidad_maxima' => 'nullable|integer|min:1',
            'necesidades_actuales' => 'nullable|array',
            'horario_atencion' => 'nullable|string',
            'recibe_voluntarios' => 'boolean',
            'user_id' => 'nullable|exists:users,id',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'sitio_web' => 'nullable|url|max:255',
            'redes_sociales' => 'nullable|array',
            'descripcion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $data = $request->all();

            // Convertir necesidades a JSON
            if (isset($data['necesidades_actuales'])) {
                $data['necesidades_actuales'] = json_encode($data['necesidades_actuales'], JSON_UNESCAPED_UNICODE);
            }

            if (isset($data['redes_sociales'])) {
                $data['redes_sociales'] = json_encode($data['redes_sociales'], JSON_UNESCAPED_UNICODE);
            }

            $fundacion = Fundacion::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $fundacion->load('user'),
                'message' => 'Fundación creada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear fundación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la fundación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar fundación
     */
    public function update(Request $request, $id)
    {
        try {
            $fundacion = Fundacion::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fundación no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'Nombre_1' => 'sometimes|required|string|max:255',
            'Direccion' => 'sometimes|required|string|unique:fundaciones,Direccion,' . $id,
            'Telefono' => 'sometimes|required|string|unique:fundaciones,Telefono,' . $id,
            'Email' => 'sometimes|required|email|unique:fundaciones,Email,' . $id,
            'registro_sanitario' => 'nullable|string|max:255',
            'capacidad_maxima' => 'nullable|integer|min:1',
            'necesidades_actuales' => 'nullable|array',
            'horario_atencion' => 'nullable|string',
            'recibe_voluntarios' => 'boolean',
            'user_id' => 'nullable|exists:users,id',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'sitio_web' => 'nullable|url|max:255',
            'redes_sociales' => 'nullable|array',
            'descripcion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();

            if (isset($data['necesidades_actuales'])) {
                $data['necesidades_actuales'] = json_encode($data['necesidades_actuales'], JSON_UNESCAPED_UNICODE);
            }

            if (isset($data['redes_sociales'])) {
                $data['redes_sociales'] = json_encode($data['redes_sociales'], JSON_UNESCAPED_UNICODE);
            }

            $fundacion->update($data);

            return response()->json([
                'success' => true,
                'data' => $fundacion->fresh('user'),
                'message' => 'Fundación actualizada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al actualizar fundación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la fundación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar fundación
     */
    public function destroy($id)
    {
        try {
            $fundacion = Fundacion::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fundación no encontrada'
            ], 404);
        }

        DB::beginTransaction();

        try {
            // Verificar relaciones
            if ($fundacion->mascotas()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la fundación porque tiene mascotas asociadas'
                ], 422);
            }

            if ($fundacion->adopciones()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la fundación porque tiene adopciones asociadas'
                ], 422);
            }

            if ($fundacion->rescates()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la fundación porque tiene rescates asociados'
                ], 422);
            }

            $fundacion->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fundación eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar fundación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la fundación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener necesidades de la fundación
     */
    public function necesidades($id)
    {
        try {
            $fundacion = Fundacion::findOrFail($id);
            $necesidades = json_decode($fundacion->necesidades_actuales, true) ?? [];

            return response()->json([
                'success' => true,
                'data' => $necesidades,
                'message' => 'Necesidades obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fundación no encontrada'
            ], 404);
        }
    }

    /**
     * Actualizar necesidades
     */
    public function actualizarNecesidades(Request $request, $id)
    {
        try {
            $fundacion = Fundacion::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fundación no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'necesidades' => 'required|array',
            'necesidades.*' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $fundacion->update([
                'necesidades_actuales' => json_encode($request->necesidades, JSON_UNESCAPED_UNICODE)
            ]);

            return response()->json([
                'success' => true,
                'data' => $request->necesidades,
                'message' => 'Necesidades actualizadas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al actualizar necesidades: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar necesidades',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
