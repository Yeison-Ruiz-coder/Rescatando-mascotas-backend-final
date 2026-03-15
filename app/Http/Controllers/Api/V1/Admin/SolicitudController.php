<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use App\Models\Mascota;
use App\Models\Adopcion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SolicitudController extends Controller
{
    /**
     * Listado de solicitudes con filtros
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'estado' => 'nullable|in:pendiente,en_revision,aprobada,rechazada,completada',
            'tipo_solicitud' => 'nullable|in:adopcion,rescate,apadrinamiento,donacion,otro',
            'per_page' => 'nullable|integer|min:1|max:100',
            'buscar' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = Solicitud::with(['usuario', 'revisor', 'solicitable'])
                ->orderBy('fecha_solicitud', 'desc');

            // Filtros
            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->filled('tipo_solicitud')) {
                $query->where('tipo_solicitud', $request->tipo_solicitud);
            }

            if ($request->filled('buscar')) {
                $buscar = $request->buscar;
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre_solicitante', 'like', "%{$buscar}%")
                      ->orWhere('email_solicitante', 'like', "%{$buscar}%")
                      ->orWhere('telefono_solicitante', 'like', "%{$buscar}%")
                      ->orWhereHas('usuario', function ($userQuery) use ($buscar) {
                          $userQuery->where('nombre', 'like', "%{$buscar}%")
                                   ->orWhere('apellidos', 'like', "%{$buscar}%")
                                   ->orWhere('email', 'like', "%{$buscar}%");
                      });
                });
            }

            $perPage = $request->get('per_page', 15);
            $solicitudes = $query->paginate($perPage);

            // Estadísticas
            $estadisticas = [
                'pendientes' => Solicitud::where('estado', 'pendiente')->count(),
                'en_revision' => Solicitud::where('estado', 'en_revision')->count(),
                'aprobadas' => Solicitud::where('estado', 'aprobada')->count(),
                'rechazadas' => Solicitud::where('estado', 'rechazada')->count(),
                'total' => Solicitud::count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $solicitudes,
                'estadisticas' => $estadisticas,
                'message' => 'Solicitudes obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener solicitudes: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las solicitudes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de solicitud
     */
    public function show($id)
    {
        try {
            $solicitud = Solicitud::with(['usuario', 'revisor', 'solicitable'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $solicitud,
                'message' => 'Solicitud obtenida exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Crear nueva solicitud (admin)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_solicitud' => 'required|in:adopcion,rescate,apadrinamiento,donacion,otro',
            'contenido' => 'required|string|min:10',
            'user_id' => 'nullable|exists:users,id',
            'nombre_solicitante' => 'required_without:user_id|string|max:255',
            'email_solicitante' => 'required_without:user_id|email|max:255',
            'telefono_solicitante' => 'nullable|string|max:20',
            'solicitable_id' => 'required|integer',
            'solicitable_type' => 'required|string|in:App\Models\Mascota,App\Models\Fundacion,App\Models\Producto',
            'datos_adopcion' => 'nullable|array',
            'datos_adopcion.apellido_solicitante' => 'nullable|string|max:255',
            'datos_adopcion.experiencia_mascotas' => 'nullable|string',
            'datos_adopcion.tipo_vivienda' => 'nullable|string',
            'datos_adopcion.motivo_adopcion' => 'nullable|string',
            'datos_adopcion.direccion' => 'nullable|string',
            'datos_adopcion.compromiso_cuidado' => 'nullable|boolean',
            'datos_adopcion.compromiso_esterilizacion' => 'nullable|boolean',
            'datos_adopcion.compromiso_seguimiento' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $solicitud = Solicitud::create([
                'tipo_solicitud' => $request->tipo_solicitud,
                'contenido' => $request->contenido,
                'fecha_solicitud' => now(),
                'estado' => 'pendiente',
                'user_id' => $request->user_id,
                'nombre_solicitante' => $request->nombre_solicitante,
                'email_solicitante' => $request->email_solicitante,
                'telefono_solicitante' => $request->telefono_solicitante,
                'solicitable_id' => $request->solicitable_id,
                'solicitable_type' => $request->solicitable_type,
            ]);

            // Guardar datos adicionales para adopción
            if ($request->tipo_solicitud === 'adopcion' && $request->has('datos_adopcion')) {
                $solicitud->setDatosAdopcion($request->datos_adopcion)->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $solicitud->load(['usuario', 'solicitable']),
                'message' => 'Solicitud creada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear solicitud: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar solicitud
     */
    public function update(Request $request, $id)
    {
        try {
            $solicitud = Solicitud::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'tipo_solicitud' => 'sometimes|required|in:adopcion,rescate,apadrinamiento,donacion,otro',
            'contenido' => 'sometimes|required|string|min:10',
            'estado' => 'sometimes|required|in:pendiente,en_revision,aprobada,rechazada,completada',
            'notas_internas' => 'nullable|string',
            'razon_rechazo' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $updateData = $request->only([
                'tipo_solicitud', 'contenido', 'estado', 'notas_internas', 'razon_rechazo'
            ]);

            // Si cambia estado a rechazado, registrar fecha
            if ($request->has('estado') && $request->estado === 'rechazada') {
                $updateData['fecha_revision'] = now();
                $updateData['revisado_por'] = auth()->id();
            }

            $solicitud->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $solicitud->fresh(['usuario', 'revisor', 'solicitable']),
                'message' => 'Solicitud actualizada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar solicitud: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de solicitud
     */
    public function cambiarEstado(Request $request, $id)
    {
        try {
            $solicitud = Solicitud::with('solicitable')->findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'estado' => 'required|in:pendiente,en_revision,aprobada,rechazada,completada',
            'razon_rechazo' => 'required_if:estado,rechazada|nullable|string|max:500',
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
            // Actualizar estado
            $solicitud->estado = $request->estado;
            $solicitud->revisado_por = auth()->id();
            $solicitud->fecha_revision = now();

            if ($request->estado === 'rechazada') {
                $solicitud->razon_rechazo = $request->razon_rechazo;
            }

            $solicitud->save();

            // Si se aprueba una solicitud de adopción, crear registro de adopción
            if ($request->estado === 'aprobada' &&
                $solicitud->tipo_solicitud === 'adopcion' &&
                $solicitud->solicitable_type === 'App\Models\Mascota') {

                Adopcion::create([
                    'solicitud_id' => $solicitud->id,
                    'user_id' => $solicitud->user_id,
                    'mascota_id' => $solicitud->solicitable_id,
                    'fundacion_id' => $solicitud->solicitable->fundacion_id ?? null,
                    'administrador_id' => auth()->id(),
                    'estado' => 'en_proceso',
                    'fecha_adopcion' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $solicitud->fresh(['usuario', 'revisor', 'solicitable']),
                'message' => 'Estado actualizado correctamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar estado: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar solicitud
     */
    public function destroy($id)
    {
        try {
            $solicitud = Solicitud::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada'
            ], 404);
        }

        try {
            $solicitud->delete();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud eliminada correctamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al eliminar solicitud: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de solicitudes
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'por_estado' => [
                    'pendiente' => Solicitud::where('estado', 'pendiente')->count(),
                    'en_revision' => Solicitud::where('estado', 'en_revision')->count(),
                    'aprobada' => Solicitud::where('estado', 'aprobada')->count(),
                    'rechazada' => Solicitud::where('estado', 'rechazada')->count(),
                    'completada' => Solicitud::where('estado', 'completada')->count(),
                ],
                'por_tipo' => [
                    'adopcion' => Solicitud::where('tipo_solicitud', 'adopcion')->count(),
                    'rescate' => Solicitud::where('tipo_solicitud', 'rescate')->count(),
                    'apadrinamiento' => Solicitud::where('tipo_solicitud', 'apadrinamiento')->count(),
                    'donacion' => Solicitud::where('tipo_solicitud', 'donacion')->count(),
                    'otro' => Solicitud::where('tipo_solicitud', 'otro')->count(),
                ],
                'ultimas_30_dias' => Solicitud::where('fecha_solicitud', '>=', now()->subDays(30))->count(),
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
        $solicitudesResueltas = Solicitud::whereNotNull('fecha_revision')
            ->whereIn('estado', ['aprobada', 'rechazada', 'completada'])
            ->get();

        if ($solicitudesResueltas->isEmpty()) {
            return 0;
        }

        $totalDias = $solicitudesResueltas->sum(function ($solicitud) {
            return $solicitud->fecha_solicitud->diffInDays($solicitud->fecha_revision);
        });

        return round($totalDias / $solicitudesResueltas->count(), 1);
    }
}
