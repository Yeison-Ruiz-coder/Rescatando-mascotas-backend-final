<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Fundacion;
use App\Models\Veterinaria;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthService
{
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new \Exception('Credenciales incorrectas');
        }

        if ($user->estado !== 'activo') {
            $mensaje = match($user->estado) {
                'pendiente' => 'Tu cuenta está pendiente de aprobación por un administrador',
                'inactivo' => 'Tu cuenta está inactiva',
                'suspendido' => 'Tu cuenta ha sido suspendida',
                default => 'No puedes iniciar sesión'
            };
            throw new \Exception($mensaje);
        }

        $this->autoRepararPerfil($user);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user->load(['fundacion', 'veterinaria'])
        ];
    }

    public function register(array $data): array
    {
        $user = User::create([
            'nombre' => $data['nombre'],
            'apellidos' => $data['apellidos'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'telefono' => $data['telefono'] ?? null,
            'tipo' => $data['tipo'],
            'estado' => $data['tipo'] === 'user' ? 'activo' : 'pendiente',
        ]);

        if ($data['tipo'] === 'fundacion') {
            Fundacion::create([
                'Nombre_1' => $data['nombre_entidad'] ?? $data['nombre'],
                'Direccion' => $data['direccion'] ?? null,
                'Telefono' => $data['telefono'] ?? null,
                'Email' => $data['email'],
                'registro_sanitario' => $data['registro_sanitario'] ?? null,
                'capacidad_maxima' => $data['capacidad'] ?? null,
                'user_id' => $user->id,
            ]);
        } elseif ($data['tipo'] === 'veterinaria') {
            Veterinaria::create([
                'Nombre_vet' => $data['nombre_entidad'] ?? $data['nombre'],
                'Direccion' => $data['direccion'] ?? null,
                'Telefono' => $data['telefono'] ?? null,
                'Email' => $data['email'],
                'servicios' => isset($data['servicios']) ? json_encode($data['servicios']) : null,
                'user_id' => $user->id,
            ]);
        }

        $token = $data['tipo'] === 'user' ? $user->createToken('auth_token')->plainTextToken : null;

        return [
            'token' => $token,
            'user' => $user->load(['fundacion', 'veterinaria']),
            'requiere_aprobacion' => $data['tipo'] !== 'user'
        ];
    }

    public function logout($user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function checkEmail(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    public function sendPasswordResetLink(string $email): string
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw new \Exception(__($status));
        }

        return __($status);
    }

    public function resetPassword(array $data): string
    {
        $status = Password::reset(
            $data,
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new \Exception(__($status));
        }

        return __($status);
    }

    private function autoRepararPerfil(User $user): void
    {
        if ($user->tipo === 'fundacion') {
            $fundacion = Fundacion::where('user_id', $user->id)->first();
            if (!$fundacion) {
                Fundacion::create([
                    'Nombre_1' => $user->nombre ?? 'Fundación ' . $user->email,
                    'Direccion' => $user->direccion ?? 'Pendiente de actualizar',
                    'Telefono' => $user->telefono ?? '000000000',
                    'Email' => $user->email,
                    'registro_sanitario' => 'AUTO_' . time() . '_' . $user->id,
                    'user_id' => $user->id,
                ]);
            }
        }

        if ($user->tipo === 'veterinaria') {
            $veterinaria = Veterinaria::where('user_id', $user->id)->first();
            if (!$veterinaria) {
                Veterinaria::create([
                    'Nombre_vet' => $user->nombre ?? 'Veterinaria ' . $user->email,
                    'Direccion' => $user->direccion ?? 'Pendiente',
                    'Telefono' => $user->telefono ?? '000000000',
                    'Email' => $user->email,
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
