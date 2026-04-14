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

        $user->tokens()->delete();
        $user->delete();
    }
}
