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
        $filters = $request->only(['especie', 'genero', 'tamano', 'apto_con_ninos', 'apto_con_otros_animales']);
        $perPage = $request->get('per_page', 15);

        $mascotas = $this->adopcionService->getMascotasDisponibles($filters, $perPage);

        return $this->successResponse($mascotas, 'Mascotas disponibles obtenidas exitosamente');
    }

    public function disponibles(Request $request)
    {
        return $this->index($request);
    }

    public function show(int $id)
    {
        try {
            $mascota = $this->adopcionService->findMascotaDisponible($id);
            return $this->successResponse($mascota, 'Mascota obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Mascota no encontrada');
        }
    }

    public function verificarDisponibilidad(int $id)
    {
        $resultado = $this->adopcionService->verificarDisponibilidad($id);

        if (!$resultado['success']) {
            return $this->notFoundResponse($resultado['message']);
        }

        return $this->successResponse($resultado['data'], 'Disponibilidad verificada');
    }

    public function destacadas()
    {
        $mascotas = $this->adopcionService->getDestacadas(6);
        return $this->successResponse($mascotas, 'Mascotas destacadas obtenidas exitosamente');
    }
}
