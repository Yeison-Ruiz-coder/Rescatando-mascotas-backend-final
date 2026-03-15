<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoVacuna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TipoVacunaController extends Controller
{
    /**
     * Listado de tipos de vacuna
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
            $query = TipoVacuna::query();

            if ($request->filled('search')) {
                $query->where('nombre_vacuna', 'like', "%{$request->search}%");
            }

            $perPage = $request->get('per_page', 20);
            $tiposVacunas = $query->orderBy('nombre_vacuna')->paginate($perPage);

            // Estadísticas
            $stats = [
                'total' => TipoVacuna::count(),
                'con_mascotas' => TipoVacuna::has('mascotas')->count(),
                'total_aplicaciones' => DB::table('mascota_vacuna')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $tiposVacunas,
                'estadisticas' => $stats,
                'message' => 'Tipos de vacuna obtenidos exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener tipos de vacuna: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los tipos de vacuna',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de tipo de vacuna
     */
    public function show($id)
    {
        try {
            $tipoVacuna = TipoVacuna::with(['mascotas' => function($q) {
                $q->withPivot('fecha_aplicacion')->latest('pivot_fecha_aplicacion');
            }])->findOrFail($id);

            $totalAplicaciones = $tipoVacuna->mascotas->count();
            $mascotasConEstaVacuna = $tipoVacuna->mascotas->unique('id')->count();
            $ultimaAplicacion = $tipoVacuna->mascotas->max('pivot.fecha_aplicacion');

            // Próximas vacunas a aplicar (basado en frecuencia)
            $proximasAplicaciones = [];
            if ($tipoVacuna->frecuencia_dias) {
                $proximasAplicaciones = $tipoVacuna->mascotas
                    ->filter(function($mascota) use ($tipoVacuna) {
                        return $mascota->pivot->fecha_aplicacion &&
                            $mascota->pivot->fecha_aplicacion->addDays($tipoVacuna->frecuencia_dias) <= now()->addDays(30);
                    })
                    ->map(function($mascota) use ($tipoVacuna) {
                        return [
                            'mascota_id' => $mascota->id,
                            'mascota_nombre' => $mascota->nombre_mascota,
                            'fecha_ultima' => $mascota->pivot->fecha_aplicacion,
                            'fecha_proxima' => $mascota->pivot->fecha_aplicacion->addDays($tipoVacuna->frecuencia_dias),
                        ];
                    })->values();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tipo_vacuna' => $tipoVacuna,
                    'estadisticas' => [
                        'total_aplicaciones' => $totalAplicaciones,
                        'mascotas_vacunadas' => $mascotasConEstaVacuna,
                        'ultima_aplicacion' => $ultimaAplicacion,
                    ],
                    'proximas_aplicaciones' => $proximasAplicaciones,
                ],
                'message' => 'Tipo de vacuna obtenido exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de vacuna no encontrado'
            ], 404);
        }
    }

    /**
     * Crear nuevo tipo de vacuna
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_vacuna' => 'required|string|max:255|unique:tipos_vacunas',
            'descripcion' => 'nullable|string',
            'frecuencia_dias' => 'nullable|integer|min:1',
            'edad_minima_dias' => 'nullable|integer|min:0',
            'edad_maxima_dias' => 'nullable|integer|min:0|gt:edad_minima_dias',
            'especie' => 'nullable|string|max:100',
            'obligatoria' => 'boolean',
            'activa' => 'boolean',
            'informacion_adicional' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tipoVacuna = TipoVacuna::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $tipoVacuna,
                'message' => 'Tipo de vacuna creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al crear tipo de vacuna: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el tipo de vacuna',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar tipo de vacuna
     */
    public function update(Request $request, $id)
    {
        try {
            $tipoVacuna = TipoVacuna::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de vacuna no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre_vacuna' => 'sometimes|required|string|max:255|unique:tipos_vacunas,nombre_vacuna,' . $id,
            'descripcion' => 'nullable|string',
            'frecuencia_dias' => 'nullable|integer|min:1',
            'edad_minima_dias' => 'nullable|integer|min:0',
            'edad_maxima_dias' => 'nullable|integer|min:0|gt:edad_minima_dias',
            'especie' => 'nullable|string|max:100',
            'obligatoria' => 'boolean',
            'activa' => 'boolean',
            'informacion_adicional' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tipoVacuna->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $tipoVacuna,
                'message' => 'Tipo de vacuna actualizado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al actualizar tipo de vacuna: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el tipo de vacuna',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar tipo de vacuna
     */
    public function destroy($id)
    {
        try {
            $tipoVacuna = TipoVacuna::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de vacuna no encontrado'
            ], 404);
        }

        try {
            // Verificar si tiene mascotas asociadas
            if ($tipoVacuna->mascotas()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el tipo de vacuna porque tiene mascotas asociadas'
                ], 422);
            }

            $tipoVacuna->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de vacuna eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al eliminar tipo de vacuna: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el tipo de vacuna',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vacunas recomendadas por especie
     */
    public function recomendadas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'especie' => 'nullable|string|in:Perro,Gato',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $vacunasRecomendadas = [
                'Perro' => [
                    ['nombre' => 'Múltiple (Canigen o similar)', 'frecuencia' => 'Anual', 'obligatoria' => true],
                    ['nombre' => 'Rabia', 'frecuencia' => 'Anual', 'obligatoria' => true],
                    ['nombre' => 'Tos de las perreras', 'frecuencia' => 'Anual', 'obligatoria' => false],
                    ['nombre' => 'Leptospirosis', 'frecuencia' => 'Anual', 'obligatoria' => false],
                ],
                'Gato' => [
                    ['nombre' => 'Trivalente (Feligen o similar)', 'frecuencia' => 'Anual', 'obligatoria' => true],
                    ['nombre' => 'Rabia', 'frecuencia' => 'Anual', 'obligatoria' => true],
                    ['nombre' => 'Leucemia felina', 'frecuencia' => 'Anual', 'obligatoria' => false],
                ],
            ];

            $data = $request->filled('especie')
                ? [$request->especie => $vacunasRecomendadas[$request->especie] ?? []]
                : $vacunasRecomendadas;

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Vacunas recomendadas obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener vacunas recomendadas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener vacunas recomendadas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de vacunación
     */
    public function estadisticas()
    {
        try {
            $totalVacunas = TipoVacuna::count();
            $totalAplicaciones = DB::table('mascota_vacuna')->count();

            // Vacunas más aplicadas
            $masAplicadas = TipoVacuna::withCount('mascotas')
                ->orderByDesc('mascotas_count')
                ->limit(5)
                ->get(['id', 'nombre_vacuna', 'mascotas_count']);

            // Aplicaciones por mes (últimos 6 meses)
            $aplicacionesPorMes = DB::table('mascota_vacuna')
                ->select(
                    DB::raw('YEAR(fecha_aplicacion) as year'),
                    DB::raw('MONTH(fecha_aplicacion) as month'),
                    DB::raw('COUNT(*) as total')
                )
                ->where('fecha_aplicacion', '>=', now()->subMonths(6))
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_vacunas' => $totalVacunas,
                    'total_aplicaciones' => $totalAplicaciones,
                    'mas_aplicadas' => $masAplicadas,
                    'aplicaciones_por_mes' => $aplicacionesPorMes,
                ],
                'message' => 'Estadísticas obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de vacunas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
