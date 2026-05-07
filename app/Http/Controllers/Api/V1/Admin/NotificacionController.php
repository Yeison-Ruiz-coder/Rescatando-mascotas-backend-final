<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NotificacionRequest;
use App\Http\Requests\Admin\NotificacionMasivoRequest;
use App\Http\Requests\Admin\NotificacionIndexRequest;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NotificacionController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected NotificacionService $notificacionService;

    public function __construct(NotificacionService $notificacionService)
    {
        $this->notificacionService = $notificacionService;
    }

    public function index(NotificacionIndexRequest $request)
    {
        $filters = $request->only(['user_id', 'creado_por_id', 'fecha_inicio', 'fecha_fin', 'search', 'leida', 'prioridad']);
        $perPage = $request->get('per_page', 15);

        $notificaciones = $this->notificacionService->getAll($filters, $perPage);
        $estadisticas = $this->notificacionService->getEstadisticas();

        return $this->successResponse([
            'data' => $notificaciones,
            'estadisticas' => $estadisticas
        ], 'Notificaciones obtenidas exitosamente');
    }

    public function show(int $id)
    {
        try {
            $notificacion = $this->notificacionService->findById($id);
            return $this->successResponse($notificacion, 'Notificación obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Notificación no encontrada');
        }
    }

    public function store(NotificacionRequest $request)
    {
        try {
            $notificacion = $this->runInTransaction(
                fn() => $this->notificacionService->create($request->validated()),
                'Error al crear notificación'
            );

            return $this->successResponse(
                $notificacion->load(['usuario', 'creadoPor']),
                'Notificación enviada exitosamente',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error al enviar la notificación', $e->getMessage(), 500);
        }
    }

    public function update(NotificacionRequest $request, int $id)
    {
        try {
            $notificacion = $this->runInTransaction(
                fn() => $this->notificacionService->update($id, $request->validated()),
                'Error al actualizar notificación'
            );

            return $this->successResponse($notificacion, 'Notificación actualizada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Notificación no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar la notificación', $e->getMessage(), 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->notificacionService->delete($id),
                'Error al eliminar notificación'
            );

            return $this->successResponse(null, 'Notificación eliminada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Notificación no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar la notificación', $e->getMessage(), 500);
        }
    }

    public function marcarComoLeida(int $id)
    {
        try {
            $notificacion = $this->runInTransaction(
                fn() => $this->notificacionService->marcarComoLeida($id),
                'Error al marcar notificación como leída'
            );

            return $this->successResponse($notificacion, 'Notificación marcada como leída');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Notificación no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al marcar la notificación', $e->getMessage(), 500);
        }
    }

    public function marcarTodasComoLeidas(int $userId)
    {
        try {
            $actualizadas = $this->runInTransaction(
                fn() => $this->notificacionService->marcarTodasComoLeidas($userId),
                'Error al marcar notificaciones como leídas'
            );

            return $this->successResponse(
                ['actualizadas' => $actualizadas],
                'Todas las notificaciones marcadas como leídas'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error al marcar las notificaciones', $e->getMessage(), 500);
        }
    }

    public function porUsuario(int $userId, Request $request)
    {
        try {
            $filters = $request->only(['solo_no_leidas', 'prioridad']);
            $perPage = $request->get('per_page', 15);

            $resultado = $this->notificacionService->getPorUsuario($userId, $filters, $perPage);

            return $this->successResponse($resultado, 'Notificaciones del usuario obtenidas exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Usuario no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener las notificaciones del usuario', $e->getMessage(), 500);
        }
    }

    public function enviarMasivo(NotificacionMasivoRequest $request)
    {
        try {
            $resultado = $this->runInTransaction(
                fn() => $this->notificacionService->enviarMasivo($request->validated()),
                'Error al enviar notificaciones masivas'
            );

            return $this->successResponse($resultado, "Notificaciones enviadas a {$resultado['total_enviadas']} usuarios", 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al enviar notificaciones masivas', $e->getMessage(), 500);
        }
    }

    public function estadisticas()
    {
        try {
            $estadisticas = $this->notificacionService->getEstadisticasAvanzadas();
            return $this->successResponse($estadisticas, 'Estadísticas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener estadísticas', $e->getMessage(), 500);
        }
    }
}
