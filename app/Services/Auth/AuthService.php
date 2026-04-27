<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Fundacion;
use App\Models\Veterinaria;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

    // 🔥 MÉTODO CORREGIDO - Enviar enlace de restablecimiento 🔥
    public function sendPasswordResetLink(string $email): string
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new \Exception('No existe un usuario con este correo electrónico');
        }

        // Crear token manualmente
        $token = Password::createToken($user);
        
        // Guardar el token en la tabla password_reset_tokens
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $token, 'created_at' => now()]
        );

        // URL del frontend
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        $resetUrl = $frontendUrl . '/reset-password?token=' . $token . '&email=' . urlencode($email);

        // Enviar email
        try {
            Mail::send('emails.password-reset', ['resetUrl' => $resetUrl, 'user' => $user], function ($message) use ($email) {
                $message->to($email)
                        ->subject('Restablecer contraseña');
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al enviar el correo: ' . $e->getMessage());
        }

        return 'Enlace de restablecimiento enviado a tu correo electrónico';
    }

    // 🔥 MÉTODO CORREGIDO - Restablecer contraseña 🔥
    public function resetPassword(array $data): string
    {
        // Buscar el token en la tabla
        $resetRecord = \DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->where('token', $data['token'])
            ->first();

        if (!$resetRecord) {
            throw new \Exception('Token inválido o expirado');
        }

        // Verificar expiración (60 minutos)
        $createdAt = strtotime($resetRecord->created_at);
        if (time() - $createdAt > 3600) {
            throw new \Exception('El enlace ha expirado. Solicita uno nuevo');
        }

        // Actualizar la contraseña del usuario
        $user = User::where('email', $data['email'])->first();
        
        if (!$user) {
            throw new \Exception('Usuario no encontrado');
        }

        $user->password = Hash::make($data['password']);
        $user->remember_token = Str::random(60);
        $user->save();

        // Eliminar el token usado
        \DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

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