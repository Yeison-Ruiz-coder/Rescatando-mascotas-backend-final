<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\EventoService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

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
        $filters = $request->only(['desde', 'hasta', 'proximos', 'tipo', 'fundacion_id']);
        $perPage = $request->get('per_page', 15);

        $eventos = $this->eventoService->getAll($filters, $perPage);
        $estadisticas = $this->eventoService->getEstadisticas();

        return $this->successResponse([
            'data' => $eventos,
            'estadisticas' => $estadisticas
        ], 'Eventos obtenidos exitosamente');
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
            'tags' => 'nullable|array',  // Permite array
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            // Obtener todos los datos excepto imagen
            $data = $request->except('imagen');

            // Procesar tags: convertir array a JSON string
            if ($request->has('tags') && is_array($request->tags)) {
                $data['tags'] = json_encode($request->tags, JSON_UNESCAPED_UNICODE);
            }

            $evento = $this->runInTransaction(
                fn() => $this->eventoService->create(
                    $data,
                    $request->file('imagen')
                ),
                'Error al crear evento'
            );

            return $this->successResponse($evento, 'Evento creado exitosamente', 201);
        } catch (\Exception $e) {
            Log::error('Error al crear evento: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            return $this->errorResponse('Error al crear el evento', $e->getMessage(), 500);
        }
    }
    public function show(int $id)
    {
        try {
            $evento = $this->eventoService->findById($id);
            return $this->successResponse($evento, 'Evento obtenido exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Evento no encontrado');
        }
    }

    public function update(Request $request, int $id)
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
                fn() => $this->eventoService->update(
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

    public function calendario()
    {
        try {
            $calendario = $this->eventoService->getCalendarData();
            return $this->successResponse($calendario, 'Datos de calendario obtenidos');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener datos del calendario', $e->getMessage(), 500);
        }
    }

    public function proximos()
    {
        try {
            $eventos = $this->eventoService->getProximos(10);
            return $this->successResponse($eventos, 'Próximos eventos obtenidos');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener próximos eventos', $e->getMessage(), 500);
        }
    }
}
