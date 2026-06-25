<?php

namespace App\Services\Entity;

use App\Models\Suscripcion;
use App\Models\Mascota;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SuscripcionEntityService
{
    public function getEntidad()
    {
        $user = Auth::user();

        if ($user->tipo === 'fundacion') {
            return $user->fundacion;
        }
        if ($user->tipo === 'veterinaria') {
            return $user->veterinaria;
        }
        return null;
    }

    /**
     * Obtener IDs de mascotas de la entidad
     */
    private function getMascotasIds()
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('Perfil de entidad no encontrado');
        }

        $user = Auth::user();

        $query = Mascota::query();

        if ($user->tipo === 'fundacion') {
            $query->where('fundacion_id', $entidad->id);
        } elseif ($user->tipo === 'veterinaria') {
            $query->where('veterinaria_id', $entidad->id);
        } else {
            throw new \Exception('Tipo de usuario no válido para suscripciones');
        }

        return $query->pluck('id');
    }

    public function getMisSuscripciones()
    {
        $mascotasIds = $this->getMascotasIds();

        return Suscripcion::with(['user', 'mascota'])
            ->whereIn('mascota_id', $mascotasIds)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findSuscripcion(int $id)
    {
        $mascotasIds = $this->getMascotasIds();

        $suscripcion = Suscripcion::with(['user', 'mascota'])
            ->whereIn('mascota_id', $mascotasIds)
            ->where('id', $id)
            ->first();

        if (!$suscripcion) {
            throw new ModelNotFoundException('Suscripción no encontrada');
        }

        return $suscripcion;
    }

    /**
     * Validar fechas de suscripción
     */
    private function validarFechas(array $data): void
    {
        if (isset($data['fecha_inicio']) && isset($data['fecha_fin']) &&
            $data['fecha_fin'] && $data['fecha_inicio'] > $data['fecha_fin']) {
            throw new \Exception('La fecha de inicio no puede ser posterior a la fecha de fin');
        }

        if (isset($data['fecha_inicio']) && $data['fecha_inicio'] < now()->startOfDay()) {
            throw new \Exception('La fecha de inicio no puede ser anterior a hoy');
        }
    }

    public function createSuscripcion(array $data)
    {
        $mascotasIds = $this->getMascotasIds();

        // Verificar que la mascota pertenece a la entidad
        if (!in_array($data['mascota_id'], $mascotasIds->toArray())) {
            throw new \Exception('La mascota no pertenece a tu entidad');
        }

        $this->validarFechas($data);

        if (!isset($data['fecha_inicio'])) {
            $data['fecha_inicio'] = now()->toDateString();
        }

        return Suscripcion::create($data);
    }

    public function updateSuscripcion(int $id, array $data)
    {
        $suscripcion = $this->findSuscripcion($id);

        // Si cambia la mascota, verificar que la nueva pertenece a la entidad
        if (isset($data['mascota_id']) && $data['mascota_id'] != $suscripcion->mascota_id) {
            $mascotasIds = $this->getMascotasIds();

            if (!in_array($data['mascota_id'], $mascotasIds->toArray())) {
                throw new \Exception('La mascota no pertenece a tu entidad');
            }
        }

        $this->validarFechas($data);

        $suscripcion->update($data);
        return $suscripcion->load(['user', 'mascota']);
    }

    public function deleteSuscripcion(int $id)
    {
        $suscripcion = $this->findSuscripcion($id);
        $suscripcion->delete();
    }

    public function getSuscripcionesPorMascota(int $mascotaId)
    {
        $mascotasIds = $this->getMascotasIds();

        if (!in_array($mascotaId, $mascotasIds->toArray())) {
            throw new \Exception('Mascota no encontrada o no pertenece a tu entidad');
        }

        return Suscripcion::with(['user'])
            ->where('mascota_id', $mascotaId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getEstadisticas()
    {
        $mascotasIds = $this->getMascotasIds();

        $totalSuscripciones = Suscripcion::whereIn('mascota_id', $mascotasIds)->count();
        $activas = Suscripcion::whereIn('mascota_id', $mascotasIds)->where('estado', 'activo')->count();
        $pausadas = Suscripcion::whereIn('mascota_id', $mascotasIds)->where('estado', 'pausado')->count();
        $canceladas = Suscripcion::whereIn('mascota_id', $mascotasIds)->where('estado', 'cancelado')->count();
        $finalizadas = Suscripcion::whereIn('mascota_id', $mascotasIds)->where('estado', 'finalizado')->count();

        $totalMensual = Suscripcion::whereIn('mascota_id', $mascotasIds)
            ->where('estado', 'activo')
            ->sum('monto_mensual');

        return [
            'total_suscripciones' => $totalSuscripciones,
            'activas' => $activas,
            'pausadas' => $pausadas,
            'canceladas' => $canceladas,
            'finalizadas' => $finalizadas,
            'ingreso_mensual_total' => $totalMensual
        ];
    }

    public function cancelarSuscripcion(int $id): Suscripcion
    {
        $suscripcion = $this->findSuscripcion($id);

        if ($suscripcion->estado === 'cancelado') {
            throw new \Exception('La suscripción ya está cancelada');
        }

        $suscripcion->update(['estado' => 'cancelado']);
        return $suscripcion;
    }

    public function pausarSuscripcion(int $id): Suscripcion
    {
        $suscripcion = $this->findSuscripcion($id);

        if ($suscripcion->estado !== 'activo') {
            throw new \Exception('Solo se pueden pausar suscripciones activas');
        }

        $suscripcion->update(['estado' => 'pausado']);
        return $suscripcion;
    }

    public function reactivarSuscripcion(int $id): Suscripcion
    {
        $suscripcion = $this->findSuscripcion($id);

        if ($suscripcion->estado !== 'pausado') {
            throw new \Exception('Solo se pueden reactivar suscripciones pausadas');
        }

        $suscripcion->update(['estado' => 'activo']);
        return $suscripcion;
    }
}
