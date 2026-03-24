<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Adopcion;
use App\Models\Mascota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdopcionController extends Controller
{
    /**
     * Listado de adopciones exitosas (para historial público)
     * GET /api/adopciones
     */
    public function index(Request $request)
    {
        $adopciones = Adopcion::with(['mascota', 'fundacion', 'adoptante'])
            ->where('estado', 'completada')
            ->latest('fecha_adopcion')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $adopciones
        ]);
    }

    /**
     * Mascotas disponibles para adopción
     * GET /api/adopciones/disponibles
     */
    public function disponibles(Request $request)
    {
        $query = Mascota::with('fundacion')
            ->where('estado', 'En adopcion');

        // Filtros opcionales
        if ($request->has('especie')) {
            $query->where('especie', $request->especie);
        }

        if ($request->has('fundacion_id')) {
            $query->where('fundacion_id', $request->fundacion_id);
        }

        if ($request->has('buscar')) {
            $query->where('nombre_mascota', 'like', '%' . $request->buscar . '%');
        }

        $mascotas = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $mascotas
        ]);
    }

    /**
     * Detalle de adopción exitosa
     * GET /api/adopciones/{id}
     */
    public function show($id)
    {
        $adopcion = Adopcion::with(['mascota', 'fundacion', 'adoptante'])
            ->where('estado', 'completada')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $adopcion
        ]);
    }

    /**
     * Verificar disponibilidad de mascota
     * GET /api/adopciones/verificar/{mascotaId}
     */
    public function verificarDisponibilidad($mascotaId)
    {
        $mascota = Mascota::find($mascotaId);

        if (!$mascota) {
            return response()->json([
                'success' => false,
                'message' => 'Mascota no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'disponible' => $mascota->estado === 'En adopcion',
                'estado' => $mascota->estado,
                'nombre' => $mascota->nombre_mascota,
                'id' => $mascota->id
            ]
        ]);
    }

    /**
     * Estadísticas de adopciones
     * GET /api/adopciones/estadisticas
     */
    public function estadisticas()
    {
        $totalAdopciones = Adopcion::where('estado', 'completada')->count();
        $adopcionesEsteMes = Adopcion::where('estado', 'completada')
            ->whereMonth('fecha_adopcion', now()->month)
            ->whereYear('fecha_adopcion', now()->year)
            ->count();

        $adopcionesPorFundacion = Adopcion::where('estado', 'completada')
            ->with('fundacion')
            ->select('fundacion_id', DB::raw('count(*) as total'))
            ->groupBy('fundacion_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'fundacion' => $item->fundacion?->Nombre_1 ?? 'Desconocida',
                    'total' => $item->total
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total_adopciones' => $totalAdopciones,
                'adopciones_este_mes' => $adopcionesEsteMes,
                'top_fundaciones' => $adopcionesPorFundacion
            ]
        ]);
    }
}
