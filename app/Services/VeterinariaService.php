<?php

namespace App\Services;

use App\Models\Veterinaria;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VeterinariaService
{
    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Veterinaria::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('Nombre_vet', 'like', "%{$search}%")
                  ->orWhere('Email', 'like', "%{$search}%")
                  ->orWhere('Telefono', 'like', "%{$search}%")
                  ->orWhere('Direccion', 'like', "%{$search}%");
            });
        }

        if (isset($filters['urgencias_24h'])) {
            $query->where('urgencias_24h', $filters['urgencias_24h']);
        }

        return $query->orderBy('Nombre_vet')->paginate($perPage);
    }

    public function getEstadisticas(): array
    {
        return [
            'total' => Veterinaria::count(),
            'urgencias_24h' => Veterinaria::where('urgencias_24h', true)->count(),
            'servicios_comunes' => $this->obtenerServiciosComunes(),
        ];
    }

    private function obtenerServiciosComunes(): array
    {
        $todosServicios = [];
        $veterinarias = Veterinaria::whereNotNull('servicios')->get(['servicios']);

        foreach ($veterinarias as $vet) {
            $servicios = json_decode($vet->servicios, true) ?? [];
            $todosServicios = array_merge($todosServicios, $servicios);
        }

        $conteo = array_count_values($todosServicios);
        arsort($conteo);

        return array_slice($conteo, 0, 5, true);
    }

    public function findById(int $id): Veterinaria
    {
        return Veterinaria::with(['rescates', 'historialMedico'])->findOrFail($id);
    }

    public function getDetalleEstadisticas(Veterinaria $veterinaria): array
    {
        return [
            'rescates_atendidos' => $veterinaria->rescates()->count(),
            'consultas_realizadas' => $veterinaria->historialMedico()->count(),
            'ultimo_rescate' => $veterinaria->rescates()->latest()->first()?->fecha_rescate,
            'total_mascotas_atendidas' => $veterinaria->historialMedico()->distinct('mascota_id')->count('mascota_id'),
        ];
    }

    public function create(array $data): Veterinaria
    {
        if (isset($data['servicios']) && is_array($data['servicios'])) {
            $data['servicios'] = json_encode($data['servicios'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['convenios']) && is_array($data['convenios'])) {
            $data['convenios'] = json_encode($data['convenios'], JSON_UNESCAPED_UNICODE);
        }

        return Veterinaria::create($data);
    }

    public function update(int $id, array $data): Veterinaria
    {
        $veterinaria = Veterinaria::findOrFail($id);

        if (isset($data['servicios']) && is_array($data['servicios'])) {
            $data['servicios'] = json_encode($data['servicios'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['convenios']) && is_array($data['convenios'])) {
            $data['convenios'] = json_encode($data['convenios'], JSON_UNESCAPED_UNICODE);
        }

        $veterinaria->update($data);
        return $veterinaria;
    }

    public function delete(int $id): void
    {
        $veterinaria = Veterinaria::findOrFail($id);

        if ($veterinaria->rescates()->exists()) {
            throw new \Exception('No se puede eliminar la veterinaria porque tiene rescates asociados');
        }

        if ($veterinaria->historialMedico()->exists()) {
            throw new \Exception('No se puede eliminar la veterinaria porque tiene historial médico asociado');
        }

        $veterinaria->delete();
    }

    public function getCercanas(float $lat, float $lng, int $radio = 10)
    {
        return Veterinaria::selectRaw(
            "*, (6371 * acos(cos(radians(?)) * cos(radians(latitud)) * cos(radians(longitud) - radians(?)) + sin(radians(?)) * sin(radians(latitud)))) AS distancia",
            [$lat, $lng, $lat]
        )
        ->whereNotNull('latitud')
        ->whereNotNull('longitud')
        ->having('distancia', '<=', $radio)
        ->orderBy('distancia')
        ->get();
    }
}
