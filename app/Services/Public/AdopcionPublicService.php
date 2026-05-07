<?php

namespace App\Services\Public;

use App\Models\Mascota;

class AdopcionPublicService
{
    public function getMascotasDisponibles(array $filters = [], int $perPage = 15)
    {
        $query = Mascota::with('fundacion')
            ->where('estado', 'En adopcion');

        // Filtros adicionales
        if (!empty($filters['especie'])) {
            $query->where('especie', $filters['especie']);
        }

        if (!empty($filters['genero'])) {
            $query->where('genero', $filters['genero']);
        }

        if (!empty($filters['tamano'])) {
            $query->where('tamano', $filters['tamano']);
        }

        if (!empty($filters['apto_con_ninos'])) {
            $query->where('apto_con_ninos', true);
        }

        if (!empty($filters['apto_con_otros_animales'])) {
            $query->where('apto_con_otros_animales', true);
        }

        return $query->latest()->paginate($perPage);
    }

    public function findMascotaDisponible(int $id): Mascota
    {
        return Mascota::with(['fundacion', 'razas', 'vacunas', 'historialMedico'])
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
                'nombre' => $mascota->nombre_mascota,
                'edad' => $mascota->edad_aprox,
                'tamano' => $mascota->tamano,
                'esterilizado' => $mascota->esterilizado,
                'vacunado' => $mascota->vacunado,
            ]
        ];
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
