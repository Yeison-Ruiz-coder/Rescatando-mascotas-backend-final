<?php

namespace App\Services;

use App\Models\Rescate;
use App\Models\Fundacion;
use App\Models\Veterinaria;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RescateService
{
    public function getAll(array $filters = [], int $perPage = 20)
    {
        $query = Rescate::with(['usuarioReporto', 'entidadResponsable', 'mascota']);

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['tipo_emergencia'])) {
            $query->where('tipo_emergencia', $filters['tipo_emergencia']);
        }

        if (!empty($filters['prioridad'])) {
            $query->where('prioridad', $filters['prioridad']);
        }

        $query->orderByRaw("FIELD(prioridad, 'alta', 'media', 'baja')");
        $query->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    public function findById(int $id): Rescate
    {
        return Rescate::with(['usuarioReporto', 'entidadResponsable', 'mascota', 'reporte', 'gestionadoPor'])
            ->findOrFail($id);
    }

    public function getEstadisticas(): array
    {
        return [
            'pendientes' => Rescate::where('estado', 'pendiente')->count(),
            'en_proceso' => Rescate::where('estado', 'en_proceso')->count(),
            'completados' => Rescate::where('estado', 'completado')->count(),
            'por_tipo' => [
                'herido' => Rescate::where('tipo_emergencia', 'herido')->count(),
                'abandonado' => Rescate::where('tipo_emergencia', 'abandonado')->count(),
                'urgente' => Rescate::where('tipo_emergencia', 'urgente')->count(),
                'otro' => Rescate::where('tipo_emergencia', 'otro')->count(),
            ],
            'por_prioridad' => [
                'alta' => Rescate::where('prioridad', 'alta')->count(),
                'media' => Rescate::where('prioridad', 'media')->count(),
                'baja' => Rescate::where('prioridad', 'baja')->count(),
            ]
        ];
    }

    public function asignar(int $id, string $entidadTipo, int $entidadId): Rescate
    {
        $rescate = Rescate::findOrFail($id);

        $entidad = null;
        if ($entidadTipo === 'fundacion') {
            $entidad = Fundacion::findOrFail($entidadId);
            $rescate->entidad_responsable_type = Fundacion::class;
        } else {
            $entidad = Veterinaria::findOrFail($entidadId);
            $rescate->entidad_responsable_type = Veterinaria::class;
        }

        $rescate->entidad_responsable_id = $entidad->id;
        $rescate->estado = 'en_proceso';
        $rescate->save();

        return $rescate;
    }

    public function updateEstado(int $id, string $estado): Rescate
    {
        $rescate = Rescate::findOrFail($id);
        $rescate->estado = $estado;
        $rescate->save();
        return $rescate;
    }
}
