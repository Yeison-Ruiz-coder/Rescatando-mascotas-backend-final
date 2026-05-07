<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UsuarioRequest;
use App\Http\Requests\Admin\CambiarEstadoUsuarioRequest;
use App\Services\UsuarioService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UsuarioController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected UsuarioService $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['tipo', 'estado', 'buscar']);
        $perPage = $request->get('per_page', 15);

        $usuarios = $this->usuarioService->getAll($filters, $perPage);

        return $this->successResponse($usuarios, 'Usuarios obtenidos exitosamente');
    }

    public function store(UsuarioRequest $request)
    {
        try {
            $usuario = $this->runInTransaction(
                fn() => $this->usuarioService->create(
                    $request->validated(),
                    $request->file('avatar')
                ),
                'Error al crear usuario'
            );

            return $this->successResponse($usuario, 'Usuario creado exitosamente', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear el usuario', $e->getMessage(), 500);
        }
    }

    public function show(int $id)
    {
        try {
            $usuario = $this->usuarioService->findById($id);
            return $this->successResponse($usuario, 'Usuario obtenido exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Usuario no encontrado');
        }
    }

    public function update(UsuarioRequest $request, int $id)
    {
        try {
            $usuario = $this->runInTransaction(
                fn() => $this->usuarioService->update(
                    $id,
                    $request->validated(),
                    $request->file('avatar')
                ),
                'Error al actualizar usuario'
            );

            return $this->successResponse($usuario, 'Usuario actualizado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Usuario no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar el usuario', $e->getMessage(), 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->usuarioService->delete($id),
                'Error al eliminar usuario'
            );

            return $this->successResponse(null, 'Usuario eliminado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Usuario no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function cambiarEstado(CambiarEstadoUsuarioRequest $request, int $id)
    {
        try {
            $usuario = $this->runInTransaction(
                fn() => $this->usuarioService->cambiarEstado($id, $request->estado),
                'Error al cambiar estado'
            );

            return $this->successResponse($usuario, 'Estado actualizado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Usuario no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al cambiar el estado', $e->getMessage(), 500);
        }
    }

    public function pendientesCount()
    {
        $count = $this->usuarioService->getPendientesCount();
        return $this->successResponse(['count' => $count], 'Conteo obtenido exitosamente');
    }

    public function verificarEmail(int $id)
    {
        try {
            $usuario = $this->runInTransaction(
                fn() => $this->usuarioService->verificarEmail($id),
                'Error al verificar email'
            );

            return $this->successResponse($usuario, 'Email verificado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Usuario no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al verificar el email', $e->getMessage(), 500);
        }
    }
}
