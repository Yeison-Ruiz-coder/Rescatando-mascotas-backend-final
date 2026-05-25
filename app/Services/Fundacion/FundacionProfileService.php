<?php

namespace App\Services\Fundacion;

use App\Models\User;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Hash;

class FundacionProfileService
{
    use ImageUploadTrait;

    /**
     * Obtener perfil completo (users + fundacion)
     */
    public function getCompleteProfile(User $user): array
    {
        $fundacion = $user->fundacion;

        return [
            'user' => $user->only([
                'id', 'nombre', 'apellidos', 'email', 'telefono', 'avatar',
                'biografia', 'direccion', 'pais', 'ciudad', 'lat', 'lng',
                'telefono_verificado', 'email_verified_at', 'puntos', 'rango'
            ]),
            'fundacion' => $fundacion ? $fundacion->toArray() : null,
            'stats' => [
                'total_mascotas' => $fundacion ? $fundacion->mascotas()->count() : 0,
                'total_adopciones' => $fundacion ? $fundacion->adopciones()->count() : 0,
                'total_donaciones' => $fundacion ? $fundacion->donaciones()->sum('monto') : 0,
            ]
        ];
    }

    /**
     * Actualizar perfil completo
     */
    public function updateCompleteProfile(User $user, array $data, $avatar = null): array
    {
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
        $fundacion = $user->fundacion;
        if ($fundacion) {
            $fundacionData = array_intersect_key($data, array_flip([
                'Nombre_1', 'Direccion', 'Email', 'registro_sanitario',
                'capacidad_maxima', 'necesidades_actuales', 'horario_atencion',
                'recibe_voluntarios', 'ciudad', 'fecha_fundacion', 'lat', 'lng',
                'radio_atencion'
            ]));

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
        $fundacion = $user->fundacion;
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
        $fundacion = $user->fundacion;
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
        $fundacion = $user->fundacion;
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
        $fundacion = $user->fundacion;

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
        $fundacion = $user->fundacion;

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
