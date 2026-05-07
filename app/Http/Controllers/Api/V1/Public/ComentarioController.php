<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Services\Public\ComentarioPublicService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComentarioController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected ComentarioPublicService $comentarioService;

    public function __construct(ComentarioPublicService $comentarioService)
    {
        $this->comentarioService = $comentarioService;
    }

    public function index(string $entidadTipo, int $entidadId)
    {
        try {
            $comentarios = $this->comentarioService->getComentarios($entidadTipo, $entidadId);
            return $this->successResponse($comentarios, 'Comentarios obtenidos exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contenido' => 'required|string|min:3|max:1000',
            'entidad_tipo' => 'required|in:mascota,evento,fundacion',
            'entidad_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $comentario = $this->runInTransaction(
                fn() => $this->comentarioService->crearComentario(
                    $request->only(['contenido', 'entidad_tipo', 'entidad_id']),
                    auth()->id()
                ),
                'Error al crear comentario'
            );

            return $this->successResponse(
                $comentario->load('usuario'),
                'Comentario creado exitosamente',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        }
    }
}
