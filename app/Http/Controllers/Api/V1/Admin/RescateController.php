<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rescate;
use App\Models\Mascota;
use App\Models\Fundacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RescateController extends Controller
{
    /**
     * Listado de rescates
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'estado' => 'nullable|in:en_proceso,completado,seguimiento',
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
            $query = Rescate::with([
                'mascota',
                'reporte',
                'usuarioReporto',
                'entidadResponsable',
                'gestionadoPor'
            ]);

            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            $perPage = $request->get('per_page', 15);
            $rescates = $query->latest()->paginate($perPage);

            // Estadísticas
            $estadisticas = [
                'en_proceso' => Rescate::where('estado', 'en_proceso')->count(),
                'completados' => Rescate::where('estado', 'completado')->count(),
                'seguimiento' => Rescate::where('estado', 'seguimiento')->count(),
                'total' => Rescate::count(),
                'este_mes' => Rescate::whereMonth('fecha_rescate', now()->month)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $rescates,
                'estadisticas' => $estadisticas,
                'message' => 'Rescates obtenidos exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener rescates: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los rescates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de rescate
     */
    public function show($id)
    {
        try {
            $rescate = Rescate::with([
                'mascota',
                'reporte',
                'usuarioReporto',
                'entidadResponsable',
                'gestionadoPor'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $rescate,
                'message' => 'Rescate obtenido exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rescate no encontrado'
            ], 404);
        }
    }

    /**
     * Crear nuevo rescate
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_rescate' => 'required|date',
            'lugar_rescate' => 'required|string|max:255',
            'descripcion_rescate' => 'required|string',
            'estado' => 'required|in:en_proceso,completado,seguimiento',
            'mascota_id' => 'nullable|exists:mascotas,id',
            'reporte_id' => 'nullable|exists:reportes,id',
            'entidad_responsable_id' => 'nullable|integer',
            'entidad_responsable_type' => 'nullable|string|in:App\Models\Fundacion,App\Models\Veterinaria',
            'usuario_reporto_id' => 'nullable|exists:users,id',
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
            $data['gestionado_por'] = auth()->id();

            $rescate = Rescate::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $rescate->load([
                    'mascota', 'reporte', 'usuarioReporto', 'entidadResponsable'
                ]),
                'message' => 'Rescate registrado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear rescate: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el rescate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar rescate
     */
    public function update(Request $request, $id)
    {
        try {
            $rescate = Rescate::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rescate no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'fecha_rescate' => 'sometimes|required|date',
            'lugar_rescate' => 'sometimes|required|string|max:255',
            'descripcion_rescate' => 'sometimes|required|string',
            'estado' => 'sometimes|required|in:en_proceso,completado,seguimiento',
            'entidad_responsable_id' => 'nullable|integer',
            'entidad_responsable_type' => 'nullable|string|in:App\Models\Fundacion,App\Models\Veterinaria',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rescate->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $rescate->fresh([
                    'mascota', 'reporte', 'usuarioReporto', 'entidadResponsable'
                ]),
                'message' => 'Rescate actualizado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al actualizar rescate: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el rescate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar rescate
     */
    public function destroy($id)
    {
        try {
            $rescate = Rescate::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rescate no encontrado'
            ], 404);
        }

        DB::beginTransaction();

        try {
            // Si tiene mascota asociada, no eliminar (solo desvincular?)
            if ($rescate->mascota_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un rescate que tiene una mascota asociada'
                ], 422);
            }

            $rescate->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rescate eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar rescate: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el rescate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Completar rescate (asignar a fundación y crear mascota)
     */
    public function completar(Request $request, $id)
    {
        try {
            $rescate = Rescate::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rescate no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'fundacion_id' => 'required|exists:fundaciones,id',
            'nombre_mascota' => 'required|string|max:255',
            'especie' => 'required|string|max:100',
            'genero' => 'nullable|in:Macho,Hembra,Desconocido',
            'edad_aprox' => 'nullable|integer|min:0|max:30',
            'condiciones_especiales' => 'nullable|string',
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
            // Crear mascota a partir del rescate
            $mascota = Mascota::create([
                'nombre_mascota' => $request->nombre_mascota,
                'especie' => $request->especie,
                'genero' => $request->genero ?? 'Desconocido',
                'edad_aprox' => $request->edad_aprox,
                'estado' => 'Rescatada',
                'lugar_rescate' => $rescate->lugar_rescate,
                'descripcion' => "Rescatada el " . $rescate->fecha_rescate->format('d/m/Y') . ". " . $rescate->descripcion_rescate,
                'condiciones_especiales' => $request->condiciones_especiales,
                'fecha_ingreso' => now(),
                'fundacion_id' => $request->fundacion_id,
            ]);

            $rescate->update([
                'mascota_id' => $mascota->id,
                'estado' => 'completado',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'rescate' => $rescate->fresh(),
                    'mascota' => $mascota,
                ],
                'message' => 'Rescate completado. Mascota creada exitosamente.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al completar rescate: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al completar el rescate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignar entidad responsable
     */
    public function asignarEntidad(Request $request, $id)
    {
        try {
            $rescate = Rescate::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rescate no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'entidad_responsable_id' => 'required|integer',
            'entidad_responsable_type' => 'required|string|in:App\Models\Fundacion,App\Models\Veterinaria',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rescate->update([
                'entidad_responsable_id' => $request->entidad_responsable_id,
                'entidad_responsable_type' => $request->entidad_responsable_type,
            ]);

            return response()->json([
                'success' => true,
                'data' => $rescate->fresh(['entidadResponsable']),
                'message' => 'Entidad asignada correctamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al asignar entidad: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al asignar la entidad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de rescates
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'por_estado' => [
                    'en_proceso' => Rescate::where('estado', 'en_proceso')->count(),
                    'completado' => Rescate::where('estado', 'completado')->count(),
                    'seguimiento' => Rescate::where('estado', 'seguimiento')->count(),
                ],
                'por_mes' => Rescate::select(
                    DB::raw('YEAR(fecha_rescate) as year'),
                    DB::raw('MONTH(fecha_rescate) as month'),
                    DB::raw('COUNT(*) as total')
                )
                ->where('fecha_rescate', '>=', now()->subYear())
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get(),
                'top_entidades' => Rescate::whereNotNull('entidad_responsable_id')
                    ->select('entidad_responsable_id', 'entidad_responsable_type', DB::raw('COUNT(*) as total'))
                    ->groupBy('entidad_responsable_id', 'entidad_responsable_type')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get()
                    ->map(function($item) {
                        $entidad = $item->entidad_responsable_type::find($item->entidad_responsable_id);
                        return [
                            'nombre' => $entidad ? ($entidad->Nombre_1 ?? $entidad->Nombre_vet ?? 'N/A') : 'N/A',
                            'tipo' => class_basename($item->entidad_responsable_type),
                            'total' => $item->total,
                        ];
                    }),
                'tiempo_promedio_resolucion' => $this->calcularTiempoPromedioResolucion(),
            ];

            return response()->json([
                'success' => true,
                'data' => $estadisticas,
                'message' => 'Estadísticas obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcular tiempo promedio de resolución
     */
    private function calcularTiempoPromedioResolucion()
    {
        $rescatesCompletados = Rescate::where('estado', 'completado')
            ->whereNotNull('mascota_id')
            ->get();

        if ($rescatesCompletados->isEmpty()) {
            return 0;
        }

        $totalDias = $rescatesCompletados->sum(function ($rescate) {
            $mascota = Mascota::find($rescate->mascota_id);
            if ($mascota && $mascota->created_at) {
                return $rescate->fecha_rescate->diffInDays($mascota->created_at);
            }
            return 0;
        });

        return round($totalDias / $rescatesCompletados->count(), 1);
    }
}
