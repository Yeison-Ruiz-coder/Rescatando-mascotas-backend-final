<?php

namespace App\Services\Public;

use App\Models\Evento;

class EventoPublicService
{
    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Evento::query()
            ->selectFields()
            ->where('fecha_evento', '>=', now()->startOfDay()); // ✅ Solo eventos desde hoy en adelante

        if (isset($filters['proximos']) && filter_var($filters['proximos'], FILTER_VALIDATE_BOOLEAN)) {
            $query->where('fecha_evento', '>=', now());
        }

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

        if (!empty($filters['buscar'])) {
            $buscar = trim($filters['buscar']);

            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_evento', 'like', '%' . $buscar . '%')
                  ->orWhere('descripcion', 'like', '%' . $buscar . '%')
                  ->orWhere('lugar_evento', 'like', '%' . $buscar . '%')
                  ->orWhere('tipo', 'like', '%' . $buscar . '%');
            });
        }

        return $query->orderBy('fecha_evento', 'asc')->paginate($perPage);
    }

    public function findById(int $id): Evento
    {
        return Evento::query()
            ->selectFields()
            ->with(['fundacion:id,Nombre_1,imagen_portada,ciudad', 'asistentes:id,nombre,email'])
            ->where('fecha_evento', '>=', now()->startOfDay()) // ✅ Solo eventos desde hoy
            ->findOrFail($id);
    }

    public function getCalendarData(): array
    {
        return Evento::query()
            ->select(['id', 'nombre_evento', 'fecha_evento', 'fecha_fin', 'descripcion', 'lugar_evento', 'imagen_url'])
            ->where('fecha_evento', '>=', now()->startOfDay()) // ✅ Solo eventos desde hoy
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
        $evento = Evento::query()
            ->select(['id'])
            ->where('fecha_evento', '>=', now()->startOfDay()) // ✅ Validar que no esté finalizado
            ->findOrFail($eventoId);

        $existe = $evento->asistentes()->where('user_id', $userId)->exists();

        if ($existe) {
            throw new \Exception('Ya has confirmado asistencia a este evento');
        }

        $evento->asistentes()->attach($userId, ['estado' => 'confirmado']);
    }

    public function cancelarAsistencia(int $eventoId, int $userId): void
    {
        $evento = Evento::query()->select(['id'])->findOrFail($eventoId);
        $evento->asistentes()->detach($userId);
    }

    public function getProximos(int $limit = 5)
    {
        return Evento::query()
            ->selectFields()
            ->with('fundacion:id,Nombre_1,imagen_portada,ciudad')
            ->where('fecha_evento', '>=', now()->startOfDay()) // ✅ Solo eventos desde hoy
            ->orderBy('fecha_evento', 'asc')
            ->limit($limit)
            ->get();
    }
}
