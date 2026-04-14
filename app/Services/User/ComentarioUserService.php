<?php

namespace App\Services\User;

use App\Models\Comentario;

class ComentarioUserService
{
    public function getByUser(int $userId, int $perPage = 15)
    {
        return Comentario::with('comentable')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function updateComentario(int $userId, int $comentarioId, array $data): Comentario
    {
        $comentario = Comentario::where('user_id', $userId)
            ->findOrFail($comentarioId);

        $comentario->update($data);
        return $comentario;
    }

    public function deleteComentario(int $userId, int $comentarioId): void
    {
        $comentario = Comentario::where('user_id', $userId)
            ->findOrFail($comentarioId);

        $comentario->delete();
    }
}
