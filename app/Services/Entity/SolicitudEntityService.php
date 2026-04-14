<?php

namespace App\Services\Entity;

use App\Models\Solicitud;
use App\Models\Adopcion;
use App\Models\Notificacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SolicitudEntityService
{
    public function getEntidad()
    {
        $user = Auth::user();

        if ($user->tipo === 'veterinaria') {
            return $user->veterinaria;
        }
        if ($user->tipo === 'fundacion') {
            return $user->fundacion;
        }
        return null;
    }

    public function getSolicitudes(array $filters = [])
    {
        $user = Auth::user();
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $query = Solicitud::with(['user', 'solicitable'])
            ->where('tipo_solicitud', 'adopcion');

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

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function findSolicitud(int $id)
    {
        $user = Auth::user();
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $solicitud = Solicitud::with(['user', 'solicitable'])->findOrFail($id);

        $mascota = $solicitud->solicitable;
        if ($user->tipo === 'fundacion' && $mascota->fundacion_id !== $entidad->id) {
            throw new \Exception('No autorizado');
        }

        return $solicitud;
    }

    public function aprobarSolicitud(int $id)
    {
        $user = Auth::user();
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $solicitud = Solicitud::with(['solicitable', 'user'])
            ->where('estado', 'pendiente')
            ->findOrFail($id);

        $mascota = $solicitud->solicitable;

        if ($user->tipo === 'fundacion' && $mascota->fundacion_id !== $entidad->id) {
            throw new \Exception('No autorizado');
        }

        if ($mascota->estado !== 'En adopcion') {
            throw new \Exception('La mascota ya no está disponible para adopción');
        }

        $solicitud->update([
            'estado' => 'aprobada',
            'revisado_por' => $user->id,
            'fecha_revision' => now(),
        ]);

        $adopcion = Adopcion::create([
            'solicitud_id' => $solicitud->id,
            'user_id' => $solicitud->user_id,
            'mascota_id' => $mascota->id,
            'fundacion_id' => $user->tipo === 'fundacion' ? $entidad->id : null,
            'administrador_id' => $user->id,
            'estado' => 'en_proceso',
            'fecha_adopcion' => now(),
        ]);

        $mascota->update(['estado' => 'Adoptado']);

        if ($solicitud->user_id) {
            Notificacion::create([
                'user_id' => $solicitud->user_id,
                'contenido' => "¡Felicidades! Tu solicitud de adopción para {$mascota->nombre_mascota} ha sido APROBADA. Un coordinador se pondrá en contacto contigo.",
                'creado_por_id' => $user->id,
            ]);
        }

        return [
            'solicitud' => $solicitud,
            'adopcion' => $adopcion
        ];
    }

    public function rechazarSolicitud(int $id, string $razonRechazo)
    {
        $user = Auth::user();
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $solicitud = Solicitud::with(['solicitable', 'user'])
            ->where('estado', 'pendiente')
            ->findOrFail($id);

        $mascota = $solicitud->solicitable;

        if ($user->tipo === 'fundacion' && $mascota->fundacion_id !== $entidad->id) {
            throw new \Exception('No autorizado');
        }

        $solicitud->update([
            'estado' => 'rechazada',
            'razon_rechazo' => $razonRechazo,
            'revisado_por' => $user->id,
            'fecha_revision' => now(),
        ]);

        if ($solicitud->user_id) {
            Notificacion::create([
                'user_id' => $solicitud->user_id,
                'contenido' => "Tu solicitud de adopción para {$mascota->nombre_mascota} ha sido RECHAZADA. Motivo: {$razonRechazo}",
                'creado_por_id' => $user->id,
            ]);
        }

        return $solicitud;
    }

    public function getEstadisticas()
    {
        $user = Auth::user();
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
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

        return [
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
    }
}
