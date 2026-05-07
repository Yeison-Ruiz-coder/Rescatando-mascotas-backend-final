<?php

namespace App\Services;

use App\Models\Suscripcion;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SuscripcionService
{
    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Suscripcion::with(['user', 'mascota']);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['mascota_id'])) {
            $query->where('mascota_id', $filters['mascota_id']);
        }

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById(int $id): Suscripcion
    {
        return Suscripcion::with(['user', 'mascota'])->findOrFail($id);
    }

    public function create(array $data): Suscripcion
    {
        return Suscripcion::create($data);
    }

    public function update(int $id, array $data): Suscripcion
    {
        $suscripcion = Suscripcion::findOrFail($id);
        $suscripcion->update($data);
        return $suscripcion;
    }

    public function delete(int $id): void
    {
        $suscripcion = Suscripcion::findOrFail($id);
        $suscripcion->delete();
    }

    public function cancelar(int $id): Suscripcion
    {
        $suscripcion = Suscripcion::findOrFail($id);
        $suscripcion->update(['estado' => 'cancelado']);
        return $suscripcion;
    }

    public function pausar(int $id): Suscripcion
    {
        $suscripcion = Suscripcion::findOrFail($id);
        $suscripcion->update(['estado' => 'pausado']);
        return $suscripcion;
    }

    public function reactivar(int $id): Suscripcion
    {
        $suscripcion = Suscripcion::findOrFail($id);
        $suscripcion->update(['estado' => 'activo']);
        return $suscripcion;
    }
}
