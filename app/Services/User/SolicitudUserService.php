<?php

namespace App\Services\User;

use App\Models\Solicitud;
use App\Models\Mascota;
use App\Models\User;
use App\Notifications\Adopcion\NuevaSolicitudAdopcion;
use App\Notifications\Adopcion\NuevaSolicitudAdopcionAdmin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SolicitudUserService
{
    public function getByUser(int $userId)
    {
        return Solicitud::with(['solicitable'])
            ->where('user_id', $userId)
            ->where('tipo_solicitud', 'adopcion')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById(int $userId, int $solicitudId): Solicitud
    {
        return Solicitud::with(['solicitable'])
            ->where('user_id', $userId)
            ->findOrFail($solicitudId);
    }

    public function getSolicitudesRecibidas(int $userId)
    {
        $user = User::with(['fundacion', 'veterinaria'])->find($userId);

        $mascotaIds = collect();

        if ($user->fundacion) {
            $mascotaIds = $mascotaIds->merge(
                Mascota::where('fundacion_id', $user->fundacion->id)->pluck('id')
            );
        }

        if ($user->veterinaria) {
            $mascotaIds = $mascotaIds->merge(
                Mascota::where('veterinaria_id', $user->veterinaria->id)->pluck('id')
            );
        }

        if ($mascotaIds->isEmpty()) {
            return collect();
        }

        return Solicitud::with(['usuario', 'solicitable'])
            ->where('tipo_solicitud', 'adopcion')
            ->where('solicitable_type', Mascota::class)
            ->whereIn('solicitable_id', $mascotaIds)
            ->where('estado', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getSolicitudesRecibidasAll(int $userId, array $filters = [])
    {
        $user = User::with(['fundacion', 'veterinaria'])->find($userId);

        $mascotaIds = collect();

        if ($user->fundacion) {
            $mascotaIds = $mascotaIds->merge(
                Mascota::where('fundacion_id', $user->fundacion->id)->pluck('id')
            );
        }

        if ($user->veterinaria) {
            $mascotaIds = $mascotaIds->merge(
                Mascota::where('veterinaria_id', $user->veterinaria->id)->pluck('id')
            );
        }

        if ($mascotaIds->isEmpty()) {
            return collect();
        }

        $query = Solicitud::with(['usuario', 'solicitable'])
            ->where('tipo_solicitud', 'adopcion')
            ->where('solicitable_type', Mascota::class)
            ->whereIn('solicitable_id', $mascotaIds);

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['fecha_desde'])) {
            $query->whereDate('created_at', '>=', $filters['fecha_desde']);
        }
        if (!empty($filters['fecha_hasta'])) {
            $query->whereDate('created_at', '<=', $filters['fecha_hasta']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 15);
    }

    public function createSolicitudAdopcion(int $userId, array $data): Solicitud
    {
        $mascota = Mascota::with(['fundacion', 'veterinaria'])->findOrFail($data['mascota_id']);

        if ($mascota->estado !== 'En adopcion') {
            throw new \Exception('Esta mascota ya no está disponible para adopción');
        }

        $datosAdicionales = [
            'apellido_solicitante' => $data['apellido'],
            'documento_identidad' => $data['documento_identidad'],
            'direccion' => $data['direccion'],
            'ciudad' => $data['ciudad'] ?? null,
            'departamento' => $data['departamento'] ?? null,
            'codigo_postal' => $data['codigo_postal'] ?? null,
            'estado_civil' => $data['estado_civil'] ?? null,
            'cantidad_hijos' => $data['cantidad_hijos'] ?? null,
            'ocupacion' => $data['ocupacion'] ?? null,
            'experiencia_mascotas' => $data['experiencia_mascotas'],
            'tipo_vivienda' => $data['tipo_vivienda'],
            'es_propietario' => $data['es_propietario'] ?? null,
            'compromiso_cuidado' => $data['compromiso_cuidado'],
            'compromiso_esterilizacion' => $data['compromiso_esterilizacion'],
            'compromiso_seguimiento' => $data['compromiso_seguimiento'],
        ];

        $solicitud = Solicitud::create([
            'tipo_solicitud' => 'adopcion',
            'contenido' => $data['motivo_adopcion'],
            'estado' => 'pendiente',
            'user_id' => $userId,
            'nombre_solicitante' => $data['nombre'],
            'email_solicitante' => $data['email'],
            'telefono_solicitante' => $data['telefono'],
            'solicitable_type' => Mascota::class,
            'solicitable_id' => $mascota->id,
            'datos_adicionales' => $datosAdicionales,
        ]);

        Log::info('Solicitud creada exitosamente', [
            'id' => $solicitud->id,
            'nombre_solicitante' => $solicitud->nombre_solicitante,
            'email_solicitante' => $solicitud->email_solicitante,
        ]);

        // ✅ ENVIAR NOTIFICACIONES (SOLO BASE DE DATOS)
        $this->enviarNotificaciones($solicitud, $mascota, $userId);

        return $solicitud;
    }

    // ✅ ENVIAR NOTIFICACIONES - SOLO BASE DE DATOS
    protected function enviarNotificaciones(Solicitud $solicitud, Mascota $mascota, int $solicitanteId)
    {
        $solicitante = User::find($solicitanteId);

        // 1. Obtener al dueño de la mascota (Fundación o Veterinaria)
        $duenoUsuario = null;

        if ($mascota->fundacion_id) {
            $entidad = $mascota->fundacion;
            $duenoUsuario = $entidad?->usuarioPrincipal;
        } elseif ($mascota->veterinaria_id) {
            $entidad = $mascota->veterinaria;
            $duenoUsuario = $entidad?->usuarioPrincipal;
        }

        // 2. Notificar al dueño de la mascota (solo base de datos)
        if ($duenoUsuario) {
            try {
                $duenoUsuario->notify(new NuevaSolicitudAdopcion($solicitud, $mascota, $solicitante));
                Log::info("Notificación enviada al dueño", [
                    'dueno_id' => $duenoUsuario->id,
                    'solicitud_id' => $solicitud->id
                ]);
            } catch (\Exception $e) {
                Log::error("Error al enviar notificación al dueño: " . $e->getMessage());
            }
        }

        // 3. Notificar a los Administradores (solo base de datos)
        $this->notificarAdministradores($solicitud, $mascota, $solicitante);
    }

    // ✅ Notificar a administradores - SOLO BASE DE DATOS
    protected function notificarAdministradores(Solicitud $solicitud, Mascota $mascota, User $solicitante)
    {
        $administradores = User::where('tipo', 'admin')->get();

        foreach ($administradores as $admin) {
            try {
                $admin->notify(new NuevaSolicitudAdopcionAdmin(
                    $solicitud,
                    $mascota,
                    $solicitante
                ));
            } catch (\Exception $e) {
                Log::error("Error al notificar a admin {$admin->id}: " . $e->getMessage());
            }
        }
    }
}
