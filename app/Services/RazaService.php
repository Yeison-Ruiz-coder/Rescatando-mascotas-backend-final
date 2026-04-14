<?php

namespace App\Services;

use App\Models\Raza;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RazaService
{
    public function getAll(array $filters = [], int $perPage = 20)
    {
        $query = Raza::query();

        if (!empty($filters['especie'])) {
            $query->where('especie', $filters['especie']);
        }

        if (!empty($filters['search'])) {
            $query->where('nombre_raza', 'like', "%{$filters['search']}%");
        }

        return $query->orderBy('especie')->orderBy('nombre_raza')->paginate($perPage);
    }

    public function getEstadisticas(): array
    {
        return [
            'total' => Raza::count(),
            'perros' => Raza::where('especie', 'Perro')->count(),
            'gatos' => Raza::where('especie', 'Gato')->count(),
            'otros' => Raza::whereNotIn('especie', ['Perro', 'Gato'])->orWhereNull('especie')->count(),
        ];
    }

    public function findById(int $id): Raza
    {
        return Raza::with('mascotas')->findOrFail($id);
    }

    public function getMascotasEstadisticas(Raza $raza): array
    {
        $mascotas = $raza->mascotas;

        return [
            'total_mascotas' => $mascotas->count(),
            'adoptadas' => $mascotas->where('estado', 'Adoptado')->count(),
            'disponibles' => $mascotas->where('estado', 'En adopcion')->count(),
            'rescatadas' => $mascotas->where('estado', 'Rescatada')->count(),
        ];
    }

    public function create(array $data): Raza
    {
        return Raza::create($data);
    }

    public function update(int $id, array $data): Raza
    {
        $raza = Raza::findOrFail($id);
        $raza->update($data);
        return $raza;
    }

    public function delete(int $id): void
    {
        $raza = Raza::findOrFail($id);

        if ($raza->mascotas()->exists()) {
            throw new \Exception('No se puede eliminar la raza porque tiene mascotas asociadas');
        }

        $raza->delete();
    }

    public function getPorEspecie(string $especie): array
    {
        return Raza::where('especie', $especie)
            ->orderBy('nombre_raza')
            ->get(['id', 'nombre_raza'])
            ->toArray();
    }

    public function getEspecies(): array
    {
        return Raza::whereNotNull('especie')
            ->distinct('especie')
            ->pluck('especie')
            ->filter()
            ->values()
            ->toArray();
    }
}
