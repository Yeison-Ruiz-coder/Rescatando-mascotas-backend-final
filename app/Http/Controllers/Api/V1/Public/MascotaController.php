<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Services\Public\MascotaPublicService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MascotaController extends Controller
{
    use ApiResponses;

    protected MascotaPublicService $mascotaService;

    public function __construct(MascotaPublicService $mascotaService)
    {
        $this->mascotaService = $mascotaService;
    }

    public function index(Request $request)
    {
        $filters = $request->only([
            'especie', 'fundacion_id', 'genero', 'tamano', 'buscar',
            'apto_con_ninos', 'apto_con_otros_animales', 'destacada'
        ]);
        $perPage = $request->get('per_page', 15);

        $mascotas = $this->mascotaService->getAll($filters, $perPage);

        return $this->successResponse($mascotas, 'Mascotas obtenidas exitosamente');
    }

    public function show(int $id)
    {
        try {
            $mascota = $this->mascotaService->findById($id);
            return $this->successResponse($mascota, 'Mascota obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Mascota no encontrada');
        }
    }

    public function porEspecie(string $especie)
    {
        $mascotas = $this->mascotaService->getPorEspecie($especie);
        return $this->successResponse($mascotas, 'Mascotas por especie obtenidas exitosamente');
    }

    public function porFundacion(int $fundacionId)
    {
        $mascotas = $this->mascotaService->getPorFundacion($fundacionId);
        return $this->successResponse($mascotas, 'Mascotas por fundación obtenidas exitosamente');
    }

    public function destacadas()
    {
        $mascotas = $this->mascotaService->getDestacadas(6);
        return $this->successResponse($mascotas, 'Mascotas destacadas obtenidas exitosamente');
    }
}
