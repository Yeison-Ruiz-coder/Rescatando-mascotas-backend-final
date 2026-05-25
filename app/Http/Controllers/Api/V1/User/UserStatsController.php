<?php
// app/Http/Controllers/Api/V1/User/UserStatsController.php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Models\Adopcion;
use App\Models\Donacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserStatsController extends Controller
{
    /**
     * Obtener estadísticas completas del usuario autenticado
     */
    public function getStats(Request $request)
    {
        $user = $request->user();

        // Calcular estadísticas adicionales si es necesario
        $totalAdopciones = Adopcion::where('user_id', $user->id)
            ->where('estado', 'completada')
            ->count();

        $totalDonaciones = Donacion::where('user_id', $user->id)
            ->where('estado', 'completada')
            ->sum('monto');

        $adopcionesEsteMes = Adopcion::where('user_id', $user->id)
            ->where('estado', 'completada')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Determinar siguiente rango
        $nextRank = $this->getNextRank($user->puntos ?? 0);
        $pointsToNextRank = $this->getPointsToNextRank($user->puntos ?? 0);

        return response()->json([
            'success' => true,
            'data' => [
                // Datos básicos del usuario
                'id' => $user->id,
                'nombre' => $user->nombre,
                'apellidos' => $user->apellidos,
                'avatar' => $user->avatar,
                'email' => $user->email,

                // Estadísticas del sistema (desde BD)
                'puntos' => $user->puntos ?? 0,
                'rango' => $user->rango ?? 'Nuevo',
                'total_mascotas_adoptadas' => $user->total_mascotas_adoptadas ?? $totalAdopciones,
                'total_donaciones' => $user->total_donaciones ?? $totalDonaciones,
                'veces_reportado' => $user->veces_reportado ?? 0,
                'estado' => $user->estado ?? 'activo',

                // Estadísticas calculadas
                'adopciones_este_mes' => $adopcionesEsteMes,
                'proximo_rango' => $nextRank,
                'puntos_para_siguiente' => $pointsToNextRank,

                // Verificaciones
                'email_verificado' => !is_null($user->email_verified_at),
                'telefono_verificado' => $user->telefono_verificado ?? false,
                'documento_verificado' => $user->documento_verificado ?? false,
            ]
        ]);
    }

    /**
     * Obtener historial de adopciones del usuario
     */
    public function getAdoptions(Request $request)
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 10);

        $adoptions = Adopcion::where('user_id', $user->id)
            ->with(['mascota' => function($query) {
                $query->select('id', 'nombre', 'especie', 'imagen');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $adoptions->items(),
            'meta' => [
                'current_page' => $adoptions->currentPage(),
                'last_page' => $adoptions->lastPage(),
                'per_page' => $adoptions->perPage(),
                'total' => $adoptions->total(),
            ]
        ]);
    }

    /**
     * Obtener historial de donaciones del usuario
     */
    public function getDonations(Request $request)
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 10);

        $donations = Donacion::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $donations->items(),
            'meta' => [
                'current_page' => $donations->currentPage(),
                'last_page' => $donations->lastPage(),
                'per_page' => $donations->perPage(),
                'total' => $donations->total(),
            ]
        ]);
    }

    /**
     * Determinar el siguiente rango basado en puntos actuales
     */
    private function getNextRank($points)
    {
        $ranks = [
            0 => 'Bronce',
            100 => 'Plata',
            300 => 'Oro',
            600 => 'Platino',
            1000 => 'Diamante',
        ];

        foreach ($ranks as $required => $rank) {
            if ($points < $required) {
                return $rank;
            }
        }

        return 'Máximo';
    }

    /**
     * Calcular puntos necesarios para el siguiente rango
     */
    private function getPointsToNextRank($points)
    {
        $thresholds = [100, 300, 600, 1000];

        foreach ($thresholds as $threshold) {
            if ($points < $threshold) {
                return $threshold - $points;
            }
        }

        return 0;
    }
}
