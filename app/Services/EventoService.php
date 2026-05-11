<?php

namespace App\Services;

use App\Models\Evento;
use Illuminate\Support\Collection;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Storage;

class EventoService
{
    use ImageUploadTrait;

    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Evento::with('fundacion');

        if (!empty($filters['desde'])) {
            $query->whereDate('fecha_evento', '>=', $filters['desde']);
        }

        if (!empty($filters['hasta'])) {
            $query->whereDate('fecha_evento', '<=', $filters['hasta']);
        }

        if (!empty($filters['proximos'])) {
            $query->whereDate('fecha_evento', '>=', now());
        }

        if (!empty($filters['tipo'])) {
            $query->where('tipo', $filters['tipo']);
        }

        if (!empty($filters['fundacion_id'])) {
            $query->where('fundacion_id', $filters['fundacion_id']);
        }

        $orden = !empty($filters['proximos']) ? 'asc' : 'desc';

        return $query->orderBy('fecha_evento', $orden)->paginate($perPage);
    }

    public function getEstadisticas(): array
    {
        return [
            'total' => Evento::count(),
            'proximos' => Evento::whereDate('fecha_evento', '>=', now())->count(),
            'pasados' => Evento::whereDate('fecha_evento', '<', now())->count(),
            'este_mes' => Evento::whereMonth('fecha_evento', now()->month)->count(),
        ];
    }

    public function findById(int $id): Evento
    {
        return Evento::with(['fundacion', 'asistentes'])->findOrFail($id);
    }

    public function create(array $data, mixed $imagen = null): Evento
    {
        if ($imagen) {
            $data['imagen_url'] = $this->uploadImage($imagen, 'eventos');
            $data['imagen_public_id'] = null;
        }

        // Asegurar que tags sea JSON válido
        if (isset($data['tags'])) {
            if (is_array($data['tags'])) {
                $data['tags'] = json_encode($data['tags'], JSON_UNESCAPED_UNICODE);
            } elseif (is_string($data['tags']) && !$this->isJson($data['tags'])) {
                // Si es string pero no es JSON, convertirlo a array y luego a JSON
                $data['tags'] = json_encode([$data['tags']], JSON_UNESCAPED_UNICODE);
            }
        }

        return Evento::create($data);
    }

    // Helper method para verificar si es JSON
    private function isJson(string $string): bool
    {
        if (!is_string($string)) return false;
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function update(int $id, array $data, mixed $imagen = null): Evento
    {
        $evento = Evento::findOrFail($id);

        if ($imagen) {
            if ($evento->imagen_url) {
                $this->deleteImage($evento->imagen_url);
            }
            $data['imagen_url'] = $this->uploadImage($imagen, 'eventos');
            $data['imagen_public_id'] = null;
        }

        // Asegurar que tags sea JSON válido
        if (isset($data['tags'])) {
            if (is_array($data['tags'])) {
                $data['tags'] = json_encode($data['tags'], JSON_UNESCAPED_UNICODE);
            } elseif (is_string($data['tags']) && !$this->isJson($data['tags'])) {
                $data['tags'] = json_encode([$data['tags']], JSON_UNESCAPED_UNICODE);
            }
        }

        $evento->update($data);
        return $evento;
    }

    public function delete(int $id): void
    {
        $evento = Evento::findOrFail($id);

        // Eliminar imagen de Cloudinary si existe
        if ($evento->imagen_url) {
            $this->deleteImage($evento->imagen_url);
        }

        $evento->delete();
    }

    public function getCalendarData(): array
    {
        return Evento::all()->map(function ($evento) {
            return [
                'id' => $evento->id,
                'title' => $evento->nombre_evento,
                'start' => $evento->fecha_evento->format('Y-m-d H:i:s'),
                'end' => $evento->fecha_fin ? $evento->fecha_fin->format('Y-m-d H:i:s') : null,
                'description' => $evento->descripcion,
                'location' => $evento->lugar_evento,
                'color' => $this->getEventColor($evento),
            ];
        })->toArray();
    }

    private function getEventColor(Evento $evento): string
    {
        if ($evento->fecha_evento->isPast()) {
            return '#6c757d'; // gray
        }
        if ($evento->fecha_evento->isToday()) {
            return '#28a745'; // green
        }
        if ($evento->fecha_evento->diffInDays(now()) <= 7) {
            return '#fd7e14'; // orange
        }
        return '#007bff'; // blue
    }

    public function getProximos(int $limit = 10): Collection
    {
        return Evento::with('fundacion')
            ->whereDate('fecha_evento', '>=', now())
            ->orderBy('fecha_evento', 'asc')
            ->limit($limit)
            ->get();
    }

    public function confirmarAsistencia(int $eventoId, int $userId): void
    {
        $evento = Evento::findOrFail($eventoId);

        $existe = $evento->asistentes()->where('user_id', $userId)->exists();

        if (!$existe) {
            $evento->asistentes()->attach($userId, ['estado' => 'confirmado']);
        } else {
            // Si existe pero está cancelado, actualizar
            $evento->asistentes()->updateExistingPivot($userId, ['estado' => 'confirmado']);
        }
    }

    public function cancelarAsistencia(int $eventoId, int $userId): void
    {
        $evento = Evento::findOrFail($eventoId);
        $evento->asistentes()->updateExistingPivot($userId, ['estado' => 'cancelado']);
    }
}
