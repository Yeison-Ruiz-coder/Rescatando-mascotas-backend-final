<?php

namespace App\Services\User;

use App\Models\User;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Hash;

class ProfileUserService
{
    use ImageUploadTrait;

    public function getProfile(User $user): User
    {
        return $user->load(['solicitudes', 'adopciones', 'donaciones', 'suscripciones']);
    }

    public function updateProfile(User $user, array $data, $avatar = null): User
    {
        if ($avatar) {
            if ($user->avatar) {
                $this->deleteImage($user->avatar);
            }
            $data['avatar'] = $this->uploadImage($avatar, 'avatars');
        }

        // Procesar redes sociales como JSON
        if (isset($data['redes_sociales']) && is_array($data['redes_sociales'])) {
            $data['redes_sociales'] = json_encode($data['redes_sociales'], JSON_UNESCAPED_UNICODE);
        }

        // Procesar preferencias de notificaciones
        if (isset($data['preferencias_notificaciones']) && is_array($data['preferencias_notificaciones'])) {
            $data['preferencias_notificaciones'] = json_encode($data['preferencias_notificaciones'], JSON_UNESCAPED_UNICODE);
        }

        $user->update($data);
        return $user;
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new \Exception('La contraseña actual no es correcta');
        }

        $user->update([
            'password' => Hash::make($newPassword)
        ]);
    }

    public function deleteAccount(User $user): void
    {
        if ($user->adopciones()->whereIn('estado', ['en_proceso', 'aprobada'])->exists()) {
            throw new \Exception('No puedes eliminar tu cuenta mientras tengas adopciones en proceso');
        }

        if ($user->solicitudes()->whereIn('estado', ['pendiente', 'en_revision'])->exists()) {
            throw new \Exception('No puedes eliminar tu cuenta mientras tengas solicitudes pendientes');
        }

        // Eliminar avatar si existe
        if ($user->avatar) {
            $this->deleteImage($user->avatar);
        }

        $user->tokens()->delete();
        $user->delete();
    }
    public function updatePreferences(User $user, array $data): User
    {
        if (isset($data['preferencias_notificaciones']) && is_array($data['preferencias_notificaciones'])) {
            $data['preferencias_notificaciones'] = json_encode($data['preferencias_notificaciones'], JSON_UNESCAPED_UNICODE);
        }

        $user->update($data);
        return $user;
    }
}
