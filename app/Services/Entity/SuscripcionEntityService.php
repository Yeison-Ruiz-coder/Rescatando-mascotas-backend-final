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

    /**
     * ✅ NUEVO: Validar fechas de suscripción
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

    /**
     * ✅ CORREGIDO: Ahora valida fechas
     */
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

        // ✅ Validar fechas
        $this->validarFechas($data);

        // Si no se especifica fecha_inicio, usar hoy
        if (!isset($data['fecha_inicio'])) {
            $data['fecha_inicio'] = now()->toDateString();
        }

        return Suscripcion::create($data);
    }

    /**
     * ✅ CORREGIDO: Ahora valida fechas en update
     */
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

        // ✅ Validar fechas si vienen en la actualización
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

    /**
     * ✅ NUEVO: Cancelar suscripción
     */
    public function cancelarSuscripcion(int $id): Suscripcion
    {
        $suscripcion = $this->findSuscripcion($id);

        if ($suscripcion->estado === 'cancelado') {
            throw new \Exception('La suscripción ya está cancelada');
        }

        $suscripcion->update(['estado' => 'cancelado']);
        return $suscripcion;
    }

    /**
     * ✅ NUEVO: Pausar suscripción
     */
    public function pausarSuscripcion(int $id): Suscripcion
    {
        $suscripcion = $this->findSuscripcion($id);

        if ($suscripcion->estado !== 'activo') {
            throw new \Exception('Solo se pueden pausar suscripciones activas');
        }

        $suscripcion->update(['estado' => 'pausado']);
        return $suscripcion;
    }

    /**
     * ✅ NUEVO: Reactivar suscripción
     */
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
