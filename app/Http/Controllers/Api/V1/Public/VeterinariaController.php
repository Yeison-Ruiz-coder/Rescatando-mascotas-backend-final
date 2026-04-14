<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Services\Public\VeterinariaPublicService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VeterinariaController extends Controller
{
    use ApiResponses;

    protected VeterinariaPublicService $veterinariaService;

    public function __construct(VeterinariaPublicService $veterinariaService)
    {
        $this->veterinariaService = $veterinariaService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['urgencias', 'ubicacion']);
        $perPage = $request->get('per_page', 15);

        $veterinarias = $this->veterinariaService->getAll($filters, $perPage);

        return $this->successResponse($veterinarias, 'Veterinarias obtenidas exitosamente');
    }

    public function show($id)
    {
        try {
            $data = $this->veterinariaService->findById($id);
            return $this->successResponse($data, 'Veterinaria obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Veterinaria no encontrada');
        }
    }

    public function mapa()
    {
        $veterinarias = $this->veterinariaService->getMapa();
        return $this->successResponse($veterinarias, 'Veterinarias para mapa obtenidas exitosamente');
    }
}
