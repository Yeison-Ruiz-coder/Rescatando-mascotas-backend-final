<?php

namespace App\Services\Public;

use App\Models\Mascota;
use Illuminate\Support\Facades\Log;

class MascotaPublicService
{
    /**
     * Estados permitidos para mostrar al público
     */
    private function getEstadosDisponibles(): array
    {
        return ['En adopcion', 'En acogida'];
    }

    public function getAll(array $filters = [], int $perPage = 15)
    {
        try {
            $query = Mascota::query()
                ->selectFields()
                ->with([
                    'fundacion:id,Nombre_1,imagen_portada,ciudad',
                    'razas:id,nombre_raza',
                ])
                ->whereIn('estado', $this->getEstadosDisponibles());

            if (!empty($filters['especie'])) {
                $query->where('especie', $filters['especie']);
            }

            if (!empty($filters['fundacion_id'])) {
                $query->where('fundacion_id', $filters['fundacion_id']);
            }

            if (!empty($filters['genero'])) {
                $query->where('genero', $filters['genero']);
            }

            if (!empty($filters['tamano'])) {
                $query->where('tamano', $filters['tamano']);
            }

            if (!empty($filters['destacada'])) {
                $query->where('destacada', true);
            }

            if (!empty($filters['buscar'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('nombre_mascota', 'like', '%' . $filters['buscar'] . '%')
                        ->orWhere('descripcion', 'like', '%' . $filters['buscar'] . '%');
                });
            }

            if (isset($filters['apto_con_ninos'])) {
                $query->where('apto_con_ninos', $filters['apto_con_ninos']);
            }

            if (isset($filters['apto_con_otros_animales'])) {
                $query->where('apto_con_otros_animales', $filters['apto_con_otros_animales']);
            }

            if (!empty($filters['exclude_id'])) {
                $query->where('id', '!=', $filters['exclude_id']);
            }

            // Ordenamiento
            $orderBy = $filters['order_by'] ?? 'created_at';
            $orderDir = $filters['order_dir'] ?? 'desc';
            $query->orderBy($orderBy, $orderDir);

            return $query->paginate($perPage);
        } catch (\Throwable $e) {
            Log::error('MascotaPublicService@getAll error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'filters' => $filters,
                'perPage' => $perPage,
            ]);
            throw $e;
        }
    }

    public function findById(int $id): Mascota
    {
        return Mascota::query()
            ->selectFields()
            ->with([
                'fundacion:id,Nombre_1,Direccion,Telefono,Email,ciudad,imagen_portada,verificado',
                'razas:id,nombre_raza',
            ])
            ->findOrFail($id);
    }

    public function getPorEspecie(string $especie, int $perPage = 15)
    {
        return Mascota::query()
            ->selectFields()
            ->with('fundacion:id,Nombre_1,imagen_portada,ciudad')
            ->where('especie', $especie)
            ->whereIn('estado', $this->getEstadosDisponibles())
            ->paginate($perPage);
    }

    public function getPorFundacion(int $fundacionId, int $perPage = 15)
    {
        return Mascota::query()
            ->selectFields()
            ->with('fundacion:id,Nombre_1,imagen_portada,ciudad')
            ->where('fundacion_id', $fundacionId)
            ->whereIn('estado', $this->getEstadosDisponibles())
            ->paginate($perPage);
    }

    public function getDestacadas(int $limit = 6)
    {
        return Mascota::query()
            ->selectFields()
            ->with([
                'fundacion:id,Nombre_1,imagen_portada,ciudad',
                'razas:id,nombre_raza',
            ])
            ->whereIn('estado', $this->getEstadosDisponibles())
            ->where('destacada', true)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function getEspeciesUnicas()
    {
        $especies = Mascota::query()
            ->whereIn('estado', $this->getEstadosDisponibles())
            ->whereNotNull('especie')
            ->distinct()
            ->pluck('especie')
            ->filter()
            ->values()
            ->toArray();

        return $especies;
    }

    /**
     * Obtener sugerencias para autocompletado
     * Busca en: nombre_mascota, especie, color, lugar_rescate
     *
     * @param string $searchTerm Término de búsqueda
     * @param int $limit Límite de resultados
     * @return array
     */
    public function getSugerencias(string $searchTerm, int $limit = 10): array
    {
        // Si la búsqueda es muy corta, retornar vacío
        if (strlen($searchTerm) < 2) {
            return [];
        }

        try {
            $results = collect();

            // Buscar por nombre
            $nombres = Mascota::query()
                ->whereIn('estado', $this->getEstadosDisponibles())
                ->where('nombre_mascota', 'LIKE', "%{$searchTerm}%")
                ->whereNotNull('nombre_mascota')
                ->limit($limit)
                ->pluck('nombre_mascota')
                ->filter()
                ->values()
                ->toArray();
            $results = $results->merge($nombres);

            // Buscar por especie
            $especies = Mascota::query()
                ->whereIn('estado', $this->getEstadosDisponibles())
                ->where('especie', 'LIKE', "%{$searchTerm}%")
                ->whereNotNull('especie')
                ->limit($limit)
                ->pluck('especie')
                ->filter()
                ->values()
                ->toArray();
            $results = $results->merge($especies);

            // Buscar por color
            $colores = Mascota::query()
                ->whereIn('estado', $this->getEstadosDisponibles())
                ->where('color', 'LIKE', "%{$searchTerm}%")
                ->whereNotNull('color')
                ->limit($limit)
                ->pluck('color')
                ->filter()
                ->values()
                ->toArray();
            $results = $results->merge($colores);

            // Buscar por lugar_rescate
            $lugares = Mascota::query()
                ->whereIn('estado', $this->getEstadosDisponibles())
                ->where('lugar_rescate', 'LIKE', "%{$searchTerm}%")
                ->whereNotNull('lugar_rescate')
                ->limit($limit)
                ->pluck('lugar_rescate')
                ->filter()
                ->values()
                ->toArray();
            $results = $results->merge($lugares);

            // Eliminar duplicados y limitar
            return $results
                ->unique()
                ->values()
                ->take($limit)
                ->toArray();

        } catch (\Throwable $e) {
            Log::error('MascotaPublicService@getSugerencias error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'searchTerm' => $searchTerm,
            ]);
            return [];
        }
    }
}
