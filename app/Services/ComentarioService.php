<?php

namespace App\Services;

use App\Models\Comentario;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ComentarioService
{
    public function getAll(array $filters = [], int $perPage = 20)
    {
        $query = Comentario::with(['usuario', 'comentable']);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['fecha_inicio'])) {
            $query->whereDate('fecha', '>=', $filters['fecha_inicio']);
        }

        if (!empty($filters['fecha_fin'])) {
            $query->whereDate('fecha', '<=', $filters['fecha_fin']);
        }

        if (!empty($filters['search'])) {
            $query->where('contenido', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function getEstadisticas(): array
    {
        return [
            'total' => Comentario::count(),
            'hoy' => Comentario::whereDate('fecha', today())->count(),
            'esta_semana' => Comentario::where('fecha', '>=', now()->subDays(7))->count(),
            'usuarios_activos' => Comentario::distinct('user_id')->count('user_id'),
        ];
    }

    public function findById(int $id): Comentario
    {
        return Comentario::with(['usuario', 'comentable'])->findOrFail($id);
    }

    public function update(int $id, array $data): Comentario
    {
        $comentario = Comentario::findOrFail($id);
        $comentario->update($data);
        return $comentario->fresh(['usuario', 'comentable']);
    }

    public function delete(int $id): void
    {
        $comentario = Comentario::findOrFail($id);
        $comentario->delete();
    }

    public function deleteMultiple(array $ids): int
    {
        return Comentario::whereIn('id', $ids)->delete();
    }
}
