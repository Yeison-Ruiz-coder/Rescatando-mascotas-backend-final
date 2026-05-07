<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Fundacion;
use App\Models\Veterinaria;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
        $userData = [
            'nombre' => $data['nombre'],
            'apellidos' => $data['apellidos'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'telefono' => $data['telefono'] ?? null,
            'tipo' => $data['tipo'],
            'estado' => $data['tipo'] === 'user' ? 'activo' : 'pendiente',
        ];

        // Agregar campos opcionales si vienen
        if (isset($data['tipo_documento'])) {
            $userData['tipo_documento'] = $data['tipo_documento'];
        }
        if (isset($data['numero_documento'])) {
            $userData['numero_documento'] = $data['numero_documento'];
        }
        if (isset($data['fecha_nacimiento'])) {
            $userData['fecha_nacimiento'] = $data['fecha_nacimiento'];
        }
        if (isset($data['direccion'])) {
            $userData['direccion'] = $data['direccion'];
        }
        if (isset($data['pais'])) {
            $userData['pais'] = $data['pais'];
        }
        if (isset($data['ciudad'])) {
            $userData['ciudad'] = $data['ciudad'];
        }

        $user = User::create($userData);

        // Crear perfil según tipo
        if ($data['tipo'] === 'fundacion') {
            $fundacionData = [
                'Nombre_1' => $data['nombre_entidad'] ?? $data['nombre'],
                'Direccion' => $data['direccion'] ?? null,
                'Telefono' => $data['telefono'] ?? null,
                'Email' => $data['email'],
                'registro_sanitario' => $data['registro_sanitario'] ?? null,
                'capacidad_maxima' => $data['capacidad'] ?? null,
                'user_id' => $user->id,
                'ciudad' => $data['ciudad'] ?? null,
                'descripcion' => $data['descripcion'] ?? null,
                'horario_atencion' => $data['horario_atencion'] ?? null,
            ];

            // Agregar lat/lng si vienen
            if (isset($data['lat'])) {
                $fundacionData['lat'] = $data['lat'];
            }
            if (isset($data['lng'])) {
                $fundacionData['lng'] = $data['lng'];
            }

            Fundacion::create($fundacionData);
        }
        elseif ($data['tipo'] === 'veterinaria') {
            $veterinariaData = [
                'Nombre_vet' => $data['nombre_entidad'] ?? $data['nombre'],
                'Direccion' => $data['direccion'] ?? null,
                'Telefono' => $data['telefono'] ?? null,
                'Email' => $data['email'],
                'servicios' => isset($data['servicios']) ? json_encode($data['servicios']) : null,
                'user_id' => $user->id,
                'ciudad' => $data['ciudad'] ?? null,
                'descripcion' => $data['descripcion'] ?? null,
                'horario_atencion' => $data['horario_atencion'] ?? null,
            ];

            // Agregar lat/lng si vienen
            if (isset($data['lat'])) {
                $veterinariaData['lat'] = $data['lat'];
            }
            if (isset($data['lng'])) {
                $veterinariaData['lng'] = $data['lng'];
            }

            Veterinaria::create($veterinariaData);
        }

        $token = $data['tipo'] === 'user' ? $user->createToken('auth_token')->plainTextToken : null;

        return [
            'token' => $token,
            'user' => $user->load(['fundacion', 'veterinaria']),
            'requiere_aprobacion' => $data['tipo'] !== 'user'
        ];
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }

    public function checkEmail(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    public function sendPasswordResetLink(string $email): string
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new \Exception('No existe un usuario con este correo electrónico');
        }

        $token = Password::createToken($user);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $token, 'created_at' => now()]
        );

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        $resetUrl = $frontendUrl . '/reset-password?token=' . $token . '&email=' . urlencode($email);

        try {
            Mail::send('emails.password-reset', ['resetUrl' => $resetUrl, 'user' => $user], function ($message) use ($email) {
                $message->to($email)->subject('Restablecer contraseña');
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al enviar el correo: ' . $e->getMessage());
        }

        return 'Enlace de restablecimiento enviado a tu correo electrónico';
    }

    public function resetPassword(array $data): string
    {
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->where('token', $data['token'])
            ->first();

        if (!$resetRecord) {
            throw new \Exception('Token inválido o expirado');
        }

        $createdAt = strtotime($resetRecord->created_at);
        if (time() - $createdAt > 3600) {
            throw new \Exception('El enlace ha expirado. Solicita uno nuevo');
        }

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            throw new \Exception('Usuario no encontrado');
        }

        $user->password = Hash::make($data['password']);
        $user->remember_token = Str::random(60);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return 'Contraseña restablecida exitosamente';
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
                    'ciudad' => $user->ciudad ?? null,
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
                    'ciudad' => $user->ciudad ?? null,
                ]);
            }
        }
    }
}
