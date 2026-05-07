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

        if (!empty($filters['verificado'])) {
            $query->where('verificado', true);
        }

        if (!empty($filters['buscar'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('Nombre_1', 'like', '%' . $filters['buscar'] . '%')
                    ->orWhere('ciudad', 'like', '%' . $filters['buscar'] . '%');
            });
        }

        if (!empty($filters['ciudad'])) {
            $query->where('ciudad', $filters['ciudad']);
        }

        return $query->orderBy('Nombre_1')->paginate($perPage);
    }

    public function findById(int $id): array
    {
        $fundacion = Fundacion::with(['mascotas' => function ($q) {
            $q->where('estado', 'En adopcion');
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
        return Fundacion::selectRaw(
            "*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance",
            [$lat, $lng, $lat]
        )
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->having('distance', '<=', $radio)
            ->orderBy('distance')
            ->get();
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
