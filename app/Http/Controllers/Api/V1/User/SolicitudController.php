<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Services\User\SolicitudUserService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class SolicitudController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected SolicitudUserService $solicitudService;

    public function __construct(SolicitudUserService $solicitudService)
    {
        $this->solicitudService = $solicitudService;
    }

    public function index()
    {
        try {
            $solicitudes = $this->solicitudService->getByUser(auth()->id());
            return $this->successResponse($solicitudes, 'Solicitudes obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al cargar las solicitudes', $e->getMessage(), 500);
        }
    }

    public function show(int $id)
    {
        try {
            $solicitud = $this->solicitudService->findById(auth()->id(), $id);
            return $this->successResponse($solicitud, 'Solicitud obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Solicitud no encontrada');
        }
    }

    public function storeAdopcion(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'mascota_id' => 'required|exists:mascotas,id',
                'nombre' => 'required|string|max:255',
                'apellido' => 'required|string|max:255',
                'email' => 'required|email',
                'telefono' => 'required|string|max:20',
                'documento_identidad' => 'required|string|max:20',
                'direccion' => 'required|string',
                'ciudad' => 'nullable|string',
                'departamento' => 'nullable|string',
                'codigo_postal' => 'nullable|string',
                'estado_civil' => 'nullable|string',
                'cantidad_hijos' => 'nullable|string',
                'ocupacion' => 'nullable|string',
                'experiencia_mascotas' => 'required|string',
                'tipo_vivienda' => 'required|string',
                'es_propietario' => 'nullable|string',
                'motivo_adopcion' => 'required|string|min:10',
                'compromiso_cuidado' => 'required|boolean',
                'compromiso_esterilizacion' => 'required|boolean',
                'compromiso_seguimiento' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Error de validación', $validator->errors(), 422);
            }

            $solicitud = $this->runInTransaction(
                fn() => $this->solicitudService->createSolicitudAdopcion(
                    auth()->id(),
                    $validator->validated()
                ),
                'Error al enviar la solicitud'
            );

            return $this->successResponse($solicitud, 'Solicitud enviada exitosamente', 201);

        } catch (ValidationException $e) {
            return $this->errorResponse('Error de validación', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }
}
