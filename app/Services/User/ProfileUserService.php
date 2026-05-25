<?php

namespace App\Services\User;

use App\Models\User;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class ProfileUserService
{
    use ImageUploadTrait;

    // ========== MÉTODOS EXISTENTES (NO TOCAR) ==========

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

        if (isset($data['redes_sociales']) && is_array($data['redes_sociales'])) {
            $data['redes_sociales'] = json_encode($data['redes_sociales'], JSON_UNESCAPED_UNICODE);
        }

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

    // ========== NUEVOS MÉTODOS (AGREGAR AL FINAL) ==========

    /**
     * Actualizar avatar específicamente
     */
    public function updateAvatar(User $user, $avatarFile): User
    {
        if ($user->avatar) {
            $this->deleteImage($user->avatar);
        }

        $avatarUrl = $this->uploadImage($avatarFile, 'avatars');

        $user->update([
            'avatar' => $avatarUrl
        ]);

        return $user;
    }

    /**
     * Eliminar avatar
     */
    public function deleteAvatar(User $user): User
    {
        if ($user->avatar) {
            $this->deleteImage($user->avatar);
        }

        $user->update([
            'avatar' => null
        ]);

        return $user;
    }

    /**
     * Actualizar ubicación
     */
    public function updateLocation(User $user, array $location): User
    {
        $user->update($location);
        return $user;
    }

    /**
     * Actualizar solo redes sociales
     */
    public function updateSocialNetworks(User $user, array $networks): User
    {
        $currentNetworks = $user->redes_sociales;

        if (is_string($currentNetworks)) {
            $currentNetworks = json_decode($currentNetworks, true) ?? [];
        }

        if (!is_array($currentNetworks)) {
            $currentNetworks = [];
        }

        $networks = array_filter($networks);
        $updatedNetworks = array_merge($currentNetworks, $networks);

        $user->update([
            'redes_sociales' => $updatedNetworks
        ]);

        return $user;
    }

    /**
     * Calcular porcentaje de completado del perfil
     */
    public function getCompletionStatus(User $user): array
    {
        $requiredFields = ['nombre', 'telefono', 'direccion', 'ciudad'];
        $filledFields = 0;

        foreach ($requiredFields as $field) {
            if (!empty($user->$field)) {
                $filledFields++;
            }
        }

        $percentage = count($requiredFields) > 0
            ? round(($filledFields / count($requiredFields)) * 100)
            : 0;

        // Bonus por verificaciones
        if ($user->telefono_verificado) $percentage += 5;
        if ($user->email_verified_at) $percentage += 5;
        if ($user->documento_verificado) $percentage += 5;

        $percentage = min($percentage, 100);

        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (empty($user->$field)) {
                $missingFields[] = $field;
            }
        }

        if (!$user->telefono_verificado) $missingFields[] = 'phone_verification';

        return [
            'percentage' => $percentage,
            'missing_fields' => $missingFields,
            'is_complete' => $percentage === 100,
            'verification_status' => [
                'email' => !is_null($user->email_verified_at),
                'phone' => (bool) $user->telefono_verificado,
                'document' => (bool) $user->documento_verificado,
            ]
        ];
    }

    /**
     * Enviar código de verificación al teléfono
     */
    public function sendPhoneVerification(User $user): array
    {
        if (!$user->telefono) {
            throw new \Exception('Debes registrar un número de teléfono primero');
        }

        if ($user->telefono_verificado) {
            throw new \Exception('El teléfono ya está verificado');
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put("phone_verification_{$user->id}", $code, 600);

        // TODO: Integrar con servicio SMS real
        // Por ahora, retornar el código para desarrollo
        if (app()->environment('local')) {
            return ['sent' => true, 'debug_code' => $code];
        }

        return ['sent' => true];
    }

    /**
     * Confirmar código de verificación de teléfono
     */
    public function confirmPhone(User $user, string $code): User
    {
        $cachedCode = Cache::get("phone_verification_{$user->id}");

        if (!$cachedCode || $cachedCode !== $code) {
            throw new \Exception('Código de verificación inválido o expirado');
        }

        $user->update([
            'telefono_verificado' => true
        ]);

        Cache::forget("phone_verification_{$user->id}");

        return $user;
    }
}
