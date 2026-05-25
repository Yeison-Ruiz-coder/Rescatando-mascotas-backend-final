<?php

namespace App\Services\Veterinaria;

use App\Models\User;
use App\Traits\ImageUploadTrait;

class VeterinariaProfileService
{
    use ImageUploadTrait;

    public function getCompleteProfile(User $user): array
    {
        $veterinaria = $user->veterinaria;

        return [
            'user' => $user->only([
                'id', 'nombre', 'apellidos', 'email', 'telefono', 'avatar',
                'biografia', 'direccion', 'pais', 'ciudad', 'lat', 'lng',
                'telefono_verificado', 'email_verified_at'
            ]),
            'veterinaria' => $veterinaria ? $veterinaria->toArray() : null,
            'stats' => [
                'total_pacientes' => $veterinaria ? $veterinaria->historialesMedicos()->count() : 0,
                'total_rescates' => $veterinaria ? $veterinaria->rescates()->count() : 0,
                'valoracion_promedio' => $veterinaria->valoracion_promedio ?? 0,
            ]
        ];
    }

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

        // Actualizar datos de Veterinaria
        $veterinaria = $user->veterinaria;
        if ($veterinaria) {
            $veterinariaData = array_intersect_key($data, array_flip([
                'Nombre_vet', 'descripcion', 'anios_experiencia', 'Direccion',
                'ciudad', 'departamento', 'Email', 'whatsapp', 'sitio_web',
                'servicios', 'servicios_detallados', 'equipo_medico',
                'horario_atencion', 'urgencias_24h', 'precio_consulta',
                'acepta_seguros', 'convenios', 'cobertura_zona', 'lat', 'lng',
                'radio_atencion'
            ]));

            // Procesar JSONs
            $jsonFields = ['servicios', 'servicios_detallados', 'equipo_medico', 'convenios', 'cobertura_zona'];
            foreach ($jsonFields as $field) {
                if (isset($veterinariaData[$field]) && is_array($veterinariaData[$field])) {
                    $veterinariaData[$field] = json_encode($veterinariaData[$field], JSON_UNESCAPED_UNICODE);
                }
            }

            $veterinaria->update($veterinariaData);
        }

        return $this->getCompleteProfile($user);
    }

    public function updateGeneralInfo(User $user, array $data)
    {
        $veterinaria = $user->veterinaria;
        $veterinaria->update(array_intersect_key($data, array_flip([
            'Nombre_vet', 'descripcion', 'anios_experiencia', 'Direccion',
            'ciudad', 'departamento', 'Email', 'whatsapp', 'sitio_web',
            'precio_consulta', 'acepta_seguros'
        ])));

        return $veterinaria;
    }

    public function updateServices(User $user, array $data)
    {
        $veterinaria = $user->veterinaria;

        if (isset($data['servicios']) && is_array($data['servicios'])) {
            $data['servicios'] = json_encode($data['servicios'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($data['servicios_detallados']) && is_array($data['servicios_detallados'])) {
            $data['servicios_detallados'] = json_encode($data['servicios_detallados'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($data['equipo_medico']) && is_array($data['equipo_medico'])) {
            $data['equipo_medico'] = json_encode($data['equipo_medico'], JSON_UNESCAPED_UNICODE);
        }

        $veterinaria->update($data);
        return $veterinaria;
    }

    public function updateSchedule(User $user, ?string $schedule, bool $urgencias24h)
    {
        $veterinaria = $user->veterinaria;
        $veterinaria->update([
            'horario_atencion' => $schedule,
            'urgencias_24h' => $urgencias24h
        ]);

        return $veterinaria;
    }

    public function uploadLogo(User $user, $logoFile)
    {
        $veterinaria = $user->veterinaria;

        if ($veterinaria->logo_public_id) {
            $this->deleteImage($veterinaria->logo_public_id);
        }

        $result = $this->uploadImage($logoFile, 'veterinarias/logos');

        $veterinaria->update([
            'logo' => $result,
            'logo_public_id' => $result
        ]);

        return $veterinaria;
    }

    public function deleteLogo(User $user)
    {
        $veterinaria = $user->veterinaria;

        if ($veterinaria->logo_public_id) {
            $this->deleteImage($veterinaria->logo_public_id);
        }

        $veterinaria->update([
            'logo' => null,
            'logo_public_id' => null
        ]);

        return $veterinaria;
    }

    public function addGalleryPhotos(User $user, array $photos)
    {
        $veterinaria = $user->veterinaria;
        $currentGallery = $veterinaria->galeria_fotos ?? [];

        if (is_string($currentGallery)) {
            $currentGallery = json_decode($currentGallery, true) ?? [];
        }

        foreach ($photos as $photo) {
            $url = $this->uploadImage($photo, 'veterinarias/galeria');
            $currentGallery[] = $url;
        }

        $veterinaria->update([
            'galeria_fotos' => json_encode($currentGallery, JSON_UNESCAPED_UNICODE)
        ]);

        return $veterinaria;
    }

    public function removeGalleryPhoto(User $user, string $photoUrl)
    {
        $veterinaria = $user->veterinaria;
        $currentGallery = $veterinaria->galeria_fotos ?? [];

        if (is_string($currentGallery)) {
            $currentGallery = json_decode($currentGallery, true) ?? [];
        }

        // Eliminar de Cloudinary
        $this->deleteImage($photoUrl);

        // Eliminar del array
        $currentGallery = array_filter($currentGallery, fn($url) => $url !== $photoUrl);

        $veterinaria->update([
            'galeria_fotos' => json_encode(array_values($currentGallery), JSON_UNESCAPED_UNICODE)
        ]);

        return $veterinaria;
    }
}
