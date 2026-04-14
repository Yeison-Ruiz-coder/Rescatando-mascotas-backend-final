<?php

namespace App\Services;

use App\Models\TipoVacuna;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class TipoVacunaService
{
    public function getAll(array $filters = [], int $perPage = 20)
    {
        $query = TipoVacuna::query();

        if (!empty($filters['search'])) {
            $query->where('nombre_vacuna', 'like', "%{$filters['search']}%");
        }

        return $query->orderBy('nombre_vacuna')->paginate($perPage);
    }

    public function getEstadisticas(): array
    {
        return [
            'total' => TipoVacuna::count(),
            'con_mascotas' => TipoVacuna::has('mascotas')->count(),
            'total_aplicaciones' => DB::table('mascota_vacuna')->count(),
        ];
    }

    public function findById(int $id): TipoVacuna
    {
        return TipoVacuna::with(['mascotas' => function($q) {
            $q->withPivot('fecha_aplicacion')->latest('pivot_fecha_aplicacion');
        }])->findOrFail($id);
    }

    public function getDetalleEstadisticas(TipoVacuna $tipoVacuna): array
    {
        $totalAplicaciones = $tipoVacuna->mascotas->count();
        $mascotasVacunadas = $tipoVacuna->mascotas->unique('id')->count();
        $ultimaAplicacion = $tipoVacuna->mascotas->max('pivot.fecha_aplicacion');

        // Próximas vacunas a aplicar
        $proximasAplicaciones = [];
        if ($tipoVacuna->frecuencia_dias) {
            $proximasAplicaciones = $tipoVacuna->mascotas
                ->filter(function($mascota) use ($tipoVacuna) {
                    return $mascota->pivot->fecha_aplicacion &&
                        $mascota->pivot->fecha_aplicacion->addDays($tipoVacuna->frecuencia_dias) <= now()->addDays(30);
                })
                ->map(function($mascota) use ($tipoVacuna) {
                    return [
                        'mascota_id' => $mascota->id,
                        'mascota_nombre' => $mascota->nombre_mascota,
                        'fecha_ultima' => $mascota->pivot->fecha_aplicacion,
                        'fecha_proxima' => $mascota->pivot->fecha_aplicacion->addDays($tipoVacuna->frecuencia_dias),
                    ];
                })->values();
        }

        return [
            'total_aplicaciones' => $totalAplicaciones,
            'mascotas_vacunadas' => $mascotasVacunadas,
            'ultima_aplicacion' => $ultimaAplicacion,
            'proximas_aplicaciones' => $proximasAplicaciones,
        ];
    }

    public function create(array $data): TipoVacuna
    {
        return TipoVacuna::create($data);
    }

    public function update(int $id, array $data): TipoVacuna
    {
        $tipoVacuna = TipoVacuna::findOrFail($id);
        $tipoVacuna->update($data);
        return $tipoVacuna;
    }

    public function delete(int $id): void
    {
        $tipoVacuna = TipoVacuna::findOrFail($id);

        if ($tipoVacuna->mascotas()->exists()) {
            throw new \Exception('No se puede eliminar el tipo de vacuna porque tiene mascotas asociadas');
        }

        $tipoVacuna->delete();
    }

    public function getRecomendadas(?string $especie = null): array
    {
        $vacunasRecomendadas = [
            'Perro' => [
                ['nombre' => 'Múltiple (Canigen o similar)', 'frecuencia' => 'Anual', 'obligatoria' => true],
                ['nombre' => 'Rabia', 'frecuencia' => 'Anual', 'obligatoria' => true],
                ['nombre' => 'Tos de las perreras', 'frecuencia' => 'Anual', 'obligatoria' => false],
                ['nombre' => 'Leptospirosis', 'frecuencia' => 'Anual', 'obligatoria' => false],
            ],
            'Gato' => [
                ['nombre' => 'Trivalente (Feligen o similar)', 'frecuencia' => 'Anual', 'obligatoria' => true],
                ['nombre' => 'Rabia', 'frecuencia' => 'Anual', 'obligatoria' => true],
                ['nombre' => 'Leucemia felina', 'frecuencia' => 'Anual', 'obligatoria' => false],
            ],
        ];

        if ($especie) {
            return $vacunasRecomendadas[$especie] ?? [];
        }

        return $vacunasRecomendadas;
    }

    public function getEstadisticasVacunacion(): array
    {
        $totalVacunas = TipoVacuna::count();
        $totalAplicaciones = DB::table('mascota_vacuna')->count();

        // Vacunas más aplicadas
        $masAplicadas = TipoVacuna::withCount('mascotas')
            ->orderByDesc('mascotas_count')
            ->limit(5)
            ->get(['id', 'nombre_vacuna', 'mascotas_count']);

        // Aplicaciones por mes (últimos 6 meses)
        $aplicacionesPorMes = DB::table('mascota_vacuna')
            ->select(
                DB::raw('YEAR(fecha_aplicacion) as year'),
                DB::raw('MONTH(fecha_aplicacion) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->where('fecha_aplicacion', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return [
            'total_vacunas' => $totalVacunas,
            'total_aplicaciones' => $totalAplicaciones,
            'mas_aplicadas' => $masAplicadas,
            'aplicaciones_por_mes' => $aplicacionesPorMes,
        ];
    }
}
