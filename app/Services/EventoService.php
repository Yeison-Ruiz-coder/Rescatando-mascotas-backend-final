<?php

namespace App\Services;

use App\Models\Evento;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Storage;

class EventoService
{
    use ImageUploadTrait;

    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Evento::with('creadoPor');

        if (!empty($filters['desde'])) {
            $query->whereDate('fecha_evento', '>=', $filters['desde']);
        }

        if (!empty($filters['hasta'])) {
            $query->whereDate('fecha_evento', '<=', $filters['hasta']);
        }

        if (!empty($filters['proximos'])) {
            $query->whereDate('fecha_evento', '>=', now());
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
        return Evento::with('creadoPor')->findOrFail($id);
    }

    public function create(array $data, $imagen = null): Evento
    {
        if ($imagen) {
            $data['imagen_url'] = $this->uploadImage($imagen, 'eventos');
        }

        if (isset($data['tags']) && is_array($data['tags'])) {
            $data['tags'] = json_encode($data['tags'], JSON_UNESCAPED_UNICODE);
        }

        $data['creado_por_id'] = auth()->id();

        return Evento::create($data);
    }

    public function update(int $id, array $data, $imagen = null): Evento
    {
        $evento = Evento::findOrFail($id);

        if ($imagen) {
            $data['imagen_url'] = $this->uploadImage($imagen, 'eventos', $evento->imagen_url);
        }

        if (isset($data['tags']) && is_array($data['tags'])) {
            $data['tags'] = json_encode($data['tags'], JSON_UNESCAPED_UNICODE);
        }

        $evento->update($data);
        return $evento;
    }

    public function delete(int $id): void
    {
        $evento = Evento::findOrFail($id);

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
                'start' => $evento->fecha_evento->format('Y-m-d'),
                'end' => $evento->fecha_fin ? $evento->fecha_fin->format('Y-m-d') : null,
                'description' => $evento->descripcion,
                'location' => $evento->lugar_evento,
                'color' => $this->getEventColor($evento),
            ];
        })->toArray();
    }

    private function getEventColor($evento): string
    {
        if ($evento->fecha_evento->isPast()) {
            return '#gray';
        }
        if ($evento->fecha_evento->isToday()) {
            return '#green';
        }
        if ($evento->fecha_evento->diffInDays(now()) <= 7) {
            return '#orange';
        }
        return '#blue';
    }

    public function getProximos(int $limit = 10)
    {
        return Evento::with('creadoPor')
            ->whereDate('fecha_evento', '>=', now())
            ->orderBy('fecha_evento', 'asc')
            ->limit($limit)
            ->get();
    }
}
