<?php

namespace App\Services;

use App\Models\Reporte;
use App\Models\Rescate;
use App\Traits\ImageUploadTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    use ImageUploadTrait;

    public function getAll(array $filters = [], int $perPage = 20)
    {
        $query = Reporte::with(['usuario', 'resueltoPor']);

        if (!empty($filters['tipo_reporte'])) {
            $query->where('tipo_reporte', $filters['tipo_reporte']);
        }

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function getEstadisticas(): array
    {
        return [
            'activos' => Reporte::where('estado', 'activo')->count(),
            'resueltos' => Reporte::where('estado', 'resuelto')->count(),
            'cerrados' => Reporte::where('estado', 'cerrado')->count(),
            'total' => Reporte::count(),
            'por_tipo' => Reporte::select('tipo_reporte', DB::raw('count(*) as total'))
                ->groupBy('tipo_reporte')
                ->get(),
            'este_mes' => Reporte::whereMonth('created_at', now()->month)->count(),
        ];
    }

    public function findById(int $id): Reporte
    {
        return Reporte::with(['usuario', 'resueltoPor'])->findOrFail($id);
    }

    public function create(array $data, $foto = null): Reporte
    {
        if ($foto) {
            $data['foto_url'] = $this->uploadImage($foto, 'reportes');
        }

        if (isset($data['datos_animal']) && is_array($data['datos_animal'])) {
            $data['datos_animal'] = json_encode($data['datos_animal'], JSON_UNESCAPED_UNICODE);
        }

        return Reporte::create($data);
    }

    public function update(int $id, array $data): Reporte
    {
        $reporte = Reporte::findOrFail($id);

        if (isset($data['estado']) && $data['estado'] === 'resuelto') {
            $data['resuelto_por'] = auth()->id();
            $data['fecha_resolucion'] = now();
        }

        $reporte->update($data);
        return $reporte->fresh(['usuario', 'resueltoPor']);
    }

    public function delete(int $id): void
    {
        $reporte = Reporte::findOrFail($id);

        if ($reporte->foto_url) {
            $this->deleteImage($reporte->foto_url);
        }

        $reporte->delete();
    }

    public function convertirARescate(int $id, array $data): Rescate
    {
        $reporte = Reporte::findOrFail($id);

        $rescate = Rescate::create([
            'fecha_rescate' => $data['fecha_rescate'],
            'lugar_rescate' => $data['lugar_rescate'],
            'descripcion_rescate' => $data['descripcion_rescate'],
            'estado' => 'en_proceso',
            'reporte_id' => $reporte->id,
            'usuario_reporto_id' => $reporte->user_id,
            'gestionado_por' => auth()->id(),
        ]);

        $reporte->update([
            'estado' => 'resuelto',
            'resuelto_por' => auth()->id(),
            'fecha_resolucion' => now(),
        ]);

        return $rescate;
    }

    public function getEstadisticasAvanzadas(): array
    {
        $reportesPorTipo = Reporte::select('tipo_reporte', DB::raw('count(*) as total'))
            ->groupBy('tipo_reporte')
            ->get();

        $reportesPorMes = Reporte::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('count(*) as total')
        )
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $tiempoResolucion = Reporte::whereNotNull('fecha_resolucion')
            ->select(DB::raw('AVG(DATEDIFF(fecha_resolucion, created_at)) as promedio_dias'))
            ->first();

        $topUbicaciones = Reporte::select('ubicacion', DB::raw('count(*) as total'))
            ->whereNotNull('ubicacion')
            ->groupBy('ubicacion')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'por_tipo' => $reportesPorTipo,
            'por_mes' => $reportesPorMes,
            'tiempo_promedio_resolucion' => round($tiempoResolucion->promedio_dias ?? 0, 1),
            'top_ubicaciones' => $topUbicaciones,
            'totales' => [
                'activos' => Reporte::where('estado', 'activo')->count(),
                'resueltos' => Reporte::where('estado', 'resuelto')->count(),
                'cerrados' => Reporte::where('estado', 'cerrado')->count(),
            ],
        ];
    }

    public function getCercanos(float $lat, float $lng, int $radio = 10)
    {
        return Reporte::where('estado', 'activo')
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->whereBetween('latitud', [$lat - 0.5, $lat + 0.5])
            ->whereBetween('longitud', [$lng - 0.5, $lng + 0.5])
            ->with('usuario')
            ->latest()
            ->get();
    }
}
