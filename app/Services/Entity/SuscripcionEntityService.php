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
        return null;
    }

    public function getMisSuscripciones()
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('Perfil de fundación no encontrado');
        }

        $mascotasIds = Mascota::where('fundacion_id', $entidad->id)->pluck('id');

        return Suscripcion::with(['user', 'mascota'])
            ->whereIn('mascota_id', $mascotasIds)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findSuscripcion(int $id)
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('Perfil de fundación no encontrado');
        }

        $mascotasIds = Mascota::where('fundacion_id', $entidad->id)->pluck('id');

        $suscripcion = Suscripcion::with(['user', 'mascota'])
            ->whereIn('mascota_id', $mascotasIds)
            ->where('id', $id)
            ->first();

        if (!$suscripcion) {
            throw new ModelNotFoundException('Suscripción no encontrada');
        }

        return $suscripcion;
    }

    public function createSuscripcion(array $data)
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('Perfil de fundación no encontrado');
        }

        // Verificar que la mascota pertenece a la fundación
        $mascota = Mascota::where('id', $data['mascota_id'])
            ->where('fundacion_id', $entidad->id)
            ->first();

        if (!$mascota) {
            throw new \Exception('La mascota no pertenece a tu fundación');
        }

        return Suscripcion::create($data);
    }

    public function updateSuscripcion(int $id, array $data)
    {
        $suscripcion = $this->findSuscripcion($id);

        // Si cambia la mascota, verificar que la nueva pertenece a la fundación
        if (isset($data['mascota_id']) && $data['mascota_id'] != $suscripcion->mascota_id) {
            $entidad = $this->getEntidad();
            $nuevaMascota = Mascota::where('id', $data['mascota_id'])
                ->where('fundacion_id', $entidad->id)
                ->first();

            if (!$nuevaMascota) {
                throw new \Exception('La mascota no pertenece a tu fundación');
            }
        }

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
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('Perfil de fundación no encontrado');
        }

        // Verificar que la mascota pertenece a la fundación
        $mascota = Mascota::where('id', $mascotaId)
            ->where('fundacion_id', $entidad->id)
            ->first();

        if (!$mascota) {
            throw new \Exception('Mascota no encontrada o no pertenece a tu fundación');
        }

        return Suscripcion::with(['user'])
            ->where('mascota_id', $mascotaId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getEstadisticas()
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('Perfil de fundación no encontrado');
        }

        $mascotasIds = Mascota::where('fundacion_id', $entidad->id)->pluck('id');

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
}
