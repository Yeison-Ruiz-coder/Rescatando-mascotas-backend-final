<?php

namespace App\Services\User;

use App\Models\Solicitud;
use App\Models\Mascota;
use Illuminate\Support\Facades\Log;

class SolicitudUserService
{
    public function getByUser(int $userId)
    {
        return Solicitud::with(['solicitable'])
            ->where('user_id', $userId)
            ->where('tipo_solicitud', 'adopcion')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById(int $userId, int $solicitudId): Solicitud
    {
        return Solicitud::with(['solicitable'])
            ->where('user_id', $userId)
            ->findOrFail($solicitudId);
    }

    public function createSolicitudAdopcion(int $userId, array $data): Solicitud
    {
        $mascota = Mascota::findOrFail($data['mascota_id']);

        if ($mascota->estado !== 'En adopcion') {
            throw new \Exception('Esta mascota ya no está disponible para adopción');
        }

        $datosAdicionales = [
            'apellido_solicitante' => $data['apellido'],
            'documento_identidad' => $data['documento_identidad'],
            'direccion' => $data['direccion'],
            'ciudad' => $data['ciudad'] ?? null,
            'departamento' => $data['departamento'] ?? null,
            'codigo_postal' => $data['codigo_postal'] ?? null,
            'estado_civil' => $data['estado_civil'] ?? null,
            'cantidad_hijos' => $data['cantidad_hijos'] ?? null,
            'ocupacion' => $data['ocupacion'] ?? null,
            'experiencia_mascotas' => $data['experiencia_mascotas'],
            'tipo_vivienda' => $data['tipo_vivienda'],
            'es_propietario' => $data['es_propietario'] ?? null,
            'compromiso_cuidado' => $data['compromiso_cuidado'],
            'compromiso_esterilizacion' => $data['compromiso_esterilizacion'],
            'compromiso_seguimiento' => $data['compromiso_seguimiento'],
        ];

        $solicitud = Solicitud::create([
            'tipo_solicitud' => 'adopcion',
            'contenido' => $data['motivo_adopcion'],
            'estado' => 'pendiente',
            'user_id' => $userId,
            'nombre_solicitante' => $data['nombre'],
            'email_solicitante' => $data['email'],
            'telefono_solicitante' => $data['telefono'],
            'solicitable_type' => Mascota::class,
            'solicitable_id' => $mascota->id,
            'datos_adicionales' => $datosAdicionales,
        ]);

        Log::info('Solicitud creada exitosamente', [
            'id' => $solicitud->id,
            'nombre_solicitante' => $solicitud->nombre_solicitante,
            'email_solicitante' => $solicitud->email_solicitante,
        ]);

        return $solicitud;
    }
}
