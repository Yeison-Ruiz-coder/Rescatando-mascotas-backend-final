<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Services\Public\EventoPublicService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Evento;
use App\Models\User;

class EventoController extends Controller
{
    use ApiResponses;

    protected EventoPublicService $eventoService;

    public function __construct(EventoPublicService $eventoService)
    {
        $this->eventoService = $eventoService;
    }

    /**
     * Listar todos los eventos (solo futuros)
     */
    public function index(Request $request)
    {
        $filters = $request->only(['mes', 'anio', 'tipo', 'fundacion_id', 'proximos', 'buscar']);
        $perPage = $request->get('per_page', 12);

        $eventos = $this->eventoService->getAll($filters, $perPage);

        if (auth()->check()) {
            foreach ($eventos->items() as $evento) {
                $evento->usuario_confirmado = $evento->usuarioConfirmoAsistencia(auth()->id());
            }
        }

        return $this->successResponse($eventos, 'Eventos obtenidos exitosamente');
    }

    public function sugerencias(Request $request)
    {
        $searchTerm = $request->input('q', '');
        $limit = $request->input('limit', 10);

        if (strlen(trim($searchTerm)) < 2) {
            return $this->successResponse([], 'No hay suficientes caracteres para buscar');
        }

        $sugerencias = $this->eventoService->getSugerencias($searchTerm, $limit);

        return $this->successResponse($sugerencias, 'Sugerencias de eventos obtenidas exitosamente');
    }

    /**
     * Ver un evento específico (solo si es futuro)
     */
    public function show(int $id)
    {
        try {
            $evento = $this->eventoService->findById($id);

            if (auth()->check()) {
                $evento->usuario_confirmado = $evento->usuarioConfirmoAsistencia(auth()->id());
            }

            return $this->successResponse($evento, 'Evento obtenido exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Evento no encontrado');
        }
    }

    /**
     * Dar like a un evento (solo si es futuro)
     */
    public function like(int $id)
    {
        try {
            $evento = Evento::query()
                ->where('fecha_evento', '>=', now()->startOfDay())
                ->findOrFail($id);

            $evento->increment('likes');

            return $this->successResponse(['likes' => $evento->likes], 'Like agregado');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Evento no encontrado o ya finalizado');
        }
    }

    /**
     * Obtener datos para el calendario (solo futuros)
     */
    public function calendario()
    {
        $calendario = $this->eventoService->getCalendarData();
        return response()->json($calendario);
    }

    /**
     * Confirmar asistencia a un evento (solo si es futuro)
     */
    public function confirmarAsistencia(int $id)
    {
        if (!auth()->check()) {
            return $this->errorResponse('Debes iniciar sesión para confirmar asistencia', null, 401);
        }

        try {
            $this->eventoService->confirmarAsistencia($id, auth()->id());
            $evento = $this->eventoService->findById($id);

            return $this->successResponse(
                ['total_asistentes' => $evento->total_asistentes],
                'Asistencia confirmada exitosamente'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Evento no encontrado o ya finalizado');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    /**
     * Cancelar asistencia a un evento
     */
    public function cancelarAsistencia(int $id)
    {
        if (!auth()->check()) {
            return $this->errorResponse('Debes iniciar sesión para cancelar asistencia', null, 401);
        }

        try {
            $this->eventoService->cancelarAsistencia($id, auth()->id());
            $evento = $this->eventoService->findById($id);

            return $this->successResponse(
                ['total_asistentes' => $evento->total_asistentes],
                'Asistencia cancelada'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Evento no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    /**
     * Filtrar eventos por tipo (solo futuros)
     */
    public function porTipo(string $tipo)
    {
        $filters = ['tipo' => $tipo];
        $eventos = $this->eventoService->getAll($filters, 100);

        if (auth()->check()) {
            foreach ($eventos->items() as $evento) {
                $evento->usuario_confirmado = $evento->usuarioConfirmoAsistencia(auth()->id());
            }
        }

        return $this->successResponse($eventos->items(), 'Eventos obtenidos exitosamente');
    }

    /**
     * Obtener solo eventos próximos
     */
    public function proximos()
    {
        $filters = ['proximos' => true];
        $eventos = $this->eventoService->getAll($filters, 100);

        if (auth()->check()) {
            foreach ($eventos->items() as $evento) {
                $evento->usuario_confirmado = $evento->usuarioConfirmoAsistencia(auth()->id());
            }
        }

        return $this->successResponse($eventos->items(), 'Próximos eventos obtenidos exitosamente');
    }

    /**
     * Obtener eventos del usuario autenticado
     */
    public function misEventos()
    {
        if (!auth()->check()) {
            return $this->errorResponse('Debes iniciar sesión', null, 401);
        }

        /** @var User|null $user */
        $user = auth()->user();

        if (!$user instanceof User) {
            return $this->errorResponse('Usuario no autenticado', null, 401);
        }

        $eventos = $user->eventosAsistencia()
            ->orderBy('fecha_evento', 'asc')
            ->get();

        foreach ($eventos as $evento) {
            $evento->usuario_confirmado = true;
        }

        return $this->successResponse($eventos, 'Tus eventos obtenidos exitosamente');
    }
}
