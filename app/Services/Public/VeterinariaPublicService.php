<?php

namespace App\Services\Public;

use App\Models\Veterinaria;

class VeterinariaPublicService
{
    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Veterinaria::query()
            ->selectFields();

        $reiniciarFiltros = isset($filters['reiniciar_filtros']) && filter_var($filters['reiniciar_filtros'], FILTER_VALIDATE_BOOLEAN);

        if (!$reiniciarFiltros && !empty($filters['urgencias'])) {
            $query->where('urgencias_24h', true);
        }

        if (!$reiniciarFiltros && !empty($filters['verificado'])) {
            $query->where('verificado', true);
        }

        if (!$reiniciarFiltros && !empty($filters['ubicacion'])) {
            $query->where(function($q) use ($filters) {
                $q->where('Direccion', 'like', '%' . $filters['ubicacion'] . '%')
                  ->orWhere('ciudad', 'like', '%' . $filters['ubicacion'] . '%');
            });
        }

        if (!$reiniciarFiltros && !empty($filters['servicio'])) {
            $servicio = $filters['servicio'];
            $query->where(function($q) use ($servicio) {
                $q->where('servicios', 'like', '%"' . $servicio . '"%')
                  ->orWhere('servicios', 'like', '%' . $servicio . '%');
            });
        }

        if (!empty($filters['buscar'])) {
            $buscar = trim($filters['buscar']);

            $query->where(function ($q) use ($buscar) {
                $q->where('Nombre_vet', 'like', '%' . $buscar . '%')
                  ->orWhere('Direccion', 'like', '%' . $buscar . '%')
                  ->orWhere('ciudad', 'like', '%' . $buscar . '%')
                  ->orWhere('descripcion', 'like', '%' . $buscar . '%')
                  ->orWhere('servicios', 'like', '%' . $buscar . '%');
            });
            $query->orderByRaw('Nombre_vet = ? DESC', [$buscar]);
        }

        return $query->orderBy('Nombre_vet')->paginate($perPage);
    }

    public function findById(int $id): array
    {
        $veterinaria = Veterinaria::query()
            ->selectFields()
            ->findOrFail($id);
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

    public function getSugerencias(string $searchTerm, int $limit = 10): array
    {
        $searchTerm = trim($searchTerm);

        if (strlen($searchTerm) < 2) {
            return [];
        }

        $results = collect();

        $nombres = Veterinaria::query()
            ->where('Nombre_vet', 'like', "%{$searchTerm}%")
            ->whereNotNull('Nombre_vet')
            ->limit($limit)
            ->pluck('Nombre_vet')
            ->filter()
            ->values();
        $results = $results->merge($nombres);

        $direcciones = Veterinaria::query()
            ->where('Direccion', 'like', "%{$searchTerm}%")
            ->whereNotNull('Direccion')
            ->limit($limit)
            ->pluck('Direccion')
            ->filter()
            ->values();
        $results = $results->merge($direcciones);

        $ciudades = Veterinaria::query()
            ->where('ciudad', 'like', "%{$searchTerm}%")
            ->whereNotNull('ciudad')
            ->limit($limit)
            ->pluck('ciudad')
            ->filter()
            ->values();
        $results = $results->merge($ciudades);

        return $results->unique()->values()->take($limit)->toArray();
    }

    public function getMapa()
    {
        return Veterinaria::query()
            ->select(['id', 'Nombre_vet', 'Direccion', 'Telefono', 'urgencias_24h', 'lat', 'lng'])
            ->where('urgencias_24h', true)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->get(['id', 'Nombre_vet', 'Direccion', 'Telefono', 'urgencias_24h', 'lat', 'lng']);
    }

    public function getCercanas(float $lat, float $lng, int $radio = 10)
    {
        return Veterinaria::query()
            ->selectRaw(
                "*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance",
                [$lat, $lng, $lat]
            )
            ->select(['id', 'Nombre_vet', 'Direccion', 'Telefono', 'urgencias_24h', 'lat', 'lng'])
        ->whereNotNull('lat')
        ->whereNotNull('lng')
        ->where('urgencias_24h', true)
        ->having('distance', '<=', $radio)
        ->orderBy('distance')
        ->get();
    }
}
