<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Services\User\ComentarioUserService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ComentarioController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected ComentarioUserService $comentarioService;

    public function __construct(ComentarioUserService $comentarioService)
    {
        $this->comentarioService = $comentarioService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $comentarios = $this->comentarioService->getByUser($request->user()->id, $perPage);

        return $this->successResponse($comentarios, 'Comentarios obtenidos exitosamente');
    }

    public function update(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'contenido' => 'required|string|min:3|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $comentario = $this->runInTransaction(
                fn() => $this->comentarioService->updateComentario(
                    $request->user()->id,
                    $id,
                    ['contenido' => $request->contenido]
                ),
                'Error al actualizar comentario'
            );

            return $this->successResponse($comentario, 'Comentario actualizado');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Comentario no encontrado');
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->comentarioService->deleteComentario(request()->user()->id, $id),
                'Error al eliminar comentario'
            );

            return $this->successResponse(null, 'Comentario eliminado');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Comentario no encontrado');
        }
    }
}
