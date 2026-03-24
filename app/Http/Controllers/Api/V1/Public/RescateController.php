<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Rescate;
use App\Models\Fundacion;
use App\Models\Veterinaria;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RescateController extends Controller
{
    /**
     * Listar rescates públicos
     */
    public function index(Request $request)
    {
        $rescates = Rescate::with(['usuarioReporto', 'entidadResponsable'])
            ->where('estado', 'en_proceso')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $rescates
        ]);
    }

    /**
     * Reportar un nuevo rescate
     */
    public function reportar(Request $request)
    {
        $validated = $request->validate([
            'lugar_rescate' => 'required|string',
            'descripcion_rescate' => 'required|string',
            'fecha_rescate' => 'required|date',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        // Analizar el tipo de emergencia
        $tipoEmergencia = $this->analizarEmergencia($validated['descripcion_rescate']);

        $rescate = Rescate::create([
            'lugar_rescate' => $validated['lugar_rescate'],
            'descripcion_rescate' => $validated['descripcion_rescate'],
            'fecha_rescate' => $validated['fecha_rescate'],
            'tipo_emergencia' => $tipoEmergencia,
            'prioridad' => $this->calcularPrioridad($tipoEmergencia),
            'lat' => $validated['lat'] ?? null,
            'lng' => $validated['lng'] ?? null,
            'estado' => 'pendiente',
            'usuario_reporto_id' => auth()->id(),
        ]);

        // Buscar entidades cercanas y notificar
        $this->notificarEntidadesCercanas($rescate);

        return response()->json([
            'success' => true,
            'message' => 'Rescate reportado exitosamente',
            'data' => $rescate
        ]);
    }

    /**
     * Ver detalle de un rescate
     */
    public function show($id)
    {
        $rescate = Rescate::with(['usuarioReporto', 'entidadResponsable', 'mascota'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $rescate
        ]);
    }

    /**
     * Analizar la descripción para determinar el tipo de emergencia
     */
    private function analizarEmergencia($descripcion)
    {
        $descripcion = strtolower($descripcion);

        $palabrasClave = [
            'herido' => ['herido', 'sangra', 'sangrando', 'golpe', 'lastimado', 'fractura', 'hueso roto', 'cojea', 'malherido'],
            'abandonado' => ['abandonado', 'cachorros', 'solo', 'sin dueño', 'vagando', 'callejero', 'botaron', 'dejaron'],
            'urgente' => ['urgente', 'emergencia', 'grave', 'critico', 'inmediato', 'ahora', 'rapido', 'muriendo', 'agonizando']
        ];

        // Verificar urgente primero
        foreach ($palabrasClave['urgente'] as $palabra) {
            if (str_contains($descripcion, $palabra)) {
                return 'urgente';
            }
        }

        // Verificar herido
        foreach ($palabrasClave['herido'] as $palabra) {
            if (str_contains($descripcion, $palabra)) {
                return 'herido';
            }
        }

        // Verificar abandonado
        foreach ($palabrasClave['abandonado'] as $palabra) {
            if (str_contains($descripcion, $palabra)) {
                return 'abandonado';
            }
        }

        return 'otro';
    }

    /**
     * Calcular prioridad según tipo de emergencia
     */
    private function calcularPrioridad($tipoEmergencia)
    {
        return match($tipoEmergencia) {
            'urgente' => 'alta',
            'herido' => 'alta',
            'abandonado' => 'media',
            default => 'baja',
        };
    }

    /**
     * Notificar a entidades cercanas según el tipo de emergencia
     */
    private function notificarEntidadesCercanas($rescate)
    {
        $tipo = $rescate->tipo_emergencia;
        $lat = $rescate->lat;
        $lng = $rescate->lng;
        $radio = 10; // km

        $entidades = [];

        // Determinar qué entidades notificar
        if ($tipo === 'urgente' || $tipo === 'herido') {
            // Notificar a veterinarias cercanas
            $veterinarias = $this->buscarVeterinariasCercanas($lat, $lng, $radio);
            $entidades = array_merge($entidades, $veterinarias);
        }

        if ($tipo === 'urgente' || $tipo === 'abandonado') {
            // Notificar a fundaciones cercanas
            $fundaciones = $this->buscarFundacionesCercanas($lat, $lng, $radio);
            $entidades = array_merge($entidades, $fundaciones);
        }

        // Crear notificaciones
        foreach ($entidades as $entidad) {
            Notificacion::create([
                'user_id' => $entidad->user_id,
                'contenido' => "Nuevo rescate {$rescate->tipo_emergencia} cerca de ti: {$rescate->lugar_rescate}",
                'creado_por_id' => 1, // Sistema
            ]);
        }

        Log::info("Rescate #{$rescate->id} notificado a " . count($entidades) . " entidades");
    }

    /**
     * Buscar veterinarias cercanas
     */
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

    /**
     * Buscar fundaciones cercanas
     */
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
