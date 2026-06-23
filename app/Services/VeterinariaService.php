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
            $query->where(function ($q) use ($search) {
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
        // Procesar arrays a JSON
        if (isset($data['servicios']) && is_array($data['servicios'])) {
            $data['servicios'] = json_encode($data['servicios'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['servicios_detallados']) && is_array($data['servicios_detallados'])) {
            $data['servicios_detallados'] = json_encode($data['servicios_detallados'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['convenios']) && is_array($data['convenios'])) {
            $data['convenios'] = json_encode($data['convenios'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['redes_sociales']) && is_array($data['redes_sociales'])) {
            $data['redes_sociales'] = json_encode($data['redes_sociales'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['equipo_medico']) && is_array($data['equipo_medico'])) {
            $data['equipo_medico'] = json_encode($data['equipo_medico'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['documentos_verificacion']) && is_array($data['documentos_verificacion'])) {
            $data['documentos_verificacion'] = json_encode($data['documentos_verificacion'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['cobertura_zona']) && is_array($data['cobertura_zona'])) {
            $data['cobertura_zona'] = json_encode($data['cobertura_zona'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['galeria_fotos']) && is_array($data['galeria_fotos'])) {
            $data['galeria_fotos'] = json_encode($data['galeria_fotos'], JSON_UNESCAPED_UNICODE);
        }

        // Compatibilidad con latitud/longitud
        if (isset($data['latitud']) && !isset($data['lat'])) {
            $data['lat'] = $data['latitud'];
        }
        if (isset($data['longitud']) && !isset($data['lng'])) {
            $data['lng'] = $data['longitud'];
        }

        // Valores por defecto
        $data['verificado'] = $data['verificado'] ?? false;
        $data['acepta_seguros'] = $data['acepta_seguros'] ?? false;
        $data['radio_atencion'] = $data['radio_atencion'] ?? 10;

        return Veterinaria::create($data);
    }

    public function update(int $id, array $data): Veterinaria
    {
        $veterinaria = Veterinaria::findOrFail($id);

        if (isset($data['servicios']) && is_array($data['servicios'])) {
            $data['servicios'] = json_encode($data['servicios'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['servicios_detallados']) && is_array($data['servicios_detallados'])) {
            $data['servicios_detallados'] = json_encode($data['servicios_detallados'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['convenios']) && is_array($data['convenios'])) {
            $data['convenios'] = json_encode($data['convenios'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['redes_sociales']) && is_array($data['redes_sociales'])) {
            $data['redes_sociales'] = json_encode($data['redes_sociales'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['equipo_medico']) && is_array($data['equipo_medico'])) {
            $data['equipo_medico'] = json_encode($data['equipo_medico'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['documentos_verificacion']) && is_array($data['documentos_verificacion'])) {
            $data['documentos_verificacion'] = json_encode($data['documentos_verificacion'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['cobertura_zona']) && is_array($data['cobertura_zona'])) {
            $data['cobertura_zona'] = json_encode($data['cobertura_zona'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['galeria_fotos']) && is_array($data['galeria_fotos'])) {
            $data['galeria_fotos'] = json_encode($data['galeria_fotos'], JSON_UNESCAPED_UNICODE);
        }

        // Compatibilidad con latitud/longitud
        if (isset($data['latitud']) && !isset($data['lat'])) {
            $data['lat'] = $data['latitud'];
        }
        if (isset($data['longitud']) && !isset($data['lng'])) {
            $data['lng'] = $data['longitud'];
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
            "*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distancia",
            [$lat, $lng, $lat]
        )
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->having('distancia', '<=', $radio)
            ->orderBy('distancia')
            ->get();
    }
}
