<?php

namespace App\Services\User;

use App\Models\Donacion;

class DonacionUserService
{
    public function getByUser(int $userId, int $perPage = 15)
    {
        return Donacion::with('fundacion')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $userId, int $donacionId): Donacion
    {
        return Donacion::with('fundacion')
            ->where('user_id', $userId)
            ->findOrFail($donacionId);
    }

    public function createDonacion(int $userId, array $data): Donacion
    {
        return Donacion::create([
            'user_id' => $userId,
            'fundacion_id' => $data['fundacion_id'],
            'valor_donacion' => $data['valor_donacion'],
            'publica' => $data['publica'] ?? false,
            'fecha_donacion' => now(),
        ]);
    }

    public function getCertificadoData(int $userId, int $donacionId): Donacion
    {
        return $this->findById($userId, $donacionId);
    }
}
