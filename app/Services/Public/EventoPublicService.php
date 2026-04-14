<?php

namespace App\Services\Public;

use App\Models\Evento;

class EventoPublicService
{
    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Evento::where('fecha_evento', '>=', now());

        if (!empty($filters['mes'])) {
            $query->whereMonth('fecha_evento', $filters['mes']);
        }

        if (!empty($filters['anio'])) {
            $query->whereYear('fecha_evento', $filters['anio']);
        }

        return $query->orderBy('fecha_evento', 'asc')->paginate($perPage);
    }

    public function findById(int $id): Evento
    {
        return Evento::with('creadoPor')->findOrFail($id);
    }

    public function getCalendario(): array
    {
        return Evento::where('fecha_evento', '>=', now())
            ->orderBy('fecha_evento', 'asc')
            ->get()
            ->map(function ($evento) {
                return [
                    'id' => $evento->id,
                    'title' => $evento->nombre_evento,
                    'start' => $evento->fecha_evento,
                    'description' => $evento->descripcion,
                    'location' => $evento->lugar_evento,
                ];
            })
            ->toArray();
    }
}
