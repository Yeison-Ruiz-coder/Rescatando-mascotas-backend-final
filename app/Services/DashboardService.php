<?php

namespace App\Services;

use App\Models\Mascota;
use App\Models\Adopcion;
use App\Models\Solicitud;
use App\Models\Reporte;
use App\Models\Rescate;
use App\Models\Donacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getEstadisticasPrincipales(): array
    {
        return [
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
    }

    public function getAdopcionesPorMes(): array
    {
        return Adopcion::select(
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
        })
        ->toArray();
    }

    public function getActividadReciente(): array
    {
        $actividad = collect();

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
            ->values()
            ->toArray();
    }
}
