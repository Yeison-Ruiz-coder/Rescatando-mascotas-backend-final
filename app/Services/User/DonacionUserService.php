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
        $donacionData = [
            'user_id' => $userId,
            'fundacion_id' => $data['fundacion_id'],
            'valor_donacion' => $data['valor_donacion'],
            'publica' => $data['publica'] ?? false,
            'fecha_donacion' => now(),
        ];

        // Nuevos campos
        if (isset($data['anonima'])) {
            $donacionData['anonima'] = $data['anonima'];
        }
        if (isset($data['metodo_pago'])) {
            $donacionData['metodo_pago'] = $data['metodo_pago'];
        }
        if (isset($data['comentarios'])) {
            $donacionData['comentarios'] = $data['comentarios'];
        }
        if (isset($data['nombre_donante']) && !$data['anonima']) {
            $donacionData['nombre_donante'] = $data['nombre_donante'];
        }
        if (isset($data['email_donante']) && !$data['anonima']) {
            $donacionData['email_donante'] = $data['email_donante'];
        }

        return Donacion::create($donacionData);
    }

    public function getCertificadoData(int $userId, int $donacionId): Donacion
    {
        $donacion = $this->findById($userId, $donacionId);

        if ($donacion->anonima) {
            throw new \Exception('No se puede generar certificado para donaciones anónimas');
        }

        return $donacion;
    }
}
