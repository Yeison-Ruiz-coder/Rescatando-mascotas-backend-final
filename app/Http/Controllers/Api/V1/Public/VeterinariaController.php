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
        $filters = $request->only(['urgencias', 'ubicacion', 'verificado', 'servicio', 'buscar']);
        $perPage = $request->get('per_page', 15);

        $veterinarias = $this->veterinariaService->getAll($filters, $perPage);

        return $this->successResponse($veterinarias, 'Veterinarias obtenidas exitosamente');
    }

    public function show(int $id)
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

    public function cercanas(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radio' => 'nullable|integer|min:1|max:100',
        ]);

        try {
            $veterinarias = $this->veterinariaService->getCercanas(
                $request->lat,
                $request->lng,
                $request->get('radio', 10)
            );

            return $this->successResponse($veterinarias, 'Veterinarias cercanas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener veterinarias cercanas', $e->getMessage(), 500);
        }
    }
}
