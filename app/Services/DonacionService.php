<?php

namespace App\Services;

use App\Models\Donacion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class DonacionService
{
    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Donacion::with(['user', 'fundacion']);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['fundacion_id'])) {
            $query->where('fundacion_id', $filters['fundacion_id']);
        }

        if (isset($filters['publica'])) {
            $query->where('publica', $filters['publica']);
        }

        if (!empty($filters['fecha_inicio'])) {
            $query->whereDate('fecha_donacion', '>=', $filters['fecha_inicio']);
        }

        if (!empty($filters['fecha_fin'])) {
            $query->whereDate('fecha_donacion', '<=', $filters['fecha_fin']);
        }

        $orden = $filters['orden'] ?? 'desc';
        $query->orderBy('fecha_donacion', $orden);

        return $query->paginate($perPage);
    }

    public function getTotales(): array
    {
        return [
            'total' => Donacion::sum('valor_donacion'),
            'publicas' => Donacion::where('publica', true)->sum('valor_donacion'),
            'privadas' => Donacion::where('publica', false)->sum('valor_donacion'),
            'este_mes' => Donacion::whereMonth('fecha_donacion', now()->month)
                ->whereYear('fecha_donacion', now()->year)
                ->sum('valor_donacion'),
            'promedio' => round(Donacion::avg('valor_donacion') ?? 0, 2),
        ];
    }

    public function findById(int $id): Donacion
    {
        return Donacion::with(['user', 'fundacion'])->findOrFail($id);
    }

    public function create(array $data): Donacion
    {
        if (!isset($data['fecha_donacion'])) {
            $data['fecha_donacion'] = now();
        }

        return Donacion::create($data);
    }

    public function update(int $id, array $data): Donacion
    {
        $donacion = Donacion::findOrFail($id);
        $donacion->update($data);
        return $donacion->fresh(['user', 'fundacion']);
    }

    public function delete(int $id): void
    {
        $donacion = Donacion::findOrFail($id);
        $donacion->delete();
    }

    public function togglePublica(int $id): Donacion
    {
        $donacion = Donacion::findOrFail($id);
        $donacion->update(['publica' => !$donacion->publica]);
        return $donacion;
    }

    public function getReporte(string $fechaInicio, string $fechaFin, ?int $fundacionId = null, ?string $agrupacion = null): array
    {
        $query = Donacion::whereBetween('fecha_donacion', [$fechaInicio, $fechaFin]);

        if ($fundacionId) {
            $query->where('fundacion_id', $fundacionId);
        }

        $donaciones = $query->with(['user', 'fundacion'])->get();

        $total = $donaciones->sum('valor_donacion');
        $promedio = $donaciones->avg('valor_donacion');
        $cantidad = $donaciones->count();
        $maxima = $donaciones->max('valor_donacion');
        $minima = $donaciones->min('valor_donacion');

        $resultado = [
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin,
            ],
            'estadisticas' => [
                'total' => $total,
                'promedio' => round($promedio ?? 0, 2),
                'cantidad' => $cantidad,
                'maxima' => $maxima,
                'minima' => $minima,
            ],
            'donaciones' => $donaciones,
        ];

        if ($agrupacion) {
            $resultado['agrupado'] = $this->agruparDonaciones($donaciones, $agrupacion);
        }

        return $resultado;
    }

    private function agruparDonaciones($donaciones, string $agrupacion): array
    {
        if ($agrupacion === 'dia') {
            return $donaciones->groupBy(function($item) {
                return $item->fecha_donacion->format('Y-m-d');
            })->map(function($grupo) {
                return [
                    'cantidad' => $grupo->count(),
                    'total' => $grupo->sum('valor_donacion')
                ];
            })->toArray();
        }

        if ($agrupacion === 'mes') {
            return $donaciones->groupBy(function($item) {
                return $item->fecha_donacion->format('Y-m');
            })->map(function($grupo) {
                return [
                    'cantidad' => $grupo->count(),
                    'total' => $grupo->sum('valor_donacion')
                ];
            })->toArray();
        }

        if ($agrupacion === 'fundacion') {
            return $donaciones->groupBy(function($item) {
                return $item->fundacion->Nombre_1 ?? 'Sin Fundación';
            })->map(function($grupo) {
                return [
                    'cantidad' => $grupo->count(),
                    'total' => $grupo->sum('valor_donacion')
                ];
            })->toArray();
        }

        return [];
    }
}
