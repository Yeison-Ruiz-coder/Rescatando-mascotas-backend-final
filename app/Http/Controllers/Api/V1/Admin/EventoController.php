<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventoRequest;
use App\Services\EventoService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EventoController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected EventoService $eventoService;

    public function __construct(EventoService $eventoService)
    {
        $this->eventoService = $eventoService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['desde', 'hasta', 'proximos']);
        $perPage = $request->get('per_page', 15);

        $eventos = $this->eventoService->getAll($filters, $perPage);
        $estadisticas = $this->eventoService->getEstadisticas();

        return $this->successResponse([
            'data' => $eventos,
            'estadisticas' => $estadisticas
        ], 'Eventos obtenidos exitosamente');
    }

    public function show($id)
    {
        try {
            $evento = $this->eventoService->findById($id);
            return $this->successResponse($evento, 'Evento obtenido exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Evento no encontrado');
        }
    }

    public function store(EventoRequest $request)
    {
        try {
            $evento = $this->runInTransaction(
                fn() => $this->eventoService->create(
                    $request->validated(),
                    $request->file('imagen_url')
                ),
                'Error al crear evento'
            );

            return $this->successResponse($evento->load('creadoPor'), 'Evento creado exitosamente', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear el evento', $e->getMessage(), 500);
        }
    }

    public function update(EventoRequest $request, $id)
    {
        try {
            $evento = $this->runInTransaction(
                fn() => $this->eventoService->update(
                    $id,
                    $request->validated(),
                    $request->file('imagen_url')
                ),
                'Error al actualizar evento'
            );

            return $this->successResponse($evento->load('creadoPor'), 'Evento actualizado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Evento no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar el evento', $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->eventoService->delete($id),
                'Error al eliminar evento'
            );

            return $this->successResponse(null, 'Evento eliminado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Evento no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar el evento', $e->getMessage(), 500);
        }
    }

    public function calendarData()
    {
        try {
            $data = $this->eventoService->getCalendarData();
            return $this->successResponse($data, 'Datos de calendario obtenidos exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener datos de calendario', $e->getMessage(), 500);
        }
    }

    public function proximos()
    {
        try {
            $eventos = $this->eventoService->getProximos(10);
            return $this->successResponse($eventos, 'Próximos eventos obtenidos exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener próximos eventos', $e->getMessage(), 500);
        }
    }
}
