<?php

namespace App\Services\Public;

use App\Models\Mascota;

class AdopcionPublicService
{
    public function getMascotasDisponibles(int $perPage = 15)
    {
        return Mascota::with('fundacion')
            ->where('estado', 'En adopcion')
            ->paginate($perPage);
    }

    public function findMascotaDisponible(int $id): Mascota
    {
        return Mascota::with(['fundacion', 'razas'])
            ->where('estado', 'En adopcion')
            ->findOrFail($id);
    }

    public function verificarDisponibilidad(int $id): array
    {
        $mascota = Mascota::find($id);

        if (!$mascota) {
            return [
                'success' => false,
                'message' => 'Mascota no encontrada'
            ];
        }

        return [
            'success' => true,
            'data' => [
                'disponible' => $mascota->estado === 'En adopcion',
                'estado' => $mascota->estado,
                'nombre' => $mascota->nombre_mascota
            ]
        ];
    }
}
