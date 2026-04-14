<?php

namespace App\Services\Public;

use App\Models\Fundacion;

class FundacionPublicService
{
    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Fundacion::withCount('mascotas');

        if (!empty($filters['recibe_voluntarios'])) {
            $query->where('recibe_voluntarios', true);
        }

        if (!empty($filters['buscar'])) {
            $query->where('Nombre_1', 'like', '%' . $filters['buscar'] . '%');
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): array
    {
        $fundacion = Fundacion::with('mascotas')
            ->withCount('mascotas')
            ->findOrFail($id);

        $necesidades = json_decode($fundacion->necesidades_actuales, true) ?? [];

        return [
            'fundacion' => $fundacion,
            'necesidades' => $necesidades,
            'mascotas' => $fundacion->mascotas()->where('estado', 'En adopcion')->get()
        ];
    }
}
