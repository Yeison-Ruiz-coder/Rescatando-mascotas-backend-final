<?php

namespace App\Services\Public;

use App\Models\Fundacion;

class FundacionPublicService
{
    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Fundacion::query()
            ->selectFields()
            ->withCount('mascotas');

        $reiniciarFiltros = isset($filters['reiniciar_filtros']) && filter_var($filters['reiniciar_filtros'], FILTER_VALIDATE_BOOLEAN);

        if (!$reiniciarFiltros && !empty($filters['recibe_voluntarios'])) {
            $query->where('recibe_voluntarios', true);
        }

        if (!$reiniciarFiltros && !empty($filters['verificado'])) {
            $query->where('verificado', true);
        }

        if (!empty($filters['buscar'])) {
            $buscar = trim($filters['buscar']);

            $query->where(function ($q) use ($buscar) {
                $q->where('Nombre_1', 'like', '%' . $buscar . '%')
                    ->orWhere('ciudad', 'like', '%' . $buscar . '%');
            });
            $query->orderByRaw('Nombre_1 = ? DESC', [$buscar]);
        }

        if (!$reiniciarFiltros && !empty($filters['ciudad'])) {
            $query->where('ciudad', $filters['ciudad']);
        }

        return $query->orderBy('Nombre_1')->paginate($perPage);
    }

    public function findById(int $id): array
    {
        $fundacion = Fundacion::query()
            ->selectFields()
            ->with(['mascotas' => function ($q) {
                $q->select(['id', 'nombre_mascota', 'foto_principal', 'estado', 'fundacion_id', 'created_at'])
                  ->where('estado', 'En adopcion');
            }])
            ->withCount('mascotas')
            ->findOrFail($id);

        $necesidades = is_string($fundacion->necesidades_actuales)
            ? json_decode($fundacion->necesidades_actuales, true)
            : ($fundacion->necesidades_actuales ?? []);

        return [
            'fundacion' => $fundacion,
            'necesidades' => $necesidades,
            'mascotas' => $fundacion->mascotas,
            'estadisticas' => [
                'mascotas_disponibles' => $fundacion->mascotas->count(),
                'verificado' => $fundacion->verificado,
                'capacidad_maxima' => $fundacion->capacidad_maxima,
            ]
        ];
    }

    public function getCercanas(float $lat, float $lng, int $radio = 10)
    {
        return Fundacion::query()
            ->selectRaw(
                "*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance",
                [$lat, $lng, $lat]
            )
            ->select(['id', 'Nombre_1', 'Direccion', 'Telefono', 'Email', 'ciudad', 'lat', 'lng', 'imagen_portada', 'verificado'])
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->having('distance', '<=', $radio)
            ->orderBy('distance')
            ->get();
    }

    public function getSugerencias(string $searchTerm, int $limit = 10): array
    {
        $searchTerm = trim($searchTerm);

        if (strlen($searchTerm) < 2) {
            return [];
        }

        $results = collect();

        $nombres = Fundacion::query()
            ->where('Nombre_1', 'like', "%{$searchTerm}%")
            ->whereNotNull('Nombre_1')
            ->limit($limit)
            ->pluck('Nombre_1')
            ->filter()
            ->values();
        $results = $results->merge($nombres);

        $ciudades = Fundacion::query()
            ->where('ciudad', 'like', "%{$searchTerm}%")
            ->whereNotNull('ciudad')
            ->limit($limit)
            ->pluck('ciudad')
            ->filter()
            ->values();
        $results = $results->merge($ciudades);

        return $results->unique()->values()->take($limit)->toArray();
    }

    public function getEstadisticas(): array
    {
        return [
            'total_fundaciones' => Fundacion::count(),
            'fundaciones_verificadas' => Fundacion::where('verificado', true)->count(),
            'fundaciones_con_voluntarios' => Fundacion::where('recibe_voluntarios', true)->count(),
            'total_mascotas' => \App\Models\Mascota::count(),
            'total_adopciones' => \App\Models\Adopcion::count(),
        ];
    }
}
