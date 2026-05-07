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

        if (!empty($filters['urgencia'])) {
            $query->where('urgencia', $filters['urgencia']);
        }

        if (!empty($filters['cercanos']) && isset($filters['lat']) && isset($filters['lng'])) {
            $radio = $filters['radio'] ?? 10;
            $lat = $filters['lat'];
            $lng = $filters['lng'];
            $query->whereNotNull('lat')
                  ->whereNotNull('lng')
                  ->whereBetween('lat', [$lat - ($radio / 111), $lat + ($radio / 111)]);
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
            'por_urgencia' => Reporte::select('urgencia', DB::raw('count(*) as total'))
                ->whereNotNull('urgencia')
                ->groupBy('urgencia')
                ->get(),
            'este_mes' => Reporte::whereMonth('created_at', now()->month)->count(),
        ];
    }

    public function findById(int $id): Reporte
    {
        return Reporte::with(['usuario', 'resueltoPor', 'rescate'])->findOrFail($id);
    }

    public function create(array $data, $foto = null): Reporte
    {
        if ($foto) {
            $data['foto_url'] = $this->uploadImage($foto, 'reportes');
        }

        // Procesar galería de fotos si viene
        if (isset($data['galeria_fotos']) && is_array($data['galeria_fotos'])) {
            $data['galeria_fotos'] = json_encode($data['galeria_fotos']);
        }

        if (isset($data['fotos_detalle']) && is_array($data['fotos_detalle'])) {
            $data['fotos_detalle'] = json_encode($data['fotos_detalle'], JSON_UNESCAPED_UNICODE);
        }

        // Procesar datos del animal
        if (isset($data['datos_animal']) && is_array($data['datos_animal'])) {
            $data['datos_animal'] = json_encode($data['datos_animal'], JSON_UNESCAPED_UNICODE);
        }

        // Compatibilidad con latitud/longitud
        if (isset($data['latitud']) && !isset($data['lat'])) {
            $data['lat'] = $data['latitud'];
        }
        if (isset($data['longitud']) && !isset($data['lng'])) {
            $data['lng'] = $data['longitud'];
        }

        // Valores por defecto
        $data['urgencia'] = $data['urgencia'] ?? 'media';
        $data['contacto_permiso'] = $data['contacto_permiso'] ?? true;
        $data['anonimo'] = $data['anonimo'] ?? false;

        return Reporte::create($data);
    }

    public function update(int $id, array $data): Reporte
    {
        $reporte = Reporte::findOrFail($id);

        if (isset($data['estado']) && $data['estado'] === 'resuelto') {
            $data['resuelto_por'] = auth()->id();
            $data['fecha_resolucion'] = now();
        }

        // Compatibilidad con latitud/longitud
        if (isset($data['latitud']) && !isset($data['lat'])) {
            $data['lat'] = $data['latitud'];
        }
        if (isset($data['longitud']) && !isset($data['lng'])) {
            $data['lng'] = $data['longitud'];
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

        if ($reporte->galeria_fotos) {
            $galeria = is_string($reporte->galeria_fotos) ? json_decode($reporte->galeria_fotos, true) : $reporte->galeria_fotos;
            if (is_array($galeria)) {
                foreach ($galeria as $foto) {
                    $this->deleteImage($foto);
                }
            }
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
            'lat' => $reporte->lat,
            'lng' => $reporte->lng,
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

        $reportesPorUrgencia = Reporte::select('urgencia', DB::raw('count(*) as total'))
            ->whereNotNull('urgencia')
            ->groupBy('urgencia')
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
            'por_urgencia' => $reportesPorUrgencia,
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
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->whereBetween('lat', [$lat - ($radio / 111), $lat + ($radio / 111)])
            ->whereBetween('lng', [$lng - ($radio / 111 / cos(deg2rad($lat))), $lng + ($radio / 111 / cos(deg2rad($lat)))])
            ->with('usuario')
            ->latest()
            ->get();
    }
}
