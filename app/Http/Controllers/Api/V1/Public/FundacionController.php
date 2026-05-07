<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Services\Public\FundacionPublicService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FundacionController extends Controller
{
    use ApiResponses;

    protected FundacionPublicService $fundacionService;

    public function __construct(FundacionPublicService $fundacionService)
    {
        $this->fundacionService = $fundacionService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['recibe_voluntarios', 'verificado', 'buscar', 'ciudad']);
        $perPage = $request->get('per_page', 15);

        $fundaciones = $this->fundacionService->getAll($filters, $perPage);

        return $this->successResponse($fundaciones, 'Fundaciones obtenidas exitosamente');
    }

    public function show(int $id)
    {
        try {
            $data = $this->fundacionService->findById($id);
            return $this->successResponse($data, 'Fundación obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Fundación no encontrada');
        }
    }

    public function porCiudad(string $ciudad)
    {
        $filters = ['ciudad' => $ciudad];
        $fundaciones = $this->fundacionService->getAll($filters, 50);

        return $this->successResponse($fundaciones, 'Fundaciones por ciudad obtenidas exitosamente');
    }

    public function recibenVoluntarios()
    {
        $filters = ['recibe_voluntarios' => true];
        $fundaciones = $this->fundacionService->getAll($filters, 50);

        return $this->successResponse($fundaciones, 'Fundaciones que reciben voluntarios obtenidas exitosamente');
    }

    public function verificadas()
    {
        $filters = ['verificado' => true];
        $fundaciones = $this->fundacionService->getAll($filters, 50);

        return $this->successResponse($fundaciones, 'Fundaciones verificadas obtenidas exitosamente');
    }

    public function estadisticas()
    {
        $estadisticas = $this->fundacionService->getEstadisticas();
        return $this->successResponse($estadisticas, 'Estadísticas obtenidas exitosamente');
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $filters = ['buscar' => $query];
        $fundaciones = $this->fundacionService->getAll($filters, 50);

        return $this->successResponse($fundaciones, 'Resultados de búsqueda obtenidos exitosamente');
    }

    public function cercanas(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radio' => 'nullable|integer|min:1|max:100',
        ]);

        try {
            $fundaciones = $this->fundacionService->getCercanas(
                $request->lat,
                $request->lng,
                $request->get('radio', 10)
            );

            return $this->successResponse($fundaciones, 'Fundaciones cercanas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener fundaciones cercanas', $e->getMessage(), 500);
        }
    }
}
