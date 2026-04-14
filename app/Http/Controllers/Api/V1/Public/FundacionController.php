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
        $filters = $request->only(['recibe_voluntarios', 'buscar']);
        $perPage = $request->get('per_page', 15);

        $fundaciones = $this->fundacionService->getAll($filters, $perPage);

        return $this->successResponse($fundaciones, 'Fundaciones obtenidas exitosamente');
    }

    public function show($id)
    {
        try {
            $data = $this->fundacionService->findById($id);
            return $this->successResponse($data, 'Fundación obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Fundación no encontrada');
        }
    }
}
