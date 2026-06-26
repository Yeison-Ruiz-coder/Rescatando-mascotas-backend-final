<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Models\Adopcion;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdopcionController extends Controller
{
    use ApiResponses;

    /**
     * Listar adopciones de la entidad (fundación o veterinaria)
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            // Obtener la entidad (fundación o veterinaria)
            $entidad = null;
            if ($user->tipo === 'fundacion') {
                $entidad = $user->fundacion;
            } elseif ($user->tipo === 'veterinaria') {
                $entidad = $user->veterinaria;
            } else {
                return $this->errorResponse('Usuario no autorizado', null, 403);
            }

            if (!$entidad) {
                return $this->errorResponse('Entidad no encontrada', null, 404);
            }

            // Construir la consulta
            $query = Adopcion::with(['mascota', 'adoptante', 'fundacion'])
                ->where('fundacion_id', $entidad->id);

            // Filtros
            if ($request->has('estado') && $request->estado) {
                $query->where('estado', $request->estado);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->whereHas('mascota', function ($q) use ($search) {
                    $q->where('nombre_mascota', 'LIKE', "%{$search}%");
                })->orWhereHas('adoptante', function ($q) use ($search) {
                    $q->where('nombre', 'LIKE', "%{$search}%");
                });
            }

            // Ordenamiento
            $sortField = $request->get('sort', 'created_at');
            $sortOrder = $request->get('order', 'desc');
            $query->orderBy($sortField, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $adopciones = $query->paginate($perPage);

            return $this->successResponse($adopciones, 'Adopciones obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener adopciones', $e->getMessage(), 500);
        }
    }

    /**
     * Mostrar una adopción específica
     */
    public function show($id)
    {
        try {
            $user = Auth::user();

            // Obtener la entidad
            $entidad = null;
            if ($user->tipo === 'fundacion') {
                $entidad = $user->fundacion;
            } elseif ($user->tipo === 'veterinaria') {
                $entidad = $user->veterinaria;
            } else {
                return $this->errorResponse('Usuario no autorizado', null, 403);
            }

            if (!$entidad) {
                return $this->errorResponse('Entidad no encontrada', null, 404);
            }

            // Buscar la adopción
            $adopcion = Adopcion::with(['mascota', 'adoptante', 'fundacion', 'seguimientos'])
                ->where('fundacion_id', $entidad->id)
                ->find($id);

            if (!$adopcion) {
                return $this->notFoundResponse('Adopción no encontrada');
            }

            return $this->successResponse($adopcion, 'Adopción obtenida exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener adopción', $e->getMessage(), 500);
        }
    }
}
