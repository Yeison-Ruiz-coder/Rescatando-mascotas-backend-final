<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $user = $request->user();

        // Si es fundación
        if ($user->tipo === 'fundacion') {
            $fundacion = $user->fundacion;

            if (!$fundacion) {
                return $this->errorResponse('Perfil de fundación no encontrado', null, 404);
            }

            // Obtener IDs de mascotas de la fundación
            $mascotasIds = $fundacion->mascotas()->pluck('id');

            // Obtener usuarios que tienen suscripciones a esas mascotas
            $usuarios = User::whereHas('suscripciones', function($query) use ($mascotasIds) {
                $query->whereIn('mascota_id', $mascotasIds);
            })->get();

            // Si no hay usuarios con suscripciones, buscar donaciones
            if ($usuarios->isEmpty()) {
                $usuarios = User::whereHas('donaciones', function($query) use ($fundacion) {
                    $query->where('fundacion_id', $fundacion->id);
                })->get();
            }

            // Si aún no hay, devolver usuarios registrados recientes (limitado)
            if ($usuarios->isEmpty()) {
                $usuarios = User::where('tipo', 'usuario')
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();
            }

            return $this->successResponse($usuarios, 'Usuarios obtenidos exitosamente');
        }

        // Si es veterinaria
        if ($user->tipo === 'veterinaria') {
            $veterinaria = $user->veterinaria;

            if (!$veterinaria) {
                return $this->errorResponse('Perfil de veterinaria no encontrado', null, 404);
            }

            // Obtener usuarios que han tenido citas o pacientes
            $usuarios = User::whereHas('mascotas', function($query) use ($veterinaria) {
                $query->where('veterinaria_id', $veterinaria->id);
            })->get();

            if ($usuarios->isEmpty()) {
                $usuarios = User::where('tipo', 'usuario')
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();
            }

            return $this->successResponse($usuarios, 'Usuarios obtenidos exitosamente');
        }

        return $this->errorResponse('No autorizado', null, 403);
    }
}
