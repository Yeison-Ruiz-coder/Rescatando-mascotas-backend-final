<?php

namespace App\Services;

use App\Models\User;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UsuarioService
{
    use ImageUploadTrait;

    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = User::query();
        $reiniciarFiltros = isset($filters['reiniciar_filtros']) && filter_var($filters['reiniciar_filtros'], FILTER_VALIDATE_BOOLEAN);

        if (!$reiniciarFiltros && !empty($filters['tipo'])) {
            $query->where('tipo', $filters['tipo']);
        }

        if (!$reiniciarFiltros && !empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['buscar'])) {
            $buscar = trim($filters['buscar']);

            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('apellidos', 'like', "%{$buscar}%")
                    ->orWhere('email', 'like', "%{$buscar}%");
            });
            $query->orderByRaw('nombre = ? DESC', [$buscar]);
        }

        return $query->latest()->paginate($perPage);
    }

    public function getSugerencias(string $searchTerm, int $limit = 10): array
    {
        $searchTerm = trim($searchTerm);

        if (strlen($searchTerm) < 2) {
            return [];
        }

        $results = collect();

        $nombres = User::query()
            ->where('nombre', 'like', "%{$searchTerm}%")
            ->whereNotNull('nombre')
            ->limit($limit)
            ->pluck('nombre')
            ->filter()
            ->values();
        $results = $results->merge($nombres);

        $apellidos = User::query()
            ->where('apellidos', 'like', "%{$searchTerm}%")
            ->whereNotNull('apellidos')
            ->limit($limit)
            ->pluck('apellidos')
            ->filter()
            ->values();
        $results = $results->merge($apellidos);

        $emails = User::query()
            ->where('email', 'like', "%{$searchTerm}%")
            ->whereNotNull('email')
            ->limit($limit)
            ->pluck('email')
            ->filter()
            ->values();
        $results = $results->merge($emails);

        return $results->unique()->values()->take($limit)->toArray();
    }

    public function findById(int $id): User
    {
        return User::with(['solicitudes', 'adopciones', 'donaciones', 'suscripciones', 'fundacion', 'veterinaria'])
            ->findOrFail($id);
    }

    public function create(array $data, $avatar = null): User
    {
        if ($avatar) {
            $data['avatar'] = $this->uploadImage($avatar, 'avatars');
        }

        $data['password'] = Hash::make($data['password']);
        $data['created_by'] = auth()->id();

        // Procesar campos JSON
        if (isset($data['redes_sociales']) && is_array($data['redes_sociales'])) {
            $data['redes_sociales'] = json_encode($data['redes_sociales'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['preferencias_notificaciones']) && is_array($data['preferencias_notificaciones'])) {
            $data['preferencias_notificaciones'] = json_encode($data['preferencias_notificaciones'], JSON_UNESCAPED_UNICODE);
        }

        // Valores por defecto
        $data['estado'] = $data['estado'] ?? 'pendiente';
        $data['idioma'] = $data['idioma'] ?? 'es';
        $data['tema'] = $data['tema'] ?? 'light';

        return User::create($data);
    }

    public function update(int $id, array $data, $avatar = null): User
    {
        $user = User::findOrFail($id);

        if ($avatar) {
            $data['avatar'] = $this->uploadImage($avatar, 'avatars', $user->avatar);
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Procesar campos JSON
        if (isset($data['redes_sociales']) && is_array($data['redes_sociales'])) {
            $data['redes_sociales'] = json_encode($data['redes_sociales'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['preferencias_notificaciones']) && is_array($data['preferencias_notificaciones'])) {
            $data['preferencias_notificaciones'] = json_encode($data['preferencias_notificaciones'], JSON_UNESCAPED_UNICODE);
        }

        $data['updated_by'] = auth()->id();

        $user->update($data);
        return $user;
    }

    public function updateProfile(int $id, array $data, $avatar = null): User
    {
        $user = User::findOrFail($id);

        if ($avatar) {
            $data['avatar'] = $this->uploadImage($avatar, 'avatars', $user->avatar);
        }

        // Campos permitidos para que el usuario actualice su propio perfil
        $permitidos = [
            'nombre', 'apellidos', 'telefono', 'direccion', 'fecha_nacimiento',
            'biografia', 'redes_sociales', 'pais', 'ciudad', 'codigo_postal',
            'idioma', 'tema', 'preferencias_notificaciones'
        ];

        $datosActualizar = array_intersect_key($data, array_flip($permitidos));

        if (isset($datosActualizar['redes_sociales']) && is_array($datosActualizar['redes_sociales'])) {
            $datosActualizar['redes_sociales'] = json_encode($datosActualizar['redes_sociales'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($datosActualizar['preferencias_notificaciones']) && is_array($datosActualizar['preferencias_notificaciones'])) {
            $datosActualizar['preferencias_notificaciones'] = json_encode($datosActualizar['preferencias_notificaciones'], JSON_UNESCAPED_UNICODE);
        }

        $user->update($datosActualizar);
        return $user;
    }

    public function delete(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->adopciones()->exists() ||
            $user->solicitudes()->exists() ||
            $user->donaciones()->exists()) {
            throw new \Exception('No se puede eliminar: tiene registros asociados');
        }

        if ($user->avatar) {
            $this->deleteImage($user->avatar);
        }

        $user->delete();
    }

    public function cambiarEstado(int $id, string $estado): User
    {
        $user = User::findOrFail($id);
        $user->update([
            'estado' => $estado,
            'updated_by' => auth()->id()
        ]);
        return $user;
    }

    public function verificarEmail(int $id): User
    {
        $user = User::findOrFail($id);
        $user->update([
            'email_verified_at' => now(),
            'updated_by' => auth()->id()
        ]);
        return $user;
    }

    public function registrarAcceso(int $id, string $ip): void
    {
        User::where('id', $id)->update([
            'ultimo_acceso' => now(),
            'ultima_ip' => $ip
        ]);
    }

    public function getPendientesCount(): int
    {
        return User::where('estado', 'pendiente')
            ->whereIn('tipo', ['fundacion', 'veterinaria'])
            ->count();
    }
}
