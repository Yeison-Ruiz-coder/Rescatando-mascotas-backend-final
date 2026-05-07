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

        if (!empty($filters['tipo'])) {
            $query->where('tipo', $filters['tipo']);
        }

        if (!empty($filters['fundacion_id'])) {
            $query->where('fundacion_id', $filters['fundacion_id']);
        }

        return $query->orderBy('fecha_evento', 'asc')->paginate($perPage);
    }

    public function findById(int $id): Evento
    {
        return Evento::with(['fundacion', 'asistentes'])->findOrFail($id);
    }

    public function getCalendarData(): array
    {
        return Evento::where('fecha_evento', '>=', now())
            ->orderBy('fecha_evento', 'asc')
            ->get()
            ->map(function ($evento) {
                return [
                    'id' => $evento->id,
                    'title' => $evento->nombre_evento,
                    'start' => $evento->fecha_evento->format('Y-m-d H:i:s'),
                    'end' => $evento->fecha_fin ? $evento->fecha_fin->format('Y-m-d H:i:s') : null,
                    'description' => $evento->descripcion,
                    'location' => $evento->lugar_evento,
                    'image_url' => $evento->imagen_url,
                ];
            })
            ->toArray();
    }

    public function confirmarAsistencia(int $eventoId, int $userId): void
    {
        $evento = Evento::findOrFail($eventoId);

        $existe = $evento->asistentes()->where('user_id', $userId)->exists();

        if ($existe) {
            throw new \Exception('Ya has confirmado asistencia a este evento');
        }

        $evento->asistentes()->attach($userId, ['estado' => 'confirmado']);
    }

    public function cancelarAsistencia(int $eventoId, int $userId): void
    {
        $evento = Evento::findOrFail($eventoId);
        $evento->asistentes()->detach($userId);
    }

    public function getProximos(int $limit = 5)
    {
        return Evento::with('fundacion')
            ->where('fecha_evento', '>=', now())
            ->orderBy('fecha_evento', 'asc')
            ->limit($limit)
            ->get();
    }
}
