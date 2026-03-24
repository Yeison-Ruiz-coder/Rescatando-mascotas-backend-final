<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rescate;
use App\Models\Fundacion;
use App\Models\Veterinaria;
use Illuminate\Http\Request;

class RescateController extends Controller
{
    /**
     * Listar todos los rescates
     */
    public function index(Request $request)
    {
        $rescates = Rescate::with(['usuarioReporto', 'entidadResponsable', 'mascota'])
            ->when($request->estado, function($query, $estado) {
                return $query->where('estado', $estado);
            })
            ->when($request->tipo_emergencia, function($query, $tipo) {
                return $query->where('tipo_emergencia', $tipo);
            })
            ->orderByRaw("FIELD(prioridad, 'alta', 'media', 'baja')")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $rescates
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
     * Asignar rescate manualmente
     */
    public function asignar(Request $request, $id)
    {
        $validated = $request->validate([
            'entidad_tipo' => 'required|in:fundacion,veterinaria',
            'entidad_id' => 'required|integer',
        ]);

        $rescate = Rescate::findOrFail($id);

        $entidad = null;
        if ($validated['entidad_tipo'] === 'fundacion') {
            $entidad = Fundacion::findOrFail($validated['entidad_id']);
            $rescate->entidad_responsable_type = Fundacion::class;
        } else {
            $entidad = Veterinaria::findOrFail($validated['entidad_id']);
            $rescate->entidad_responsable_type = Veterinaria::class;
        }

        $rescate->entidad_responsable_id = $entidad->id;
        $rescate->estado = 'en_proceso';
        $rescate->save();

        return response()->json([
            'success' => true,
            'message' => 'Rescate asignado exitosamente',
            'data' => $rescate
        ]);
    }

    /**
     * Estadísticas de rescates
     */
    public function estadisticas()
    {
        $stats = [
            'pendientes' => Rescate::where('estado', 'pendiente')->count(),
            'en_proceso' => Rescate::where('estado', 'en_proceso')->count(),
            'completados' => Rescate::where('estado', 'completado')->count(),
            'por_tipo' => [
                'herido' => Rescate::where('tipo_emergencia', 'herido')->count(),
                'abandonado' => Rescate::where('tipo_emergencia', 'abandonado')->count(),
                'urgente' => Rescate::where('tipo_emergencia', 'urgente')->count(),
                'otro' => Rescate::where('tipo_emergencia', 'otro')->count(),
            ],
            'por_prioridad' => [
                'alta' => Rescate::where('prioridad', 'alta')->count(),
                'media' => Rescate::where('prioridad', 'media')->count(),
                'baja' => Rescate::where('prioridad', 'baja')->count(),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
