<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\DonacionRequest;
use Illuminate\Http\Request;
use App\Services\User\DonacionUserService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DonacionController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected DonacionUserService $donacionService;

    public function __construct(DonacionUserService $donacionService)
    {
        $this->donacionService = $donacionService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $donaciones = $this->donacionService->getByUser($request->user()->id, $perPage);

        return $this->successResponse($donaciones, 'Donaciones obtenidas exitosamente');
    }

    public function show($id)
    {
        try {
            $donacion = $this->donacionService->findById(request()->user()->id, $id);
            return $this->successResponse($donacion, 'Donación obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Donación no encontrada');
        }
    }

    public function store(DonacionRequest $request)
    {
        try {
            $donacion = $this->runInTransaction(
                fn() => $this->donacionService->createDonacion(
                    $request->user()->id,
                    $request->validated()
                ),
                'Error al procesar la donación'
            );

            return $this->successResponse(
                $donacion->load('fundacion'),
                '¡Gracias por tu donación!',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error al procesar la donación', $e->getMessage(), 500);
        }
    }

    public function certificado($id)
    {
        try {
            $donacion = $this->donacionService->getCertificadoData(request()->user()->id, $id);

            return $this->successResponse([
                'donacion' => $donacion,
                'certificado_url' => '/api/v1/user/donaciones/' . $id . '/certificado.pdf'
            ], 'Certificado generado');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Donación no encontrada');
        }
    }
}
