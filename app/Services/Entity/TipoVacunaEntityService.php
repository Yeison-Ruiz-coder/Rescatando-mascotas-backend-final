<?php
// app/Services/Entity/TipoVacunaEntityService.php

namespace App\Services\Entity;

use App\Models\TipoVacuna;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TipoVacunaEntityService
{
    public function getAll(array $filters = [], int $perPage = 20)
    {
        $query = TipoVacuna::query();

        if (!empty($filters['search'])) {
            $query->where('nombre_vacuna', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('nombre_vacuna')->paginate($perPage);
    }

    public function findById(int $id): TipoVacuna
    {
        $tipoVacuna = TipoVacuna::find($id);
        if (!$tipoVacuna) {
            throw new ModelNotFoundException('Tipo de vacuna no encontrada');
        }
        return $tipoVacuna;
    }

    public function create(array $data): TipoVacuna
    {
        return TipoVacuna::create($data);
    }

    public function update(int $id, array $data): TipoVacuna
    {
        $tipoVacuna = $this->findById($id);
        $tipoVacuna->update($data);
        return $tipoVacuna->fresh();
    }

    public function delete(int $id): bool
    {
        $tipoVacuna = $this->findById($id);

        // Verificar si tiene mascotas asociadas
        if ($tipoVacuna->mascotas()->exists()) {
            throw new \Exception('No se puede eliminar el tipo de vacuna porque está siendo utilizado por una o más mascotas');
        }

        return $tipoVacuna->delete();
    }

    public function getRecomendadas(?string $especie = null)
    {
        $query = TipoVacuna::query();

        // Si se especifica especie, se pueden filtrar vacunas recomendadas para esa especie
        // Esto puede expandirse según necesidades

        return $query->orderBy('nombre_vacuna')->get(['id', 'nombre_vacuna', 'frecuencia_dias']);
    }
}
