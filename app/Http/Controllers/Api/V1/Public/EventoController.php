<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Services\Public\EventoPublicService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EventoController extends Controller
{
    use ApiResponses;

    protected EventoPublicService $eventoService;

    public function __construct(EventoPublicService $eventoService)
    {
        $this->eventoService = $eventoService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['mes', 'anio']);
        $perPage = $request->get('per_page', 15);

        $eventos = $this->eventoService->getAll($filters, $perPage);

        return $this->successResponse($eventos, 'Eventos obtenidos exitosamente');
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

    public function calendario()
    {
        $eventos = $this->eventoService->getCalendario();
        return $this->successResponse($eventos, 'Datos de calendario obtenidos exitosamente');
    }
}
