<?php
// app/Services/Public/RescatePublicService.php

namespace App\Services\Public;

use App\Models\Rescate;
use App\Models\Fundacion;
use App\Models\Veterinaria;
use App\Models\Notificacion;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Log;

class RescatePublicService
{
    use ImageUploadTrait;

    public function getAll(int $perPage = 15)
    {
        return Rescate::with(['usuarioReporto', 'entidadResponsable'])
            ->where('estado', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): Rescate
    {
        return Rescate::with(['usuarioReporto', 'entidadResponsable', 'mascota'])
            ->findOrFail($id);
    }

    public function reportar(array $data, $fotoPrincipal = null, array $galeriaFotos = []): Rescate
    {
        // Analizar tipo de emergencia y prioridad
        if (isset($data['tipo_emergencia']) && !empty($data['tipo_emergencia'])) {
            $tipoEmergencia = $data['tipo_emergencia'];
            $prioridad = $data['prioridad'] ?? $this->calcularPrioridad($tipoEmergencia);
        } else {
            $tipoEmergencia = $this->analizarEmergencia($data['descripcion_rescate']);
            $prioridad = $this->calcularPrioridad($tipoEmergencia);
        }

        $rescateData = [
            'lugar_rescate'       => $data['lugar_rescate'],
            'descripcion_rescate' => $data['descripcion_rescate'],
            'fecha_rescate'       => $data['fecha_rescate'],
            'tipo_emergencia'     => $tipoEmergencia,
            'prioridad'           => $prioridad,
            'lat'                 => $data['lat'] ?? null,
            'lng'                 => $data['lng'] ?? null,
            'estado'              => 'pendiente',
            'nombre_reportante'   => $data['nombre_reportante'] ?? null,
            'email_reportante'    => $data['email_reportante'] ?? null,
            'telefono_reportante' => $data['telefono_reportante'] ?? null,
            'usuario_reporto_id'  => auth()->id(),
        ];

        // Subir foto principal si existe
        if ($fotoPrincipal && $fotoPrincipal->isValid()) {
            $rescateData['foto_principal'] = $this->uploadImage($fotoPrincipal, 'rescates');
        }

        $rescate = Rescate::create($rescateData);

        // Subir galería de fotos
        if (!empty($galeriaFotos)) {
            $galeriaUrls = [];
            foreach ($galeriaFotos as $foto) {
                if ($foto && $foto->isValid()) {
                    $galeriaUrls[] = $this->uploadImage($foto, 'rescates/galeria');
                }
            }
            if (!empty($galeriaUrls)) {
                $rescate->galeria_fotos = json_encode($galeriaUrls);
                $rescate->save();
            }
        }

        $this->notificarEntidadesCercanas($rescate);
        $this->verificarEscalamientoAutomatico($rescate);

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
            if (str_contains($descripcion, $palabra)) return 'urgente';
        }
        foreach ($palabrasClave['herido'] as $palabra) {
            if (str_contains($descripcion, $palabra)) return 'herido';
        }
        foreach ($palabrasClave['abandonado'] as $palabra) {
            if (str_contains($descripcion, $palabra)) return 'abandonado';
        }
        return 'otro';
    }

    private function calcularPrioridad(string $tipoEmergencia): string
    {
        return match ($tipoEmergencia) {
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

        $entidades = collect();

        if (in_array($tipo, ['urgente', 'herido'])) {
            $entidades = $entidades->concat($this->buscarVeterinariasCercanas($lat, $lng, $radio));
        }
        if (in_array($tipo, ['urgente', 'abandonado'])) {
            $entidades = $entidades->concat($this->buscarFundacionesCercanas($lat, $lng, $radio));
        }

        foreach ($entidades as $entidad) {
            if ($entidad->user_id) {
                Notificacion::create([
                    'user_id'      => $entidad->user_id,
                    'contenido'    => "Nuevo rescate {$rescate->tipo_emergencia} cerca de ti: {$rescate->lugar_rescate}",
                    'creado_por_id' => 1,
                    'tipo' => 'alert',
                    'prioridad' => $rescate->prioridad === 'alta' ? 'urgente' : 'media',
                ]);
            }
        }

        Log::info("Rescate #{$rescate->id} notificado a " . count($entidades) . " entidades");
    }

    private function buscarVeterinariasCercanas(float|null $lat, float|null $lng, int|float $radio)
    {
        $query = Veterinaria::where('urgencias_24h', true);
        if ($lat && $lng) {
            return $query->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance", [$lat, $lng, $lat])
                ->having('distance', '<', $radio)
                ->orderBy('distance')
                ->get();
        }
        return $query->get();
    }

    private function buscarFundacionesCercanas(float|null $lat, float|null $lng, int|float $radio)
    {
        $query = Fundacion::where('capacidad_maxima', '>', 0);
        if ($lat && $lng) {
            return $query->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance", [$lat, $lng, $lat])
                ->having('distance', '<', $radio)
                ->orderBy('distance')
                ->get();
        }
        return $query->get();
    }

    private function verificarEscalamientoAutomatico(Rescate $rescate): void
    {
        $tipo = $rescate->tipo_emergencia;
        $hayEntidades = false;

        if (in_array($tipo, ['urgente', 'herido'])) {
            $hayEntidades = Veterinaria::where('urgencias_24h', true)->exists();
        }
        if (!$hayEntidades && in_array($tipo, ['urgente', 'abandonado'])) {
            $hayEntidades = Fundacion::where('capacidad_maxima', '>', 0)->exists();
        }

        if (!$hayEntidades) {
            $admins = \App\Models\User::where('tipo', 'admin')->get();
            foreach ($admins as $admin) {
                Notificacion::create([
                    'user_id'      => $admin->id,
                    'contenido'    => "Rescate #{$rescate->id} (tipo {$tipo}) no tiene entidades cercanas. Requiere asignación manual.",
                    'creado_por_id' => 1,
                    'tipo' => 'warning',
                    'prioridad' => 'alta',
                ]);
            }
            Log::warning("Rescate #{$rescate->id} escalado a admin por falta de entidades.");
        }
    }
}
