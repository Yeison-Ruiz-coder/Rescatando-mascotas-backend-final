<?php
// app/Services/Entity/RazaEntityService.php

namespace App\Services\Entity;

use App\Models\Raza;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RazaEntityService
{
    public function getAll(array $filters = [], int $perPage = 20)
    {
        $query = Raza::query();

        if (!empty($filters['especie'])) {
            $query->where('especie', $filters['especie']);
        }

        if (!empty($filters['search'])) {
            $query->where('nombre_raza', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('nombre_raza')->paginate($perPage);
    }

    public function findById(int $id): Raza
    {
        $raza = Raza::find($id);
        if (!$raza) {
            throw new ModelNotFoundException('Raza no encontrada');
        }
        return $raza;
    }

    public function create(array $data): Raza
    {
        return Raza::create($data);
    }

    public function update(int $id, array $data): Raza
    {
        $raza = $this->findById($id);
        $raza->update($data);
        return $raza->fresh();
    }

    public function delete(int $id): bool
    {
        $raza = $this->findById($id);

        // Verificar si tiene mascotas asociadas
        if ($raza->mascotas()->exists()) {
            throw new \Exception('No se puede eliminar la raza porque está siendo utilizada por una o más mascotas');
        }

        return $raza->delete();
    }

    public function getPorEspecie(string $especie)
    {
        return Raza::where('especie', $especie)
            ->orderBy('nombre_raza')
            ->get(['id', 'nombre_raza']);
    }

    public function getEspecies()
    {
        return Raza::whereNotNull('especie')
            ->distinct()
            ->pluck('especie')
            ->toArray();
    }
}
