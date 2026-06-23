<?php

namespace App\Services\Public;

use App\Models\Rescate;
use App\Models\Fundacion;
use App\Models\Veterinaria;
use App\Models\Notificacion;
use App\Models\User;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Log;

class RescatePublicService
{
    use ImageUploadTrait;

    public function getAll(int $perPage = 15)
    {
        return Rescate::query()
            ->selectFields()
            ->with(['usuarioReporto:id,nombre,email', 'entidadResponsable'])
            ->where('estado', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): Rescate
    {
        return Rescate::query()
            ->selectFields()
            ->with(['usuarioReporto:id,nombre,email', 'entidadResponsable', 'mascota:id,nombre_mascota,foto_principal,estado'])
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

        // ✅ 1. ASIGNAR ENTIDAD RESPONSABLE MÁS CERCANA (según tipo de emergencia)
        $entidadAsignada = $this->asignarEntidadResponsable(
            $data['lat'] ?? null,
            $data['lng'] ?? null,
            $tipoEmergencia
        );

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
            // ✅ 2. ASIGNAR ENTIDAD RESPONSABLE
            'entidad_responsable_type' => $entidadAsignada ? get_class($entidadAsignada) : null,
            'entidad_responsable_id'   => $entidadAsignada ? $entidadAsignada->id : null,
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

        // ✅ 3. NOTIFICAR a las entidades cercanas
        $this->notificarEntidadesCercanas($rescate, $entidadAsignada);

        // ✅ 4. ESCALAR a admin si es necesario
        $this->verificarEscalamientoAutomatico($rescate);

        Log::info('✅ Rescate creado', [
            'id' => $rescate->id,
            'tipo' => $tipoEmergencia,
            'prioridad' => $prioridad,
            'entidad_asignada' => $entidadAsignada ? get_class($entidadAsignada) . ' #' . $entidadAsignada->id : 'Ninguna'
        ]);

        return $rescate;
    }

    /**
     * ✅ ASIGNAR ENTIDAD RESPONSABLE (NUEVO MÉTODO)
     * Busca la mejor entidad según el tipo de emergencia
     */
    private function asignarEntidadResponsable(?float $lat, ?float $lng, string $tipoEmergencia, int $radio = 30)
    {
        // Si no hay coordenadas, no se puede asignar
        if (!$lat || !$lng) {
            Log::info('📍 Sin coordenadas, no se asigna entidad');
            return null;
        }

        Log::info('📍 Buscando entidad responsable', ['lat' => $lat, 'lng' => $lng, 'tipo' => $tipoEmergencia]);

        // ✅ Para emergencias urgentes/heridas: priorizar veterinarias con urgencias 24h
        if (in_array($tipoEmergencia, ['urgente', 'herido'])) {
            $veterinaria = $this->buscarVeterinariaCercana($lat, $lng, $radio);
            if ($veterinaria) {
                Log::info('🏥 Veterinaria asignada', [
                    'id' => $veterinaria->id,
                    'nombre' => $veterinaria->Nombre_vet,
                    'distancia' => round($veterinaria->distance ?? 0, 2) . ' km'
                ]);
                return $veterinaria;
            }
        }

        // ✅ Para abandonados o si no hay veterinaria: buscar fundación
        if (in_array($tipoEmergencia, ['urgente', 'abandonado', 'otro'])) {
            $fundacion = $this->buscarFundacionCercana($lat, $lng, $radio);
            if ($fundacion) {
                Log::info('🏢 Fundación asignada', [
                    'id' => $fundacion->id,
                    'nombre' => $fundacion->Nombre_1,
                    'distancia' => round($fundacion->distance ?? 0, 2) . ' km'
                ]);
                return $fundacion;
            }
        }

        Log::info('⚠️ No se encontró entidad en el radio de ' . $radio . ' km');
        return null;
    }

    /**
     * Buscar veterinaria más cercana (con urgencias 24h prioritarias)
     */
    private function buscarVeterinariaCercana(float $lat, float $lng, int $radio)
    {
        // Primero buscar con urgencias 24h
        $veterinaria = Veterinaria::selectRaw("
                id,
                Nombre_vet,
                user_id,
                lat,
                lng,
                urgencias_24h,
                (6371 * acos(
                    cos(radians(?)) * cos(radians(lat)) *
                    cos(radians(lng) - radians(?)) +
                    sin(radians(?)) * sin(radians(lat))
                )) AS distance
            ", [$lat, $lng, $lat])
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->having('distance', '<', $radio)
            ->orderBy('urgencias_24h', 'desc')
            ->orderBy('distance')
            ->first();

        if ($veterinaria) {
            return $veterinaria;
        }

        // Si no hay con urgencias, buscar cualquier veterinaria
        return Veterinaria::selectRaw("
                id,
                Nombre_vet,
                user_id,
                lat,
                lng,
                (6371 * acos(
                    cos(radians(?)) * cos(radians(lat)) *
                    cos(radians(lng) - radians(?)) +
                    sin(radians(?)) * sin(radians(lat))
                )) AS distance
            ", [$lat, $lng, $lat])
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->having('distance', '<', $radio)
            ->orderBy('distance')
            ->first();
    }

    /**
     * Buscar fundación más cercana
     */
    private function buscarFundacionCercana(float $lat, float $lng, int $radio)
    {
        return Fundacion::selectRaw("
                id,
                Nombre_1,
                user_id,
                lat,
                lng,
                (6371 * acos(
                    cos(radians(?)) * cos(radians(lat)) *
                    cos(radians(lng) - radians(?)) +
                    sin(radians(?)) * sin(radians(lat))
                )) AS distance
            ", [$lat, $lng, $lat])
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->having('distance', '<', $radio)
            ->orderBy('distance')
            ->first();
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

    /**
     * Notificar a entidades cercanas (MODIFICADO)
     */
    private function notificarEntidadesCercanas(Rescate $rescate, $entidadAsignada = null): void
    {
        $tipo = $rescate->tipo_emergencia;
        $lat = $rescate->lat;
        $lng = $rescate->lng;
        $radio = 30;

        $entidades = collect();

        // ✅ Si ya hay una entidad asignada, notificar solo a esa
        if ($entidadAsignada) {
            $entidades->push($entidadAsignada);
            Log::info('📨 Notificando a entidad asignada', ['id' => $entidadAsignada->id]);
        } else {
            // Si no hay entidad asignada, buscar y notificar a todas las cercanas
            if (in_array($tipo, ['urgente', 'herido'])) {
                $veterinarias = $this->buscarVeterinariasCercanas($lat, $lng, $radio);
                $entidades = $entidades->concat($veterinarias);
            }
            if (in_array($tipo, ['urgente', 'abandonado'])) {
                $fundaciones = $this->buscarFundacionesCercanas($lat, $lng, $radio);
                $entidades = $entidades->concat($fundaciones);
            }
        }

        foreach ($entidades as $entidad) {
            if ($entidad->user_id) {
                Notificacion::create([
                    'user_id'      => $entidad->user_id,
                    'contenido'    => "🚨 Nuevo rescate {$rescate->tipo_emergencia} cerca de ti: {$rescate->lugar_rescate}",
                    'creado_por_id' => 1,
                    'tipo' => 'alert',
                    'prioridad' => $rescate->prioridad === 'alta' ? 'urgente' : 'media',
                ]);
            }
        }

        Log::info("📨 Rescate #{$rescate->id} notificado a " . count($entidades) . " entidades");
    }

    private function buscarVeterinariasCercanas(float|null $lat, float|null $lng, int|float $radio)
    {
        $query = Veterinaria::where('urgencias_24h', true)
            ->whereNotNull('lat')
            ->whereNotNull('lng');

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
        $query = Fundacion::where('capacidad_maxima', '>', 0)
            ->whereNotNull('lat')
            ->whereNotNull('lng');

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
            $admins = User::where('tipo', 'admin')->get();
            foreach ($admins as $admin) {
                Notificacion::create([
                    'user_id'      => $admin->id,
                    'contenido'    => "⚠️ Rescate #{$rescate->id} (tipo {$tipo}) no tiene entidades cercanas. Requiere asignación manual.",
                    'creado_por_id' => 1,
                    'tipo' => 'warning',
                    'prioridad' => 'alta',
                ]);
            }
            Log::warning("⚠️ Rescate #{$rescate->id} escalado a admin por falta de entidades.");
        }
    }

    /**
     * Marcar rescate como disponible para administradores
     */
    public function marcarDisponibleParaAdmin(int $rescateId)
    {
        $rescate = Rescate::findOrFail($rescateId);
        $rescate->update([
            'disponible_para_admin' => true
        ]);
        return $rescate;
    }
}
