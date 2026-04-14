<?php

namespace App\Services\Public;

use App\Models\Veterinaria;

class VeterinariaPublicService
{
    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Veterinaria::query();

        if (!empty($filters['urgencias'])) {
            $query->where('urgencias_24h', true);
        }

        if (!empty($filters['ubicacion'])) {
            $query->where('Direccion', 'like', '%' . $filters['ubicacion'] . '%');
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): array
    {
        $veterinaria = Veterinaria::findOrFail($id);
        $servicios = json_decode($veterinaria->servicios, true) ?? [];

        return [
            'veterinaria' => $veterinaria,
            'servicios' => $servicios
        ];
    }

    public function getMapa()
    {
        return Veterinaria::where('urgencias_24h', true)
            ->get(['id', 'Nombre_vet', 'Direccion', 'Telefono', 'urgencias_24h']);
    }
}
