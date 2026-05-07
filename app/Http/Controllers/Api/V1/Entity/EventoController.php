<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Services\Entity\EventoEntityService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EventoController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected EventoEntityService $eventoService;

    public function __construct(EventoEntityService $eventoService)
    {
        $this->eventoService = $eventoService;
    }

    public function index()
    {
        try {
            $eventos = $this->eventoService->getMisEventos();
            return $this->successResponse($eventos, 'Eventos obtenidos exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 403);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_evento' => 'required|string|max:255',
            'lugar_evento' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fecha_evento' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_evento',
            'capacidad_maxima' => 'nullable|integer|min:1',
            'costo' => 'nullable|numeric|min:0',
            'organizador' => 'nullable|string|max:255',
            'telefono_contacto' => 'nullable|string|max:20',
            'email_contacto' => 'nullable|email|max:255',
            'categoria' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $evento = $this->runInTransaction(
                fn() => $this->eventoService->createEvento(
                    $request->except('imagen'),
                    $request->file('imagen')
                ),
                'Error al crear evento'
            );

            return $this->successResponse($evento, 'Evento creado exitosamente', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear el evento', $e->getMessage(), 500);
        }
    }

    public function show(int$id)
    {
        try {
            $evento = $this->eventoService->findEvento($id);
            return $this->successResponse($evento, 'Evento obtenido exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Evento no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 403);
        }
    }

    public function update(Request $request,int $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre_evento' => 'sometimes|string|max:255',
            'lugar_evento' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|string',
            'fecha_evento' => 'sometimes|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_evento',
            'capacidad_maxima' => 'nullable|integer|min:1',
            'costo' => 'nullable|numeric|min:0',
            'organizador' => 'nullable|string|max:255',
            'telefono_contacto' => 'nullable|string|max:20',
            'email_contacto' => 'nullable|email|max:255',
            'categoria' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $evento = $this->runInTransaction(
                fn() => $this->eventoService->updateEvento(
                    $id,
                    $request->except('imagen'),
                    $request->file('imagen')
                ),
                'Error al actualizar evento'
            );

            return $this->successResponse($evento, 'Evento actualizado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Evento no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar el evento', $e->getMessage(), 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->eventoService->deleteEvento($id),
                'Error al eliminar evento'
            );

            return $this->successResponse(null, 'Evento eliminado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Evento no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar el evento', $e->getMessage(), 500);
        }
    }
}
