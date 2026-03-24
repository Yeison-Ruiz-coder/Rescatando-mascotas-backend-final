<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use App\Models\Adopcion;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SolicitudController extends Controller
{
    /**
     * Listar solicitudes de adopción para la entidad
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $entidad = $this->getEntidad($user);

        if (!$entidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la entidad asociada'
            ], 404);
        }

        $query = Solicitud::with(['user', 'solicitable'])
            ->where('tipo_solicitud', 'adopcion');

        // Filtrar por mascotas de la entidad
        if ($user->tipo === 'fundacion') {
            $query->whereHas('solicitable', function($q) use ($entidad) {
                $q->where('fundacion_id', $entidad->id);
            });
        } else {
            // Para veterinaria, buscar solicitudes relacionadas con mascotas que atendieron
            $query->whereHas('solicitable', function($q) use ($entidad) {
                $q->whereHas('historialMedico', function($h) use ($entidad) {
                    $h->where('veterinaria_id', $entidad->id);
                });
            });
        }

        // Filtros
        if ($request->estado) {
            $query->where('estado', $request->estado);
        }

        $solicitudes = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $solicitudes
        ]);
    }

    /**
     * Ver detalle de una solicitud
     */
    public function show($id)
    {
        $user = auth()->user();
        $entidad = $this->getEntidad($user);

        if (!$entidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la entidad asociada'
            ], 404);
        }

        $solicitud = Solicitud::with(['user', 'solicitable'])
            ->findOrFail($id);

        // Verificar que la mascota pertenece a la entidad
        $mascota = $solicitud->solicitable;
        if ($user->tipo === 'fundacion' && $mascota->fundacion_id !== $entidad->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $solicitud
        ]);
    }

    /**
     * Aprobar una solicitud de adopción
     */
    public function aprobar($id)
    {
        $user = auth()->user();
        $entidad = $this->getEntidad($user);

        if (!$entidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la entidad asociada'
            ], 404);
        }

        $solicitud = Solicitud::with(['solicitable', 'user'])
            ->where('estado', 'pendiente')
            ->findOrFail($id);

        $mascota = $solicitud->solicitable;

        // Verificar propiedad
        if ($user->tipo === 'fundacion' && $mascota->fundacion_id !== $entidad->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        // Verificar que la mascota sigue disponible
        if ($mascota->estado !== 'En adopcion') {
            return response()->json([
                'success' => false,
                'message' => 'La mascota ya no está disponible para adopción'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Actualizar solicitud
            $solicitud->update([
                'estado' => 'aprobada',
                'revisado_por' => $user->id,
                'fecha_revision' => now(),
            ]);

            // Crear registro de adopción
            $adopcion = Adopcion::create([
                'solicitud_id' => $solicitud->id,
                'user_id' => $solicitud->user_id,
                'mascota_id' => $mascota->id,
                'fundacion_id' => $user->tipo === 'fundacion' ? $entidad->id : null,
                'administrador_id' => $user->id,
                'estado' => 'en_proceso',
                'fecha_adopcion' => now(),
            ]);

            // Cambiar estado de la mascota
            $mascota->update(['estado' => 'Adoptado']);

            // Notificar al solicitante
            if ($solicitud->user_id) {
                Notificacion::create([
                    'user_id' => $solicitud->user_id,
                    'contenido' => "¡Felicidades! Tu solicitud de adopción para {$mascota->nombre_mascota} ha sido APROBADA. Un coordinador se pondrá en contacto contigo.",
                    'creado_por_id' => $user->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud aprobada exitosamente',
                'data' => [
                    'solicitud' => $solicitud,
                    'adopcion' => $adopcion
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechazar una solicitud de adopción
     */
    public function rechazar(Request $request, $id)
    {
        $user = auth()->user();
        $entidad = $this->getEntidad($user);

        if (!$entidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la entidad asociada'
            ], 404);
        }

        $validated = $request->validate([
            'razon_rechazo' => 'required|string|min:10'
        ]);

        $solicitud = Solicitud::with(['solicitable', 'user'])
            ->where('estado', 'pendiente')
            ->findOrFail($id);

        $mascota = $solicitud->solicitable;

        // Verificar propiedad
        if ($user->tipo === 'fundacion' && $mascota->fundacion_id !== $entidad->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $solicitud->update([
                'estado' => 'rechazada',
                'razon_rechazo' => $validated['razon_rechazo'],
                'revisado_por' => $user->id,
                'fecha_revision' => now(),
            ]);

            // Notificar al solicitante
            if ($solicitud->user_id) {
                Notificacion::create([
                    'user_id' => $solicitud->user_id,
                    'contenido' => "Tu solicitud de adopción para {$mascota->nombre_mascota} ha sido RECHAZADA. Motivo: {$validated['razon_rechazo']}",
                    'creado_por_id' => $user->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud rechazada',
                'data' => $solicitud
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de solicitudes para la entidad
     */
    public function estadisticas()
    {
        $user = auth()->user();
        $entidad = $this->getEntidad($user);

        if (!$entidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la entidad asociada'
            ], 404);
        }

        $query = Solicitud::where('tipo_solicitud', 'adopcion');

        if ($user->tipo === 'fundacion') {
            $query->whereHas('solicitable', function($q) use ($entidad) {
                $q->where('fundacion_id', $entidad->id);
            });
        } else {
            $query->whereHas('solicitable', function($q) use ($entidad) {
                $q->whereHas('historialMedico', function($h) use ($entidad) {
                    $h->where('veterinaria_id', $entidad->id);
                });
            });
        }

        $stats = [
            'pendientes' => (clone $query)->where('estado', 'pendiente')->count(),
            'aprobadas' => (clone $query)->where('estado', 'aprobada')->count(),
            'rechazadas' => (clone $query)->where('estado', 'rechazada')->count(),
            'completadas' => (clone $query)->where('estado', 'completada')->count(),
            'total' => (clone $query)->count(),
            'ultimas_solicitudes' => (clone $query)
                ->with(['user', 'solicitable'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Obtener la entidad asociada al usuario
     */
    private function getEntidad($user)
    {
        if ($user->tipo === 'veterinaria') {
            return $user->veterinaria;
        }
        if ($user->tipo === 'fundacion') {
            return $user->fundacion;
        }
        return null;
    }
}
