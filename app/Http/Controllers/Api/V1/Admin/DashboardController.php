<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mascota;
use App\Models\Adopcion;
use App\Models\Solicitud;
use App\Models\Reporte;
use App\Models\Rescate;
use App\Models\Donacion;
use App\Models\User;
use App\Models\Fundacion;
use App\Models\Veterinaria;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Estadísticas generales para el dashboard
     */
    public function index()
    {
        // Estadísticas principales
        $stats = [
            'mascotas' => [
                'total' => Mascota::count(),
                'en_adopcion' => Mascota::where('estado', 'En adopcion')->count(),
                'adoptadas' => Mascota::where('estado', 'Adoptado')->count(),
                'rescatadas' => Mascota::where('estado', 'Rescatada')->count(),
            ],
            'usuarios' => [
                'total' => User::count(),
                'activos' => User::where('estado', 'activo')->count(),
                'fundaciones' => User::where('tipo', 'fundacion')->count(),
                'veterinarias' => User::where('tipo', 'veterinaria')->count(),
            ],
            'solicitudes' => [
                'pendientes' => Solicitud::where('estado', 'pendiente')->count(),
                'en_revision' => Solicitud::where('estado', 'en_revision')->count(),
                'aprobadas' => Solicitud::where('estado', 'aprobada')->count(),
            ],
            'adopciones' => [
                'mes_actual' => Adopcion::whereMonth('created_at', now()->month)->count(),
                'totales' => Adopcion::count(),
            ],
            'donaciones' => [
                'mes_actual' => Donacion::whereMonth('created_at', now()->month)->sum('valor_donacion'),
                'totales' => Donacion::sum('valor_donacion'),
            ],
            'rescates' => [
                'activos' => Rescate::where('estado', 'en_proceso')->count(),
                'completados' => Rescate::where('estado', 'completado')->count(),
            ],
            'reportes' => [
                'activos' => Reporte::where('estado', 'activo')->count(),
            ],
        ];

        // Gráfico de adopciones por mes (últimos 6 meses)
        $adopcionesPorMes = Adopcion::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
        ->where('created_at', '>=', now()->subMonths(6))
        ->groupBy('year', 'month')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get()
        ->map(function ($item) {
            return [
                'fecha' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                'total' => $item->total
            ];
        });

        // Actividad reciente (últimos 10 eventos)
        $actividadReciente = $this->getActividadReciente();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'graficos' => [
                    'adopciones_por_mes' => $adopcionesPorMes
                ],
                'actividad_reciente' => $actividadReciente
            ]
        ]);
    }

    /**
     * Obtener actividad reciente combinada
     */
    private function getActividadReciente()
    {
        $actividad = collect();

        // Últimas adopciones
        $adopciones = Adopcion::with(['adoptante', 'mascota'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'tipo' => 'adopcion',
                    'descripcion' => "Adopción: {$item->mascota?->nombre_mascota} para {$item->adoptante?->nombre}",
                    'fecha' => $item->created_at,
                    'icon' => 'heart',
                    'color' => 'success',
                    'id' => $item->id
                ];
            });

        // Últimas solicitudes
        $solicitudes = Solicitud::with('solicitable')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'tipo' => 'solicitud',
                    'descripcion' => "Solicitud: {$item->tipo_solicitud} - {$item->nombre_solicitante}",
                    'fecha' => $item->created_at,
                    'icon' => 'file',
                    'color' => 'info',
                    'id' => $item->id
                ];
            });

        // Últimos reportes
        $reportes = Reporte::latest()
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'tipo' => 'reporte',
                    'descripcion' => "Reporte: {$item->titulo} - {$item->tipo_reporte}",
                    'fecha' => $item->created_at,
                    'icon' => 'flag',
                    'color' => 'warning',
                    'id' => $item->id
                ];
            });

        return $actividad->concat($adopciones)
            ->concat($solicitudes)
            ->concat($reportes)
            ->sortByDesc('fecha')
            ->take(10)
            ->values();
    }
}
