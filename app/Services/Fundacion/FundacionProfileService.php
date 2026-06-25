<?php

namespace App\Services\Fundacion;

use App\Models\User;
use App\Models\Fundacion;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class FundacionProfileService
{
    use ImageUploadTrait;

    /**
     * Obtener o crear fundación para el usuario
     */
    private function getOrCreateFundacion(User $user)
    {
        $fundacion = $user->fundacion;

        if (!$fundacion) {
            Log::info('🆕 Creando fundación automáticamente para usuario: ' . $user->id);

            $fundacion = Fundacion::create([
                'Nombre_1' => $user->nombre ?? 'Fundación de ' . $user->email,
                'user_id' => $user->id,
                'Email' => $user->email,
                'Direccion' => $user->direccion ?? 'Por definir',
                'Telefono' => $user->telefono ?? '000000000',
                'registro_sanitario' => 'PENDIENTE_' . $user->id,
                'ciudad' => $user->ciudad ?? null,
                'recibe_voluntarios' => false,
                'capacidad_maxima' => 0,
            ]);

            // Recargar el usuario para que tenga la fundación
            $user->refresh();
        }

        return $fundacion;
    }

    /**
     * Obtener perfil completo (users + fundacion)
     */
    public function getCompleteProfile(User $user): array
    {
        $fundacion = $this->getOrCreateFundacion($user);

        return [
            'user' => $user->only([
                'id', 'nombre', 'apellidos', 'email', 'telefono', 'avatar',
                'biografia', 'direccion', 'pais', 'ciudad', 'lat', 'lng',
                'telefono_verificado', 'email_verified_at', 'puntos', 'rango'
            ]),
            'fundacion' => $fundacion->toArray(),
            'stats' => [
                'total_mascotas' => $fundacion->mascotas()->count(),
                'total_adopciones' => $fundacion->adopciones()->count(),
                'total_donaciones' => $fundacion->donaciones()->sum('monto'),
            ]
        ];
    }

    /**
     * Actualizar perfil completo
     */
    public function updateCompleteProfile(User $user, array $data, $avatar = null): array
    {
        // 🔥 Asegurar que la fundación existe
        $fundacion = $this->getOrCreateFundacion($user);

        // Actualizar datos de User
        $userData = array_intersect_key($data, array_flip([
            'nombre', 'apellidos', 'telefono', 'biografia'
        ]));

        if ($avatar) {
            if ($user->avatar) {
                $this->deleteImage($user->avatar);
            }
            $userData['avatar'] = $this->uploadImage($avatar, 'avatars');
        }

        if (!empty($userData)) {
            $user->update($userData);
        }

        // Actualizar datos de Fundación
        $fundacionData = array_intersect_key($data, array_flip([
            'Nombre_1', 'Direccion', 'Email', 'registro_sanitario',
            'capacidad_maxima', 'necesidades_actuales', 'horario_atencion',
            'recibe_voluntarios', 'ciudad', 'fecha_fundacion', 'lat', 'lng',
            'radio_atencion'
        ]));

        if (!empty($fundacionData)) {
            // Procesar JSON
            if (isset($fundacionData['necesidades_actuales']) && is_array($fundacionData['necesidades_actuales'])) {
                $fundacionData['necesidades_actuales'] = json_encode($fundacionData['necesidades_actuales']);
            }

            $fundacion->update($fundacionData);
        }

        return $this->getCompleteProfile($user);
    }

    /**
     * Actualizar información general
     */
    public function updateGeneralInfo(User $user, array $data)
    {
        $fundacion = $this->getOrCreateFundacion($user);
        $fundacion->update(array_intersect_key($data, array_flip([
            'Nombre_1', 'Direccion', 'Email', 'registro_sanitario',
            'capacidad_maxima', 'ciudad', 'fecha_fundacion'
        ])));

        return $fundacion;
    }

    /**
     * Actualizar necesidades
     */
    public function updateNeeds(User $user, array $needs)
    {
        $fundacion = $this->getOrCreateFundacion($user);
        $fundacion->update([
            'necesidades_actuales' => json_encode($needs)
        ]);

        return $fundacion;
    }

    /**
     * Actualizar horario
     */
    public function updateSchedule(User $user, string $schedule, bool $recibeVoluntarios)
    {
        $fundacion = $this->getOrCreateFundacion($user);
        $fundacion->update([
            'horario_atencion' => $schedule,
            'recibe_voluntarios' => $recibeVoluntarios
        ]);

        return $fundacion;
    }

    /**
     * Subir imagen de portada
     */
    public function uploadCoverImage(User $user, $imageFile)
    {
        $fundacion = $this->getOrCreateFundacion($user);

        if ($fundacion->imagen_portada_public_id) {
            $this->deleteImage($fundacion->imagen_portada_public_id);
        }

        $result = $this->uploadImage($imageFile, 'fundaciones/portadas');

        $fundacion->update([
            'imagen_portada' => $result,
            'imagen_portada_public_id' => $result
        ]);

        return $fundacion;
    }

    /**
     * Eliminar imagen de portada
     */
    public function deleteCoverImage(User $user)
    {
        $fundacion = $this->getOrCreateFundacion($user);

        if ($fundacion->imagen_portada_public_id) {
            $this->deleteImage($fundacion->imagen_portada_public_id);
        }

        $fundacion->update([
            'imagen_portada' => null,
            'imagen_portada_public_id' => null
        ]);

        return $fundacion;
    }
}
