<?php

namespace App\Services\Public;

use App\Models\Rescate;
use App\Models\Fundacion;
use App\Models\Veterinaria;
use App\Models\Notificacion;
use Illuminate\Support\Facades\Log;

class RescatePublicService
{
    public function getAll(int $perPage = 15)
    {
        return Rescate::with(['usuarioReporto', 'entidadResponsable'])
            ->where('estado', 'en_proceso')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): Rescate
    {
        return Rescate::with(['usuarioReporto', 'entidadResponsable', 'mascota'])
            ->findOrFail($id);
    }

    public function reportar(array $data, int $userId): Rescate
    {
        $tipoEmergencia = $this->analizarEmergencia($data['descripcion_rescate']);
        $prioridad = $this->calcularPrioridad($tipoEmergencia);

        $rescate = Rescate::create([
            'lugar_rescate' => $data['lugar_rescate'],
            'descripcion_rescate' => $data['descripcion_rescate'],
            'fecha_rescate' => $data['fecha_rescate'],
            'tipo_emergencia' => $tipoEmergencia,
            'prioridad' => $prioridad,
            'lat' => $data['lat'] ?? null,
            'lng' => $data['lng'] ?? null,
            'estado' => 'pendiente',
            'usuario_reporto_id' => $userId,
        ]);

        $this->notificarEntidadesCercanas($rescate);

        return $rescate;
    }

    private function analizarEmergencia(string $descripcion): string
    {
        $descripcion = strtolower($descripcion);

        $palabrasClave = [
            'herido' => ['herido', 'sangra', 'sangrando', 'golpe', 'lastimado', 'fractura', 'hueso roto', 'cojea', 'malherido'],
            'abandonado' => ['abandonado', 'cachorros', 'solo', 'sin dueño', 'vagando', 'callejero', 'botaron', 'dejaron'],
            'urgente' => ['urgente', 'emergencia', 'grave', 'critico', 'inmediato', 'ahora', 'rapido', 'muriendo', 'agonizando']
        ];

        foreach ($palabrasClave['urgente'] as $palabra) {
            if (str_contains($descripcion, $palabra)) {
                return 'urgente';
            }
        }

        foreach ($palabrasClave['herido'] as $palabra) {
            if (str_contains($descripcion, $palabra)) {
                return 'herido';
            }
        }

        foreach ($palabrasClave['abandonado'] as $palabra) {
            if (str_contains($descripcion, $palabra)) {
                return 'abandonado';
            }
        }

        return 'otro';
    }

    private function calcularPrioridad(string $tipoEmergencia): string
    {
        return match($tipoEmergencia) {
            'urgente', 'herido' => 'alta',
            'abandonado' => 'media',
            default => 'baja',
        };
    }

    private function notificarEntidadesCercanas(Rescate $rescate): void
    {
        $tipo = $rescate->tipo_emergencia;
        $lat = $rescate->lat;
        $lng = $rescate->lng;
        $radio = 10;

        $entidades = [];

        if ($tipo === 'urgente' || $tipo === 'herido') {
            $entidades = array_merge($entidades, $this->buscarVeterinariasCercanas($lat, $lng, $radio));
        }

        if ($tipo === 'urgente' || $tipo === 'abandonado') {
            $entidades = array_merge($entidades, $this->buscarFundacionesCercanas($lat, $lng, $radio));
        }

        foreach ($entidades as $entidad) {
            if ($entidad->user_id) {
                Notificacion::create([
                    'user_id' => $entidad->user_id,
                    'contenido' => "Nuevo rescate {$rescate->tipo_emergencia} cerca de ti: {$rescate->lugar_rescate}",
                    'creado_por_id' => 1,
                ]);
            }
        }

        Log::info("Rescate #{$rescate->id} notificado a " . count($entidades) . " entidades");
    }

    private function buscarVeterinariasCercanas($lat, $lng, $radio)
    {
        if (!$lat || !$lng) {
            return Veterinaria::where('urgencias_24h', true)->get();
        }

        return Veterinaria::where('urgencias_24h', true)
            ->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance", [$lat, $lng, $lat])
            ->having('distance', '<', $radio)
            ->orderBy('distance')
            ->get();
    }

    private function buscarFundacionesCercanas($lat, $lng, $radio)
    {
        if (!$lat || !$lng) {
            return Fundacion::where('capacidad_maxima', '>', 0)->get();
        }

        return Fundacion::where('capacidad_maxima', '>', 0)
            ->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance", [$lat, $lng, $lat])
            ->having('distance', '<', $radio)
            ->orderBy('distance')
            ->get();
    }
}
