<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class NotificacionController extends Controller
{
    /**
     * GET /api/admin/notificaciones
     * Listado de notificaciones con filtros
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'creado_por_id' => 'nullable|exists:users,id',
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
            $query = Notificacion::with(['usuario', 'creadoPor']);

            // Filtros
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('creado_por_id')) {
                $query->where('creado_por_id', $request->creado_por_id);
            }

            if ($request->filled('fecha_inicio')) {
                $query->whereDate('fecha_envio', '>=', $request->fecha_inicio);
            }

            if ($request->filled('fecha_fin')) {
                $query->whereDate('fecha_envio', '<=', $request->fecha_fin);
            }

            if ($request->filled('search')) {
                $query->where('contenido', 'like', "%{$request->search}%");
            }

            $perPage = $request->get('per_page', 15);
            $notificaciones = $query->orderBy('fecha_envio', 'desc')->paginate($perPage);

            // Estadísticas
            $stats = [
                'total' => Notificacion::count(),
                'hoy' => Notificacion::whereDate('fecha_envio', today())->count(),
                'esta_semana' => Notificacion::where('fecha_envio', '>=', now()->subDays(7))->count(),
                'usuarios_notificados' => Notificacion::distinct('user_id')->count('user_id'),
                'promedio_diario' => round(Notificacion::count() / max(1, Notificacion::distinct(DB::raw('DATE(fecha_envio)'))->count()), 1),
            ];

            return response()->json([
                'success' => true,
                'data' => $notificaciones,
                'estadisticas' => $stats,
                'message' => 'Notificaciones obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener notificaciones: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las notificaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/admin/notificaciones/usuario/{userId}
     * Notificaciones de un usuario específico
     */
    public function porUsuario($userId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'nullable|integer|min:1|max:100',
            'no_leidas' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($userId);

            $query = Notificacion::with('creadoPor')
                ->where('user_id', $userId);

            if ($request->boolean('no_leidas')) {
                // Si agregas campo 'leida' en el futuro
                // $query->where('leida', false);
            }

            $perPage = $request->get('per_page', 15);
            $notificaciones = $query->orderBy('fecha_envio', 'desc')->paginate($perPage);

            $noLeidas = 0; // Para cuando agregues el campo 'leida'
            // $noLeidas = Notificacion::where('user_id', $userId)->where('leida', false)->count();

            return response()->json([
                'success' => true,
                'data' => $notificaciones,
                'estadisticas' => [
                    'total' => Notificacion::where('user_id', $userId)->count(),
                    'no_leidas' => $noLeidas,
                ],
                'message' => 'Notificaciones del usuario obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener notificaciones por usuario: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las notificaciones del usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/admin/notificaciones/{id}
     * Mostrar detalle de notificación
     */
    public function show($id)
    {
        try {
            $notificacion = Notificacion::with(['usuario', 'creadoPor'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $notificacion,
                'message' => 'Notificación obtenida exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notificación no encontrada'
            ], 404);
        }
    }

    /**
     * POST /api/admin/notificaciones
     * Crear nueva notificación (individual)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contenido' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'fecha_envio' => 'nullable|date',
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
            $data['creado_por_id'] = auth()->id();

            if (!isset($data['fecha_envio'])) {
                $data['fecha_envio'] = now();
            }

            $notificacion = Notificacion::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $notificacion->load(['usuario', 'creadoPor']),
                'message' => 'Notificación enviada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear notificación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la notificación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/admin/notificaciones/{id}
     * Actualizar notificación
     */
    public function update(Request $request, $id)
    {
        try {
            $notificacion = Notificacion::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notificación no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'contenido' => 'sometimes|required|string',
            'user_id' => 'sometimes|required|exists:users,id',
            'fecha_envio' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $notificacion->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $notificacion->fresh(['usuario', 'creadoPor']),
                'message' => 'Notificación actualizada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al actualizar notificación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la notificación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/admin/notificaciones/{id}
     * Eliminar notificación
     */
    public function destroy($id)
    {
        try {
            $notificacion = Notificacion::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notificación no encontrada'
            ], 404);
        }

        try {
            $notificacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notificación eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al eliminar notificación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la notificación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/admin/notificaciones/enviar-masivo
     * Enviar notificaciones masivas
     */
    public function enviarMasivo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contenido' => 'required|string',
            'tipo_destinatarios' => 'required|in:todos,usuarios,administradores,fundaciones,veterinarias',
            'fecha_envio' => 'nullable|date',
            'user_ids' => 'nullable|array', // Opcional: enviar a usuarios específicos
            'user_ids.*' => 'exists:users,id',
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
            // Si se especifican usuarios específicos
            if ($request->has('user_ids') && !empty($request->user_ids)) {
                $destinatarios = User::whereIn('id', $request->user_ids)
                    ->where('estado', 'activo')
                    ->get();
            } else {
                // Construir query de destinatarios
                $query = User::where('estado', 'activo');

                switch ($request->tipo_destinatarios) {
                    case 'administradores':
                        $query->where('tipo', 'admin');
                        break;
                    case 'fundaciones':
                        $query->where('tipo', 'fundacion');
                        break;
                    case 'veterinarias':
                        $query->where('tipo', 'veterinaria');
                        break;
                    case 'usuarios':
                        $query->where('tipo', 'user');
                        break;
                    // 'todos' no necesita filtro adicional
                }

                $destinatarios = $query->get();
            }

            $fechaEnvio = $request->fecha_envio ?? now();
            $notificacionesCreadas = [];

            // Crear notificación para cada destinatario
            foreach ($destinatarios as $destinatario) {
                $notificacion = Notificacion::create([
                    'contenido' => $request->contenido,
                    'user_id' => $destinatario->id,
                    'creado_por_id' => auth()->id(),
                    'fecha_envio' => $fechaEnvio,
                ]);
                $notificacionesCreadas[] = $notificacion;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_enviadas' => count($notificacionesCreadas),
                    'tipo_destinatarios' => $request->tipo_destinatarios,
                    'fecha_envio' => $fechaEnvio,
                ],
                'message' => "Notificaciones enviadas a " . count($notificacionesCreadas) . " usuarios"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al enviar notificaciones masivas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar notificaciones masivas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/admin/notificaciones/estadisticas
     * Estadísticas de notificaciones
     */
    public function estadisticas()
    {
        try {
            $ultimos7Dias = collect();
            for ($i = 6; $i >= 0; $i--) {
                $fecha = now()->subDays($i);
                $ultimos7Dias->push([
                    'fecha' => $fecha->format('Y-m-d'),
                    'total' => Notificacion::whereDate('fecha_envio', $fecha)->count(),
                ]);
            }

            $notificacionesPorAdmin = Notificacion::with('creadoPor')
                ->select('creado_por_id', DB::raw('count(*) as total'))
                ->whereNotNull('creado_por_id')
                ->groupBy('creado_por_id')
                ->orderByDesc('total')
                ->limit(5)
                ->get()
                ->map(function($item) {
                    return [
                        'admin' => $item->creadoPor?->nombre ?? 'Desconocido',
                        'total' => $item->total,
                    ];
                });

            $notificacionesPorUsuario = Notificacion::with('usuario')
                ->select('user_id', DB::raw('count(*) as total'))
                ->groupBy('user_id')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(function($item) {
                    return [
                        'usuario' => $item->usuario?->nombre ?? 'Usuario eliminado',
                        'total' => $item->total,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'totales' => [
                        'total' => Notificacion::count(),
                        'hoy' => Notificacion::whereDate('fecha_envio', today())->count(),
                        'esta_semana' => Notificacion::where('fecha_envio', '>=', now()->startOfWeek())->count(),
                        'este_mes' => Notificacion::whereMonth('fecha_envio', now()->month)->count(),
                    ],
                    'ultimos_7_dias' => $ultimos7Dias,
                    'top_administradores' => $notificacionesPorAdmin,
                    'top_usuarios_notificados' => $notificacionesPorUsuario,
                ],
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
}
