<?php

namespace App\Services\Public;

use App\Models\Mascota;

class MascotaPublicService
{
    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Mascota::with(['fundacion', 'razas'])
            ->where('estado', 'En adopcion');

        if (!empty($filters['especie'])) {
            $query->where('especie', $filters['especie']);
        }

        if (!empty($filters['fundacion_id'])) {
            $query->where('fundacion_id', $filters['fundacion_id']);
        }

        if (!empty($filters['genero'])) {
            $query->where('genero', $filters['genero']);
        }

        if (!empty($filters['tamano'])) {
            $query->where('tamano', $filters['tamano']);
        }

        if (!empty($filters['destacada'])) {
            $query->where('destacada', true);
        }

        if (!empty($filters['buscar'])) {
            $query->where(function($q) use ($filters) {
                $q->where('nombre_mascota', 'like', '%' . $filters['buscar'] . '%')
                  ->orWhere('descripcion', 'like', '%' . $filters['buscar'] . '%');
            });
        }

        if (isset($filters['apto_con_ninos'])) {
            $query->where('apto_con_ninos', $filters['apto_con_ninos']);
        }

        if (isset($filters['apto_con_otros_animales'])) {
            $query->where('apto_con_otros_animales', $filters['apto_con_otros_animales']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById(int $id): Mascota
    {
        return Mascota::with(['fundacion', 'razas', 'vacunas', 'historialMedico', 'suscripciones'])
            ->findOrFail($id);
    }

    public function getPorEspecie(string $especie, int $perPage = 15)
    {
        return Mascota::with('fundacion')
            ->where('especie', $especie)
            ->where('estado', 'En adopcion')
            ->paginate($perPage);
    }

    public function getPorFundacion(int $fundacionId, int $perPage = 15)
    {
        return Mascota::with('fundacion')
            ->where('fundacion_id', $fundacionId)
            ->where('estado', 'En adopcion')
            ->paginate($perPage);
    }

    public function getDestacadas(int $limit = 6)
    {
        return Mascota::with(['fundacion', 'razas'])
            ->where('estado', 'En adopcion')
            ->where('destacada', true)
            ->latest()
            ->limit($limit)
            ->get();
    }
}
