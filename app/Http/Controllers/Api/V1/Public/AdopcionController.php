<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Services\Public\AdopcionPublicService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdopcionController extends Controller
{
    use ApiResponses;

    protected AdopcionPublicService $adopcionService;

    public function __construct(AdopcionPublicService $adopcionService)
    {
        $this->adopcionService = $adopcionService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $mascotas = $this->adopcionService->getMascotasDisponibles($perPage);

        return $this->successResponse($mascotas, 'Mascotas disponibles obtenidas exitosamente');
    }

    public function disponibles(Request $request)
    {
        return $this->index($request);
    }

    public function show($id)
    {
        try {
            $mascota = $this->adopcionService->findMascotaDisponible($id);
            return $this->successResponse($mascota, 'Mascota obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Mascota no encontrada');
        }
    }

    public function verificarDisponibilidad($id)
    {
        $resultado = $this->adopcionService->verificarDisponibilidad($id);

        if (!$resultado['success']) {
            return $this->notFoundResponse($resultado['message']);
        }

        return $this->successResponse($resultado['data'], 'Disponibilidad verificada');
    }
}
