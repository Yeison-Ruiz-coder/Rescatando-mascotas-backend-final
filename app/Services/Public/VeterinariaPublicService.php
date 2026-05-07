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

        if (!empty($filters['verificado'])) {
            $query->where('verificado', true);
        }

        if (!empty($filters['ubicacion'])) {
            $query->where(function($q) use ($filters) {
                $q->where('Direccion', 'like', '%' . $filters['ubicacion'] . '%')
                  ->orWhere('ciudad', 'like', '%' . $filters['ubicacion'] . '%');
            });
        }

        if (!empty($filters['servicio'])) {
            $servicio = $filters['servicio'];
            $query->where(function($q) use ($servicio) {
                $q->where('servicios', 'like', '%"' . $servicio . '"%')
                  ->orWhere('servicios', 'like', '%' . $servicio . '%');
            });
        }

        return $query->orderBy('Nombre_vet')->paginate($perPage);
    }

    public function findById(int $id): array
    {
        $veterinaria = Veterinaria::findOrFail($id);
        $servicios = [];

        if ($veterinaria->servicios) {
            $servicios = is_string($veterinaria->servicios)
                ? json_decode($veterinaria->servicios, true)
                : ($veterinaria->servicios ?? []);
        }

        $serviciosDetallados = [];
        if ($veterinaria->servicios_detallados) {
            $serviciosDetallados = is_string($veterinaria->servicios_detallados)
                ? json_decode($veterinaria->servicios_detallados, true)
                : ($veterinaria->servicios_detallados ?? []);
        }

        return [
            'veterinaria' => $veterinaria,
            'servicios' => $servicios,
            'servicios_detallados' => $serviciosDetallados,
            'equipo_medico' => $veterinaria->equipo_medico,
            'galeria_fotos' => $veterinaria->galeria_fotos,
            'verificado' => $veterinaria->verificado,
        ];
    }

    public function getMapa()
    {
        return Veterinaria::where('urgencias_24h', true)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->get(['id', 'Nombre_vet', 'Direccion', 'Telefono', 'urgencias_24h', 'lat', 'lng']);
    }

    public function getCercanas(float $lat, float $lng, int $radio = 10)
    {
        return Veterinaria::selectRaw(
            "*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance",
            [$lat, $lng, $lat]
        )
        ->whereNotNull('lat')
        ->whereNotNull('lng')
        ->where('urgencias_24h', true)
        ->having('distance', '<=', $radio)
        ->orderBy('distance')
        ->get();
    }
}
