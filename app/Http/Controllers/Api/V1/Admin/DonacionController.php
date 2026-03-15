<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donacion;
use App\Models\User;
use App\Models\Fundacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DonacionController extends Controller
{
    /**
     * Listado de donaciones
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'fundacion_id' => 'nullable|exists:fundaciones,id',
            'publica' => 'nullable|boolean',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'per_page' => 'nullable|integer|min:1|max:100',
            'orden' => 'nullable|in:asc,desc',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = Donacion::with(['user', 'fundacion']);

            // Filtros
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('fundacion_id')) {
                $query->where('fundacion_id', $request->fundacion_id);
            }

            if ($request->filled('publica')) {
                $query->where('publica', $request->publica);
            }

            if ($request->filled('fecha_inicio')) {
                $query->whereDate('fecha_donacion', '>=', $request->fecha_inicio);
            }

            if ($request->filled('fecha_fin')) {
                $query->whereDate('fecha_donacion', '<=', $request->fecha_fin);
            }

            // Ordenamiento
            $orden = $request->get('orden', 'desc');
            $query->orderBy('fecha_donacion', $orden);

            $perPage = $request->get('per_page', 15);
            $donaciones = $query->paginate($perPage);

            // Totales
            $totales = [
                'total' => Donacion::sum('valor_donacion'),
                'publicas' => Donacion::where('publica', true)->sum('valor_donacion'),
                'privadas' => Donacion::where('publica', false)->sum('valor_donacion'),
                'este_mes' => Donacion::whereMonth('fecha_donacion', now()->month)
                    ->whereYear('fecha_donacion', now()->year)
                    ->sum('valor_donacion'),
                'promedio' => round(Donacion::avg('valor_donacion') ?? 0, 2),
            ];

            return response()->json([
                'success' => true,
                'data' => $donaciones,
                'totales' => $totales,
                'message' => 'Donaciones obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener donaciones: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las donaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de donación
     */
    public function show($id)
    {
        try {
            $donacion = Donacion::with(['user', 'fundacion'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $donacion,
                'message' => 'Donación obtenida exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Donación no encontrada'
            ], 404);
        }
    }

    /**
     * Crear nueva donación (admin)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'valor_donacion' => 'required|numeric|min:1000',
            'user_id' => 'nullable|exists:users,id',
            'fundacion_id' => 'nullable|exists:fundaciones,id',
            'publica' => 'boolean',
            'fecha_donacion' => 'nullable|date',
            'metodo_pago' => 'nullable|string|max:100',
            'comentarios' => 'nullable|string|max:500',
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

            // Si no se proporciona fecha, usar actual
            if (!isset($data['fecha_donacion'])) {
                $data['fecha_donacion'] = now();
            }

            $donacion = Donacion::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $donacion->load(['user', 'fundacion']),
                'message' => 'Donación registrada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear donación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la donación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar donación
     */
    public function update(Request $request, $id)
    {
        try {
            $donacion = Donacion::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Donación no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'valor_donacion' => 'sometimes|required|numeric|min:1000',
            'user_id' => 'nullable|exists:users,id',
            'fundacion_id' => 'nullable|exists:fundaciones,id',
            'publica' => 'boolean',
            'fecha_donacion' => 'nullable|date',
            'metodo_pago' => 'nullable|string|max:100',
            'comentarios' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $donacion->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $donacion->fresh(['user', 'fundacion']),
                'message' => 'Donación actualizada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al actualizar donación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la donación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar donación
     */
    public function destroy($id)
    {
        try {
            $donacion = Donacion::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Donación no encontrada'
            ], 404);
        }

        try {
            $donacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Donación eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al eliminar donación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la donación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar visibilidad de donación
     */
    public function togglePublica($id)
    {
        try {
            $donacion = Donacion::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Donación no encontrada'
            ], 404);
        }

        try {
            $donacion->update([
                'publica' => !$donacion->publica
            ]);

            return response()->json([
                'success' => true,
                'data' => ['publica' => $donacion->publica],
                'message' => 'Visibilidad actualizada correctamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al cambiar visibilidad: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar visibilidad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reporte de donaciones por período
     */
    public function reporte(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'fundacion_id' => 'nullable|exists:fundaciones,id',
            'agrupacion' => 'nullable|in:dia,mes,fundacion',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = Donacion::whereBetween('fecha_donacion', [$request->fecha_inicio, $request->fecha_fin]);

            if ($request->filled('fundacion_id')) {
                $query->where('fundacion_id', $request->fundacion_id);
            }

            $donaciones = $query->with(['user', 'fundacion'])->get();

            // Estadísticas generales
            $total = $donaciones->sum('valor_donacion');
            $promedio = $donaciones->avg('valor_donacion');
            $cantidad = $donaciones->count();
            $maxima = $donaciones->max('valor_donacion');
            $minima = $donaciones->min('valor_donacion');

            $resultado = [
                'periodo' => [
                    'inicio' => $request->fecha_inicio,
                    'fin' => $request->fecha_fin,
                ],
                'estadisticas' => [
                    'total' => $total,
                    'promedio' => round($promedio ?? 0, 2),
                    'cantidad' => $cantidad,
                    'maxima' => $maxima,
                    'minima' => $minima,
                ],
                'donaciones' => $donaciones,
            ];

            // Agrupar según lo solicitado
            $agrupacion = $request->get('agrupacion', 'dia');

            if ($agrupacion === 'dia') {
                $resultado['agrupado'] = $donaciones->groupBy(function($item) {
                    return $item->fecha_donacion->format('Y-m-d');
                })->map(function($grupo) {
                    return [
                        'cantidad' => $grupo->count(),
                        'total' => $grupo->sum('valor_donacion')
                    ];
                });
            } elseif ($agrupacion === 'mes') {
                $resultado['agrupado'] = $donaciones->groupBy(function($item) {
                    return $item->fecha_donacion->format('Y-m');
                })->map(function($grupo) {
                    return [
                        'cantidad' => $grupo->count(),
                        'total' => $grupo->sum('valor_donacion')
                    ];
                });
            } elseif ($agrupacion === 'fundacion') {
                $resultado['agrupado'] = $donaciones->groupBy(function($item) {
                    return $item->fundacion->Nombre_1 ?? 'Sin Fundación';
                })->map(function($grupo) {
                    return [
                        'cantidad' => $grupo->count(),
                        'total' => $grupo->sum('valor_donacion')
                    ];
                });
            }

            return response()->json([
                'success' => true,
                'data' => $resultado,
                'message' => 'Reporte generado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al generar reporte: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
