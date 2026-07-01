<?php

namespace App\Services\Public;

use App\Models\Mascota;

class AdopcionPublicService
{
    public function getMascotasDisponibles(array $filters = [], int $perPage = 15)
    {
        $query = Mascota::query()
            ->selectFields()
            ->with('fundacion:id,Nombre_1,imagen_portada,ciudad')
            ->where('estado', 'En adopcion');

        $reiniciarFiltros = isset($filters['reiniciar_filtros']) && filter_var($filters['reiniciar_filtros'], FILTER_VALIDATE_BOOLEAN);

        if (!empty($filters['buscar'])) {
            $buscar = trim($filters['buscar']);
            $query->where(function($q) use ($buscar) {
                $q->where('nombre_mascota', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%")
                  ->orWhere('especie', 'like', "%{$buscar}%")
                  ->orWhere('lugar_rescate', 'like', "%{$buscar}%");
            });
            $query->orderByRaw('nombre_mascota = ? DESC', [$buscar]);
        }

        if (!$reiniciarFiltros && !empty($filters['especie'])) {
            $query->where('especie', $filters['especie']);
        }

        if (!$reiniciarFiltros && !empty($filters['genero'])) {
            $query->where('genero', $filters['genero']);
        }

        if (!$reiniciarFiltros && !empty($filters['tamano'])) {
            $query->where('tamano', $filters['tamano']);
        }

        if (!$reiniciarFiltros && !empty($filters['apto_con_ninos'])) {
            $query->where('apto_con_ninos', true);
        }

        if (!$reiniciarFiltros && !empty($filters['apto_con_otros_animales'])) {
            $query->where('apto_con_otros_animales', true);
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

        $nombres = Mascota::query()
            ->where('nombre_mascota', 'like', "%{$searchTerm}%")
            ->whereNotNull('nombre_mascota')
            ->limit($limit)
            ->pluck('nombre_mascota')
            ->filter()
            ->values();
        $results = $results->merge($nombres);

        $especies = Mascota::query()
            ->where('especie', 'like', "%{$searchTerm}%")
            ->whereNotNull('especie')
            ->limit($limit)
            ->pluck('especie')
            ->filter()
            ->values();
        $results = $results->merge($especies);

        return $results->unique()->values()->take($limit)->toArray();
    }

    public function findMascotaDisponible(int $id): Mascota
    {
        return Mascota::query()
            ->selectFields()
            ->with(['fundacion:id,Nombre_1,Direccion,Telefono,Email,ciudad,imagen_portada,verificado', 'razas:id,nombre_raza', 'vacunas:id,nombre_vacuna', 'historialMedico:id,mascota_id,fecha,descripcion'])
            ->where('estado', 'En adopcion')
            ->findOrFail($id);
    }

    public function verificarDisponibilidad(int $id): array
    {
        $mascota = Mascota::find($id);

        if (!$mascota) {
            return [
                'success' => false,
                'message' => 'Mascota no encontrada'
            ];
        }

        return [
            'success' => true,
            'data' => [
                'disponible' => $mascota->estado === 'En adopcion',
                'estado' => $mascota->estado,
                'nombre' => $mascota->nombre_mascota,
                'edad' => $mascota->edad_aprox,
                'tamano' => $mascota->tamano,
                'esterilizado' => $mascota->esterilizado,
                'vacunado' => $mascota->vacunado,
            ]
        ];
    }
    public function getDestacadas(int $limit = 6)
    {
        return Mascota::query()
            ->selectFields()
            ->with(['fundacion:id,Nombre_1,imagen_portada,ciudad', 'razas:id,nombre_raza'])
            ->where('estado', 'En adopcion')
            ->where('destacada', true)
            ->latest()
            ->limit($limit)
            ->get();
    }
}
