<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ComentarioRequest;
use App\Http\Requests\Admin\ComentarioMasivoRequest;
use App\Services\ComentarioService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ComentarioController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected ComentarioService $comentarioService;

    public function __construct(ComentarioService $comentarioService)
    {
        $this->comentarioService = $comentarioService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['user_id', 'fecha_inicio', 'fecha_fin', 'search']);
        $perPage = $request->get('per_page', 20);

        $comentarios = $this->comentarioService->getAll($filters, $perPage);
        $estadisticas = $this->comentarioService->getEstadisticas();

        return $this->successResponse([
            'data' => $comentarios,
            'estadisticas' => $estadisticas
        ], 'Comentarios obtenidos exitosamente');
    }

    public function show(int $id)
    {
        try {
            $comentario = $this->comentarioService->findById($id);
            return $this->successResponse($comentario, 'Comentario obtenido exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Comentario no encontrado');
        }
    }

    public function update(ComentarioRequest $request, int $id)
    {
        try {
            $comentario = $this->runInTransaction(
                fn() => $this->comentarioService->update($id, $request->validated()),
                'Error al actualizar comentario'
            );

            return $this->successResponse($comentario, 'Comentario actualizado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Comentario no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar el comentario', $e->getMessage(), 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->comentarioService->delete($id),
                'Error al eliminar comentario'
            );

            return $this->successResponse(null, 'Comentario eliminado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Comentario no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar el comentario', $e->getMessage(), 500);
        }
    }

    public function masivo(ComentarioMasivoRequest $request)
    {
        try {
            $eliminados = $this->runInTransaction(
                fn() => $this->comentarioService->deleteMultiple($request->comentarios),
                'Error en acción masiva'
            );

            return $this->successResponse(
                ['eliminados' => $eliminados],
                'Comentarios eliminados exitosamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error al realizar la acción masiva', $e->getMessage(), 500);
        }
    }
}
