<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Traits\ApiResponses;

class DashboardController extends Controller
{
    use ApiResponses;

    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $stats = $this->dashboardService->getEstadisticasPrincipales();
        $adopcionesPorMes = $this->dashboardService->getAdopcionesPorMes();
        $actividadReciente = $this->dashboardService->getActividadReciente();

        return $this->successResponse([
            'stats' => $stats,
            'graficos' => [
                'adopciones_por_mes' => $adopcionesPorMes
            ],
            'actividad_reciente' => $actividadReciente
        ], 'Dashboard obtenido exitosamente');
    }
}
