<?php


namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Services\Public\RescatePublicService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RescateController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected RescatePublicService $rescateService;

    public function __construct(RescatePublicService $rescateService)
    {
        $this->rescateService = $rescateService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $rescates = $this->rescateService->getAll($perPage);
        return $this->successResponse($rescates, 'Rescates obtenidos exitosamente');
    }

    public function show(int $id)
    {
        try {
            $rescate = $this->rescateService->findById($id);
            return $this->successResponse($rescate, 'Rescate obtenido exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Rescate no encontrado');
        }
    }

    public function reportar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lugar_rescate'      => 'required|string|max:255',
            'descripcion_rescate'=> 'required|string',
            'fecha_rescate'      => 'required|date',
            'lat'                => 'nullable|numeric',
            'lng'                => 'nullable|numeric',
            'tipo_emergencia'    => 'nullable|in:herido,abandonado,urgente,otro',
            'prioridad'          => 'nullable|in:alta,media,baja',
            'nombre_reportante'  => 'nullable|string|max:255',
            'email_reportante'   => 'nullable|email|max:255',
            'telefono_reportante'=> 'nullable|string|max:20',
            'foto_principal'     => 'nullable|image|max:5120',
            'galeria_fotos'      => 'nullable|array',
            'galeria_fotos.*'    => 'image|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $rescate = $this->runInTransaction(
                fn() => $this->rescateService->reportar(
                    $request->all(),
                    $request->file('foto_principal'),
                    $request->file('galeria_fotos', [])
                ),
                'Error al reportar rescate'
            );

            return $this->successResponse($rescate, 'Rescate reportado exitosamente', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al reportar rescate', $e->getMessage(), 500);
        }
    }
}
