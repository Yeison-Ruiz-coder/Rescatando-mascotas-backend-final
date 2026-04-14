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

        if (!empty($filters['tipo'])) {
            $query->where('tipo', $filters['tipo']);
        }

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['buscar'])) {
            $query->where(function($q) use ($filters) {
                $q->where('nombre', 'like', "%{$filters['buscar']}%")
                    ->orWhere('apellidos', 'like', "%{$filters['buscar']}%")
                    ->orWhere('email', 'like', "%{$filters['buscar']}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById(int $id): User
    {
        return User::with(['solicitudes', 'adopciones', 'donaciones', 'suscripciones'])
            ->findOrFail($id);
    }

    public function create(array $data, $avatar = null): User
    {
        if ($avatar) {
            $data['avatar'] = $this->uploadImage($avatar, 'avatars');
        }

        $data['password'] = Hash::make($data['password']);
        $data['created_by'] = auth()->id();

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

        $data['updated_by'] = auth()->id();

        $user->update($data);
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

    public function getPendientesCount(): int
    {
        return User::where('estado', 'pendiente')
            ->whereIn('tipo', ['fundacion', 'veterinaria'])
            ->count();
    }
}
